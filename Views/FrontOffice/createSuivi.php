<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Suivi Journalier</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">
</head>
<?php $last = $lastSuivi ?? []; ?>
<body>




<nav class="main-navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
           <img src="/Views/assets/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="List-Produit.php" class="active">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="#">Commande</a></li>
        </ul>

        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action active">Santé</a>
            <a href="#" class="nav-action">Profil</a>
        </div>
    </div>
</nav>

<div class="container">
   <h1>Ajouter un suivi journalier</h1>

<form method="POST" id="profilForm">

<!-- POIDS -->
<div class="form-group">
    <label>Poids (kg)</label>
    <input type="number" step="0.01"
           name="poids"
           placeholder="Dernier: <?= $last['poids'] ?? '' ?>"
           data-last="<?= $last['poids'] ?? '' ?>">
           <small id="err_poids" class="error"></small>
</div>

<!-- CALORIES -->
<div class="form-group">
    <label>Calories</label>
    <input type="number"
           name="calories"
           placeholder="Dernier: <?= $last['calories'] ?? '' ?>"
           data-last="<?= $last['calories'] ?? '' ?>">
           <small id="err_calories" class="error"></small>
</div>

<!-- SOMMEIL -->
<div class="form-group">
    <label>Sommeil (heures)</label>
    <input type="number" step="0.1"
           name="sommeil_heures"
           placeholder="Dernier: <?= $last['sommeil_heures'] ?? '' ?>"
           data-last="<?= $last['sommeil_heures'] ?? '' ?>">
           <small id="err_sommeil" class="error"></small>
</div>

<!-- PAS -->
<div class="form-group">
    <label>Nombre de pas</label>
   <input type="number"
       name="nbr_pas"
       step="1000"
       placeholder="Dernier: <?= $last['nbr_pas'] ?? '' ?>"
       data-last="<?= $last['nbr_pas'] ?? '' ?>">
       <small id="err_pas" class="error"></small>
</div>

<!-- SPORT -->
<div class="form-group">
    <label>Sport (activités)</label>
    <input type="number"
           name="nbr_activites_sport"
           placeholder="Dernier: <?= $last['nbr_activites_sport'] ?? '' ?>"
           data-last="<?= $last['nbr_activites_sport'] ?? '' ?>">
            <small id="err_sport" class="error"></small>
</div>

<!-- HYDRATATION -->
<div class="form-group">
    <label>Hydratation</label>

    <?php $h = $last['hydratation_litre'] ?? ''; ?>

    <div class="radio-group">
        <label><input type="radio" name="hydratation_litre" value="moins_1L"> Moins 1L</label>
        <label><input type="radio" name="hydratation_litre" value="1_1.5L"> 1 à 1.5L</label>
        <label><input type="radio" name="hydratation_litre" value="1.5_2L"> 1.5 à 2L</label>
        <label><input type="radio" name="hydratation_litre" value="plus_2L"> +2L</label>
    </div>

    <small>Dernier choix: <?= htmlspecialchars($h) ?></small>
     <small id="err_hydratation" class="error"></small>
</div>

<button type="submit">Enregistrer</button>

</form>
<script src="Views/FrontOffice/createSuivi.js"></script>
<!-- JS magique -->
<script>
document.querySelectorAll("input[data-last]").forEach(input => {

    input.addEventListener("focus", function () {
        if (this.value === "" && this.dataset.last) {
            this.value = this.dataset.last;
        }
    });

});
</script>

</body>
</html>