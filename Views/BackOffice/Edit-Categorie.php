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
        <!-- sous-nav produit ici -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBack">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarBack">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="List-Produit.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Recette.php">Recettes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Categorie.php">Catégories</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php
include '../../Controllers/CategorieController.php';
require_once __DIR__ . '/../../Models/Categorie.php';

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
if (strtolower($categorie->getNom()) === 'non classé') {
    die("Impossible de modifier la catégorie de secours.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $errors = [];

    // Nom obligatoire
    if ($nom === '') {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }

    // Longueur minimale
    if (strlen($nom) < 2) {
        $errors[] = "Le nom de la catégorie doit contenir au moins 2 caractères.";
    }

    // Seulement lettres et espaces
    if ($nom !== '' && !preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $nom)) {
        $errors[] = "Le nom ne doit contenir que des lettres et des espaces.";
    }

    // Description trop longue
    if (strlen($description) > 255) {
        $errors[] = "La description ne doit pas dépasser 255 caractères.";
    }

    if (strlen($description) < 20) { 
        $errors[] = "La description doit dépasser 20 caractères."; 
        }

    // Vérification doublon en excluant la catégorie actuelle
    if ($nom !== '') {
        $categories = $categorieController->listCategories();

        foreach ($categories as $cat) {
            if (
                $cat->getIdCategorie() != $id &&
                mb_strtolower(trim($cat->getNom())) === mb_strtolower($nom)
            ) {
                $errors[] = "Une catégorie avec ce nom existe déjà.";
                break;
            }
        }
    }

    if (!empty($errors)) {
        $error = implode(" ",$errors);
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
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
</head>
<body>

<div class="container py-5">
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
</body>
</html>