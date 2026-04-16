<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - SmartBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Ton CSS -->
    <link rel="stylesheet" href="/Views/assets/css/style.css">
</head>
<body>
<!-- NAVBAR ICI -->
<nav class="main-navbar">
    <div class="nav-container">
        
        <a href="index.php" class="nav-logo">
            <img src="../assets/images/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="List-Produit.php" class="active">Produits</a></li>
            <li><a href="#">Recettes</a></li>
            <li><a href="#">Communauté</a></li>
        </ul>

        <div class="nav-user">
            <a href="#" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Santé</a>
            <a href="#" class="nav-profile">Profil</a>
        </div>

    </div>
</nav>

<div class="container my-5">

    <div class="text-center mb-5">
        <h1 class="fw-bold">Bienvenue sur HappyBite</h1>
        <p class="text-muted">Explorez nos produits et recettes intelligentes</p>
    </div>

    <div class="row g-4 justify-content-center">

        <div class="col-md-4">
            <div class="card home-card shadow-lg border-0 text-center p-4">
                
                <h4 class="fw-bold">Produits</h4>
                <p class="text-muted">Explorez nos produits alimentaires</p>
                <a href="List-Produit.php" class="btn btn-success rounded-pill px-4">Voir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card home-card shadow-lg border-0 text-center p-4">
                
                <h4 class="fw-bold">Recettes</h4>
                <p class="text-muted">Découvrez des recettes adaptées</p>
                <a href="List-Recette.php" class="btn btn-outline-success rounded-pill px-4">Voir</a>
            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>