<?php
include '../../Controllers/ProduitController.php';
include '../../Controllers/CategorieController.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();

$idFournisseur = 2; // temporaire

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $idProduit = (int) $_GET['delete'];
    $produitController->deleteProduitByIdAndUtilisateur($idProduit, $idFournisseur);

    header("Location: List-Produit-Fournisseur.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$categories = $categorieController->listCategories();
$produits = $produitController->listProduitsByUtilisateur(
    $idFournisseur,
    $search,
    $idCategorie !== '' ? $idCategorie : null
);
if (isset($_GET['export_excel']) && $_GET['export_excel'] == '1') {

    $produitsExport = $produits;

    $titreExport = (!empty($search) || !empty($idCategorie))
        ? 'Mes produits filtrés'
        : 'Liste complète de mes produits';

    $nomFichier = "HappyBite_Mes_Produits_" . date("Y-m-d_H-i") . ".xls";

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
.title-cell { font-size: 26px; font-weight: bold; color: #1b5e20; letter-spacing: 1px; }
.title { background-color: #e8f5e9; font-size: 20px; font-weight: bold; color: #2e7d32; text-align: center; border: 2px solid #2e7d32; }
.subtitle { background-color: #f1f8e9; color: #666; text-align: center; font-size: 12px; }
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
    <td colspan="8" class="title-cell">HappyBite</td>
</tr>

<tr><td colspan="10" class="title"><?php echo $titreExport; ?></td></tr>
<tr><td colspan="10" class="subtitle">Export généré le <?php echo date('d/m/Y à H:i'); ?></td></tr>

<tr class="header">
    <td>ID</td>
    <td>Nom</td>
    <td>Catégorie</td>
    <td>Prix normal</td>
    <td>Promo</td>
    <td>Prix promo</td>
    <td>Calories</td>
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
    <td><strong><?php echo htmlspecialchars($produit['nom'] ?? ''); ?></strong></td>
    <td class="center"><?php echo htmlspecialchars($produit['nom_categorie'] ?? 'Sans catégorie'); ?></td>
    <td class="center"><?php echo htmlspecialchars($produit['prix'] ?? ''); ?> DT</td>
    <td class="<?php echo $isPromo ? 'promo-oui' : 'promo-non'; ?>"><?php echo $isPromo ? 'Oui' : 'Non'; ?></td>
    <td class="<?php echo $isPromo ? 'prix-promo' : 'center'; ?>">
        <?php echo $isPromo ? htmlspecialchars($produit['promo']) . ' DT' : '-'; ?>
    </td>
    <td class="center"><?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?></td>
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/style.css">

    <style>
        .promo-card {
            border: 2px solid #f0ad4e !important;
            background: #fff8e1 !important;
        }

        .promo-badge {
            display: inline-block;
            background: #f0ad4e;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
        }

        .promo-old-price {
            color: #8c8c8c;
            text-decoration: line-through;
            margin-right: 8px;
        }

        .promo-new-price {
            color: #d97706;
            font-weight: 800;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mes produits</h2>
        <a href="Add-Produit-Fournisseur.php" class="btn btn-success rounded-pill px-4">
            Ajouter un produit
        </a>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-5">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Rechercher par nom ou taper promo..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
        </div>

        <div class="col-md-4">
            <select name="id_categorie" class="form-select">
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

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-success w-100">Filtrer</button>
        </div>
    </form>

    <div class="row">
        <?php if (!empty($produits)) { ?>
            <?php foreach ($produits as $produit) { ?>
                <?php
                    $allergenes = !empty($produit['allergene'])
                        ? array_filter(array_map('trim', explode(',', $produit['allergene'])))
                        : [];
                    $isPromo = isset($produit['promo']) && $produit['promo'] !== null && $produit['promo'] !== '';
                ?>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm rounded-4 <?php echo $isPromo ? 'promo-card' : 'border-0'; ?>">
                        <?php if (!empty($produit['image'])) { ?>
                            <img
                                src="/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                class="card-img-top rounded-top-4"
                                alt="Produit"
                                style="height: 230px; object-fit: cover;"
                            >
                        <?php } else { ?>
                            <div
                                class="d-flex align-items-center justify-content-center bg-light text-muted rounded-top-4"
                                style="height: 230px;"
                            >
                                Aucune image
                            </div>
                        <?php } ?>

                        <div class="card-body">
                            <?php if ($isPromo) { ?>
                                <div class="mb-2">
                                    <span class="promo-badge">En promo</span>
                                </div>
                            <?php } ?>

                            <h5 class="card-title fw-bold mb-2">
                                <?php echo htmlspecialchars($produit['nom']); ?>
                            </h5>

                            <p class="mb-2">
                                <span class="badge bg-light text-dark">
                                    <?php echo htmlspecialchars($produit['nom_categorie'] ?? 'Sans catégorie'); ?>
                                </span>
                            </p>

                            <p class="card-text mb-3">
                                <strong>Prix :</strong>
                                <?php if ($isPromo) { ?>
                                    <span class="promo-old-price">
                                        <?php echo htmlspecialchars($produit['prix']); ?> DT
                                    </span>
                                    <span class="promo-new-price">
                                        <?php echo htmlspecialchars($produit['promo']); ?> DT
                                    </span>
                                <?php } else { ?>
                                    <span class="text-success fw-bold">
                                        <?php echo htmlspecialchars($produit['prix']); ?> DT
                                    </span>
                                <?php } ?>
                            </p>

                            <div class="mb-2">
                                <strong>Allergènes :</strong>
                            </div>

                            <div class="mb-3">
                                <?php if (!empty($allergenes)) { ?>
                                    <?php foreach ($allergenes as $item) { ?>
                                        <span class="badge bg-danger me-1 mb-1">
                                            <?php echo htmlspecialchars($item); ?>
                                        </span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="text-muted">Aucun allergène</span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between">
                                <a
                                    href="Detail-Produit-Fournisseur.php?id=<?php echo $produit['id_produit']; ?>"
                                    class="btn btn-success btn-sm rounded-pill"
                                >
                                    Voir détail
                                </a>

                                <a
                                    href="Edit-Produit-Fournisseur.php?id=<?php echo $produit['id_produit']; ?>"
                                    class="btn btn-warning btn-sm rounded-pill"
                                >
                                    Modifier
                                </a>

                                <a
                                    href="List-Produit-Fournisseur.php?delete=<?php echo $produit['id_produit']; ?>"
                                    class="btn btn-danger btn-sm rounded-pill"
                                    onclick="return confirm('Voulez-vous supprimer ce produit ?');"
                                >
                                    Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="col-12">
                <div class="alert alert-info rounded-4">
                    Aucun produit trouvé.
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>