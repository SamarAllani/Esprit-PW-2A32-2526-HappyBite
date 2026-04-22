<?php
include '../../Controllers/ProduitController.php';

$produitController = new ProduitController();

$idFournisseur = 2; // temporaire

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du produit manquant.");
}

$idProduit = (int) $_GET['id'];

// si tu as ajouté cette méthode dans ProduitController, utilise-la
if (method_exists($produitController, 'getProduitDetailsByIdAndUtilisateur')) {
    $produit = $produitController->getProduitDetailsByIdAndUtilisateur($idProduit, $idFournisseur);
} else {
    $produit = $produitController->getProduitByIdAndUtilisateur($idProduit, $idFournisseur);
}

if (!$produit) {
    die("Produit introuvable ou accès refusé.");
}

$allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
$benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du produit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-success text-white rounded-top-4">
                    <h3 class="mb-0">Détail du produit</h3>
                </div>

                <div class="card-body p-4">
                    <div class="mb-4">
                        <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($produit['nom']); ?></h2>

                        <span class="badge bg-light text-dark fs-6">
                            <?php
                                echo htmlspecialchars(
                                    $produit['nom_categorie'] ?? ('Catégorie #' . $produit['id_categorie'])
                                );
                            ?>
                        </span>
                    </div>

                    <div class="mb-4 text-center">
                        <h5 class="fw-bold mb-3">Image du produit</h5>

                        <?php if (!empty($produit['image'])) { ?>
                            <img
                                src="/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                                style="max-width: 250px; max-height: 250px; object-fit: cover; border-radius: 16px;"
                            >
                        <?php } else { ?>
                            <p class="text-muted mb-0">Aucune image définie.</p>
                        <?php } ?>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <strong>Prix :</strong><br>
                                <span class="text-success fs-5 fw-bold">
                                    <?php echo htmlspecialchars($produit['prix']); ?> DT
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <strong>Calories :</strong><br>
                                <span class="fs-5">
                                    <?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">Allergènes / composants sensibles</h5>
                        <?php if (!empty($allergenes)) { ?>
                            <?php foreach ($allergenes as $item) { ?>
                                <span class="badge bg-danger me-1 mb-1">
                                    <?php echo htmlspecialchars($item); ?>
                                </span>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted mb-0">Aucun allergène précisé.</p>
                        <?php } ?>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">Bénéfices</h5>
                        <?php if (!empty($benefices)) { ?>
                            <?php foreach ($benefices as $item) { ?>
                                <span class="badge bg-success me-1 mb-1">
                                    <?php echo htmlspecialchars($item); ?>
                                </span>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted mb-0">Aucun bénéfice précisé.</p>
                        <?php } ?>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold">Informations supplémentaires</h5>
                        <p class="mb-0">
                            <strong>Date d'ajout :</strong>
                            <?php echo htmlspecialchars($produit['date_ajout']); ?>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="List-Produit-Fournisseur.php" class="btn btn-secondary rounded-pill">
                            Retour à la liste
                        </a>

                        <a href="Edit-Produit-Fournisseur.php?id=<?php echo $produit['id_produit']; ?>" class="btn btn-success rounded-pill">
                            Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>