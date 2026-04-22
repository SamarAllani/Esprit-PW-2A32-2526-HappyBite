<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Dashbord</a>

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
require_once __DIR__ . '/../Controllers/CategorieController.php';

$categorieController = new CategorieController();

// Suppression sécurisée
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $categorieController->deleteCategorie($id);
    }

    header('Location: List-Categorie.php');
    exit;
}

// Recherche
$motCle = trim($_GET['motCle'] ?? '');

if (!empty($motCle)) {
    $categories = $categorieController->rechercherCategories($motCle);
} else {
    $categories = $categorieController->listCategories();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des catégories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
</head>
<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Liste des catégories</h2>
            <p class="text-muted mb-0">Gérez les catégories de vos produits</p>
        </div>
        <a href="Add-Categorie.php" class="btn btn-success rounded-pill px-4">
            Ajouter une catégorie
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-10">
                        <label for="motCle" class="form-label fw-semibold">Rechercher une catégorie</label>
                        <input
                            type="text"
                            class="form-control"
                            id="motCle"
                            name="motCle"
                            placeholder="Rechercher par nom..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100 rounded-pill">
                            Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0 rounded-4">
        <div class="card-body">
            <?php if (empty($categories)) { ?>
                <div class="alert alert-info mb-0 text-center">
                    Aucune catégorie trouvée.
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th style="width: 25%;">Nom</th>
                                <th style="width: 40%;">Description</th>
                                <th style="width: 25%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $categorie) { ?>
                                <tr>
                                    <td class="text-center">
                                        <?php echo htmlspecialchars($categorie->getIdCategorie()); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark fs-6">
                                            <?php echo htmlspecialchars($categorie->getNom()); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (!empty($categorie->getDescription())) { ?>
                                            <?php echo htmlspecialchars($categorie->getDescription()); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">Aucune description</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="Edit-Categorie.php?id=<?php echo $categorie->getIdCategorie(); ?>" class="btn btn-warning btn-sm me-1 mb-1">
                                            Modifier
                                        </a>

                                        <a
                                            href="List-Categorie.php?delete=<?php echo $categorie->getIdCategorie(); ?>"
                                            class="btn btn-danger btn-sm mb-1"
                                            onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');"
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

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>