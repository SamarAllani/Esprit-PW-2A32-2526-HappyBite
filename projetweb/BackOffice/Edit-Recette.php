<?php
require_once __DIR__ . '/../Controllers/RecetteController.php';
require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/../Models/Recette.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$error = "";

$recetteController = new RecetteController();
$produitController = new ProduitController();

$produits = $produitController->listProduits();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $produitsSelectionnes = $_POST['produits'] ?? [];

    if (empty($nom)) {
        $error = "Le nom de la recette est obligatoire.";
    } elseif (strlen($nom) < 2) {
        $error = "Le nom de la recette doit contenir au moins 2 caractères.";
    } elseif (empty($description)) {
        $error = "La description est obligatoire.";
    } elseif (empty($produitsSelectionnes)) {
        $error = "Veuillez sélectionner au moins un produit.";
    } else {
        $calories = $recetteController->calculerCaloriesRecette($produitsSelectionnes);

        $recette = new Recette($nom, $description, $calories);
        $idRecette = $recetteController->addRecette($recette);

        if ($idRecette) {
            $recetteController->ajouterProduitsRecette($idRecette, $produitsSelectionnes);
            header('Location: List-Recette.php');
            exit;
        } else {
            $error = "Erreur lors de l'ajout de la recette.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une recette</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php bo_layout_start('produit'); ?>

<div class="container py-0" style="max-width: 980px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-success text-white rounded-top-4">
                    <h3 class="mb-0">Ajouter une recette</h3>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="nom" class="form-label fw-semibold">Nom de la recette</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Produits de la recette</label>
                            <?php if (empty($produits)) { ?>
                                <div class="alert alert-info">Aucun produit disponible.</div>
                            <?php } else { ?>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($produits as $produit) { ?>
                                        <div class="form-check mb-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="produits[]"
                                                value="<?php echo $produit['id_produit']; ?>"
                                                id="produit_<?php echo $produit['id_produit']; ?>"
                                                <?php echo (isset($_POST['produits']) && in_array($produit['id_produit'], $_POST['produits'])) ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="produit_<?php echo $produit['id_produit']; ?>">
                                                <?php echo htmlspecialchars($produit['nom']); ?>
                                                <span class="text-muted">
                                                    (<?php echo htmlspecialchars($produit['calories'] ?? 0); ?> cal)
                                                </span>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="List-Recette.php" class="btn btn-secondary rounded-pill px-4">Retour</a>
                            <button type="submit" class="btn btn-success rounded-pill px-4">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<?php bo_layout_end(); ?>
</body>
</html>