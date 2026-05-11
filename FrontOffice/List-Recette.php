<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Controllers/RecetteController.php';
require_once __DIR__ . '/../Controllers/AiRecetteController.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/includes/panier_session.php';

$recetteController = new RecetteController();
panier_ensure_session();

$analyseIA = null;
$imageAnalysePreview = null;
$action = $_GET['action'] ?? 'normal';
$motCle = trim($_GET['motCle'] ?? '');

$loggedIn = !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$idUtilisateur = $loggedIn ? (int) ($_SESSION['user_id'] ?? 0) : 0;
if (!$loggedIn && $action === 'smart') {
    $action = 'normal';
}

$profilSante = false;
if ($loggedIn && $idUtilisateur > 0) {
    $db = Config::getConnexion();
    $stmt = $db->prepare('SELECT * FROM profil_sante WHERE id_utilisateur = :id_utilisateur LIMIT 1');
    $stmt->execute(['id_utilisateur' => $idUtilisateur]);
    $profilSante = $stmt->fetch(PDO::FETCH_ASSOC);
}

$profilSantePourIa = is_array($profilSante) ? $profilSante : [];

// TRAITEMENT IA PHOTO DIRECTEMENT DANS LA PAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']['tmp_name'])) {
    $imagePath = $_FILES['image']['tmp_name'];

    // Prévisualisation de l'image envoyée
    $mimeType = mime_content_type($imagePath);
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageAnalysePreview = "data:" . $mimeType . ";base64," . $imageData;

    $ai = new AiRecetteController();
    $analyseIA = $ai->analyserPlatPhoto($imagePath, $profilSantePourIa);
}

if ($action === 'smart' && $loggedIn && $profilSante) {
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style-original-views.css">
    <style>
        .panier-toast {
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            min-width: 260px;
            max-width: 90%;
            background: #f59e0b;
            color: #fff;
            border-radius: 999px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            font-weight: 600;
            padding: 12px 20px;
            animation: panierToastIn 0.25s ease-out;
        }

        @keyframes panierToastIn {
            from {
                opacity: 0;
                transform: translate(-50%, -12px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }
        .ai-image-preview {
    width: 100%;
    display: flex;
    justify-content: center;
}

.ai-image-frame {
    width: fit-content;
    max-width: 100%;
    background: transparent;
    border: none;
    border-radius: 22px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.ai-image-frame img {
    max-width: 100%;
    max-height: 360px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 18px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
}

        /* Même style que le bouton « Demandez-moi » (Ai.php) */
        .caloryeye-analyse-btn {
            width: 100%;
            min-height: 52px;
            border: 2px solid #43a047;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 12px 34px rgba(19, 30, 23, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            color: inherit;
            text-decoration: none;
        }

        .caloryeye-analyse-btn:hover {
            filter: brightness(0.98);
        }

        .caloryeye-analyse-btn__label {
            background: linear-gradient(90deg, #e53935 0%, #fb8c00 52%, #43a047 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .caloryeye-analyse-btn__icon {
            width: 22px;
            height: 22px;
            object-fit: contain;
            display: block;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<?php
$nav_active = 'recettes';
require __DIR__ . '/includes/nav_front.php';
?>

<div id="panier-toast" class="panier-toast" role="status" aria-live="polite" style="display:none;"></div>

<main class="commande-wrap">
<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Nos Recettes</h2>
        <p class="text-muted">Choisissez selon votre besoin</p>
    </div>

    <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
        <a href="?action=normal" class="btn btn-outline-secondary rounded-pill px-4">
            Toutes les recettes
        </a>
        <?php if ($loggedIn): ?>
            <a href="?action=smart" class="btn btn-success rounded-pill px-4">
                Recettes personnalisées
            </a>
        <?php else: ?>
            <span class="btn btn-success rounded-pill px-4 disabled" style="opacity:0.65;cursor:not-allowed;" title="Connectez-vous pour le mode personnalisé">
                Recettes personnalisées
            </span>
        <?php endif; ?>
    </div>

    <!-- BLOC IA PHOTO -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h5 class="fw-bold mb-3">CaloryEye: Analyse ton plat</h5>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="caloryeye-analyse-btn w-100">
                            <img src="images/analyse.png" alt="" class="caloryeye-analyse-btn__icon">
                            <span class="caloryeye-analyse-btn__label">Analyser</span>
                        </button>
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
                <?php if (!empty($imageAnalysePreview)) { ?>
    <div class="ai-image-preview mb-4">
        <div class="ai-image-frame">
            <img
                src="<?php echo htmlspecialchars($imageAnalysePreview); ?>"
                alt="Plat analysé"
            >
        </div>
    </div>
<?php } ?>

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

    <?php if ($action === 'smart' && $loggedIn && $profilSante) { ?>
        <div class="alert alert-success text-center shadow-sm">
            <strong>Mode personnalisé activé :</strong><br>
            <?php
            $infos = [];
            foreach (['allergenes' => 'Allergènes', 'carences' => 'Carences', 'maladies' => 'Maladies'] as $field => $label) {
                $raw = $profilSante[$field] ?? '';
                $items = [];
                if (is_string($raw) && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    $items = is_array($decoded)
                        ? array_values(array_filter(array_map('trim', array_map('strval', $decoded))))
                        : array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $raw))));
                }
                if (!empty($items)) {
                    $infos[] = $label . ' : ' . htmlspecialchars(implode(', ', $items), ENT_QUOTES, 'UTF-8');
                }
            }

            if (!empty($profilSante['objectif'])) {
                $infos[] = 'Objectif : ' . htmlspecialchars((string) $profilSante['objectif'], ENT_QUOTES, 'UTF-8');
            }

            echo !empty($infos) ? implode(' | ', $infos) : 'Vos recettes sont filtrées selon votre profil (objectifs et calories).';
            ?>
        </div>
    <?php } elseif ($action === 'smart' && $loggedIn) { ?>
        <div class="alert alert-warning text-center shadow-sm">
            Aucun profil santé trouvé pour votre compte. Complétez votre profil santé pour activer le mode personnalisé.
        </div>
    <?php } else { ?>
        <div class="alert alert-secondary text-center shadow-sm">
            <?php if ($loggedIn): ?>
                Affichage de toutes les recettes disponibles
            <?php else: ?>
                Toutes les recettes — connectez-vous pour le mode personnalisé selon votre profil santé
            <?php endif; ?>
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
                                        src="../uploads/<?php echo htmlspecialchars($recette['image']); ?>"
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
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="action_panier_recette" value="ajouter_panier_recette">
                                            <input type="hidden" name="id_recette" value="<?php echo $recette['id_recette']; ?>">
                                            <button type="button"
                                                    class="btn btn-warning w-100 rounded-pill btn-sm js-toast-btn js-panier-recette-btn"
                                                    data-recette-id="<?php echo (int) $recette['id_recette']; ?>"
                                                    data-toast-message="Ajouter aux panier">
                                                Ajouter au panier
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
</main>

<footer>
    © 2026 HappyBite
</footer>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var toast = document.getElementById('panier-toast');
        var buttons = document.querySelectorAll('.js-toast-btn');
        var hideTimer = null;

        if (!toast || buttons.length === 0) {
            return;
        }

        function hideToast() {
            toast.style.opacity = '0';
            setTimeout(function () {
                toast.style.display = 'none';
            }, 300);
        }

        function showToast(message) {
            toast.textContent = message;
            toast.style.display = 'block';
            toast.style.opacity = '1';
            toast.style.transition = 'opacity 0.3s ease';
            if (hideTimer) {
                clearTimeout(hideTimer);
            }
            hideTimer = setTimeout(hideToast, 2200);
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                if (button.classList.contains('js-panier-recette-btn')) {
                    var recetteId = button.dataset.recetteId || '';
                    if (!recetteId) {
                        showToast('Recette introuvable');
                        return;
                    }
                    button.disabled = true;
                    fetch('ajouter_panier_recette.php?ajax=1&id_recette=' + encodeURIComponent(recetteId), {
                        method: 'GET',
                        credentials: 'same-origin'
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (data && data.ok) {
                                showToast(button.dataset.toastMessage || 'Ajouter');
                            } else {
                                showToast((data && data.message) ? data.message : 'Ajout impossible');
                            }
                        })
                        .catch(function () {
                            showToast('Erreur reseau, reessayez');
                        })
                        .finally(function () {
                            button.disabled = false;
                        });
                    return;
                }
                showToast(button.dataset.toastMessage || 'Ajouter');
            });
        });
    })();
</script>
</body>
</html>



