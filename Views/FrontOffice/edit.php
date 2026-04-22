<?php
$profil['allergenes'] = json_decode($profil['allergenes'], true) ?? [];
$profil['carences']   = json_decode($profil['carences'], true) ?? [];
$profil['maladies']   = json_decode($profil['maladies'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Profil Santé</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">

    <style>
        .error {
            color: red;
            font-size: 13px;
            margin-top: 3px;
        }
             button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #036612, #036612);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
        }
.error {
    color: #e74c3c;
    background: #ffe6e6;
    padding: 8px;
    border-radius: 8px;
    font-size: 13px;
    margin-top:15px;
    display: none;
}
    </style>
</head>

<body>

<?php include __DIR__ . '/../partials/navbar.php'; ?>
<h1>Modifier mon profil santé</h1>

<!-- FORM -->
<form method="POST" id="profilForm">

    <!-- TAILLE -->
    <label>Taille (cm) :</label><br>
    <input type="number" step="0.01" name="taille"
           value="<?= htmlspecialchars($profil['taille'] ?? '') ?>">

    <div id="err-taille" class="error"></div>
    <br>

    <!-- POIDS -->
    <label>Poids actuel (kg) :</label><br>
    <input type="number" step="0.01" name="poids_actuel"
           value="<?= htmlspecialchars($profil['poids_actuel'] ?? '') ?>">

    <div id="err-poids" class="error"></div>
    <br><br>

    <!-- OBJECTIF -->
    <label>Objectif :</label><br>
    <select name="objectif">
        <option value="Perte de poids"
            <?= ($profil['objectif'] ?? '') === 'Perte de poids' ? 'selected' : '' ?>>
            Perte de poids
        </option>

        <option value="Prise de masse"
            <?= ($profil['objectif'] ?? '') === 'Prise de masse' ? 'selected' : '' ?>>
            Prise de masse
        </option>

        <option value="Maintien"
            <?= ($profil['objectif'] ?? '') === 'Maintien' ? 'selected' : '' ?>>
            Maintien
        </option>
    </select>

    <br><br>

    <!-- ALLERGÈNES -->
    <label>Allergènes :</label><br>

    <input type="checkbox" name="allergenes[]" value="Gluten"
        <?= in_array('Gluten', $profil['allergenes']) ? 'checked' : '' ?>> Gluten<br>

    <input type="checkbox" name="allergenes[]" value="Lactose"
        <?= in_array('Lactose', $profil['allergenes']) ? 'checked' : '' ?>> Lactose<br>

    <input type="checkbox" name="allergenes[]" value="Sucre"
        <?= in_array('Sucre', $profil['allergenes']) ? 'checked' : '' ?>> Sucre<br>

    <input type="checkbox" name="allergenes[]" value="Fruits à coque"
        <?= in_array('Fruits à coque', $profil['allergenes']) ? 'checked' : '' ?>> Fruits à coque<br>

    <br>

    <!-- CARENCES -->
    <label>Carences :</label><br>

    <input type="checkbox" name="carences[]" value="Fer"
        <?= in_array('Fer', $profil['carences']) ? 'checked' : '' ?>> Fer<br>

    <input type="checkbox" name="carences[]" value="Calcium"
        <?= in_array('Calcium', $profil['carences']) ? 'checked' : '' ?>> Calcium<br>

    <input type="checkbox" name="carences[]" value="Vitamine C"
        <?= in_array('Vitamine C', $profil['carences']) ? 'checked' : '' ?>> Vitamine C<br>

    <input type="checkbox" name="carences[]" value="Vitamine D"
        <?= in_array('Vitamine D', $profil['carences']) ? 'checked' : '' ?>> Vitamine D<br>

    <br>

    <!-- MALADIES -->
    <label>Maladies :</label><br>

    <input type="checkbox" name="maladies[]" value="Diabète"
        <?= in_array('Diabète', $profil['maladies']) ? 'checked' : '' ?>> Diabète<br>

    <input type="checkbox" name="maladies[]" value="Cholestérol"
        <?= in_array('Cholestérol', $profil['maladies']) ? 'checked' : '' ?>> Cholestérol<br>

    <input type="checkbox" name="maladies[]" value="Hypertension"
        <?= in_array('Hypertension', $profil['maladies']) ? 'checked' : '' ?>> Hypertension<br>

    <br>

    <button type="submit">Mettre à jour</button>

</form>

<br>

<a href="index.php">Retour</a>

<!-- JS -->
<script src="/Views/FrontOffice/edit.js"></script>

</body>
</html>