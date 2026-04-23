<?php
declare(strict_types=1);

if (!isset($nav_active)) {
    $nav_active = '';
}

$nav_class = static function (string $key, string $current): string {
    return $key === $current ? ' nav-link-active' : '';
};

$nav_icon_class = static function (string $key, string $current): string {
    return $key === $current ? ' nav-icon-active' : '';
};
?>
<nav class="main-nav">
    <a class="nav-brand" href="Home.php" aria-label="HappyBite — accueil">
        <img class="nav-brand-logo" src="images/logo.png" alt="" width="76" height="76">
    </a>
    <div class="nav-links-wrap">
        <div class="nav-links">
            <a href="Home.php" class="nav-link<?php echo $nav_class('accueil', $nav_active); ?>">Accueil</a>
            <a href="List-Produit.php" class="nav-link<?php echo $nav_class('produits', $nav_active); ?>">Produits</a>
            <a href="List-Recette.php" class="nav-link<?php echo $nav_class('recettes', $nav_active); ?>">Recettes</a>
            <a href="#" class="nav-link<?php echo $nav_class('communaute', $nav_active); ?>">Communauté</a>
        </div>
    </div>
    <div class="nav-icons">
        <?php /* Icônes rapides (à droite) : accès direct Profile / Panier / Santé / Frigo. */ ?>
        <a href="frigo.php"
           class="nav-cart-link nav-icon-link<?php echo $nav_icon_class('frigo', $nav_active); ?>"
           aria-label="Frigo">
            <img class="nav-cart-img" src="images/frigo.png" alt="" width="40" height="40">
            <span class="nav-icon-label" aria-hidden="true">Frigo</span>
        </a>
        <a href="sante.php"
           class="nav-cart-link nav-icon-link<?php echo $nav_icon_class('sante', $nav_active); ?>"
           aria-label="Santé">
            <img class="nav-cart-img" src="images/sante.png" alt="" width="40" height="40">
            <span class="nav-icon-label" aria-hidden="true">Santé</span>
        </a>
        <a href="panier.php"
           class="nav-cart-link nav-icon-link<?php echo $nav_icon_class('panier', $nav_active); ?>"
           aria-label="Panier">
            <img class="nav-cart-img" src="images/panier.png" alt="" width="40" height="40">
            <span class="nav-icon-label" aria-hidden="true">Panier</span>
        </a>
        <details class="nav-profile-dropdown<?php echo $nav_icon_class('profile', $nav_active); ?>">
            <summary class="nav-profile-trigger nav-icon-link" aria-label="Compte">
                <img class="nav-profile-img" src="images/profile.png" alt="" width="40" height="40">
                <span class="nav-icon-label" aria-hidden="true">Profil</span>
            </summary>
            <div class="nav-profile-menu">
                <a href="#" class="nav-profile-btn nav-profile-signup">S'inscrire</a>
                <a href="#" class="nav-profile-btn nav-profile-login">Se connecter</a>
            </div>
        </details>
    </div>
</nav>
