<?php
$suivi = $suivi ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Suivi Journalier</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">
</head>

<body>

<?php include __DIR__ . '/../partials/navbar.php'; ?>

<!-- TITRE -->
<h1>Modifier un suivi journalier</h1>

<form method="POST"
      id="profilForm"
      action="index.php?action=updateSuivi&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $id_utilisateur ?>">

    <label>Date :</label><br>
    <input type="date" name="date_jour"
           value="<?= htmlspecialchars($suivi['date_jour'] ?? '') ?>">
    <div id="err-date" class="error"></div>
    <br>

    <label>Poids (kg) :</label><br>
    <input type="number" step="0.01" name="poids"
           value="<?= htmlspecialchars($suivi['poids'] ?? '') ?>">
    <div id="err-poids" class="error"></div>
    <br>

    <label>Calories :</label><br>
    <input type="number" name="calories"
           value="<?= htmlspecialchars($suivi['calories'] ?? '') ?>">
    <div id="err-calories" class="error"></div>
    <br>

    <label>Sommeil (heures) :</label><br>
    <input type="number" step="0.01" name="sommeil_heures"
           value="<?= htmlspecialchars($suivi['sommeil_heures'] ?? '') ?>">
    <div id="err-sommeil" class="error"></div>
    <br>

    <label>Nombre de pas :</label><br>
    <input type="number" name="nbr_pas"
           value="<?= htmlspecialchars($suivi['nbr_pas'] ?? '') ?>">
    <div id="err-pas" class="error"></div>
    <br>

    <label>Activités sport :</label><br>
    <input type="number" name="nbr_activites_sport"
           value="<?= htmlspecialchars($suivi['nbr_activites_sport'] ?? '') ?>">
    <div id="err-sport" class="error"></div>
    <br>

    <div class="form-group">
    <label>Combien d’eau avez-vous bu aujourd’hui ?</label>
     <?php
$hydratation = $suivi['hydratation_litre'] ?? '';
?>

<div class="radio-group">
    <label>
        <input type="radio" name="hydratation_litre" value="moins_1L"
            <?= $hydratation === 'moins_1L' ? 'checked' : '' ?>>
        Moins de 1L
    </label>

    <label>
        <input type="radio" name="hydratation_litre" value="1_1.5L"
            <?= $hydratation === '1_1.5L' ? 'checked' : '' ?>>
        Entre 1L et 1,5L
    </label>

    <label>
        <input type="radio" name="hydratation_litre" value="1.5_2L"
            <?= $hydratation === '1.5_2L' ? 'checked' : '' ?>>
        Entre 1,5L et 2L
    </label>

    <label>
        <input type="radio" name="hydratation_litre" value="plus_2L"
            <?= $hydratation === 'plus_2L' ? 'checked' : '' ?>>
        Plus de 2L
    </label>
</div>
    <div id="err-hydratation" class="error"></div>
    <br>

    <button type="submit">Mettre à jour</button>
</form>
<script src="Views/FrontOffice/editSuivi.js"></script>
<br>

<a href="index.php?action=showProfilSante&id_utilisateur=<?= $id_utilisateur ?? '' ?>">
    Retour
</a>
</div>
</body>
</html>