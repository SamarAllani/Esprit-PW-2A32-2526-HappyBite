<?php
require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/../Controllers/CategorieController.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $produitController->deleteProduit($id);
    }
    header('Location: List-Produit.php');
    exit;
}

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$categories = $categorieController->listCategories();

if ($motCle !== '' || $idCategorie !== '') {
    $produits = $produitController->rechercherProduits($motCle, $idCategorie);
} else {
    $produits = $produitController->listProduits();
}

$imgModify = is_file(__DIR__ . '/images/modify.png') ? 'images/modify.png' : 'images/modify.svg';
$imgDelete = is_file(__DIR__ . '/images/delete.png') ? 'images/delete.png' : 'images/delete.svg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Liste des produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-bo">

<?php bo_layout_start('produit', 'produits'); ?>

        <div class="bo-page-head">
            <div>
                <h1 class="bo-title">Liste des produits</h1>
                <p class="bo-subtitle">Gérez les produits du catalogue</p>
            </div>
            <a href="Add-Produit.php" class="bo-btn-primary">+ Ajouter un produit</a>
        </div>

        <section class="bo-panel" aria-label="Filtres">
            <form method="GET" action="">
                <div class="bo-form-row">
                    <div class="bo-field">
                        <label for="motCle">Rechercher un produit</label>
                        <input
                            type="text"
                            id="motCle"
                            name="motCle"
                            placeholder="Nom du produit..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>
                    <div class="bo-field">
                        <label for="id_categorie">Filtrer par catégorie</label>
                        <select id="id_categorie" name="id_categorie">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $categorie) { ?>
                                <option
                                    value="<?php echo (int) $categorie->getIdCategorie(); ?>"
                                    <?php echo ($idCategorie == $categorie->getIdCategorie()) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($categorie->getNom()); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="bo-field bo-field-submit">
                        <button type="submit" class="bo-btn-primary">Filtrer</button>
                    </div>
                </div>
            </form>
        </section>

        <div class="bo-table-wrap">
            <?php if (empty($produits)) { ?>
                <p class="bo-empty">Aucun produit trouvé.</p>
            <?php } else { ?>
                <div class="bo-table-scroll">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prix</th>
                                <th>Image</th>
                                <th>Calories</th>
                                <th>Catégorie</th>
                                <th>Allergènes</th>
                                <th>Bénéfices</th>
                                <th>Date ajout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $produit) { ?>
                                <?php
                                $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                                $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                                ?>
                                <tr>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) $produit['id_produit']); ?></td>
                                    <td class="bo-td-left"><strong><?php echo htmlspecialchars($produit['nom']); ?></strong></td>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) $produit['prix']); ?> DT</td>
                                    <td class="bo-td-center">
                                        <?php if (!empty($produit['image'])) { ?>
                                            <img
                                                src="../uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                                alt=""
                                                style="width: 56px; height: 56px; object-fit: cover; border-radius: 10px;"
                                            >
                                        <?php } else { ?>
                                            <span class="bo-pill bo-pill--muted">Aucune</span>
                                        <?php } ?>
                                    </td>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) ($produit['calories'] ?? 'Non défini')); ?></td>
                                    <td class="bo-td-center">
                                        <span class="bo-pill bo-pill--muted"><?php echo htmlspecialchars($produit['nom_categorie']); ?></span>
                                    </td>
                                    <td class="bo-td-left">
                                        <?php if (!empty($allergenes)) { ?>
                                            <?php foreach ($allergenes as $item) { ?>
                                                <span class="bo-pill bo-pill--danger"><?php echo htmlspecialchars($item); ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span style="color: #888;">Aucun</span>
                                        <?php } ?>
                                    </td>
                                    <td class="bo-td-left">
                                        <?php if (!empty($benefices)) { ?>
                                            <?php foreach ($benefices as $item) { ?>
                                                <span class="bo-pill bo-pill--success"><?php echo htmlspecialchars($item); ?></span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span style="color: #888;">Non précisé</span>
                                        <?php } ?>
                                    </td>
                                    <td class="bo-td-center"><?php echo htmlspecialchars((string) $produit['date_ajout']); ?></td>
                                    <td class="bo-td-center">
                                        <span class="bo-icon-actions">
                                            <a href="Edit-Produit.php?id=<?php echo (int) $produit['id_produit']; ?>" title="Modifier" aria-label="Modifier">
                                                <img src="<?php echo htmlspecialchars($imgModify); ?>" alt="">
                                            </a>
                                            <a
                                                href="List-Produit.php?delete=<?php echo (int) $produit['id_produit']; ?>"
                                                title="Supprimer"
                                                aria-label="Supprimer"
                                                onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');"
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
