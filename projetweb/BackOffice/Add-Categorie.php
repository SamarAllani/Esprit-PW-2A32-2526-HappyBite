<?php
require_once __DIR__ . '/../Controllers/CategorieController.php';
require_once __DIR__ . '/../Models/Categorie.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$error = "";

$categorieController = new CategorieController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) {
        $error = "Le nom de la catégorie est obligatoire.";
    } elseif (strlen($nom) < 2) {
        $error = "Le nom de la catégorie doit contenir au moins 2 caractères.";
    } else {
        $categorie = new Categorie($nom, $description);
        $categorieController->addCategorie($categorie);

        header('Location: List-Categorie.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une catégorie</title>
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
                    <h3 class="mb-0">Ajouter une catégorie</h3>
                </div>

                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Ajoutez une nouvelle catégorie pour mieux organiser vos produits.
                    </p>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="nom" class="form-label fw-semibold">Nom de la catégorie</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nom"
                                name="nom"
                                placeholder="Ex : Fruits, Légumes, Boissons..."
                                value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Décrivez brièvement cette catégorie..."
                            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="List-Categorie.php" class="btn btn-secondary rounded-pill px-4">Retour</a>
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