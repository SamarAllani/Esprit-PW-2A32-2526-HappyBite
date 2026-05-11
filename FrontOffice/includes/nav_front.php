<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nav_logged_in = !empty($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true);

if (!isset($nav_active)) {
    $nav_active = '';
}

$nav_class = static function (string $key, string $current): string {
    return $key === $current ? ' nav-link-active' : '';
};

$nav_icon_class = static function (string $key, string $current): string {
    return $key === $current ? ' nav-icon-active' : '';
};

$nav_profile_img_src = 'images/profile.png';
$nav_user_display_name = '';
$nav_user_email = '';

if ($nav_logged_in) {
    $nav_user_display_name = trim((string) ($_SESSION['user_nom'] ?? '') . ' ' . (string) ($_SESSION['user_prenom'] ?? ''));
    $nav_user_email = (string) ($_SESSION['user_email'] ?? '');
    if ($nav_user_display_name === '') {
        $nav_user_display_name = 'Membre';
    }
    try {
        require_once __DIR__ . '/../../config/Database.php';
        require_once __DIR__ . '/../../Controllers/UtilisateurPhotoSql.php';
        $nav_uid = (int) ($_SESSION['user_id'] ?? 0);
        $nav_rel = utilisateur_fetch_profile_relative_path(Database::getConnection(), $nav_uid);
        $nav_custom_src = utilisateur_nav_profile_img_src($nav_rel);
        if ($nav_custom_src !== null) {
            $nav_profile_img_src = $nav_custom_src;
        }
    } catch (Throwable $e) {
        // garder l’icône par défaut
    }
}
?>
<style>
        /* Hover des liens texte (Accueil, Produits, …) — main-nav */
        .main-nav .nav-link:hover {
            color: var(--hb-forest-mid);
            border-bottom-color: rgba(37, 107, 45, 0.4);
        }

        .main-nav .nav-link.nav-link-active:hover {
            color: var(--hb-forest-mid);
            border-bottom-color: var(--hb-forest);
        }

        /* Déconnexion rouge partout (pages avec style-original-views ou sans css/style.css). */
        .nav-profile-logout {
            background-color: #b91c1c !important;
            color: #fff !important;
            border: 2px solid #991b1b !important;
        }

        .nav-profile-logout:hover {
            background-color: #991b1b !important;
            filter: brightness(1.05);
        }

        .nav-profile-userblock {
            padding: 4px 6px 10px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e8ecf0;
            text-align: left;
        }

        .nav-profile-name {
            font-weight: 700;
            font-size: 0.88rem;
            color: #2C7E34;
            line-height: 1.35;
            word-break: break-word;
        }

        .nav-profile-email {
            margin-top: 4px;
            font-size: 0.78rem;
            color: #5c6d66;
            line-height: 1.35;
            word-break: break-all;
        }

        .nav-profile-img--photo {
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(44, 126, 52, 0.35);
        }

        .main-nav .nav-profile-menu {
            min-width: 220px;
        }
</style>
<nav class="main-nav">
    <a class="nav-brand" href="Home.php" aria-label="HappyBite — accueil">
        <img class="nav-brand-logo" src="images/logo.png" alt="" width="76" height="76">
    </a>
    <div class="nav-links-wrap">
        <div class="nav-links">
            <a href="Home.php" class="nav-link<?php echo $nav_class('accueil', $nav_active); ?>">Accueil</a>
            <a href="List-Produit.php" class="nav-link<?php echo $nav_class('produits', $nav_active); ?>">Produits</a>
            <a href="List-Recette.php" class="nav-link<?php echo $nav_class('recettes', $nav_active); ?>">Recettes</a>
            <a href="communaute.php" class="nav-link<?php echo $nav_class('communaute', $nav_active); ?>">Communauté</a>
        </div>
    </div>
    <div class="nav-icons">
        <?php /* Icônes rapides (à droite) : accès direct Profile / Panier / Santé / Frigo. */ ?>
        <a href="List-Frigo.php"
           class="nav-cart-link nav-icon-link<?php echo $nav_icon_class('frigo', $nav_active); ?>"
           aria-label="Frigo">
            <img class="nav-cart-img" src="images/frigo.png" alt="" width="40" height="40">
            <span class="nav-icon-label" aria-hidden="true">Frigo</span>
        </a>
        <a href="user_health_space.php"
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
                <img class="nav-profile-img<?php echo $nav_profile_img_src !== 'images/profile.png' ? ' nav-profile-img--photo' : ''; ?>"
                     src="<?php echo htmlspecialchars($nav_profile_img_src, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="" width="40" height="40">
                <span class="nav-icon-label" aria-hidden="true">Profil</span>
            </summary>
            <div class="nav-profile-menu">
                <?php if (!$nav_logged_in): ?>
                    <a href="auth/register.php" class="nav-profile-btn nav-profile-signup">S'inscrire</a>
                    <a href="auth/login.php" class="nav-profile-btn nav-profile-login">Se connecter</a>
                <?php else: ?>
                    <div class="nav-profile-userblock">
                        <div class="nav-profile-name"><?php echo htmlspecialchars($nav_user_display_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php if ($nav_user_email !== ''): ?>
                            <div class="nav-profile-email"><?php echo htmlspecialchars($nav_user_email, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                    </div>
                    <a href="../Controllers/AuthProcess.php?action=logout" class="nav-profile-btn nav-profile-logout">Se déconnecter</a>
                <?php endif; ?>
            </div>
        </details>
    </div>
</nav>
<?php include_once __DIR__ . '/../Ai.php'; ?>
