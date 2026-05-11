<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sante_session.php';
require_once __DIR__ . '/../config/Database.php';

$uid = sante_require_user_id();
$pdo = Database::getConnection();

$stProfil = $pdo->prepare('SELECT id FROM profil_sante WHERE id_utilisateur = :u LIMIT 1');
$stProfil->execute(['u' => $uid]);
$rowProfil = $stProfil->fetch(PDO::FETCH_ASSOC);
$idProfil = $rowProfil ? (int) $rowProfil['id'] : 0;

if ($idProfil < 1) {
    header('Location: user_health_space.php');
    exit;
}

$stLast = $pdo->prepare(
    'SELECT sj.* FROM suivi_journalier sj
     INNER JOIN profil_sante ps ON ps.id = sj.id_profil_sante
     WHERE ps.id_utilisateur = :id
     ORDER BY sj.date_jour DESC LIMIT 1'
);
$stLast->execute(['id' => $uid]);
/** @var array<string, mixed> $last */
$last = $stLast->fetch(PDO::FETCH_ASSOC) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trim = static fn(string $k): string => trim((string) ($_POST[$k] ?? ''));
    $emptyOrString = static function (string $k) use ($trim): ?string {
        $v = $trim($k);
        return $v === '' ? null : $v;
    };
    $sportStr = $trim('nbr_activites_sport');
    $sport = $sportStr === '' ? 0 : (int) $sportStr;

    $payload = [
        'poids' => $emptyOrString('poids'),
        'cal' => $emptyOrString('calories'),
        'som' => $emptyOrString('sommeil_heures'),
        'pas' => $emptyOrString('nbr_pas'),
        'sport' => $sport,
        'hydr' => $emptyOrString('hydratation_litre'),
    ];

    $today = date('Y-m-d');
    $check = $pdo->prepare(
        'SELECT id FROM suivi_journalier WHERE id_profil_sante = :p AND date_jour = :d LIMIT 1'
    );
    $check->execute(['p' => $idProfil, 'd' => $today]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    try {
        if ($existing && isset($existing['id'])) {
            $upd = $pdo->prepare(
                'UPDATE suivi_journalier SET
                poids = :poids,
                calories = :cal,
                sommeil_heures = :som,
                nbr_pas = :pas,
                nbr_activites_sport = :sport,
                hydratation_litre = :hydr
                WHERE id = :id AND id_profil_sante = :pid'
            );
            $upd->execute([
                'poids' => $payload['poids'],
                'cal' => $payload['cal'],
                'som' => $payload['som'],
                'pas' => $payload['pas'],
                'sport' => $payload['sport'],
                'hydr' => $payload['hydr'],
                'id' => (int) $existing['id'],
                'pid' => $idProfil,
            ]);
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO suivi_journalier
                (id_profil_sante, date_jour, poids, calories, sommeil_heures, nbr_pas, nbr_activites_sport, hydratation_litre)
                VALUES (:p, :d, :poids, :cal, :som, :pas, :sport, :hydr)'
            );
            $ins->execute([
                'p' => $idProfil,
                'd' => $today,
                'poids' => $payload['poids'],
                'cal' => $payload['cal'],
                'som' => $payload['som'],
                'pas' => $payload['pas'],
                'sport' => $payload['sport'],
                'hydr' => $payload['hydr'],
            ]);
        }
        header('Location: user_health_space.php?notice=suivi_saved');
        exit;
    } catch (Throwable $e) {
        header('Location: user_health_space.php?notice=suivi_db_error');
        exit;
    }
}

$h = (string) ($last['hydratation_litre'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Ajouter un suivi journalier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-original-views.css">
    <style>
        .sante-form-wrap {
            max-width: min(920px, 100%);
            width: 100%;
            margin: 0 auto;
            padding: 2rem clamp(1rem, 3vw, 2rem) 3.75rem;
            box-sizing: border-box;
            background: linear-gradient(180deg, #eef8f1 0%, #f4fbf7 40%, #f7fcf9 100%);
            min-height: calc(100vh - 120px);
        }
        .sante-form-wrap h1 {
            font-family: var(--hb-font-main, "Poppins", sans-serif);
            text-align: center;
            font-size: clamp(1.75rem, 3vw, 2rem);
            font-weight: 700;
            color: #2C7E34;
            margin-bottom: 1.65rem;
        }
        .sante-form-card {
            background: #fff;
            border: 1px solid rgba(227, 235, 230, 0.95);
            border-radius: 20px;
            padding: clamp(2rem, 4vw, 2.65rem) clamp(1.85rem, 4vw, 2.75rem);
            box-shadow: 0 10px 36px rgba(15, 42, 28, 0.08), 0 4px 14px rgba(0, 0, 0, 0.05);
        }
        .sante-form-card label { font-weight: 500; display: block; margin-bottom: 10px; color: #1a1a1a; font-size: 1rem; }
        .sante-form-card input[type="number"] {
            width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid #e3ebe6;
            margin-bottom: 16px; font-family: inherit;
            font-size: 1rem;
        }
        .sante-form-card .radio-group label {
            font-weight: 400; display: block; margin-bottom: 6px;
        }
        .sante-form-card button[type="submit"] {
            width: 100%; margin-top: 16px; padding: 16px 18px; border: none; border-radius: 16px;
            background: #2C7E34; color: #fff; font-weight: 600; font-size: 1.08rem; cursor: pointer; font-family: inherit;
        }
        .sante-form-card button[type="submit"]:hover { filter: brightness(1.05); }
        .sante-form-card .error { color: #b91c1c; font-size: 0.85rem; display: none; margin-top: 4px; }
        .sante-back { display: inline-block; margin-top: 1rem; color: #2C7E34; font-weight: 500; text-decoration: none; }
        .sante-muted { font-size: 0.88rem; color: #5c6b62; margin-top: 6px; display: block; }
    </style>
</head>
<body>
<?php $nav_active = 'sante'; require __DIR__ . '/includes/nav_front.php'; ?>

<main class="sante-form-wrap">
    <h1>Ajouter un suivi journalier</h1>
    <div class="sante-form-card">
        <form method="post" action="createSuivi.php" id="profilForm">
            <div class="form-group">
                <label>Poids (kg)</label>
                <input type="number" step="0.01" name="poids"
                       placeholder="Dernier: <?= htmlspecialchars((string) ($last['poids'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-last="<?= htmlspecialchars((string) ($last['poids'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <small id="err_poids" class="error"></small>
            </div>
            <div class="form-group">
                <label>Calories</label>
                <input type="number" name="calories"
                       placeholder="Dernier: <?= htmlspecialchars((string) ($last['calories'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-last="<?= htmlspecialchars((string) ($last['calories'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <small id="err_calories" class="error"></small>
            </div>
            <div class="form-group">
                <label>Sommeil (heures)</label>
                <input type="number" step="0.1" name="sommeil_heures"
                       placeholder="Dernier: <?= htmlspecialchars((string) ($last['sommeil_heures'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-last="<?= htmlspecialchars((string) ($last['sommeil_heures'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <small id="err_sommeil" class="error"></small>
            </div>
            <div class="form-group">
                <label>Nombre de pas</label>
                <input type="number" name="nbr_pas" step="1000"
                       placeholder="Dernier: <?= htmlspecialchars((string) ($last['nbr_pas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-last="<?= htmlspecialchars((string) ($last['nbr_pas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <small id="err_pas" class="error"></small>
            </div>
            <div class="form-group">
                <label>Sport (activités)</label>
                <input type="number" name="nbr_activites_sport"
                       placeholder="Dernier: <?= htmlspecialchars((string) ($last['nbr_activites_sport'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-last="<?= htmlspecialchars((string) ($last['nbr_activites_sport'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <small id="err_sport" class="error"></small>
            </div>
            <div class="form-group">
                <label>Hydratation</label>
                <div class="radio-group">
                    <label><input type="radio" name="hydratation_litre" value="moins_1L"> Moins de 1L</label>
                    <label><input type="radio" name="hydratation_litre" value="1_1.5L"> Entre 1L et 1,5L</label>
                    <label><input type="radio" name="hydratation_litre" value="1.5_2L"> Entre 1,5L et 2L</label>
                    <label><input type="radio" name="hydratation_litre" value="plus_2L"> Plus de 2L</label>
                </div>
                <?php if ($h !== ''): ?>
                    <span class="sante-muted">Dernier choix : <?= htmlspecialchars($h, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <small id="err_hydratation" class="error"></small>
            </div>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
    <a class="sante-back" href="user_health_space.php">← Retour à l’espace santé</a>
</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<script src="createSuivi.js"></script>
<script>
document.querySelectorAll('input[data-last]').forEach(function (input) {
    input.addEventListener('focus', function () {
        if (this.value === '' && this.dataset.last) {
            this.value = this.dataset.last;
        }
    });
});
</script>
</body>
</html>
