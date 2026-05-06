<?php
require_once '../../Controllers/ProduitController.php';
require_once '../../Controllers/CategorieController.php';
require_once '../../Controllers/FrigoController.php';
require_once '../../Controllers/AiRecetteController.php';
require_once '../../Config.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();
$frigoController = new FrigoController();

$categories = $categorieController->listCategories();

$action = $_GET['action'] ?? 'normal';
$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$resultatIA = null;

// PROFIL FIXE TEMPORAIRE
$idUtilisateur = 12;

$db = Config::getConnexion();
$stmt = $db->prepare("SELECT * FROM profil_sante WHERE id_utilisateur = :id_utilisateur LIMIT 1");
$stmt->execute([
    'id_utilisateur' => $idUtilisateur
]);
$profilSante = $stmt->fetch(PDO::FETCH_ASSOC);

// IA BUDGET + SANTÉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_ia_budget'] ?? '') === 'alternative_budget') {
    $produitCher = trim($_POST['produit_cher'] ?? '');
    $budget = trim($_POST['budget'] ?? '');

    if (!empty($produitCher)) {
        $ai = new AiRecetteController();
        $resultatIA = $ai->proposerAlternativeBudgetSante($produitCher, $budget, $profilSante);
    } else {
        $resultatIA = "Veuillez saisir un produit.";
    }
}

// AJOUT AU FRIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_frigo'] ?? '') === 'ajouter_frigo') {
    $idProduitAjout = (int)($_POST['id_produit'] ?? 0);
    $quantiteAjout = (int)($_POST['quantite'] ?? 1);

    if ($idProduitAjout > 0 && $quantiteAjout > 0) {
        $frigoController->ajouterAuFrigo($idUtilisateur, $idProduitAjout, $quantiteAjout);
    }

    $queryParams = [
        'action' => $action,
        'motCle' => $motCle,
        'id_categorie' => $idCategorie
    ];

    $queryString = http_build_query($queryParams);
    $redirectUrl = 'List-Produit.php';

    if (!empty($queryString)) {
        $redirectUrl .= '?' . $queryString;
    }

    $redirectUrl .= '#produit-' . $idProduitAjout;

    header('Location: ' . $redirectUrl);
    exit;
}

if ($action === 'smart' && $profilSante) {
    $produits = $produitController->rechercherProduitsIntelligents(
        $idUtilisateur,
        $motCle,
        $idCategorie
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

    <style>
        .promo-card {
            border: 2px solid #f0ad4e !important;
            background: #fff8e1 !important;
        }

        .promo-badge {
            display: inline-block;
            background: #f0ad4e;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
        }

        .promo-old-price {
            color: #8c8c8c;
            text-decoration: line-through;
            margin-right: 8px;
        }

        .promo-new-price {
            color: #d97706;
            font-weight: 800;
            font-size: 1.1rem;
        }
    </style>
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
            <li><a href="List-Produit.php" class="active">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="#">Communauté</a></li>
        </ul>

        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
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

    <?php if ($action === 'smart' && $profilSante) { ?>
        <div class="alert alert-success text-center shadow-sm">
            <strong>Mode personnalisé activé :</strong><br>
            <?php
            $infos = [];

            if (!empty($profilSante['allergenes'])) {
                $infos[] = "Allergènes : " . htmlspecialchars($profilSante['allergenes']);
            }

            if (!empty($profilSante['carences'])) {
                $infos[] = "Carences : " . htmlspecialchars($profilSante['carences']);
            }

            if (!empty($profilSante['maladies'])) {
                $infos[] = "Maladies : " . htmlspecialchars($profilSante['maladies']);
            }

            if (!empty($profilSante['objectif'])) {
                $infos[] = "Objectif : " . htmlspecialchars($profilSante['objectif']);
            }

            echo implode(' | ', $infos);
            ?>
        </div>
    <?php } elseif ($action === 'smart') { ?>
        <div class="alert alert-warning text-center shadow-sm">
            Aucun profil santé trouvé pour l'utilisateur fixe.
        </div>
    <?php } else { ?>
        <div class="alert alert-secondary text-center shadow-sm">
            Affichage normal de tous les produits
        </div>
    <?php } ?>

    <!-- BLOC IA BUDGET + SANTÉ -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h5 class="fw-bold mb-3">🤖 Assistant intelligent Budget & Santé</h5>

            <form method="POST">
                <input type="hidden" name="action_ia_budget" value="alternative_budget">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Produit cher ou interdit</label>
                        <input
                            type="text"
                            name="produit_cher"
                            class="form-control"
                            placeholder="ex: saumon, lait, pain..."
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Budget disponible (DT)</label>
                        <input
                            type="number"
                            name="budget"
                            class="form-control"
                            placeholder="ex: 20"
                            min="0"
                            step="0.1"
                        >
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            Trouver alternative
                        </button>
                    </div>
                </div>
            </form>

            <?php if (!empty($resultatIA)) { ?>
                <div class="alert alert-info mt-3">
                    <?php echo nl2br(htmlspecialchars($resultatIA)); ?>
                </div>
            <?php } ?>

        </div>
    </div>

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
                        <label for="motCle" class="form-label">Rechercher un produit ou un fournisseur</label>
                        <input
                            type="text"
                            class="form-control"
                            id="motCle"
                            name="motCle"
                            placeholder="Nom du produit, fournisseur ou promo..."
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
                $isPromo = isset($produit['promo']) && $produit['promo'] !== null && $produit['promo'] !== '';
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm rounded-4 <?php echo $isPromo ? 'promo-card' : 'border-0'; ?>" id="produit-<?php echo $produit['id_produit']; ?>">
                        <div class="card-body d-flex flex-column">

                            <?php if ($isPromo) { ?>
                                <div class="mb-2">
                                    <span class="promo-badge">En promo</span>
                                </div>
                            <?php } ?>

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
                                <strong>Fournisseur :</strong>
                                <span class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($produit['nom_fournisseur'] ?? 'Non renseigné'); ?>
                                </span>
                            </p>

                            <p class="mb-2">
                                <strong>Prix :</strong>
                                <?php if ($isPromo) { ?>
                                    <span class="promo-old-price">
                                        <?php echo htmlspecialchars($produit['prix']); ?> DT
                                    </span>
                                    <span class="promo-new-price">
                                        <?php echo htmlspecialchars($produit['promo']); ?> DT
                                    </span>
                                <?php } else { ?>
                                    <span class="text-success fw-bold">
                                        <?php echo htmlspecialchars($produit['prix']); ?> DT
                                    </span>
                                <?php } ?>
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
                                <div class="row g-2 align-items-end">

                                    <div class="col-4">
                                        <a href="Detail-Produit.php?id=<?php echo $produit['id_produit']; ?>"
                                           class="btn btn-outline-success w-100 rounded-pill btn-sm">
                                            Détails
                                        </a>
                                    </div>

                                    <div class="col-4">
                                        <button type="button"
                                                class="btn btn-warning w-100 rounded-pill btn-sm"
                                                disabled>
                                            Panier
                                        </button>
                                    </div>

                                    <div class="col-4">
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="action_frigo" value="ajouter_frigo">
                                            <input type="hidden" name="id_produit" value="<?php echo $produit['id_produit']; ?>">

                                            <input
                                                type="number"
                                                name="quantite"
                                                min="1"
                                                value="1"
                                                class="form-control form-control-sm text-center rounded-pill mb-1"
                                            >

                                            <button type="submit" class="btn btn-success w-100 rounded-pill btn-sm">
                                                Frigo
                                            </button>
                                        </form>
                                    </div>

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