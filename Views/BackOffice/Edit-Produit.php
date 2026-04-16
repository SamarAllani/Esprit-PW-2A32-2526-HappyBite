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
include '../../Controllers/ProduitController.php';
include '../../Controllers/CategorieController.php';
require_once __DIR__ . '/../../Models/Produit.php';
require_once __DIR__ . '/../../Models/Categorie.php';

$error = "";

$produitController = new ProduitController();
$categorieController = new CategorieController();
$categories = $categorieController->listCategories();

// Listes fixes
$listeAllergenes = [
    'Gluten',
    'Lactose',
    'Sulfites',
    'Sucre élevé',
    'Sel élevé'
];

$listeBenefices = [
    'Vitamine A',
    'Vitamine B',
    'Vitamine C',
    'Vitamine D',
    'Fer',
    'Calcium',
    'Magnésium',
    'Fibres',
    'Protéines'
];

// Vérification de l'id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du produit manquant.");
}

$id = (int) $_GET['id'];

// Récupération du produit existant
$produitData = $produitController->getProduitById($id);

if (!$produitData) {
    die("Produit introuvable.");
}

// Préremplissage initial avec les données existantes
$nom = $produitData['nom'] ?? '';
$prix = $produitData['prix'] ?? '';
$image = $produitData['image'] ?? '';
$calories = $produitData['calories'] ?? '';
$id_categorie = $produitData['id_categorie'] ?? '';
$id_utilisateur = $produitData['id_utilisateur'] ?? 1;
$date_ajout = $produitData['date_ajout'] ?? date('Y-m-d');

$allergenesSelectionnes = !empty($produitData['allergene'])
    ? array_map('trim', explode(',', $produitData['allergene']))
    : [];

$beneficesSelectionnes = !empty($produitData['benefices'])
    ? array_map('trim', explode(',', $produitData['benefices']))
    : [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prix = trim($_POST['prix'] ?? '');
    $calories = trim($_POST['calories'] ?? '');
    $id_categorie = trim($_POST['id_categorie'] ?? '');

    $allergenesSelectionnes = $_POST['allergenes'] ?? [];
    $beneficesSelectionnes = $_POST['benefices_list'] ?? [];

    $allergene = implode(',', $allergenesSelectionnes);
    $benefices = implode(',', $beneficesSelectionnes);

    // Garder l'ancienne image si aucune nouvelle n'est envoyée
    $image = $produitData['image'] ?? '';

    // Upload nouvelle image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $originalName = $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            $error = "Format d'image non autorisé. Utilise jpg, jpeg, png, gif ou webp.";
        } else {
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $uploadDir = __DIR__ . '/../../uploads/';
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                $image = $newFileName;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        }
    }

    if (empty($error)) {
        if (empty($nom)) {
            $error = "Le nom du produit est obligatoire.";
        } elseif (strlen($nom) < 2) {
            $error = "Le nom du produit doit contenir au moins 2 caractères.";
        } elseif (empty($prix)) {
            $error = "Le prix est obligatoire.";
        } elseif (!is_numeric($prix) || $prix <= 0) {
            $error = "Le prix doit être un nombre positif.";
        } elseif (!empty($calories) && (!ctype_digit($calories) || (int)$calories < 0)) {
            $error = "Les calories doivent être un entier positif ou zéro.";
        } elseif (empty($id_categorie)) {
            $error = "La catégorie est obligatoire.";
        } else {
            $produit = new Produit(
                $nom,
                (float)$prix,
                $image,
                $allergene,
                $benefices,
                $calories !== '' ? (int)$calories : null,
                $date_ajout,
                $id_utilisateur,
                (int)$id_categorie
            );

            $produitController->updateProduit($produit, $id);

            header('Location: List-Produit.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un produit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0">Modifier un produit</h3>
                </div>

                <div class="card-body">
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom du produit</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nom"
                                name="nom"
                                value="<?php echo htmlspecialchars($nom); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="prix" class="form-label">Prix</label>
                            <input
                                type="text"
                                class="form-control"
                                id="prix"
                                name="prix"
                                value="<?php echo htmlspecialchars($prix); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input
                                type="file"
                                class="form-control"
                                id="image"
                                name="image"
                                accept="image/*"
                            >
                            <?php if (!empty($produitData['image'])) { ?>
                                <small class="text-muted d-block mt-2">
                                    Image actuelle : <?php echo htmlspecialchars($produitData['image']); ?>
                                </small>
                                <img
                                    src="/uploads/<?php echo htmlspecialchars($produitData['image']); ?>"
                                    alt="Image actuelle"
                                    style="max-width: 120px; margin-top: 10px; border-radius: 10px;"
                                >
                            <?php } ?>
                        </div>

                        <div class="mb-3">
                            <label for="calories" class="form-label">Calories</label>
                            <input
                                type="text"
                                class="form-control"
                                id="calories"
                                name="calories"
                                value="<?php echo htmlspecialchars($calories); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select class="form-select" id="id_categorie" name="id_categorie">
                                <option value="">-- Choisir une catégorie --</option>
                                <?php foreach ($categories as $categorie) { ?>
                                    <option
                                        value="<?php echo $categorie->getIdCategorie(); ?>"
                                        <?php echo ($id_categorie == $categorie->getIdCategorie()) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($categorie->getNom()); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Allergènes / composants sensibles</label>
                            <?php foreach ($listeAllergenes as $item) { ?>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="allergenes[]"
                                        value="<?php echo $item; ?>"
                                        id="allergene_<?php echo md5($item); ?>"
                                        <?php echo in_array($item, $allergenesSelectionnes) ? 'checked' : ''; ?>
                                    >
                                    <label class="form-check-label" for="allergene_<?php echo md5($item); ?>">
                                        <?php echo htmlspecialchars($item); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bénéfices</label>
                            <?php foreach ($listeBenefices as $item) { ?>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="benefices_list[]"
                                        value="<?php echo $item; ?>"
                                        id="benefice_<?php echo md5($item); ?>"
                                        <?php echo in_array($item, $beneficesSelectionnes) ? 'checked' : ''; ?>
                                    >
                                    <label class="form-check-label" for="benefice_<?php echo md5($item); ?>">
                                        <?php echo htmlspecialchars($item); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="List-Produit.php" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-warning">Modifier</button>
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