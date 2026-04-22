<?php
include '../../Controllers/RecetteController.php';
include '../../Controllers/ProduitController.php';
require_once __DIR__ . '/../../Models/Recette.php';

$error = [];

$recetteController = new RecetteController();
$produitController = new ProduitController();

$produits = $produitController->listProduits();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de la recette manquant.");
}

$id = (int) $_GET['id'];
$recetteData = $recetteController->getRecetteById($id);

if (!$recetteData) {
    die("Recette introuvable.");
}

$nom = $recetteData['nom'] ?? '';
$description = $recetteData['description'] ?? '';
$image = $recetteData['image'] ?? '';
$calories = $recetteData['calories'] ?? 0;

$produitsAssocies = $recetteController->getProduitsByRecette($id);
$produitsSelectionnes = array_map(function ($produit) {
    return $produit['id_produit'];
}, $produitsAssocies);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $produitsSelectionnes = $_POST['produits'] ?? [];

    $errors = [];

    if ($nom === '') {
        $errors[] = "Le nom de la recette est obligatoire.";
    }

    if (mb_strlen($nom) < 2) {
        $errors[] = "Le nom de la recette doit contenir au moins 2 caractères.";
    }

    if ($nom !== '' && !preg_match("/^[a-zA-ZÀ-ÿ0-9\s%\-\'()]+$/u", $nom)) {
        $errors[] = "Le nom de la recette contient des caractères non autorisés.";
    }

    preg_match_all('/[a-zA-ZÀ-ÿ]/u', $nom, $matches);
    if ($nom !== '' && count($matches[0]) < 3) {
        $errors[] = "Le nom de la recette doit contenir au moins 3 lettres.";
    }

    if ($nom !== '' && !preg_match('/[a-zA-ZÀ-ÿ]/u', $nom)) {
        $errors[] = "Le nom de la recette ne peut pas être composé uniquement de chiffres ou de symboles.";
    }

    if ($description === '') {
        $errors[] = "La description est obligatoire.";
    }

    if ($description !== '' && mb_strlen($description) < 10) {
        $errors[] = "La description doit contenir au moins 10 caractères.";
    }

    if (empty($produitsSelectionnes)) {
        $errors[] = "Veuillez sélectionner au moins un produit.";
    }

    $image = $recetteData['image'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
        if ($_FILES['image']['error'] === 0) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $originalName = $_FILES['image']['name'];
            $tmpName = $_FILES['image']['tmp_name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = "Format d'image non autorisé. Utilise jpg, jpeg, png, gif ou webp.";
            } else {
                $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $uploadDir = __DIR__ . '/../../uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $image = $newFileName;
                } else {
                    $errors[] = "Erreur lors de l'upload de l'image.";
                }
            }
        } else {
            $errors[] = "Erreur lors du téléchargement de l'image.";
        }
    }

    if (empty($errors)) {
        $calories = $recetteController->calculerCaloriesRecette($produitsSelectionnes);

        $recette = new Recette($nom, $description, $calories, $image);
        $success = $recetteController->updateRecette($recette, $id);

        if ($success) {
            $recetteController->supprimerProduitsRecette($id);
            $recetteController->ajouterProduitsRecette($id, $produitsSelectionnes);

            header('Location: List-Recette.php');
            exit;
        } else {
            $errors[] = "Erreur lors de la modification de la recette.";
        }
    }

    $error = $errors;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une recette</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">

    <style>
        .image-preview {
            max-width: 140px;
            max-height: 140px;
            border-radius: 12px;
            margin-top: 10px;
            border: 1px solid #ddd;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-warning text-dark rounded-top-4">
                    <h3 class="mb-0">Modifier une recette</h3>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($error as $err) { ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="nom" class="form-label fw-semibold">Nom de la recette</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nom"
                                name="nom"
                                value="<?php echo htmlspecialchars($nom); ?>"
                            >
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                            ><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label fw-semibold">Image</label>
                            <input
                                type="file"
                                class="form-control"
                                id="image"
                                name="image"
                                accept="image/*"
                            >

                            <small class="text-muted d-block mt-2">Aperçu :</small>

                            <?php if (!empty($image)) { ?>
                                <img
                                    id="imagePreview"
                                    src="/uploads/<?php echo htmlspecialchars($image); ?>"
                                    alt="Image de la recette"
                                    class="image-preview"
                                >
                            <?php } else { ?>
                                <img
                                    id="imagePreview"
                                    src=""
                                    alt="Aperçu image"
                                    class="image-preview"
                                    style="display:none;"
                                >
                            <?php } ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Produits de la recette</label>
                            <?php if (empty($produits)) { ?>
                                <div class="alert alert-info">Aucun produit disponible.</div>
                            <?php } else { ?>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($produits as $produit) { ?>
                                        <div class="form-check mb-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="produits[]"
                                                value="<?php echo $produit['id_produit']; ?>"
                                                id="produit_<?php echo $produit['id_produit']; ?>"
                                                <?php echo in_array($produit['id_produit'], $produitsSelectionnes) ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="produit_<?php echo $produit['id_produit']; ?>">
                                                <?php echo htmlspecialchars($produit['nom']); ?>
                                                <span class="text-muted">
                                                    (<?php echo htmlspecialchars($produit['calories'] ?? 0); ?> cal)
                                                </span>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="List-Recette.php" class="btn btn-secondary rounded-pill px-4">Retour</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-4">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('image').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>