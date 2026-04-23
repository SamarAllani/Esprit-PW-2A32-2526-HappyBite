<?php
require_once __DIR__ . '/../Controllers/RecetteController.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$recetteController = new RecetteController();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $recetteController->deleteRecette($id);
    }
    header('Location: List-Recette.php');
    exit;
}

$motCle = trim($_GET['motCle'] ?? '');

if ($motCle !== '') {
    $recettes = $recetteController->rechercherRecettes($motCle);
} else {
    $recettes = $recetteController->listRecettes();
}

$imgModify = is_file(__DIR__ . '/images/modify.png') ? 'images/modify.png' : 'images/modify.svg';
$imgDelete = is_file(__DIR__ . '/images/delete.png') ? 'images/delete.png' : 'images/delete.svg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Liste des recettes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-bo">

<?php bo_layout_start('', 'recettes'); ?>

        <div class="bo-page-head">
            <div>
                <h1 class="bo-title">Liste des recettes</h1>
                <p class="bo-subtitle">Gérez les recettes du catalogue</p>
            </div>
            <a href="Add-Recette.php" class="bo-btn-primary">+ Ajouter une recette</a>
        </div>

        <section class="bo-panel" aria-label="Recherche">
            <form method="GET" action="">
                <div class="bo-form-row" style="grid-template-columns: 1fr auto;">
                    <div class="bo-field">
                        <label for="motCle">Rechercher une recette</label>
                        <input
                            type="text"
                            id="motCle"
                            name="motCle"
                            placeholder="Nom de la recette..."
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
            <?php if (empty($recettes)) { ?>
                <p class="bo-empty">Aucune recette trouvée.</p>
            <?php } else { ?>
                <div class="bo-table-scroll">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Calories</th>
                                <th>Produits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recettes as $recette) { ?>
                                <?php $produitsRecette = $recetteController->getProduitsByRecette((int) $recette['id_recette']); ?>
                                <tr>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) $recette['id_recette']); ?></td>
                                    <td class="bo-td-left"><strong><?php echo htmlspecialchars($recette['nom']); ?></strong></td>
                                    <td class="bo-td-left"><?php echo htmlspecialchars($recette['description']); ?></td>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) ($recette['calories'] ?? 0)); ?> cal</td>
                                    <td class="bo-td-left">
                                        <?php if (!empty($produitsRecette)) { ?>
                                            <?php foreach ($produitsRecette as $produit) { ?>
                                                <span class="bo-pill bo-pill--success"><?php echo htmlspecialchars($produit['nom']); ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span style="color: #888;">Aucun produit</span>
                                        <?php } ?>
                                    </td>
                                    <td class="bo-td-center">
                                        <span class="bo-icon-actions">
                                            <a href="Edit-Recette.php?id=<?php echo (int) $recette['id_recette']; ?>" title="Modifier" aria-label="Modifier">
                                                <img src="<?php echo htmlspecialchars($imgModify); ?>" alt="">
                                            </a>
                                            <a
                                                href="List-Recette.php?delete=<?php echo (int) $recette['id_recette']; ?>"
                                                title="Supprimer"
                                                aria-label="Supprimer"
                                                onclick="return confirm('Voulez-vous vraiment supprimer cette recette ?');"
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
