
<?php
include '../../Controllers/RecetteController.php';

$recetteController = new RecetteController();

// 🔥 bouton cliqué
$action = $_GET['action'] ?? 'normal';

$motCle = trim($_GET['motCle'] ?? '');

// 🔥 profil simulé (temporaire)
$allergie = "Lactose";
$objectif = "perte de poids";
$budget = 20;

// 🔥 logique principale
if ($action === 'smart') {
    $recettes = $recetteController->rechercherRecettesIntelligentes(
        $motCle,
        $allergie,
        $objectif,
        $budget
    );
} else {
    $recettes = !empty($motCle)
        ? $recetteController->rechercherRecettes($motCle)
        : $recetteController->listRecettes();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Recettes</title>
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
            <li><a href="List-Produit.php">Produits</a></li>
            <li><a href="List-Recette.php" class="active">Recettes</a></li>
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

    <!-- 🔥 TITRE -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">Nos Recettes</h2>
        <p class="text-muted">Choisissez selon votre besoin</p>
    </div>

    <!-- 🔥 BOUTONS -->
    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="?action=normal" class="btn btn-outline-secondary rounded-pill px-4">
            Toutes les recettes
        </a>
        <a href="?action=smart" class="btn btn-success rounded-pill px-4">
            Recettes personnalisées
        </a>
    </div>

    <!-- 🔥 MESSAGE -->
    <?php if ($action === 'smart') { ?>
        <div class="alert alert-success text-center shadow-sm">
            <strong>Mode personnalisé activé :</strong><br>
            Allergie : <?php echo $allergie; ?> |
            Objectif : <?php echo $objectif; ?> |
            Budget : <?php echo $budget; ?> DT
        </div>
    <?php } else { ?>
        <div class="alert alert-secondary text-center shadow-sm">
            Affichage normal de toutes les recettes
        </div>
    <?php } ?>

    <!-- 🔥 RECHERCHE -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="action" value="<?php echo $action; ?>">

                <div class="row g-3">
                    <div class="col-md-10">
                        <input
                            type="text"
                            name="motCle"
                            class="form-control"
                            placeholder="Rechercher une recette..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-success w-100">Rechercher</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 🔥 LISTE -->
    <?php if (empty($recettes)) { ?>
        <div class="alert alert-info text-center">
            Aucune recette trouvée.
        </div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($recettes as $recette) { ?>
                <?php $produitsRecette = $recetteController->getProduitsByRecette($recette['id_recette']); ?>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <div class="card-body d-flex flex-column">

                            <!-- nom -->
                            <h5 class="fw-bold mb-2">
                                <?php echo htmlspecialchars($recette['nom']); ?>
                            </h5>

                            <!-- description -->
                            <p class="text-muted">
                                <?php echo htmlspecialchars($recette['description']); ?>
                            </p>

                            <!-- calories -->
                            <p>
                                <strong>Calories :</strong>
                                <span class="text-success fw-bold">
                                    <?php echo $recette['calories']; ?> cal
                                </span>
                            </p>

                            <!-- produits -->
                            <div class="mb-3">
                                <strong>Produits :</strong><br>
                                <?php foreach ($produitsRecette as $produit) { ?>
                                    <span class="badge bg-success me-1 mb-1">
                                        <?php echo htmlspecialchars($produit['nom']); ?>
                                    </span>
                                <?php } ?>
                            </div>

                            <!-- bouton -->
                            <div class="mt-auto">
                                <a href="Detail-Recette.php?id=<?php echo $recette['id_recette']; ?>"
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