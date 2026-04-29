<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - HappyBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ton CSS -->
    <link rel="stylesheet" href="/Views/assets/css/style.css">
    <style>
        :root {
            --main-green: #2f6f57;
            --green-dark: #1f4d3a;
            --green-light: #eaf4ef;
            --green-soft: #5f8f78;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f0f2f5;
        }

        /* navbar uniquement */
        .main-navbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            font-family: "DM Sans", sans-serif;
        }

        .main-navbar .nav-container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .main-navbar .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none !important;
            flex-shrink: 0;
        }

        .main-navbar .nav-logo img {
            height: 42px;
            width: auto;
            display: block;
        }

        .main-navbar .nav-logo span {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--main-green) !important;
        }

        .main-navbar .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 35px;
            margin: 0;
            padding: 0;
        }

        .main-navbar .nav-links li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-navbar .nav-links li a {
            text-decoration: none !important;
            color: #333 !important;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.25s ease;
        }

        .main-navbar .nav-links li a:hover {
            color: var(--main-green) !important;
        }

        .main-navbar .nav-links li a.active {
            color: var(--main-green) !important;
            font-weight: 700;
        }

        .main-navbar .nav-user {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .main-navbar .nav-action {
            text-decoration: none !important;
            color: var(--main-green) !important;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 12px;
            transition: all 0.25s ease;
            line-height: 1;
        }

        .main-navbar .nav-action:hover {
            background: #f1f3f2 !important;
            color: var(--main-green) !important;
        }

        .main-navbar .nav-action.active {
            background: var(--main-green);
            color: #fff !important;
        }

        .main-navbar .nav-action.active:hover {
            background: var(--green-dark) !important;
            color: #fff !important;
        }

        .main-navbar .nav-profile {
            background: var(--main-green);
            color: #fff !important;
            padding: 10px 16px;
            border-radius: 14px;
            text-decoration: none !important;
            font-weight: 600;
            transition: all 0.25s ease;
            line-height: 1;
        }

        .main-navbar .nav-profile:hover {
            background: #f1f3f2 !important;
            color: var(--main-green) !important;
        }

        .main-navbar a,
        .main-navbar a:hover,
        .main-navbar a:focus,
        .main-navbar a:active {
            text-decoration: none !important;
        }

        .hero-section {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--green-light) 0%, #ffffff 100%);
        }

        .hero-content {
            text-align: center;
            max-width: 600px;
            padding: 20px;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            color: var(--main-green);
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .cta-btn-primary {
            background: var(--main-green);
            color: #fff;
        }

        .cta-btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
        }

        .cta-btn-secondary {
            background: #fff;
            color: var(--main-green);
            border: 2px solid var(--main-green);
        }

        .cta-btn-secondary:hover {
            background: var(--green-light);
            transform: translateY(-2px);
        }

        @media (max-width: 1100px) {
            .main-navbar .nav-container {
                flex-wrap: wrap;
                justify-content: center;
            }

            .main-navbar .nav-links,
            .main-navbar .nav-user {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<nav class="main-navbar">
    <div class="nav-container">
        <a href="Home.php" class="nav-logo">
            <img src="assets/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>

        <ul class="nav-links">
            <li><a href="Home.php" class="active">Accueil</a></li>
            <li><a href="List-Produit.php">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="Communaute.php">Communauté</a></li>
        </ul>

        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Santé</a>
            <a href="#" class="nav-action">Profil</a>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="hero-content">
        <h1>Bienvenue sur HappyBite</h1>
        <p>Découvrez des produits délicieux, partagez vos recettes préférées et rejoignez notre communauté culinaire.</p>
        <div class="cta-buttons">
            <a href="List-Produit.php" class="cta-btn cta-btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Explorer les Produits
            </a>
            <a href="Communaute.php" class="cta-btn cta-btn-secondary">
                <i class="fas fa-users me-2"></i>Rejoindre la Communauté
            </a>
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
