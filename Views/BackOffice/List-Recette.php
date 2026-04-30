<?php
include '../../Controllers/RecetteController.php';

$recetteController = new RecetteController();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $recetteController->deleteRecette($id);
    }

    header('Location: List-Recette.php');
    exit;
}

$motCle = trim($_GET['motCle'] ?? '');

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
| - Si recherche active : export des résultats affichés
| - Sinon : export de toute la liste
| - Toutes les colonnes utiles sauf l'image
*/
if (isset($_GET['export_excel']) && $_GET['export_excel'] == '1') {

    if (!empty($motCle)) {
        $recettesExport = $recetteController->rechercherRecettes($motCle);
        $titreExport = 'Liste des recettes filtrées';
    } else {
        $recettesExport = $recetteController->listRecettes();
        $titreExport = 'Liste complète des recettes';
    }

    $nomFichier = "HappyBite_Recettes_" . date("Y-m-d_H-i") . ".xls";

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
table { border-collapse: collapse; width: 100%; font-family: Arial; font-size: 12px; }
.brand { background-color: #ffffff; border-bottom: 3px solid #2e7d32; height: 80px; }
.logo-cell { width: 120px; padding-left: 10px; }
.title-cell { font-size: 26px; font-weight: bold; color: #1b5e20; }
.title { background-color: #e8f5e9; font-size: 20px; font-weight: bold; color: #2e7d32; text-align: center; border: 2px solid #2e7d32; }
.subtitle { background-color: #f1f8e9; color: #666; text-align: center; font-size: 12px; }
.header td { background-color: #2e7d32; color: white; font-weight: bold; text-align: center; }
td { border: 1px solid #a5d6a7; padding: 7px; }
.center { text-align: center; }
.nom-col { width: 220px; font-weight: bold; }
.produits { background-color: #e8f5e9; color: #2e7d32; font-weight: bold; }
.cal-high { background-color: #f8d7da; color: #842029; font-weight: bold; text-align: center; }
.cal-medium { background-color: #fff3cd; color: #856404; text-align: center; }
.cal-low { background-color: #e8f5e9; color: #2e7d32; text-align: center; }
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
    <td colspan="2" class="title-cell">HappyBite</td>
</tr>

<tr><td colspan="4" class="title"><?php echo $titreExport; ?></td></tr>
<tr><td colspan="4" class="subtitle">Export généré le <?php echo date('d/m/Y à H:i'); ?></td></tr>

<tr class="header">
    <td>ID</td>
    <td>Nom</td>
    <td>Calories</td>
    <td>Produits</td>
</tr>

<?php foreach ($recettesExport as $recette) {
    $produitsRecette = $recetteController->getProduitsByRecette($recette['id_recette']);
    $nomsProduits = [];

    if (!empty($produitsRecette)) {
        foreach ($produitsRecette as $produit) {
            $nomsProduits[] = $produit['nom'];
        }
    }

    $cal = intval($recette['calories'] ?? 0);

    if ($cal > 500) {
        $calClass = 'cal-high';
    } elseif ($cal > 300) {
        $calClass = 'cal-medium';
    } else {
        $calClass = 'cal-low';
    }
?>
<tr>
    <td class="center"><?php echo htmlspecialchars($recette['id_recette']); ?></td>
    <td class="nom-col"><?php echo htmlspecialchars($recette['nom']); ?></td>
    <td class="<?php echo $calClass; ?>"><?php echo $cal; ?> cal</td>
    <td class="produits">
        <?php echo !empty($nomsProduits) ? htmlspecialchars(implode(', ', $nomsProduits)) : 'Aucun produit'; ?>
    </td>
</tr>
<?php } ?>
</table>
</body>
</html>

<?php
exit;
}
if (!empty($motCle)) {
    $recettes = $recetteController->rechercherRecettes($motCle);
} else {
    $recettes = $recetteController->listRecettes();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des recettes</title>
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
                <a href="List-Produit.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Produits
                </a>

                <a href="List-Recette.php" class="btn btn-success rounded-pill px-4 py-2">
                    Recettes
                </a>

                <a href="List-Categorie.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Catégories
                </a>

                <a href="List-Frigo-Back.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Frigo
                </a>

                <a href="Dashboard-Produit.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Dashboard
                </a>

            </div>
        </div>

        <div class="container py-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Liste des recettes</h2>
                    <p class="text-muted mb-0">Gérez les recettes du catalogue</p>
                </div>
                <a href="Add-Recette.php" class="btn btn-success rounded-pill px-4">
                    Ajouter une recette
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="motCle" class="form-label fw-semibold">Rechercher une recette</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="motCle"
                                    name="motCle"
                                    placeholder="Nom de la recette..."
                                    value="<?php echo htmlspecialchars($motCle); ?>"
                                >
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100 rounded-pill">
                                    Rechercher
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
                    <?php if (empty($recettes)) { ?>
                        <div class="alert alert-info mb-0 text-center">
                            Aucune recette trouvée.
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Image</th>
                                        <th>Calories</th>
                                        <th>Produits</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recettes as $recette) { ?>
                                        <?php $produitsRecette = $recetteController->getProduitsByRecette($recette['id_recette']); ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php echo htmlspecialchars($recette['id_recette']); ?>
                                            </td>

                                            <td>
                                                <strong><?php echo htmlspecialchars($recette['nom']); ?></strong>
                                            </td>

                                            <td class="text-center">
                                                <?php if (!empty($recette['image'])) { ?>
                                                    <img
                                                        src="/uploads/<?php echo htmlspecialchars($recette['image']); ?>"
                                                        alt="Image recette"
                                                        style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;"
                                                    >
                                                <?php } else { ?>
                                                    <span class="text-muted">Aucune</span>
                                                <?php } ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($recette['calories'] ?? 0); ?> cal
                                            </td>

                                            <td>
                                                <?php if (!empty($produitsRecette)) { ?>
                                                    <?php foreach ($produitsRecette as $produit) { ?>
                                                        <span class="badge bg-success me-1 mb-1">
                                                            <?php echo htmlspecialchars($produit['nom']); ?>
                                                        </span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">Aucun produit</span>
                                                <?php } ?>
                                            </td>

                                            <td class="text-center">
                                                <a href="Detail-Recette.php?id=<?php echo $recette['id_recette']; ?>" class="btn btn-info btn-sm me-1 mb-1">
                                                    Voir détail
                                                </a>

                                                <a href="Edit-Recette.php?id=<?php echo $recette['id_recette']; ?>" class="btn btn-warning btn-sm me-1 mb-1">
                                                    Modifier
                                                </a>

                                                <a
                                                    href="List-Recette.php?delete=<?php echo $recette['id_recette']; ?>"
                                                    class="btn btn-danger btn-sm mb-1"
                                                    onclick="return confirm('Voulez-vous vraiment supprimer cette recette ?');"
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