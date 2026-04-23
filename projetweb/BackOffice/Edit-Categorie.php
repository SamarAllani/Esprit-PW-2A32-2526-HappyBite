<?php
require_once __DIR__ . '/../Controllers/CategorieController.php';
require_once __DIR__ . '/../Models/Categorie.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$error = "";

$categorieController = new CategorieController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de la catégorie manquant.");
}

$id = intval($_GET['id']);
$categorie = $categorieController->showCategorie($id);

if (!$categorie) {
    die("Catégorie introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) {
        $error = "Le nom de la catégorie est obligatoire.";
    } elseif (strlen($nom) < 2) {
        $error = "Le nom de la catégorie doit contenir au moins 2 caractères.";
    } else {
        $categorieModifiee = new Categorie($nom, $description);
        $categorieController->updateCategorie($categorieModifiee, $id);

        header('Location: List-Categorie.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une catégorie</title>
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
                <div class="card-header bg-warning rounded-top-4">
                    <h3 class="mb-0">Modifier une catégorie</h3>
                </div>

                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Mettez à jour les informations de cette catégorie.
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
                                value="<?php echo htmlspecialchars($_POST['nom'] ?? $categorie->getNom()); ?>"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                            ><?php echo htmlspecialchars($_POST['description'] ?? $categorie->getDescription()); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="List-Categorie.php" class="btn btn-secondary rounded-pill px-4">Retour</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-4">Mettre à jour</button>
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