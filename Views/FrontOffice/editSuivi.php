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


<!-- TITRE -->
<h1>Modifier un suivi journalier</h1>

<form method="POST"
      id="profilForm"
      action="index.php?action=updateSuivi&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $id_utilisateur ?>">

    <label>Date :</label><br>
  <input type="text"
       value="<?= htmlspecialchars($suivi['date_jour'] ?? '') ?>"
       disabled>


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
    <input type="number" name="nbr_pas" step="1000"
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

    <button class="button-enregister" type="submit">Mettre à jour</button>
</form>

<br>

<a href="index.php?action=showProfilSante&id_utilisateur=<?= $id_utilisateur ?? '' ?>">
    Retour
</a>
</div>
<script src="Views/FrontOffice/editSuivi.js"></script>
</body>
</html>