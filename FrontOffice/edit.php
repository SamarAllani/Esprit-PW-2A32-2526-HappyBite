<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sante_session.php';
require_once __DIR__ . '/../config/Database.php';

$uid = sante_require_user_id();
$pdo = Database::getConnection();

$stmt = $pdo->prepare('SELECT * FROM profil_sante WHERE id_utilisateur = :id LIMIT 1');
$stmt->execute(['id' => $uid]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profil) {
    header('Location: create.php');
    exit;
}

$profil['allergenes'] = json_decode((string) ($profil['allergenes'] ?? ''), true) ?? [];
$profil['carences'] = json_decode((string) ($profil['carences'] ?? ''), true) ?? [];
$profil['maladies'] = json_decode((string) ($profil['maladies'] ?? ''), true) ?? [];

if (!is_array($profil['allergenes'])) {
    $profil['allergenes'] = [];
}
if (!is_array($profil['carences'])) {
    $profil['carences'] = [];
}
if (!is_array($profil['maladies'])) {
    $profil['maladies'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $up = $pdo->prepare(
        'UPDATE profil_sante SET
        taille = :t,
        poids_actuel = :p,
        objectif = :o,
        allergenes = :a,
        carences = :c,
        maladies = :m
        WHERE id = :id'
    );
    $up->execute([
        't' => $_POST['taille'] ?? null,
        'p' => $_POST['poids_actuel'] ?? null,
        'o' => $_POST['objectif'] ?? null,
        'a' => json_encode($_POST['allergenes'] ?? [], JSON_UNESCAPED_UNICODE),
        'c' => json_encode($_POST['carences'] ?? [], JSON_UNESCAPED_UNICODE),
        'm' => json_encode($_POST['maladies'] ?? [], JSON_UNESCAPED_UNICODE),
        'id' => $profil['id'],
    ]);
    header('Location: user_health_space.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Modifier profil santé</title>
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
        .sante-form-card input[type="number"], .sante-form-card select {
            width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid #e3ebe6;
            margin-bottom: 12px; font-family: inherit;
        }
        .sante-form-card button[type="submit"] {
            width: 100%; margin-top: 8px; padding: 12px; border: none; border-radius: 12px;
            background: #2C7E34; color: #fff; font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .sante-form-card button[type="submit"]:hover { filter: brightness(1.05); }
        .sante-form-card .error {
            color: #e74c3c; background: #ffe6e6; padding: 8px; border-radius: 8px;
            font-size: 13px; margin-top: 6px; display: none;
        }
        .sante-back { display: inline-block; margin-top: 1rem; color: #2C7E34; font-weight: 500; text-decoration: none; }
    </style>
</head>
<body>
<?php $nav_active = 'sante'; require __DIR__ . '/includes/nav_front.php'; ?>

<main class="sante-form-wrap">
    <h1>Modifier mon profil santé</h1>
    <div class="sante-form-card">
        <form method="post" action="edit.php" id="profilForm">
            <label for="taille">Taille (cm)</label>
            <input type="number" step="0.01" name="taille" id="taille"
                   value="<?= htmlspecialchars((string) ($profil['taille'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-taille" class="error"></div>

            <label for="poids_actuel">Poids actuel (kg)</label>
            <input type="number" step="0.01" name="poids_actuel" id="poids_actuel"
                   value="<?= htmlspecialchars((string) ($profil['poids_actuel'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div id="err-poids" class="error"></div>

            <label for="objectif">Objectif</label>
            <select name="objectif" id="objectif">
                <option value="Perte de poids" <?= ($profil['objectif'] ?? '') === 'Perte de poids' ? 'selected' : '' ?>>Perte de poids</option>
                <option value="Prise de masse" <?= ($profil['objectif'] ?? '') === 'Prise de masse' ? 'selected' : '' ?>>Prise de masse</option>
                <option value="Maintien" <?= ($profil['objectif'] ?? '') === 'Maintien' ? 'selected' : '' ?>>Maintien</option>
            </select>

            <label style="margin-top:12px;">Allergènes</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Gluten" <?= in_array('Gluten', $profil['allergenes'], true) ? 'checked' : '' ?>> Gluten</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Lactose" <?= in_array('Lactose', $profil['allergenes'], true) ? 'checked' : '' ?>> Lactose</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Sucre" <?= in_array('Sucre', $profil['allergenes'], true) ? 'checked' : '' ?>> Sucre</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Fruits à coque" <?= in_array('Fruits à coque', $profil['allergenes'], true) ? 'checked' : '' ?>> Fruits à coque</label>

            <label style="margin-top:12px;">Carences</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Fer" <?= in_array('Fer', $profil['carences'], true) ? 'checked' : '' ?>> Fer</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Calcium" <?= in_array('Calcium', $profil['carences'], true) ? 'checked' : '' ?>> Calcium</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Vitamine C" <?= in_array('Vitamine C', $profil['carences'], true) ? 'checked' : '' ?>> Vitamine C</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Vitamine D" <?= in_array('Vitamine D', $profil['carences'], true) ? 'checked' : '' ?>> Vitamine D</label>

            <label style="margin-top:12px;">Maladies</label>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Diabète" <?= in_array('Diabète', $profil['maladies'], true) ? 'checked' : '' ?>> Diabète</label>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Cholestérol" <?= in_array('Cholestérol', $profil['maladies'], true) ? 'checked' : '' ?>> Cholestérol</label>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Hypertension" <?= in_array('Hypertension', $profil['maladies'], true) ? 'checked' : '' ?>> Hypertension</label>

            <button type="submit">Mettre à jour</button>
        </form>
    </div>
    <a class="sante-back" href="user_health_space.php">← Retour à l’espace santé</a>
</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<script src="edit.js"></script>
</body>
</html>
