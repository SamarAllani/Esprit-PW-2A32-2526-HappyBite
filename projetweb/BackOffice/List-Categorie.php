<?php
require_once __DIR__ . '/../Controllers/CategorieController.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$categorieController = new CategorieController();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $categorieController->deleteCategorie($id);
    }
    header('Location: List-Categorie.php');
    exit;
}

$motCle = trim($_GET['motCle'] ?? '');

if ($motCle !== '') {
    $categories = $categorieController->rechercherCategories($motCle);
} else {
    $categories = $categorieController->listCategories();
}

$imgModify = is_file(__DIR__ . '/images/modify.png') ? 'images/modify.png' : 'images/modify.svg';
$imgDelete = is_file(__DIR__ . '/images/delete.png') ? 'images/delete.png' : 'images/delete.svg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Liste des catégories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-bo">

<?php bo_layout_start('', 'categories'); ?>

        <div class="bo-page-head">
            <div>
                <h1 class="bo-title">Liste des catégories</h1>
                <p class="bo-subtitle">Gérez les catégories de vos produits</p>
            </div>
            <a href="Add-Categorie.php" class="bo-btn-primary">+ Ajouter une catégorie</a>
        </div>

        <section class="bo-panel" aria-label="Recherche">
            <form method="GET" action="">
                <div class="bo-form-row" style="grid-template-columns: 1fr auto;">
                    <div class="bo-field">
                        <label for="motCle">Rechercher une catégorie</label>
                        <input
                            type="text"
                            id="motCle"
                            name="motCle"
                            placeholder="Rechercher par nom..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>
                    <div class="bo-field bo-field-submit">
                        <button type="submit" class="bo-btn-primary">Rechercher</button>
                    </div>
                </div>
            </form>
        </section>

        <div class="bo-table-wrap">
            <?php if (empty($categories)) { ?>
                <p class="bo-empty">Aucune catégorie trouvée.</p>
            <?php } else { ?>
                <div class="bo-table-scroll">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 25%;">Nom</th>
                                <th style="width: 45%;">Description</th>
                                <th style="width: 20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $categorie) { ?>
                                <tr>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) $categorie->getIdCategorie()); ?></td>
                                    <td class="bo-td-left">
                                        <span class="bo-pill bo-pill--muted"><?php echo htmlspecialchars($categorie->getNom()); ?></span>
                                    </td>
                                    <td class="bo-td-left">
                                        <?php if (!empty($categorie->getDescription())) { ?>
                                            <?php echo htmlspecialchars($categorie->getDescription()); ?>
                                        <?php } else { ?>
                                            <span style="color: #888;">Aucune description</span>
                                        <?php } ?>
                                    </td>
                                    <td class="bo-td-center">
                                        <span class="bo-icon-actions">
                                            <a href="Edit-Categorie.php?id=<?php echo (int) $categorie->getIdCategorie(); ?>" title="Modifier" aria-label="Modifier">
                                                <img src="<?php echo htmlspecialchars($imgModify); ?>" alt="">
                                            </a>
                                            <a
                                                href="List-Categorie.php?delete=<?php echo (int) $categorie->getIdCategorie(); ?>"
                                                title="Supprimer"
                                                aria-label="Supprimer"
                                                onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');"
                                            >
                                                <img src="<?php echo htmlspecialchars($imgDelete); ?>" alt="">
                                            </a>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>

<?php bo_layout_end(); ?>

</body>
</html>
