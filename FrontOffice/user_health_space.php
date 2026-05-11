<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$sessionUid = $loggedIn ? (int) ($_SESSION['user_id'] ?? 0) : 0;
$guestHealthSpace = !$loggedIn || $sessionUid < 1;

$healthNotice = isset($_GET['notice']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['notice']) : '';

if ($guestHealthSpace) {
    $user = null;
    $profil = null;
    $suivis = [];
    $suivisFiltered = [];
    $suivisPaged = [];
    $page = 1;
    $limit = 3;
    $offset = 0;
    $total = 0;
    $totalPages = 1;
    $noSuivisEver = true;
    $emptyAfterFilter = false;
    $hasFilter = false;
    $date = '';
    $sort = '';
} else {
    require_once __DIR__ . '/../config/Database.php';
$pdo = Database::getConnection();

/** Colonne PK de `utilisateur` (schéma ancien = id_utilisateur, schéma récent = id). */
$healthSpacePkStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
);
$healthSpacePkStmt->execute(['table' => 'utilisateur', 'column' => 'id']);
$utilisateurPk = ((int) $healthSpacePkStmt->fetchColumn() > 0)
    ? 'id'
    : 'id_utilisateur';
if ($utilisateurPk === 'id_utilisateur') {
    $healthSpacePkStmt->execute(['table' => 'utilisateur', 'column' => 'id_utilisateur']);
    if ((int) $healthSpacePkStmt->fetchColumn() === 0) {
        $utilisateurPk = 'id';
    }
}

/** @var array<string, mixed>|null $user */
$user = null;
if ($utilisateurPk === 'id' || $utilisateurPk === 'id_utilisateur') {
    $sqlUser = sprintf(
        'SELECT %s AS uid, prenom, nom, email FROM utilisateur WHERE %s = :id LIMIT 1',
        $utilisateurPk,
        $utilisateurPk
    );
    $st = $pdo->prepare($sqlUser);
    $st->execute(['id' => $sessionUid]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (is_array($row) && isset($row['uid'])) {
        $user = [
            'id' => (int) $row['uid'],
            'prenom' => (string) ($row['prenom'] ?? ''),
            'nom' => (string) ($row['nom'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
        ];
    }
}

if ($user === null) {
    http_response_code(404);
    exit('Utilisateur introuvable.');
}

$idUtilisateur = $user['id'];

$stProfil = $pdo->prepare('SELECT * FROM profil_sante WHERE id_utilisateur = :u LIMIT 1');
$stProfil->execute(['u' => $idUtilisateur]);
/** @var array<string, mixed>|null $profil */
$profil = $stProfil->fetch(PDO::FETCH_ASSOC) ?: null;

/** @var list<array<string, mixed>> $suivis */
$suivis = [];
if ($profil !== null) {
    foreach (['allergenes', 'carences', 'maladies'] as $jsonKey) {
        $raw = $profil[$jsonKey] ?? '';
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $profil[$jsonKey] = is_array($decoded) ? $decoded : [];
    }
    $stSuivi = $pdo->prepare(
        'SELECT * FROM suivi_journalier WHERE id_profil_sante = :pid ORDER BY date_jour DESC'
    );
    $stSuivi->execute(['pid' => (int) $profil['id']]);
    $fetched = $stSuivi->fetchAll(PDO::FETCH_ASSOC);
    $suivis = is_array($fetched) ? $fetched : [];
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 3;
$offset = ($page - 1) * $limit;

$suivisFiltered = $suivis;
$date = isset($_GET['date']) ? trim((string) $_GET['date']) : '';
$hasFilter = $date !== '';

if ($hasFilter) {
    $suivisFiltered = array_values(
        array_filter(
            $suivisFiltered,
            static function (array $s) use ($date): bool {
                return (string) ($s['date_jour'] ?? '') === $date;
            }
        )
    );
}

$sort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : '';

if ($sort === '') {
    usort(
        $suivisFiltered,
        static function (array $a, array $b): int {
            $ta = strtotime((string) ($a['date_jour'] ?? '1970-01-01')) ?: 0;
            $tb = strtotime((string) ($b['date_jour'] ?? '1970-01-01')) ?: 0;
            return $tb <=> $ta;
        }
    );
} else {
    $map = [
        'poids_asc' => static fn(array $a, array $b): int => ((float) ($a['poids'] ?? 0)) <=> ((float) ($b['poids'] ?? 0)),
        'poids_desc' => static fn(array $a, array $b): int => ((float) ($b['poids'] ?? 0)) <=> ((float) ($a['poids'] ?? 0)),
        'calories_asc' => static fn(array $a, array $b): int => ((float) ($a['calories'] ?? 0)) <=> ((float) ($b['calories'] ?? 0)),
        'calories_desc' => static fn(array $a, array $b): int => ((float) ($b['calories'] ?? 0)) <=> ((float) ($a['calories'] ?? 0)),
        'sommeil_asc' => static fn(array $a, array $b): int => ((float) ($a['sommeil_heures'] ?? 0)) <=> ((float) ($b['sommeil_heures'] ?? 0)),
        'sommeil_desc' => static fn(array $a, array $b): int => ((float) ($b['sommeil_heures'] ?? 0)) <=> ((float) ($a['sommeil_heures'] ?? 0)),
        'pas_asc' => static fn(array $a, array $b): int => ((float) ($a['nbr_pas'] ?? 0)) <=> ((float) ($b['nbr_pas'] ?? 0)),
        'pas_desc' => static fn(array $a, array $b): int => ((float) ($b['nbr_pas'] ?? 0)) <=> ((float) ($a['nbr_pas'] ?? 0)),
    ];
    if (isset($map[$sort])) {
        usort($suivisFiltered, $map[$sort]);
    }
}

$total = count($suivisFiltered);
$totalPages = max(1, (int) ceil($total / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}
$suivisPaged = array_slice($suivisFiltered, $offset, $limit);

$noSuivisEver = $suivis === [];
$emptyAfterFilter = !$noSuivisEver && $suivisFiltered === [];

}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Mon espace santé</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-original-views.css">
    <style>
        .health-page {
            max-width: min(1180px, 100%);
            width: 100%;
            margin: 0 auto;
            padding: 2rem clamp(1rem, 3vw, 2rem) 3.75rem;
            box-sizing: border-box;
            background: linear-gradient(180deg, #eef8f1 0%, #f4fbf7 35%, #f7fcf9 100%);
            border-radius: 0;
            min-height: calc(100vh - 120px);
        }
        .health-page .page-header { margin-bottom: 1.75rem; text-align: center; }
        .health-page .page-header h1 {
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--hb-forest, #2C7E34);
            margin: 0 0 0.4rem;
        }
        .health-page .page-header p {
            margin: 0;
            color: #6b7280;
            font-weight: 400;
            font-size: 0.95rem;
        }
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: clamp(14px, 2vw, 22px);
            margin-bottom: 1.75rem;
        }
        @media (max-width: 900px) {
            .user-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 480px) {
            .user-info-grid { grid-template-columns: 1fr; }
        }
        .health-page .info-card {
            display: flex;
            align-items: center;
            gap: clamp(14px, 2vw, 20px);
            padding: clamp(20px, 2.5vw, 28px) clamp(22px, 3vw, 30px);
            min-height: 96px;
            background: #fff;
            border: 1px solid rgba(227, 235, 230, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 28px rgba(15, 42, 28, 0.07), 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .health-page .info-card i {
            color: var(--hb-forest, #2C7E34);
            font-size: clamp(1.4rem, 2.5vw, 1.65rem);
            flex-shrink: 0;
        }
        .health-page .info-card span {
            display: block;
            font-size: clamp(0.8rem, 1.2vw, 0.88rem);
            color: #9ca3af;
            font-weight: 500;
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }
        .health-page .info-card strong {
            font-size: clamp(1rem, 1.6vw, 1.12rem);
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
            word-break: break-word;
        }
        .health-page hr { border: none; border-top: 1px solid var(--hb-card-border, #e3ebe6); margin: 1.5rem 0; }
        .health-page .card {
            background: #fff;
            border: 1px solid rgba(227, 235, 230, 0.95);
            border-radius: 20px;
            padding: clamp(1.75rem, 3vw, 2.25rem) clamp(1.75rem, 3.5vw, 2.5rem);
            margin-bottom: 1.65rem;
            box-shadow: 0 10px 36px rgba(15, 42, 28, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04);
        }
        .health-page .card h2 {
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--hb-forest, #2C7E34);
            margin: 0 0 1.25rem;
            text-align: center;
        }
        .profil-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
        }
        @media (max-width: 1100px) {
            .profil-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .profil-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .profil-item {
            padding: 18px 14px;
            background: #f8faf9;
            border-radius: 14px;
            border: 1px solid #e8ecf0;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .profil-item .icon { color: var(--hb-forest, #2C7E34); margin-bottom: 10px; display: block; font-size: 1.22rem; }
        .profil-item h4 { margin: 0 0 4px; font-size: 0.8rem; font-weight: 500; color: #5c6b62; text-transform: uppercase; letter-spacing: 0.03em; }
        .profil-item p { margin: 0; font-weight: 400; font-size: 0.95rem; color: #1a1a1a; }
        .profil-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 1.15rem;
            justify-content: center;
            align-items: center;
        }
        .profil-actions form { display: inline; margin: 0; padding: 0; }
        .btn-edit_profil, .btn-add, .btn-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.92rem;
            text-decoration: none;
            background: var(--hb-forest, #2C7E34);
            color: #fff !important;
            border: none;
            cursor: pointer;
        }
        .btn-edit_profil:hover, .btn-add:hover, .btn-filter:hover { filter: brightness(1.05); }
        .btn-delete_profil, .btn-delete {
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
        }
        .btn-edit {
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            background: #ecfdf3;
            color: var(--hb-forest, #2C7E34);
            text-decoration: none;
            border: 1px solid #bbf7d0;
            font-size: 0.88rem;
        }
        .btn-edit:hover { filter: brightness(0.98); }
        .suivi-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .suivi-toolbar .search-suivi-wrap {
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 260px;
        }
        .search-suivi-box {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }
        .search-suivi-box input[type="date"], .search-suivi-box select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #e3ebe6;
            font-family: inherit;
        }
        .btn-cancel-green {
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            background: #fff;
            color: #2C7E34;
            border: 2px solid #2C7E34;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
        }
        .btn-cancel-green:hover { background: #f0fdf4; }
        .health-flash {
            max-width: min(1180px, 100%);
            margin: 0 auto 1rem;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
        }
        .health-flash--ok {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .health-flash--err {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .suivi-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .suivi-container {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }
        }
        @media (max-width: 600px) {
            .suivi-container { grid-template-columns: 1fr; }
        }
        .card-suivi {
            border: 1px solid #e8ecef;
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 6px 22px rgba(15, 42, 28, 0.07), 0 2px 8px rgba(0,0,0,0.04);
        }
        .card-suivi-strip {
            height: 12px;
            background: var(--hb-forest, #2C7E34);
        }
        .card-suivi-head {
            text-align: center;
            padding: 14px 16px 12px;
            background: #fff;
        }
        .card-suivi-head .fa-calendar {
            color: #2C7E34;
            margin-right: 8px;
        }
        .card-suivi-date {
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            font-weight: 700;
            font-size: 1.05rem;
            color: #2C7E34;
        }
        .card-suivi-body {
            padding: 4px 0 8px;
        }
        .suivi-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 11px 20px;
            border-bottom: 1px solid #f0f4f2;
        }
        .suivi-row:last-child { border-bottom: none; }
        .suivi-row-left {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #374151;
            font-weight: 500;
            font-size: 0.92rem;
            min-width: 0;
        }
        .suivi-row-left i {
            width: 1.25rem;
            text-align: center;
            flex-shrink: 0;
        }
        .suivi-row-val {
            font-weight: 700;
            color: #111827;
            font-size: 0.95rem;
            text-align: right;
            flex-shrink: 0;
        }
        .card-suivi-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            padding: 16px 18px;
            border-top: 1px solid #f0f4f2;
            background: #fafdfb;
        }
        /* Conseil — rond, idea.png / idea_hover.png (taille alignée sur Modifier / Supprimer dans le pied de carte) */
        .btn-conseil {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 2px solid #43a047;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 6px 18px rgba(19, 30, 23, 0.18);
            cursor: pointer;
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            transition: box-shadow 0.2s ease, transform 0.15s ease;
            flex-shrink: 0;
        }
        .btn-conseil:hover {
            box-shadow: 0 8px 22px rgba(19, 30, 23, 0.22);
        }
        .btn-conseil:active {
            transform: scale(0.96);
        }
        .btn-conseil__iconwrap {
            flex-shrink: 0;
            background-image: url('images/idea.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }
        .btn-conseil:hover .btn-conseil__iconwrap {
            background-image: url('images/idea_hover.png');
        }
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-top: 1rem;
            font-family: var(--hb-font-main, "Poppins", sans-serif);
        }
        .btn-page {
            padding: 10px 18px;
            min-width: 48px;
            border-radius: 10px;
            border: none;
            background: var(--hb-forest, #2C7E34);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            line-height: 1;
            transition: background 0.2s ease, transform 0.12s ease;
        }
        .btn-page:hover:not(:disabled) {
            background: #256d2d;
        }
        .btn-page:active:not(:disabled) {
            transform: scale(0.97);
        }
        .btn-page:disabled {
            opacity: 0.42;
            cursor: not-allowed;
        }
        .popup {
            display: none; position: fixed; inset: 0; z-index: 100000;
            background: rgba(15, 42, 28, 0.45);
            align-items: center; justify-content: center; padding: 1rem;
            font-family: var(--hb-font-main, "Poppins", sans-serif);
        }
        .popup-box {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #ddd;
            border-top: 8px solid var(--hb-forest, #2C7E34);
            padding: 26px 28px 28px;
            max-width: min(480px, 100%);
            width: 100%;
            box-shadow: 0 12px 40px rgba(19, 42, 28, 0.14);
            box-sizing: border-box;
        }
        .popup-box h3 {
            margin: 0 0 20px;
            font-weight: 700;
            color: var(--hb-forest, #2C7E34);
            text-align: center;
            font-size: 1.35rem;
            letter-spacing: -0.02em;
        }
        #popupContent {
            margin: 0;
        }
        .conseil-box {
            color: #333;
            text-align: left;
            font-size: 0.95rem;
            font-weight: 400;
            line-height: 1.65;
        }
        .conseil-box br {
            display: block;
            margin-bottom: 14px;
            content: "";
        }
        .btn-popup-fermer {
            display: block;
            width: 100%;
            margin-top: 24px;
            padding: 14px 20px;
            border: none;
            border-radius: 50px;
            background: var(--hb-forest, #2C7E34);
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            cursor: pointer;
            box-sizing: border-box;
            transition: background 0.2s ease, transform 0.12s ease;
        }
        .btn-popup-fermer:hover {
            background: #256d2d;
        }
        .btn-popup-fermer:active {
            transform: scale(0.99);
        }
        .health-muted { color: #5c6b62; font-size: 0.92rem; margin: 0 0 12px; line-height: 1.5; }
        .health-empty { text-align: center; color: #b45309; font-weight: 500; padding: 12px; grid-column: 1 / -1; }
        .suivi-actions-inline {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .card-suivi-foot .suivi-actions-inline form {
            display: inline-flex;
            align-items: center;
            margin: 0;
        }
        /* Hauteur commune des trois actions */
        .card-suivi-foot .btn-edit,
        .card-suivi-foot .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .card-suivi-foot .btn-conseil {
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
        }
        .card-suivi-foot .btn-conseil__iconwrap {
            width: 26px;
            height: 26px;
        }
        .points-card {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.35rem;
            padding: clamp(1rem, 2vw, 1.35rem) clamp(1.15rem, 2.5vw, 1.5rem);
            background: linear-gradient(135deg, #ecfdf3 0%, #f0fdf4 50%, #fff 100%);
            border: 1px solid #bbf7d0;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(44, 126, 52, 0.08);
        }
        .points-card-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .points-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #2C7E34;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .points-card-left h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            font-weight: 700;
            color: #166534;
        }
        .points-card-left p {
            margin: 0;
            font-size: 0.95rem;
            color: #374151;
        }
        .points-card-left strong {
            color: #2C7E34;
            font-size: 1.15rem;
        }
        /* Même style que le bouton « Analyser » (List-Recette.php / CaloryEye) */
        .caloryeye-analyse-btn {
            min-width: min(100%, 280px);
            min-height: 52px;
            border: 2px solid #43a047;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(19, 30, 23, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            color: inherit;
            text-decoration: none;
            box-sizing: border-box;
        }
        .caloryeye-analyse-btn:hover {
            filter: brightness(0.98);
        }
        .caloryeye-analyse-btn__label {
            background: linear-gradient(90deg, #e53935 0%, #fb8c00 52%, #43a047 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .caloryeye-analyse-btn__icon {
            width: 22px;
            height: 22px;
            object-fit: contain;
            display: block;
            flex-shrink: 0;
        }
        @media (max-width: 600px) {
            .points-card {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            .points-card-left {
                flex-direction: column;
            }
            .points-card .caloryeye-analyse-btn {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>
<body>

<?php
$nav_active = 'sante';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="health-page">

<?php if ($guestHealthSpace): ?>
<div class="page-header">
    <h1>Espace santé</h1>
    <p>Suivi du profil et des habitudes quotidiennes. Connectez-vous pour accéder à votre tableau de bord personnel.</p>
</div>

<?php else: ?>

<?php if ($healthNotice === 'suivi_saved'): ?>
<div class="health-flash health-flash--ok">Suivi journalier enregistré pour aujourd’hui.</div>

<?php elseif ($healthNotice === 'suivi_db_error'): ?>
<div class="health-flash health-flash--err">Impossible d’enregistrer le suivi. Vérifiez les champs ou la base de données.</div>

<?php elseif ($healthNotice === 'ia_plus'): ?>
<div class="health-flash health-flash--ok">Analyse IA terminée : bravo, vous avez gagné +10 points santé.</div>

<?php elseif ($healthNotice === 'ia_minus'): ?>
<div class="health-flash health-flash--err">Analyse IA terminée : votre suivi n’est pas assez aligné. -10 points santé.</div>

<?php elseif ($healthNotice === 'ia_done'): ?>
<div class="health-flash health-flash--err">L’analyse IA a déjà été effectuée pour aujourd’hui.</div>

<?php elseif ($healthNotice === 'ia_no_suivi'): ?>
<div class="health-flash health-flash--err">Aucun suivi journalier pour aujourd’hui. Enregistrez votre suivi avant de lancer l’analyse IA.</div>

<?php elseif ($healthNotice === 'ia_profile'): ?>
<div class="health-flash health-flash--err">Ce profil santé ne correspond pas à votre compte ou est introuvable.</div>

<?php elseif ($healthNotice === 'ia_api_key'): ?>
<div class="health-flash health-flash--err">Clé API OpenAI manquante. Choisissez une option : (1) créez le fichier <code style="font-size:0.9em;">config/openai.key</code> et collez votre clé <strong>seule</strong> sur la 1<sup>re</sup> ligne (voir <code style="font-size:0.9em;">config/openai.key.example</code>) ; ou (2) dans <code style="font-size:0.9em;">config/secrets.php</code>, remplacez les guillemets vides par votre clé dans <code style="font-size:0.9em;">OPENAI_API_KEY</code> ; ou (3) variable d’environnement <code style="font-size:0.9em;">OPENAI_API_KEY</code>.</div>

<?php elseif ($healthNotice === 'ia_ai_fail'): ?>
<div class="health-flash health-flash--err">L’API IA n’a pas renvoyé une réponse valide. Vérifiez la clé, votre connexion, ou réessayez plus tard.</div>

<?php elseif ($healthNotice === 'ia_error'): ?>
<div class="health-flash health-flash--err">Impossible de lancer l’analyse IA pour le moment.</div>
<?php endif; ?>

<div class="page-header">
    <h1>Espace santé de l&rsquo;utilisateur</h1>
    <p>Suivi complet du profil et des habitudes quotidiennes.</p>
</div>

<div class="user-info-grid">
    <div class="info-card">
        <i class="fas fa-id-card"></i>
        <div>
            <span>ID</span>
            <strong><?php echo htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>
    <div class="info-card">
        <i class="fas fa-user"></i>
        <div>
            <span>Prénom</span>
            <strong><?php echo htmlspecialchars($user['prenom'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>
    <div class="info-card">
        <i class="fas fa-user-tag"></i>
        <div>
            <span>Nom</span>
            <strong><?php echo htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>
    <div class="info-card">
        <i class="fas fa-envelope"></i>
        <div>
            <span>Email</span>
            <strong><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>
</div>

<hr>

<div class="card">
    <h2>Profil santé</h2>

    <?php if ($profil !== null): ?>

        <div class="profil-grid">
            <div class="profil-item">
                <i class="fas fa-ruler-vertical icon"></i>
                <h4>Taille</h4>
                <p><?php echo htmlspecialchars((string) ($profil['taille'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?> cm</p>
            </div>
            <div class="profil-item">
                <i class="fas fa-weight-scale icon"></i>
                <h4>Poids</h4>
                <p><?php echo htmlspecialchars((string) ($profil['poids_actuel'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?> kg</p>
            </div>
            <div class="profil-item">
                <i class="fas fa-bullseye icon"></i>
                <h4>Objectif</h4>
                <p><?php echo htmlspecialchars((string) ($profil['objectif'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="profil-item">
                <i class="fas fa-utensils icon"></i>
                <h4>Allergènes</h4>
                <p><?php echo !empty($profil['allergenes']) ? htmlspecialchars(implode(', ', $profil['allergenes']), ENT_QUOTES, 'UTF-8') : 'Aucun'; ?></p>
            </div>
            <div class="profil-item">
                <i class="fas fa-pills icon"></i>
                <h4>Carences</h4>
                <p><?php echo !empty($profil['carences']) ? htmlspecialchars(implode(', ', $profil['carences']), ENT_QUOTES, 'UTF-8') : 'Aucune'; ?></p>
            </div>
            <div class="profil-item">
                <i class="fas fa-heart-pulse icon"></i>
                <h4>Maladies</h4>
                <p><?php echo !empty($profil['maladies']) ? htmlspecialchars(implode(', ', $profil['maladies']), ENT_QUOTES, 'UTF-8') : 'Aucune'; ?></p>
            </div>
        </div>
        <div class="points-card">
            <div class="points-card-left">
                <div class="points-card-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <h3>Mes points santé</h3>
                    <p>
                        <strong><?php echo htmlspecialchars((string) ($profil['points'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></strong>
                        points
                    </p>
                </div>
            </div>

            <a href="analyse_gamification.php?id_profil_sante=<?php echo (int) $profil['id']; ?>" class="caloryeye-analyse-btn">
                <img src="images/analyse.png" alt="" class="caloryeye-analyse-btn__icon" width="22" height="22">
                <span class="caloryeye-analyse-btn__label">Lancer l’analyse IA du jour</span>
            </a>
        </div>
        <div class="profil-actions">
            <a href="edit.php" class="btn-edit">Modifier le profil</a>
            <form method="post" action="delete_profil_sante.php" onsubmit="return confirm('Supprimer votre profil santé ? Cette action est difficile à annuler.');">
                <button type="submit" class="btn-delete_profil">Supprimer le profil</button>
            </form>
        </div>

    <?php else: ?>

        <p class="health-muted" style="text-align:center;">Aucun profil santé pour votre compte.</p>
        <div class="profil-actions">
            <a href="create.php" class="btn-add">Créer profil santé</a>
        </div>

    <?php endif; ?>

</div>

<hr>

<div class="card">
    <h2>Suivis journaliers</h2>

    <?php if ($profil === null): ?>
        <p class="health-muted" style="text-align:center;">Créez d’abord un profil santé pour enregistrer des suivis journaliers.</p>
    <?php else: ?>

        <div class="suivi-toolbar">
            <a href="createSuivi.php" class="btn-add">+ Ajouter suivis journaliers</a>
            <div class="search-suivi-wrap">
                <form id="searchForm" class="search-suivi" method="get" action="user_health_space.php">
                    <div class="search-suivi-box">
                        <i class="fas fa-calendar" style="color:var(--hb-forest,#2C7E34);"></i>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-filter">Rechercher</button>

                        <select name="sort">
                            <option value="">Tri par défaut (date)</option>
                            <option value="poids_asc" <?php echo $sort === 'poids_asc' ? 'selected' : ''; ?>>Poids ↑</option>
                            <option value="poids_desc" <?php echo $sort === 'poids_desc' ? 'selected' : ''; ?>>Poids ↓</option>
                            <option value="calories_asc" <?php echo $sort === 'calories_asc' ? 'selected' : ''; ?>>Calories ↑</option>
                            <option value="calories_desc" <?php echo $sort === 'calories_desc' ? 'selected' : ''; ?>>Calories ↓</option>
                            <option value="sommeil_asc" <?php echo $sort === 'sommeil_asc' ? 'selected' : ''; ?>>Sommeil ↑</option>
                            <option value="sommeil_desc" <?php echo $sort === 'sommeil_desc' ? 'selected' : ''; ?>>Sommeil ↓</option>
                            <option value="pas_asc" <?php echo $sort === 'pas_asc' ? 'selected' : ''; ?>>Pas ↑</option>
                            <option value="pas_desc" <?php echo $sort === 'pas_desc' ? 'selected' : ''; ?>>Pas ↓</option>
                        </select>

                        <button type="submit" class="btn-filter">Filtrer</button>
                        <button type="button" id="resetFilter" class="btn-cancel-green">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

            <div class="suivi-container" id="suiviContainer">
                <?php if ($suivisPaged !== []): ?>
                    <?php foreach ($suivisPaged as $suivi): ?>
                        <?php
                        $dj = (string) ($suivi['date_jour'] ?? '');
                        $dateLabel = $dj !== ''
                            ? date('d / m / Y', strtotime($dj) ?: time())
                            : '—';
                        $poidsRaw = $suivi['poids'] ?? null;
                        $poidsTxt = ($poidsRaw !== null && $poidsRaw !== '')
                            ? number_format((float) $poidsRaw, 2, '.', '') . ' kg'
                            : '—';
                        $calTxt = ($suivi['calories'] ?? '') !== '' && $suivi['calories'] !== null
                            ? htmlspecialchars((string) $suivi['calories'], ENT_QUOTES, 'UTF-8') . ' kcal'
                            : '—';
                        $somRaw = $suivi['sommeil_heures'] ?? null;
                        $somTxt = ($somRaw !== null && $somRaw !== '')
                            ? number_format((float) $somRaw, 2, '.', '') . ' h'
                            : '—';
                        $pasTxt = htmlspecialchars(
                            number_format((float) ($suivi['nbr_pas'] ?? 0), 0, ',', ' '),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        $nSport = (int) ($suivi['nbr_activites_sport'] ?? 0);
                        if ($nSport === 0) {
                            $sportTxt = '0 séance';
                        } elseif ($nSport === 1) {
                            $sportTxt = '1 séance';
                        } else {
                            $sportTxt = $nSport . ' séances';
                        }
                        $hydRaw = (string) ($suivi['hydratation_litre'] ?? '');
                        $hydTxt = $hydRaw !== ''
                            ? htmlspecialchars($hydRaw, ENT_QUOTES, 'UTF-8')
                            : '—';
                        ?>
                        <div class="card-suivi">
                            <div class="card-suivi-strip" aria-hidden="true"></div>
                            <div class="card-suivi-head">
                                <i class="fas fa-calendar"></i>
                                <span class="card-suivi-date"><?php echo htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="card-suivi-body">
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-weight-scale" style="color:#2C7E34;"></i> Poids</span>
                                    <strong class="suivi-row-val"><?php echo htmlspecialchars($poidsTxt, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-fire" style="color:#ea580c;"></i> Calories</span>
                                    <strong class="suivi-row-val"><?php echo $calTxt; ?></strong>
                                </div>
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-moon" style="color:#7c3aed;"></i> Sommeil</span>
                                    <strong class="suivi-row-val"><?php echo htmlspecialchars($somTxt, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-shoe-prints" style="color:#2563eb;"></i> Pas</span>
                                    <strong class="suivi-row-val"><?php echo $pasTxt; ?></strong>
                                </div>
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-running" style="color:#2C7E34;"></i> Sport</span>
                                    <strong class="suivi-row-val"><?php echo htmlspecialchars($sportTxt, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <div class="suivi-row">
                                    <span class="suivi-row-left"><i class="fas fa-tint" style="color:#0891b2;"></i> Hydratation</span>
                                    <strong class="suivi-row-val"><?php echo $hydTxt; ?></strong>
                                </div>
                            </div>
                            <div class="card-suivi-foot">
                                <div class="suivi-actions-inline">
                                    <button type="button" class="btn-conseil" onclick="afficherConseil(<?php echo (int) ($suivi['id'] ?? 0); ?>)" title="Conseil du jour" aria-label="Conseil du jour">
                                        <span class="btn-conseil__iconwrap" aria-hidden="true"></span>
                                    </button>
                                    <a href="editSuivi.php?id=<?php echo (int) ($suivi['id'] ?? 0); ?>" class="btn-edit">Modifier</a>
                                    <form method="post" action="delete_suivi_journalier.php" onsubmit="return confirm('Supprimer ce suivi ?');">
                                        <input type="hidden" name="id" value="<?php echo (int) ($suivi['id'] ?? 0); ?>">
                                        <button type="submit" class="btn-delete">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="health-empty">
                        <?php
                        if ($noSuivisEver) {
                            echo 'Aucun suivi journalier enregistré.';
                        } elseif ($emptyAfterFilter || $hasFilter || $sort !== '') {
                            echo 'Aucun résultat pour ces critères.';
                        } else {
                            echo 'Aucun suivi à afficher sur cette page.';
                        }
                        ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="pagination" id="pagination">
                <button type="button" class="btn-page" onclick="loadPage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>&lt;</button>
                <span class="page-info">Page <?php echo (int) $page; ?> / <?php echo (int) $totalPages; ?></span>
                <button type="button" class="btn-page" onclick="loadPage(<?php echo $page + 1; ?>)" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>&gt;</button>
            </div>

    <?php endif; ?>

</div>

<div id="popup" class="popup">
    <div class="popup-box">
        <h3>Conseil du jour</h3>
        <div id="popupContent"></div>
        <button type="button" class="btn-popup-fermer" onclick="fermerPopup()">Fermer</button>
    </div>
</div>

<script>
function afficherConseil(id) {
    fetch('get_conseil_suivi.php?id=' + encodeURIComponent(id))
        .then(function(res) {
            return res.text().then(function(text) {
                try {
                    return { ok: res.ok, status: res.status, data: JSON.parse(text) };
                } catch (e) {
                    console.error('Réponse conseil (non JSON):', text.slice(0, 400));
                    throw e;
                }
            });
        })
        .then(function(r) {
            var data = r.data;
            if (data.error) {
                alert(data.error);
                return;
            }
            if (!r.ok) {
                alert('Erreur serveur (' + r.status + ')');
                return;
            }
            document.getElementById('popupContent').innerHTML =
                '<div class="conseil-box">' + (data.conseil_ai || '') + '</div>';
            document.getElementById('popup').style.display = 'flex';
        })
        .catch(function() { alert('Erreur serveur'); });
}
function fermerPopup() {
    document.getElementById('popup').style.display = 'none';
}

function loadPage(page) {
    if (page < 1) return;
    var url = new URL(window.location.href);
    url.searchParams.set('page', page);
    fetch(url.toString())
        .then(function(res) { return res.text(); })
        .then(function(html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var sc = doc.getElementById('suiviContainer');
            var pg = doc.getElementById('pagination');
            if (sc) document.getElementById('suiviContainer').innerHTML = sc.innerHTML;
            if (pg) document.getElementById('pagination').innerHTML = pg.innerHTML;
            window.history.pushState({}, '', url);
        })
        .catch(function() { alert('Erreur chargement'); });
}

var searchForm = document.getElementById('searchForm');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var url = new URL(window.location.href.split('?')[0], window.location.origin);
        if (!url.pathname.endsWith('user_health_space.php')) {
            url = new URL(window.location.href);
        } else {
            url = new URL('user_health_space.php', window.location.href);
        }
        var fd = new FormData(searchForm);
        fd.forEach(function(value, key) {
            if (value !== '') url.searchParams.set(key, value);
            else url.searchParams.delete(key);
        });
        url.searchParams.set('page', '1');
        fetch(url.toString())
            .then(function(res) { return res.text(); })
            .then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var sc = doc.getElementById('suiviContainer');
                var pg = doc.getElementById('pagination');
                if (sc) document.getElementById('suiviContainer').innerHTML = sc.innerHTML;
                if (pg) document.getElementById('pagination').innerHTML = pg.innerHTML;
                window.history.pushState({}, '', url);
            })
            .catch(function() { alert('Erreur recherche'); });
    });
}

var dateInput = document.querySelector('input[name="date"]');
if (dateInput) {
    dateInput.addEventListener('input', function() {
        if (this.value !== '') return;
        var url = new URL(window.location.href);
        url.searchParams.delete('date');
        url.searchParams.set('page', '1');
        fetch(url.toString())
            .then(function(res) { return res.text(); })
            .then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var sc = doc.getElementById('suiviContainer');
                var pg = doc.getElementById('pagination');
                if (sc) document.getElementById('suiviContainer').innerHTML = sc.innerHTML;
                if (pg) document.getElementById('pagination').innerHTML = pg.innerHTML;
                window.history.pushState({}, '', url);
            });
    });
}

var resetBtn = document.getElementById('resetFilter');
if (resetBtn) {
    resetBtn.addEventListener('click', function() {
        var di = document.querySelector('input[name="date"]');
        var sel = document.querySelector('select[name="sort"]');
        if (di) di.value = '';
        if (sel) sel.value = '';
        var url = new URL('user_health_space.php', window.location.href);
        url.searchParams.set('page', '1');
        fetch(url.toString())
            .then(function(res) { return res.text(); })
            .then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var sc = doc.getElementById('suiviContainer');
                var pg = doc.getElementById('pagination');
                if (sc) document.getElementById('suiviContainer').innerHTML = sc.innerHTML;
                if (pg) document.getElementById('pagination').innerHTML = pg.innerHTML;
                window.history.pushState({}, '', url);
            });
    });
}
</script>

<?php endif; ?>

</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<?php if ($guestHealthSpace) {
    require __DIR__ . '/includes/guest_login_gate.php';
} ?>

</body>
</html>
