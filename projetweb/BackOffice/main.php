<?php
declare(strict_types=1);

$pages = [
    'produits' => 'List-Produit.php',
    'categories' => 'List-Categorie.php',
    'recettes' => 'List-Recette.php',
    'commandes' => 'list-com-liv.php',
];

$active = isset($_GET['page']) ? (string) $_GET['page'] : 'produits';
if (!isset($pages[$active])) {
    $active = 'produits';
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
        <a class="bo-sidebar-brand" href="main.php?page=produits">
            <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="" class="bo-sidebar-logo" width="100" height="100">
        </a>
        <nav class="bo-sidebar-nav">
            <a href="main.php?page=produits" class="bo-sidebar-link<?php echo $active === 'produits' ? ' is-active' : ''; ?>" target="_self">Produits</a>
            <a href="main.php?page=categories" class="bo-sidebar-link<?php echo $active === 'categories' ? ' is-active' : ''; ?>" target="_self">Categories</a>
            <a href="main.php?page=recettes" class="bo-sidebar-link<?php echo $active === 'recettes' ? ' is-active' : ''; ?>" target="_self">Recettes</a>
            <a href="main.php?page=commandes" class="bo-sidebar-link<?php echo $active === 'commandes' ? ' is-active' : ''; ?>" target="_self">Commande &amp; livraison</a>
        </nav>
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
