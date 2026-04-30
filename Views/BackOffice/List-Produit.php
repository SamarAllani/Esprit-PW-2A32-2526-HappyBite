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
        $titreExport = 'Liste des produits filtrés';
    } else {
        $produitsExport = $produitController->listProduits();
        $titreExport = 'Liste complète des produits';
    }

    $nomFichier = "HappyBite_Produits_" . date("Y-m-d_H-i") . ".xls";

    $logoPath = realpath(__DIR__ . '/../assets/images/logo.png');
    $logoPath = $logoPath ? str_replace("\\", "/", $logoPath) : '';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nomFichier\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF";
?>

<html>
<head>
<meta charset="UTF-8">
<style>
table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
.brand { background-color: #ffffff; border-bottom: 3px solid #2e7d32; height: 80px; }
.logo-cell { width: 120px; text-align: left; padding-left: 10px; }
.title-cell { font-size: 26px; font-weight: bold; color: #1b5e20; text-align: left; letter-spacing: 1px; }
.title { background-color: #e8f5e9; font-size: 20px; font-weight: bold; color: #2e7d32; text-align: center; height: 40px; border: 2px solid #2e7d32; }
.subtitle { background-color: #f1f8e9; color: #666; text-align: center; font-size: 12px; height: 28px; }
.header td { background-color: #2e7d32; color: white; font-weight: bold; text-align: center; border: 1px solid #1b5e20; }
td { border: 1px solid #a5d6a7; padding: 7px; vertical-align: middle; }
.center { text-align: center; }
.promo-oui { background-color: #fff3cd; color: #856404; font-weight: bold; text-align: center; }
.promo-non { background-color: #eeeeee; color: #555; text-align: center; }
.prix-promo { background-color: #ffe082; color: #5d4037; font-weight: bold; text-align: center; }
.allergene { background-color: #f8d7da; color: #842029; font-weight: bold; }
</style>
</head>

<body>
<table>
<tr class="brand">
    <td colspan="2" class="logo-cell">
        <?php if (!empty($logoPath)) { ?>
            <img src="file:///<?php echo $logoPath; ?>" width="70">
        <?php } ?>
    </td>
    <td colspan="9" class="title-cell">HappyBite</td>
</tr>

<tr><td colspan="11" class="title"><?php echo $titreExport; ?></td></tr>
<tr><td colspan="11" class="subtitle">Export généré le <?php echo date('d/m/Y à H:i'); ?></td></tr>

<tr class="header">
    <td>ID</td>
    <td>Nom</td>
    <td>Fournisseur</td>
    <td>Prix normal</td>
    <td>Promo</td>
    <td>Prix promo</td>
    <td>Calories</td>
    <td>Catégorie</td>
    <td>Allergènes</td>
    <td>Bénéfices</td>
    <td>Date ajout</td>
</tr>

<?php foreach ($produitsExport as $produit) { ?>
<?php
$isPromo = isset($produit['promo']) && $produit['promo'] !== null && $produit['promo'] !== '';
$hasAllergene = !empty($produit['allergene']) && strtolower(trim($produit['allergene'])) !== 'aucun';
?>
<tr>
    <td class="center"><?php echo htmlspecialchars($produit['id_produit'] ?? ''); ?></td>
    <td><?php echo htmlspecialchars($produit['nom'] ?? ''); ?></td>
    <td><?php echo htmlspecialchars($produit['nom_fournisseur'] ?? 'Non renseigné'); ?></td>
    <td class="center"><?php echo htmlspecialchars($produit['prix'] ?? ''); ?> DT</td>
    <td class="<?php echo $isPromo ? 'promo-oui' : 'promo-non'; ?>"><?php echo $isPromo ? 'Oui' : 'Non'; ?></td>
    <td class="<?php echo $isPromo ? 'prix-promo' : 'center'; ?>">
        <?php echo $isPromo ? htmlspecialchars($produit['promo']) . ' DT' : '-'; ?>
    </td>
    <td class="center"><?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?></td>
    <td class="center"><?php echo htmlspecialchars($produit['nom_categorie'] ?? ''); ?></td>
    <td class="<?php echo $hasAllergene ? 'allergene' : 'center'; ?>">
        <?php echo htmlspecialchars($produit['allergene'] ?? 'Aucun'); ?>
    </td>
    <td><?php echo htmlspecialchars($produit['benefices'] ?? 'Non précisé'); ?></td>
    <td class="center"><?php echo htmlspecialchars($produit['date_ajout'] ?? ''); ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>

<?php
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