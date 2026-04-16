<?php
include '../../Controllers/CategorieController.php';

$categorieController = new CategorieController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de la catégorie manquant.");
}

$id = intval($_GET['id']);
$categorie = $categorieController->showCategorie($id);

if (!$categorie) {
    die("Catégorie introuvable.");
}

$isProtected = (mb_strtolower(trim($categorie->getNom())) === 'non classé');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail catégorie</title>
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
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0 rounded-4">
                        <div class="card-header bg-info text-white rounded-top-4">
                            <h3 class="mb-0">Détail de la catégorie</h3>
                        </div>

                        <div class="card-body p-4">
                            <p class="mb-3">
                                <strong>ID :</strong>
                                <?php echo htmlspecialchars($categorie->getIdCategorie()); ?>
                            </p>

                            <p class="mb-3">
                                <strong>Nom :</strong>
                                <?php echo htmlspecialchars($categorie->getNom()); ?>
                            </p>

                            <p class="mb-4">
                                <strong>Description :</strong><br>
                                <?php
                                if (trim($categorie->getDescription()) !== '') {
                                    echo nl2br(htmlspecialchars($categorie->getDescription()));
                                } else {
                                    echo '<span class="text-muted">Aucune description</span>';
                                }
                                ?>
                            </p>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="List-Categorie.php" class="btn btn-secondary rounded-pill px-4">
                                    Retour
                                </a>

                                <?php if (!$isProtected) { ?>
                                    <a href="Edit-Categorie.php?id=<?php echo $categorie->getIdCategorie(); ?>" class="btn btn-warning rounded-pill px-4">
                                        Modifier
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-secondary rounded-pill px-4" disabled>
                                        Modifier
                                    </button>
                                <?php } ?>
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