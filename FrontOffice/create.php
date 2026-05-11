<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/sante_session.php';
require_once __DIR__ . '/../config/Database.php';

$uid = sante_require_user_id();
$pdo = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check = $pdo->prepare('SELECT id FROM profil_sante WHERE id_utilisateur = :id LIMIT 1');
    $check->execute(['id' => $uid]);
    if ($check->fetch()) {
        header('Location: user_health_space.php');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO profil_sante
        (id_utilisateur, taille, poids_actuel, objectif, allergenes, carences, maladies)
        VALUES (:id, :t, :p, :o, :a, :c, :m)'
    );
    $stmt->execute([
        'id' => $uid,
        't' => $_POST['taille'] ?? null,
        'p' => $_POST['poids_actuel'] ?? null,
        'o' => $_POST['objectif'] ?? null,
        'a' => json_encode($_POST['allergenes'] ?? [], JSON_UNESCAPED_UNICODE),
        'c' => json_encode($_POST['carences'] ?? [], JSON_UNESCAPED_UNICODE),
        'm' => json_encode($_POST['maladies'] ?? [], JSON_UNESCAPED_UNICODE),
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
    <title>HappyBite — Créer profil santé</title>
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
        .sante-form-card input[type="number"], .sante-form-card select {
            width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid #e3ebe6;
            margin-bottom: 16px; font-family: inherit;
            font-size: 1rem;
        }
        .sante-form-card input:focus, .sante-form-card select:focus {
            border-color: #2C7E34; outline: none;
        }
        .sante-form-card button[type="submit"] {
            width: 100%; margin-top: 14px; padding: 16px 18px; border: none; border-radius: 16px;
            background: #2C7E34; color: #fff; font-weight: 600; font-size: 1.08rem; cursor: pointer;
            font-family: inherit;
        }
        .sante-form-card button[type="submit"]:hover { filter: brightness(1.05); }
        .sante-form-card .error {
            background: #ffe6e6; color: #e74c3c; padding: 8px; border-radius: 8px;
            font-size: 13px; margin-bottom: 8px; display: none;
        }
        .sante-back { display: inline-block; margin-top: 1rem; color: #2C7E34; font-weight: 500; text-decoration: none; }
        .sante-back:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php $nav_active = 'sante'; require __DIR__ . '/includes/nav_front.php'; ?>

<main class="sante-form-wrap">
    <h1>Créer mon profil santé</h1>
    <div class="sante-form-card">
        <form method="post" action="create.php" id="formProfil">
            <label for="taille">Taille (cm)</label>
            <input type="number" step="0.01" name="taille" id="taille">
            <div class="error" id="error_taille"></div>

            <label for="poids">Poids actuel (kg)</label>
            <input type="number" step="0.01" name="poids_actuel" id="poids">
            <div class="error" id="error_poids"></div>

            <label for="objectif">Objectif</label>
            <select name="objectif" id="objectif">
                <option value="">-- Choisir --</option>
                <option value="Perte de poids">Perte de poids</option>
                <option value="Prise de masse">Prise de masse</option>
                <option value="Maintien">Maintien</option>
            </select>
            <div class="error" id="error_objectif"></div>

            <label>Allergènes</label>
            <div class="info" id="info_allergenes" style="font-size:0.85rem;color:#5c6b62;margin-bottom:8px;"></div>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Gluten"> Gluten</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Lactose"> Lactose</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Sucre"> Sucre</label>
            <label style="font-weight:400;"><input type="checkbox" name="allergenes[]" value="Fruits à coque"> Fruits à coque</label>

            <label style="margin-top:12px;">Carences</label>
            <div class="info" id="info_carences" style="font-size:0.85rem;color:#5c6b62;margin-bottom:8px;"></div>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Fer"> Fer</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Calcium"> Calcium</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Vitamine C"> Vitamine C</label>
            <label style="font-weight:400;"><input type="checkbox" name="carences[]" value="Vitamine D"> Vitamine D</label>

            <label style="margin-top:12px;">Maladies</label>
            <div class="info" id="info_maladies" style="font-size:0.85rem;color:#5c6b62;margin-bottom:8px;"></div>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Diabète"> Diabète</label>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Cholestérol"> Cholestérol</label>
            <label style="font-weight:400;"><input type="checkbox" name="maladies[]" value="Hypertension"> Hypertension</label>

            <div class="error" id="error_allergenes"></div>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
    <a class="sante-back" href="user_health_space.php">← Retour à l’espace santé</a>
</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<script src="create.js"></script>
</body>
</html>
