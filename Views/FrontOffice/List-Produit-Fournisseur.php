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