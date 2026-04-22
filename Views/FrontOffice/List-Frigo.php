<?php
require_once '../../Controllers/FrigoController.php';
require_once '../../Controllers/CategorieController.php';

$frigoController = new FrigoController();
$categorieController = new CategorieController();

// utilisateur fixe temporaire
$idUtilisateur = 12;

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$categories = $categorieController->listCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idProduit = (int)($_POST['id_produit'] ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 1);

    if ($action === 'ajouter' && $idProduit > 0 && $quantite > 0) {
        $frigoController->ajouterAuFrigo($idUtilisateur, $idProduit, $quantite);
    }

    if ($action === 'modifier' && $idProduit > 0) {
        $frigoController->updateQuantite($idUtilisateur, $idProduit, $quantite);
    }

    if ($action === 'supprimer' && $idProduit > 0) {
        $frigoController->supprimerDuFrigo($idUtilisateur, $idProduit);
    }

    $queryString = http_build_query([
        'motCle' => $motCle,
        'id_categorie' => $idCategorie
    ]);

    $redirectUrl = 'List-Frigo.php';
    if (!empty($queryString) && $queryString !== 'motCle=&id_categorie=') {
        $redirectUrl .= '?' . $queryString;
    }
    $redirectUrl .= '#frigo-zone';

    header('Location: ' . $redirectUrl);
    exit;
}

$produitsFrigo = $frigoController->getFrigoByUtilisateur($idUtilisateur, $motCle, $idCategorie);
$totalProduits = $frigoController->getNombreProduitsDansFrigo($idUtilisateur);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Frigo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/style.css">
</head>
<body>

<nav class="main-navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
            <img src="../assets/images/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="List-Produit.php">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="#">Communauté</a></li>
        </ul>

        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-profile">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Santé</a>
            <a href="#" class="nav-action">Profil</a>
        </div>
    </div>
</nav>

<div class="container py-5" id="frigo-zone">

    <div class="text-center mb-4">
        <h2 class="fw-bold">Mon Frigo</h2>
        <p class="text-muted">Retrouvez ici les produits ajoutés à votre frigo</p>
    </div>

    <div class="alert alert-success text-center shadow-sm mb-4">
        <strong>Profil fixe temporaire :</strong> utilisateur ID <?php echo $idUtilisateur; ?><br>
        <strong>Nombre d'articles distincts :</strong> <?php echo $totalProduits; ?>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="motCle" class="form-label">Rechercher par nom</label>
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

    <?php if (empty($produitsFrigo)) { ?>
        <div class="alert alert-info text-center shadow-sm">
            Votre frigo est vide.
        </div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($produitsFrigo as $produit) { ?>
                <?php
                $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                ?>
                <div class="col-md-6 col-lg-4 mb-4" id="frigo-produit-<?php echo $produit['id_produit']; ?>">
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

                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($produit['nom']); ?></h5>

                            <p class="mb-2">
                                <strong>Catégorie :</strong>
                                <?php echo htmlspecialchars($produit['nom_categorie'] ?? 'Non classé'); ?>
                            </p>

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

                            <p class="mb-2">
                                <strong>Quantité :</strong>
                                <?php echo htmlspecialchars($produit['quantite']); ?>
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
                                <div class="row g-2">
                                    <div class="col-7">
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="action" value="modifier">
                                            <input type="hidden" name="id_produit" value="<?php echo $produit['id_produit']; ?>">
                                            <input type="number"
                                                   name="quantite"
                                                   min="0"
                                                   value="<?php echo (int)$produit['quantite']; ?>"
                                                   class="form-control form-control-sm rounded-pill">
                                    </div>
                                    <div class="col-5">
                                            <button type="submit" class="btn btn-outline-success w-100 rounded-pill btn-sm">
                                                Modifier
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id_produit" value="<?php echo $produit['id_produit']; ?>">
                                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill btn-sm">
                                            Supprimer du frigo
                                        </button>
                                    </form>
                                </div>
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