<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
$nav_active = 'accueil';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="home-wrap">
        <div class="home-hero">
            <h1>Bienvenue sur HappyBite</h1>
            <p>Explorez nos produits et recettes intelligentes</p>
        </div>

        <div class="home-cards-row">
            <section class="home-card-tile">
                <h2>Produits</h2>
                <p>Explorez nos produits alimentaires</p>
                <a href="List-Produit.php" class="btn-commande-primary">Voir</a>
            </section>
            <section class="home-card-tile">
                <h2>Recettes</h2>
                <p>Découvrez des recettes adaptées</p>
                <a href="List-Recette.php" class="btn-commande-outline">Voir</a>
            </section>
        </div>
    </div>
</main>

<footer>
    © 2026 HappyBite
</footer>

</body>
</html>
