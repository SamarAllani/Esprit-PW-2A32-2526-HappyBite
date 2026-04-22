<?php
include '../../Controllers/RecetteController.php';

$recetteController = new RecetteController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de la recette manquant.");
}

$id = intval($_GET['id']);
$recette = $recetteController->showRecetteDetails($id);

if (!$recette) {
    die("Recette introuvable.");
}

$produitsRecette = $recetteController->getProduitsByRecette($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail recette</title>
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
            </div>
        </div>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0 rounded-4">
                        <div class="card-header text-white rounded-top-4" style="background-color: #2e7d32;">
                            <h3 class="mb-0">Détail de la recette</h3>
                        </div>

                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($recette['nom']); ?></h2>
                            </div>

                            <div class="mb-4 text-center">
                                <h5 class="fw-bold mb-3">Image de la recette</h5>
                                <?php if (!empty($recette['image'])) { ?>
                                    <img
                                        src="/uploads/<?php echo htmlspecialchars($recette['image']); ?>"
                                        alt="<?php echo htmlspecialchars($recette['nom']); ?>"
                                        style="max-width: 280px; max-height: 280px; object-fit: cover; border-radius: 16px;"
                                    >
                                <?php } else { ?>
                                    <p class="text-muted mb-0">Aucune image définie.</p>
                                <?php } ?>
                            </div>

                            <div class="mb-4">
                                <strong>Description :</strong><br>
                                <?php
                                if (trim($recette['description'] ?? '') !== '') {
                                    echo nl2br(htmlspecialchars($recette['description']));
                                } else {
                                    echo '<span class="text-muted">Aucune description</span>';
                                }
                                ?>
                            </div>

                            <div class="mb-4">
                                <div class="p-3 bg-light rounded-3">
                                    <strong>Calories totales :</strong><br>
                                    <span class="text-success fs-5 fw-bold">
                                        <?php echo htmlspecialchars($recette['calories'] ?? 0); ?> cal
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="fw-bold">Produits composant la recette</h5>

                                <?php if (!empty($produitsRecette)) { ?>
                                    <div class="row">
                                        <?php foreach ($produitsRecette as $produit) { ?>
                                            <?php
                                            $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                                            $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                                            ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($produit['nom']); ?></h6>

                                                    <p class="mb-2">
                                                        <strong>Prix :</strong>
                                                        <?php echo htmlspecialchars($produit['prix']); ?> DT
                                                    </p>

                                                    <p class="mb-2">
                                                        <strong>Calories :</strong>
                                                        <?php echo htmlspecialchars($produit['calories'] ?? 0); ?> cal
                                                    </p>

                                                    <div class="mb-2">
                                                        <strong>Allergènes :</strong><br>
                                                        <?php if (!empty($allergenes)) { ?>
                                                            <?php foreach ($allergenes as $item) { ?>
                                                                <span class="badge bg-danger me-1 mb-1">
                                                                    <?php echo htmlspecialchars($item); ?>
                                                                </span>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <span class="text-muted">Aucun</span>
                                                        <?php } ?>
                                                    </div>

                                                    <div>
                                                        <strong>Bénéfices :</strong><br>
                                                        <?php if (!empty($benefices)) { ?>
                                                            <?php foreach ($benefices as $item) { ?>
                                                                <span class="badge bg-success me-1 mb-1">
                                                                    <?php echo htmlspecialchars($item); ?>
                                                                </span>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <span class="text-muted">Non précisé</span>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <p class="text-muted mb-0">Aucun produit associé à cette recette.</p>
                                <?php } ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="List-Recette.php" class="btn btn-secondary rounded-pill px-4">
                                    Retour
                                </a>

                                <a href="Edit-Recette.php?id=<?php echo $recette['id_recette']; ?>" class="btn btn-warning rounded-pill px-4">
                                    Modifier
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>