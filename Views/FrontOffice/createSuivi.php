<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Suivi Journalier</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">
</head>

<body>


<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="container">
    <h1>Ajouter un suivi journalier</h1>

<form id="profilForm" method="POST">

  <div class="form-group">
    <label>Quelle est la date d'aujourd'hui ?</label>
    <input type="date" name="date_jour">
    <span id="err_date" class="error"></span>
</div>

<div class="form-group">
    <label>Quel est votre poids aujourd’hui (kg) ?</label>
    <input type="number" name="poids" step="0.01">
    <span id="err_poids" class="error"></span>
</div>

<div class="form-group">
    <label>Combien de calories avez-vous consommées ?</label>
    <input type="number" name="calories">
    <span id="err_calories" class="error"></span>
</div>

<div class="form-group">
    <label>Combien d’heures avez-vous dormi ?</label>
    <input type="number" name="sommeil_heures" step="0.1">
    <span id="err_sommeil" class="error"></span>
</div>

<div class="form-group">
    <label>Combien de pas avez-vous fait aujourd’hui ?</label>
    <input type="number" name="nbr_pas">
    <span id="err_pas" class="error"></span>
</div>

<div class="form-group">
    <label>Avez-vous fait du sport ? Combien d’activités ?</label>
    <input type="number" name="nbr_activites_sport">
    <span id="err_sport" class="error"></span>
</div>

<div class="form-group">
    <label>Combien d’eau avez-vous bu aujourd’hui ?</label>

    <div class="radio-group">
        <label  class="radio-row">
            <input type="radio" name="hydratation_litre" value="moins_1L">
            Moins de 1L
        </label>

        <label class="radio-row">
            <input type="radio" name="hydratation_litre" value="1_1.5L">
            Entre 1L et 1,5L
        </label>

        <label class="radio-row">
            <input type="radio" name="hydratation_litre" value="1.5_2L">
            Entre 1,5L et 2L
        </label>

        <label>
            <input type="radio" name="hydratation_litre" value="plus_2L">
            Plus de 2L
        </label>
    </div>

    <span id="err_hydratation" class="error"></span>
</div>

    <button type="submit" class="button-enregister">Enregistrer</button>

</form>
</div>

<script src="/Views/FrontOffice/createSuivi.js"></script>
</body>
</html>