<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sante_session.php';
require_once __DIR__ . '/../config/Database.php';

$uid = sante_require_user_id();
$pdo = Database::getConnection();

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: user_health_space.php');
    exit;
}

$st = $pdo->prepare(
    'SELECT sj.* FROM suivi_journalier sj
     INNER JOIN profil_sante ps ON ps.id = sj.id_profil_sante
     WHERE sj.id = :sid AND ps.id_utilisateur = :uid
     LIMIT 1'
);
$st->execute(['sid' => $id, 'uid' => $uid]);
$suivi = $st->fetch(PDO::FETCH_ASSOC);

if (!$suivi) {
    http_response_code(404);
    exit('Suivi introuvable.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $up = $pdo->prepare(
        'UPDATE suivi_journalier SET
        poids = :p,
        calories = :c,
        sommeil_heures = :s,
        nbr_pas = :n,
        nbr_activites_sport = :a,
        hydratation_litre = :h
        WHERE id = :id'
    );
    $up->execute([
        'p' => $_POST['poids'],
        'c' => $_POST['calories'],
        's' => $_POST['sommeil_heures'],
        'n' => $_POST['nbr_pas'],
        'a' => $_POST['nbr_activites_sport'],
        'h' => $_POST['hydratation_litre'] ?? null,
        'id' => $id,
    ]);
    header('Location: user_health_space.php');
    exit;
}

$hydratation = (string) ($suivi['hydratation_litre'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Modifier suivi journalier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-original-views.css">
    <style>
        .sante-form-wrap { max-width: 720px; margin: 0 auto; padding: 1.25rem 1rem 3rem; box-sizing: border-box; }
        .sante-form-wrap h1 {
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            text-align: center;
            font-size: 1.65rem;
            font-weight: 700;
            color: #2C7E34;
            margin-bottom: 1.25rem;
        }
        .sante-form-card {
            background: #fff;
            border: 1px solid var(--hb-card-border, #e3ebe6);
            border-radius: 14px;
            padding: 1.5rem 1.35rem;
            box-shadow: 0 2px 14px rgba(0,0,0,0.04);
        }
        .sante-form-card label { font-weight: 500; display: block; margin-bottom: 6px; color: #1a1a1a; }
        .sante-form-card input[type="number"], .sante-form-card input[type="text"]:disabled {
            width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #e3ebe6;
            margin-bottom: 12px; font-family: inherit;
        }
        .sante-form-card input:disabled { background: #f3f4f6; color: #4b5563; }
        .sante-form-card .radio-group label { font-weight: 400; display: block; margin-bottom: 6px; }
        .sante-form-card button[type="submit"] {
            width: 100%; margin-top: 12px; padding: 12px; border: none; border-radius: 12px;
            background: #2C7E34; color: #fff; font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .sante-form-card .error { color: #b91c1c; font-size: 0.85rem; display: none; margin-top: 4px; }
        .sante-back { display: inline-block; margin-top: 1rem; color: #2C7E34; font-weight: 500; text-decoration: none; }
    </style>
</head>
<body>
<?php $nav_active = 'sante'; require __DIR__ . '/includes/nav_front.php'; ?>

<main class="sante-form-wrap">
    <h1>Modifier un suivi journalier</h1>
    <div class="sante-form-card">
        <form method="post" action="editSuivi.php?id=<?= (int) $id ?>" id="profilForm">
            <label>Date</label>
            <input type="text" value="<?= htmlspecialchars((string) ($suivi['date_jour'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" disabled>
            <div id="err-date" class="error"></div>

            <label>Poids (kg)</label>
            <input type="number" step="0.01" name="poids"
                   value="<?= htmlspecialchars((string) ($suivi['poids'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-poids" class="error"></div>

            <label>Calories</label>
            <input type="number" name="calories"
                   value="<?= htmlspecialchars((string) ($suivi['calories'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-calories" class="error"></div>

            <label>Sommeil (heures)</label>
            <input type="number" step="0.01" name="sommeil_heures"
                   value="<?= htmlspecialchars((string) ($suivi['sommeil_heures'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-sommeil" class="error"></div>

            <label>Nombre de pas</label>
            <input type="number" name="nbr_pas" step="1000"
                   value="<?= htmlspecialchars((string) ($suivi['nbr_pas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-pas" class="error"></div>

            <label>Activités sport</label>
            <input type="number" name="nbr_activites_sport"
                   value="<?= htmlspecialchars((string) ($suivi['nbr_activites_sport'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-sport" class="error"></div>

            <label>Hydratation</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="hydratation_litre" value="moins_1L" <?= $hydratation === 'moins_1L' ? 'checked' : '' ?>>
                    Moins de 1L
                </label>
                <label>
                    <input type="radio" name="hydratation_litre" value="1_1.5L" <?= $hydratation === '1_1.5L' ? 'checked' : '' ?>>
                    Entre 1L et 1,5L
                </label>
                <label>
                    <input type="radio" name="hydratation_litre" value="1.5_2L" <?= $hydratation === '1.5_2L' ? 'checked' : '' ?>>
                    Entre 1,5L et 2L
                </label>
                <label>
                    <input type="radio" name="hydratation_litre" value="plus_2L" <?= $hydratation === 'plus_2L' ? 'checked' : '' ?>>
                    Plus de 2L
                </label>
            </div>
            <div id="err-hydratation" class="error"></div>

            <button type="submit">Mettre à jour</button>
        </form>
    </div>
    <a class="sante-back" href="user_health_space.php">← Retour à l’espace santé</a>
</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<script src="editSuivi.js"></script>
</body>
</html>
