
<?php
require_once '../../Controllers/ProduitController.php';
require_once '../../Controllers/CategorieController.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();

$categories = $categorieController->listCategories();

// Bouton cliqué
$action = $_GET['action'] ?? 'normal';

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

// Profil simulé
$allergie = "Lactose";
$objectif = "perte de poids";
$budget = 20;

// Choix logique
if ($action === 'smart') {
    $produits = $produitController->rechercherProduitsIntelligents(
        $motCle,
        $idCategorie,
        $allergie,
        $objectif,
        $budget
    );
} else {
    $produits = (!empty($motCle) || !empty($idCategorie))
        ? $produitController->rechercherProduits($motCle, $idCategorie)
        : $produitController->listProduits();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/style.css">
</head>
<body>
<!-- NAVBAR ICI -->
<nav class="main-navbar">
    <div class="nav-container">
        
        <a href="index.php" class="nav-logo">
            <img src="../assets/images/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="List-Produit.php" class="active">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="#">Communauté</a></li>
        </ul>

        <div class="nav-user">
            <a href="#" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Santé</a>
            <a href="#" class="nav-profile">Profil</a>
        </div>

    </div>
</nav>
<div class="container py-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold">Nos Produits</h2>
        <p class="text-muted">Choisissez selon votre besoin</p>
    </div>

    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="?action=normal" class="btn btn-outline-secondary rounded-pill px-4">
            Tous les produits
        </a>
        <a href="?action=smart" class="btn btn-success rounded-pill px-4">
            Produits personnalisés
        </a>
    </div>

    <?php if ($action === 'smart') { ?>
        <div class="alert alert-success text-center shadow-sm">
            <strong>Mode personnalisé activé :</strong><br>
            Allergie : <?php echo htmlspecialchars($allergie); ?> |
            Objectif : <?php echo htmlspecialchars($objectif); ?> |
            Budget : <?php echo htmlspecialchars($budget); ?> DT
        </div>
    <?php } else { ?>
        <div class="alert alert-secondary text-center shadow-sm">
            Affichage normal de tous les produits
        </div>
    <?php } ?>
        <!-- SECTION NOS RAYONS ICI -->
<!-- SECTION NOS RAYONS -->
<div class="rayons-section mb-4">
    <div class="rayons-header mb-3">
        <h4 class="mb-2">Nos rayons</h4>
        <p class="mb-0">
            Nos produits sont organisés par catégories pour vous aider.
        </p>
    </div>

    <?php if (!empty($categories)) { ?>
        <div class="rayons-scroll">
            <?php foreach ($categories as $categorie) { ?>
                <div class="rayon-card-mini">
                    <h5><?php echo htmlspecialchars($categorie->getNom()); ?></h5>
                    <p>
                        <?php
                        $description = trim($categorie->getDescription() ?? '');
                        echo !empty($description)
                            ? htmlspecialchars($description)
                            : 'Découvrez les produits de cette catégorie dans notre catalogue.';
                        ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">

                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="motCle" class="form-label">Rechercher un produit</label>
                        <input
                            type="text"
                            class="form-control"
                            id="motCle"
                            name="motCle"
                            placeholder="Nom du produit..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>

                    <div class="col-md-5">
                        <label for="id_categorie" class="form-label">Catégorie</label>
                        <select class="form-select" id="id_categorie" name="id_categorie">
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

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Filtrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($produits)) { ?>
        <div class="alert alert-info text-center shadow-sm">
            Aucun produit trouvé.
        </div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($produits as $produit) { ?>
                <?php
                $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-body d-flex flex-column">

                            <div class="text-center mb-3">
                                <?php if (!empty($produit['image'])) { ?>
                                    <img
                                        src="/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                        alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                                        style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 15px;"
                                    >
                                <?php } else { ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-4"
                                         style="height: 200px;">
                                        <span class="text-muted">Aucune image</span>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="mb-3">
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($produit['nom']); ?></h5>
                                <span class="badge bg-light text-dark">
                                    <?php echo htmlspecialchars($produit['nom_categorie']); ?>
                                </span>
                            </div>

                            <p class="mb-2">
                                <strong>Prix :</strong>
                                <span class="text-success fw-bold">
                                    <?php echo htmlspecialchars($produit['prix']); ?> DT
                                </span>
                            </p>

                            <p class="mb-2">
                                <strong>Calories :</strong>
                                <?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?> cal
                            </p>

                            <div class="mb-3">
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

                            <div class="mb-3">
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

                            <div class="mt-auto">
                                <a href="Detail-Produit.php?id=<?php echo $produit['id_produit']; ?>"
                                   class="btn btn-outline-success w-100 rounded-pill">
                                    Voir détails
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>