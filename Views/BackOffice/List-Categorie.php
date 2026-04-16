<?php
session_start();

include '../../Controllers/CategorieController.php';
include '../../Controllers/ProduitController.php';

$categorieController = new CategorieController();
$produitController = new ProduitController();

// Suppression sécurisée avec réaffectation
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $categorieSecours = $categorieController->createCategorieIfNotExists(
            'Non classé',
            'Catégorie utilisée automatiquement pour les produits réaffectés.'
        );

        $idCategorieSecours = (int)$categorieSecours['id_categorie'];

        if ($id === $idCategorieSecours) {
            $_SESSION['popup_error'] = "Impossible de supprimer la catégorie de secours.";
            header('Location: List-Categorie.php');
            exit;
        } else {
            $produitController->reassignProduitsToCategorie($id, $idCategorieSecours);
            $categorieController->deleteCategorie($id);

            $_SESSION['popup_success'] = 'La catégorie a été supprimée et ses produits ont été déplacés vers "Non classé".';
            header('Location: List-Categorie.php');
            exit;
        }
    }
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

    <style>
        .categorie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .categorie-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f1f1;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .categorie-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .categorie-id {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .categorie-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #212529;
        }

        .categorie-description {
            color: #6c757d;
            min-height: 70px;
            margin-bottom: 20px;
        }

        .categorie-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .categorie-badge-protected {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
    </style>
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

            <?php if (empty($categories)) { ?>
                <div class="card shadow border-0 rounded-4">
                    <div class="card-body">
                        <div class="alert alert-info mb-0 text-center">
                            Aucune catégorie trouvée.
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="categorie-grid">
                    <?php foreach ($categories as $categorie) { ?>
                        <?php $isProtected = (mb_strtolower(trim($categorie->getNom())) === 'non classé'); ?>

                        <div class="categorie-card">
                            

                            <div class="categorie-title">
                                <?php echo htmlspecialchars($categorie->getNom()); ?>
                            </div>

                            <div class="categorie-description">
                                <?php
                                $description = trim($categorie->getDescription());
                                if ($description !== '') {
                                    echo htmlspecialchars(mb_strimwidth($description, 0, 100, '...'));
                                } else {
                                    echo '<span class="text-muted">Aucune description</span>';
                                }
                                ?>
                            </div>

                            <div class="categorie-actions">
                                <a href="Detail-Categorie.php?id=<?php echo $categorie->getIdCategorie(); ?>" class="btn btn-info btn-sm text-white">
                                    Voir détail
                                </a>

                                <?php if (!$isProtected) { ?>
                                    <a href="Edit-Categorie.php?id=<?php echo $categorie->getIdCategorie(); ?>" class="btn btn-warning btn-sm">
                                        Modifier
                                    </a>

                                    <a
                                        href="List-Categorie.php?delete=<?php echo $categorie->getIdCategorie(); ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');"
                                    >
                                        Supprimer
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        Modifier
                                    </button>

                                    <button class="btn btn-secondary btn-sm" disabled>
                                        Supprimer
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </main>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['popup_success'])) { ?>
<script>
    alert(<?php echo json_encode($_SESSION['popup_success']); ?>);
</script>
<?php unset($_SESSION['popup_success']); } ?>

<?php if (isset($_SESSION['popup_error'])) { ?>
<script>
    alert(<?php echo json_encode($_SESSION['popup_error']); ?>);
</script>
<?php unset($_SESSION['popup_error']); } ?>

</body>
</html>