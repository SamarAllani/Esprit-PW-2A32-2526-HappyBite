<?php
require_once '../../Controllers/RecetteController.php';
require_once '../../Controllers/AiRecetteController.php';
require_once '../../Config.php';

$recetteController = new RecetteController();

$analyseIA = null;

$action = $_GET['action'] ?? 'normal';
$motCle = trim($_GET['motCle'] ?? '');

// PROFIL FIXE TEMPORAIRE
$idUtilisateur = 12;

$db = Config::getConnexion();
$stmt = $db->prepare("SELECT * FROM profil_sante WHERE id_utilisateur = :id_utilisateur LIMIT 1");
$stmt->execute([
    'id_utilisateur' => $idUtilisateur
]);
$profilSante = $stmt->fetch(PDO::FETCH_ASSOC);

// TRAITEMENT IA PHOTO DIRECTEMENT DANS LA PAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']['tmp_name'])) {
    $imagePath = $_FILES['image']['tmp_name'];

    $ai = new AiRecetteController();
    $analyseIA = $ai->analyserPlatPhoto($imagePath, $profilSante);
}

if ($action === 'smart' && $profilSante) {
    $recettes = $recetteController->rechercherRecettesIntelligentes($idUtilisateur, $motCle);
} else {
    $recettes = !empty($motCle)
        ? $recetteController->rechercherRecettes($motCle)
        : $recetteController->listRecettes();
}

usort($recettes, function ($a, $b) {
    return intval($b['mise_en_avant'] ?? 0) <=> intval($a['mise_en_avant'] ?? 0);
});
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
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Santé</a>
            <a href="#" class="nav-profile">Profil</a>
        </div>
    </div>
</nav>

<div class="container py-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold">Nos Recettes</h2>
        <p class="text-muted">Choisissez selon votre besoin</p>
    </div>

    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="?action=normal" class="btn btn-outline-secondary rounded-pill px-4">
            Toutes les recettes
        </a>
        <a href="?action=smart" class="btn btn-success rounded-pill px-4">
            Recettes personnalisées
        </a>
    </div>

    <!-- BLOC IA PHOTO -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h5 class="fw-bold mb-3">📸 Analyse ton plat</h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">Analyser</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($analyseIA)) { ?>
    <?php
    $analyseData = json_decode($analyseIA, true);

    if (is_array($analyseData)) {
        $niveau = strtolower($analyseData['niveau'] ?? 'orange');

        $badgeClass = 'bg-warning text-dark';
        if ($niveau === 'vert') {
            $badgeClass = 'bg-success';
        } elseif ($niveau === 'rouge') {
            $badgeClass = 'bg-danger';
        }
    ?>

        <div class="card border-0 shadow-sm mt-4 rounded-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Résultat de l’analyse</h5>
                    <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                        Score : <?php echo htmlspecialchars($analyseData['score_sante'] ?? '-'); ?>/10
                    </span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <strong>Calories</strong><br>
                            <span class="text-success fw-bold">
                                <?php echo htmlspecialchars($analyseData['calories_estimees'] ?? '-'); ?> kcal
                            </span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <strong>Protéines</strong><br>
                            <?php echo htmlspecialchars($analyseData['proteines'] ?? '-'); ?>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <strong>Glucides</strong><br>
                            <?php echo htmlspecialchars($analyseData['glucides'] ?? '-'); ?>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <strong>Lipides</strong><br>
                            <?php echo htmlspecialchars($analyseData['lipides'] ?? '-'); ?>
                        </div>
                    </div>
                </div>

                <p><strong>Ingrédients détectés :</strong></p>
                <div class="mb-3">
                    <?php foreach (($analyseData['ingredients_detectes'] ?? []) as $ingredient) { ?>
                        <span class="badge bg-success me-1 mb-1">
                            <?php echo htmlspecialchars($ingredient); ?>
                        </span>
                    <?php } ?>
                </div>

                <p><strong>Analyse :</strong></p>
                <p class="text-muted">
                    <?php echo htmlspecialchars($analyseData['analyse'] ?? ''); ?>
                </p>

                <p><strong>Comment rééquilibrer :</strong></p>
                <ul>
                    <?php foreach (($analyseData['reequilibrage'] ?? []) as $conseil) { ?>
                        <li><?php echo htmlspecialchars($conseil); ?></li>
                    <?php } ?>
                </ul>

                <div class="alert alert-light border mt-3">
                    <strong>Sport conseillé :</strong><br>
                    <?php echo htmlspecialchars($analyseData['sport_conseille'] ?? ''); ?>
                </div>

                <?php if (!empty($analyseData['avertissement_sante'])) { ?>
                    <div class="alert alert-warning mt-3">
                        <strong>Attention santé :</strong><br>
                        <?php echo htmlspecialchars($analyseData['avertissement_sante']); ?>
                    </div>
                <?php } ?>

            </div>
        </div>

    <?php } else { ?>
        <div class="alert alert-success mt-3">
            <?php echo nl2br(htmlspecialchars($analyseIA)); ?>
        </div>
    <?php } ?>
<?php } ?>

        </div>
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
            Affichage normal de toutes les recettes
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">

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

    <?php if (empty($recettes)) { ?>
        <div class="alert alert-info text-center">
            Aucune recette trouvée.
        </div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($recettes as $recette) { ?>
                <?php $produitsRecette = $recetteController->getProduitsByRecette($recette['id_recette']); ?>
                <?php $miseEnAvant = intval($recette['mise_en_avant'] ?? 0); ?>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 <?php echo $miseEnAvant ? 'border border-warning bg-warning-subtle' : ''; ?>">
                        <div class="card-body d-flex flex-column">

                            <?php if ($miseEnAvant) { ?>
                                <div class="mb-2">
                                    <span class="badge rounded-pill text-bg-warning">Mise en avant</span>
                                </div>
                            <?php } ?>

                            <div class="text-center mb-3">
                                <?php if (!empty($recette['image'])) { ?>
                                    <img
                                        src="/uploads/<?php echo htmlspecialchars($recette['image']); ?>"
                                        alt="<?php echo htmlspecialchars($recette['nom']); ?>"
                                        style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 15px;"
                                    >
                                <?php } else { ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-4"
                                         style="height: 220px;">
                                        <span class="text-muted">Aucune image</span>
                                    </div>
                                <?php } ?>
                            </div>

                            <h5 class="fw-bold mb-2">
                                <?php echo htmlspecialchars($recette['nom']); ?>
                            </h5>

                            <p class="text-muted">
                                <?php echo htmlspecialchars($recette['description']); ?>
                            </p>

                            <p>
                                <strong>Calories :</strong>
                                <span class="text-success fw-bold">
                                    <?php echo htmlspecialchars($recette['calories'] ?? 0); ?> cal
                                </span>
                            </p>

                            <div class="mb-3">
                                <strong>Produits :</strong><br>
                                <?php if (!empty($produitsRecette)) { ?>
                                    <?php foreach ($produitsRecette as $produit) { ?>
                                        <span class="badge bg-success me-1 mb-1">
                                            <?php echo htmlspecialchars($produit['nom']); ?>
                                        </span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="text-muted">Aucun produit</span>
                                <?php } ?>
                            </div>

                            <div class="mt-auto">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="Detail-Recette.php?id=<?php echo $recette['id_recette']; ?>&action=<?php echo urlencode($action); ?>&motCle=<?php echo urlencode($motCle); ?>"
                                           class="btn btn-outline-success w-100 rounded-pill btn-sm">
                                            Détails
                                        </a>
                                    </div>

                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-warning w-100 rounded-pill btn-sm"
                                                disabled>
                                            Ajouter au panier
                                        </button>
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