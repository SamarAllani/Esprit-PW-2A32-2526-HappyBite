<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bo_require_admin.php';

$pages = [
    'accueil' => 'home.php',
    'utilisateur' => 'admin.php',
    'produits' => 'List-Produit.php',
    'categories' => 'List-Categorie.php',
    'recettes' => 'List-Recette.php',
    'commandes' => 'list-com-liv.php',
    'post' => 'list_posts.php',
    'sante' => 'affiche.php',
];

$active = isset($_GET['page']) ? (string) $_GET['page'] : 'accueil';
if (!isset($pages[$active])) {
    $active = 'accueil';
}

$iframeSrc = $pages[$active] . '?embed=1';

$logoSrc = is_file(dirname(__DIR__) . '/images/logo.png')
    ? '../FrontOffice/images/logo.png'
    : 'images/logo.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite - BackOffice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-bo-main">
<div class="page-bo">
    <aside class="bo-sidebar" aria-label="Menu principal">
        <a class="bo-sidebar-brand" href="main.php?page=accueil">
            <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="" class="bo-sidebar-logo" width="100" height="100">
        </a>
        <nav class="bo-sidebar-nav">
            <a href="main.php?page=accueil" class="bo-sidebar-link<?php echo $active === 'accueil' ? ' is-active' : ''; ?>" target="_self">Accueil</a>
            <a href="main.php?page=utilisateur" class="bo-sidebar-link<?php echo $active === 'utilisateur' ? ' is-active' : ''; ?>" target="_self">Gestion utilisateur</a>
            <a href="main.php?page=produits" class="bo-sidebar-link<?php echo $active === 'produits' ? ' is-active' : ''; ?>" target="_self">Gestion Produits</a>
            <a href="main.php?page=commandes" class="bo-sidebar-link<?php echo $active === 'commandes' ? ' is-active' : ''; ?>" target="_self">Gestion Commande</a>
            <a href="main.php?page=post" class="bo-sidebar-link<?php echo $active === 'post' ? ' is-active' : ''; ?>" target="_self">Gestion Post</a>
            <a href="main.php?page=sante" class="bo-sidebar-link<?php echo $active === 'sante' ? ' is-active' : ''; ?>" target="_self">Gestion Santé</a>
        </nav>
        <a href="logout.php" class="bo-sidebar-logout" target="_top">Se déconnecter</a>
    </aside>
    <main class="bo-main-frame-wrap">
        <iframe
            title="BackOffice Content"
            class="bo-main-frame"
            src="<?php echo htmlspecialchars($iframeSrc); ?>"
            name="bo-content-frame"
        ></iframe>
    </main>
</div>
</body>
</html>
