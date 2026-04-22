<?php
include '../../Controllers/ProduitController.php';
include '../../Controllers/CategorieController.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $produitController->deleteProduit($id);
    }

    header('Location: List-Produit.php');
    exit;
}

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$categories = $categorieController->listCategories();

if (isset($_GET['export_excel']) && $_GET['export_excel'] == '1') {
    if (!empty($motCle) || !empty($idCategorie)) {
        $produitsExport = $produitController->rechercherProduits($motCle, $idCategorie);
        $nomFichier = 'produits_filtres.xls';
    } else {
        $produitsExport = $produitController->listProduits();
        $nomFichier = 'produits.xls';
    }

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nomFichier\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF";

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Nom</th>";
    echo "<th>Fournisseur</th>";
    echo "<th>Prix normal (DT)</th>";
    echo "<th>Promo</th>";
    echo "<th>Prix promo (DT)</th>";
    echo "<th>Calories</th>";
    echo "<th>Catégorie</th>";
    echo "<th>Allergènes</th>";
    echo "<th>Bénéfices</th>";
    echo "<th>Date ajout</th>";
    echo "</tr>";

    foreach ($produitsExport as $produit) {
        $isPromo = isset($produit['promo']) && $produit['promo'] !== null && $produit['promo'] !== '';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($produit['id_produit'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($produit['nom'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($produit['nom_fournisseur'] ?? 'Non renseigné') . "</td>";
        echo "<td>" . htmlspecialchars($produit['prix'] ?? '') . "</td>";
        echo "<td>" . ($isPromo ? 'Oui' : 'Non') . "</td>";
        echo "<td>" . ($isPromo ? htmlspecialchars($produit['promo']) : '') . "</td>";
        echo "<td>" . htmlspecialchars($produit['calories'] ?? 'Non défini') . "</td>";
        echo "<td>" . htmlspecialchars($produit['nom_categorie'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($produit['allergene'] ?? 'Aucun') . "</td>";
        echo "<td>" . htmlspecialchars($produit['benefices'] ?? 'Non précisé') . "</td>";
        echo "<td>" . htmlspecialchars($produit['date_ajout'] ?? '') . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
}

if (!empty($motCle) || !empty($idCategorie)) {
    $produits = $produitController->rechercherProduits($motCle, $idCategorie);
} else {
    $produits = $produitController->listProduits();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="#" class="admin-logo">
                <img src="../assets/images/logo.png" alt="HappyBite">
                <span>HappyBite</span>
            </a>
        </div>

        <nav class="admin-main-menu">
            <a href="#" class="admin-main-link active">Produit</a>
            <a href="#" class="admin-main-link">Communauté</a>
            <a href="#" class="admin-main-link">Post</a>
            <a href="#" class="admin-main-link">Utilisateur</a>
            <a href="#" class="admin-main-link">Santé</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="container mt-4">
            <div class="d-flex justify-content-end flex-wrap gap-3">
                <a href="List-Produit.php" class="btn btn-success rounded-pill px-4 py-2">Produits</a>
                <a href="List-Recette.php" class="btn btn-outline-success rounded-pill px-4 py-2">Recettes</a>
                <a href="List-Categorie.php" class="btn btn-outline-success rounded-pill px-4 py-2">Catégories</a>
                <a href="List-Frigo-Back.php" class="btn btn-outline-success rounded-pill px-4 py-2">Frigo</a>
                <a href="Dashboard-Produit.php" class="btn btn-outline-success rounded-pill px-4 py-2">Dashboard</a>
            </div>
        </div>

        <div class="container py-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Liste des produits</h2>
                    <p class="text-muted mb-0">Gérez les produits du catalogue</p>
                </div>
                <a href="Add-Produit.php" class="btn btn-success rounded-pill px-4">
                    Ajouter un produit
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="motCle" class="form-label fw-semibold">Rechercher un produit</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="motCle"
                                    name="motCle"
                                    placeholder="Nom du produit, fournisseur ou promo..."
                                    value="<?php echo htmlspecialchars($motCle); ?>"
                                >
                            </div>

                            <div class="col-md-4">
                                <label for="id_categorie" class="form-label fw-semibold">Filtrer par catégorie</label>
                                <select class="form-select" id="id_categorie" name="id_categorie">
                                    <option value="">-- Toutes les catégories --</option>
                                    <?php foreach ($categories as $categorie) { ?>
                                        <option
                                            value="<?php echo $categorie->getIdCategorie(); ?>"
                                            <?php echo ($idCategorie == $categorie->getIdCategorie()) ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($categorie->getNom()); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100 rounded-pill">
                                    Filtrer
                                </button>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="export_excel" value="1" class="btn btn-outline-success w-100 rounded-pill">
                                    Exporter Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body">
                    <?php if (empty($produits)) { ?>
                        <div class="alert alert-info mb-0 text-center">
                            Aucun produit trouvé.
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Fournisseur</th>
                                        <th>Prix normal</th>
                                        <th>Promo</th>
                                        <th>Prix promo</th>
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
                                            $isPromo = isset($produit['promo']) && $produit['promo'] !== null && $produit['promo'] !== '';
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo htmlspecialchars($produit['id_produit']); ?></td>

                                            <td><strong><?php echo htmlspecialchars($produit['nom']); ?></strong></td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($produit['nom_fournisseur'] ?? 'Non renseigné'); ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($produit['prix']); ?> DT
                                            </td>

                                            <td class="text-center">
                                                <?php if ($isPromo) { ?>
                                                    <span class="badge bg-warning text-dark">Oui</span>
                                                <?php } else { ?>
                                                    <span class="badge bg-secondary">Non</span>
                                                <?php } ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo $isPromo ? htmlspecialchars($produit['promo']) . ' DT' : '-'; ?>
                                            </td>

                                            <td class="text-center">
                                                <?php if (!empty($produit['image'])) { ?>
                                                    <img
                                                        src="/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                                        alt="Image produit"
                                                        style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;"
                                                    >
                                                <?php } else { ?>
                                                    <span class="text-muted">Aucune</span>
                                                <?php } ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($produit['nom_categorie']); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if (!empty($allergenes)) { ?>
                                                    <?php foreach ($allergenes as $item) { ?>
                                                        <span class="badge bg-danger me-1 mb-1">
                                                            <?php echo htmlspecialchars($item); ?>
                                                        </span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">Aucun</span>
                                                <?php } ?>
                                            </td>

                                            <td>
                                                <?php if (!empty($benefices)) { ?>
                                                    <?php foreach ($benefices as $item) { ?>
                                                        <span class="badge bg-success me-1 mb-1">
                                                            <?php echo htmlspecialchars($item); ?>
                                                        </span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">Non précisé</span>
                                                <?php } ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($produit['date_ajout']); ?>
                                            </td>

                                            <td class="text-center">
                                                <a href="Edit-Produit.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-warning btn-sm me-1 mb-1">
                                                    Modifier
                                                </a>

                                                <a
                                                    href="List-Produit.php?delete=<?php echo $produit['id_produit']; ?>"
                                                    class="btn btn-danger btn-sm mb-1"
                                                    onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');"
                                                >
                                                    Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>