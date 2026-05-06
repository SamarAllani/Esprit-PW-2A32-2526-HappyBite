<?php
session_start();


require_once '../../Controllers/FrigoController.php';
require_once '../../Controllers/CategorieController.php';
require_once '../../Controllers/AiRecetteController.php';
require_once '../../Config.php';

$frigoController = new FrigoController();
$categorieController = new CategorieController();

$idUtilisateur = 12;

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$categories = $categorieController->listCategories();
$recetteIA = null;
$menuArray = [];

if (isset($_SESSION['chefbot_menu'][$idUtilisateur])) {
    $recetteIA = $_SESSION['chefbot_menu'][$idUtilisateur];
    $menuArray = json_decode($recetteIA, true);
}

$db = Config::getConnexion();

$stmt = $db->prepare("
    SELECT objectif, allergenes, carences, maladies
    FROM profil_sante
    WHERE id_utilisateur = :id_utilisateur
    LIMIT 1
");

$stmt->execute([
    'id_utilisateur' => $idUtilisateur
]);

$profilSante = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['generer_recette_ia'])) {
        $produitsFrigoTemp = $frigoController->getFrigoByUtilisateur($idUtilisateur, $motCle, $idCategorie);

        if (!empty($produitsFrigoTemp)) {
            $aiController = new AiRecetteController();
            $recetteIA = $aiController->genererMenuSemaine($produitsFrigoTemp, $profilSante);

// sauvegarder le menu pour cet utilisateur
            $_SESSION['chefbot_menu'][$idUtilisateur] = $recetteIA;

            $menuArray = json_decode($recetteIA, true);
        } else {
            $recetteIA = "Votre frigo est vide. Impossible de générer une recette.";
        }

    } else {
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

        header('Location: List-Frigo.php#frigo-zone');
        exit;
    }
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
    <style>
.chefbot-header {
    background: linear-gradient(135deg, #e8f8ef, #ffffff);
    border-radius: 24px;
    padding: 28px;
    margin-bottom: 25px;
    border-left: 6px solid #20b978;
}

.chefbot-header h3 {
    color: #13a66b;
    font-weight: 800;
    margin-bottom: 8px;
}

.chefbot-header p {
    color: #555;
    margin-bottom: 15px;
}

.chefbot-profile {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.chefbot-profile span {
    background: white;
    border-radius: 999px;
    padding: 10px 16px;
    font-weight: 600;
    color: #2f6b4f;
    border: 1px solid #d7f0e2;
}

.chefbot-scroll {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding: 10px 5px 25px;
    scroll-snap-type: x mandatory;
}

.chefbot-card {
    min-width: 360px;
    max-width: 360px;
    background: white;
    border-radius: 24px;
    padding: 24px;
    scroll-snap-align: start;
    border: 1px solid #eef4ef;
}

.chefbot-day {
    background: #20b978;
    color: white;
    display: inline-block;
    padding: 8px 15px;
    border-radius: 999px;
    font-weight: 700;
    margin-bottom: 15px;
}

.chefbot-card h4 {
    font-weight: 800;
    color: #173b2c;
    margin-bottom: 15px;
}

.chefbot-card h6 {
    color: #13a66b;
    font-weight: 800;
    margin-top: 15px;
}

.priority {
    background: #f4fff8;
    border-radius: 16px;
    padding: 12px;
    color: #456;
}

.why-box {
    background: #fff8e6;
    border-radius: 16px;
    padding: 14px;
    margin-top: 15px;
    color: #5d4a1f;
}

.chefbot-scroll::-webkit-scrollbar {
    height: 8px;
}

.chefbot-scroll::-webkit-scrollbar-thumb {
    background: #20b978;
    border-radius: 999px;
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

    <form method="POST" class="text-center mb-4">
        <button type="submit" name="generer_recette_ia" class="btn btn-warning px-4 py-2 rounded-pill">
             Générer une recette avec mon frigo
        </button>
    </form>

    <?php if (!empty($menuArray) && is_array($menuArray)): ?>

<div class="chefbot-section mb-5">

    <div class="chefbot-header shadow-sm">
        <h3> ChefBot Planner</h3>
        <p>ChefBot a analysé votre frigo, votre profil santé et les produits les plus anciens pour limiter le gaspillage.</p>

        <div class="chefbot-profile">
            <span> Objectif : <?php echo htmlspecialchars($profilSante['objectif'] ?? 'non précisé'); ?></span>
            <span> Santé : <?php echo htmlspecialchars(($profilSante['maladies'] ?? 'aucune maladie') . ' | Allergènes : ' . ($profilSante['allergenes'] ?? 'aucun') . ' | Carences : ' . ($profilSante['carences'] ?? 'aucune')); ?></span>
        </div>
    </div>

    <div class="chefbot-scroll">
        <?php foreach ($menuArray as $jour): ?>
            <div class="chefbot-card shadow-sm">

                <div class="chefbot-day">
                    📅 <?php echo htmlspecialchars($jour['jour'] ?? 'Jour'); ?>
                </div>

                <h4><?php echo htmlspecialchars($jour['titre'] ?? 'Recette'); ?></h4>

                <p class="priority">
                     <strong>Produits prioritaires :</strong><br>
                    <?php echo htmlspecialchars($jour['produits_prioritaires'] ?? 'non précisé'); ?>
                </p>

                <h6> Ingrédients</h6>
                <ul>
                    <?php foreach (($jour['ingredients'] ?? []) as $ingredient): ?>
                        <li><?php echo htmlspecialchars($ingredient); ?></li>
                    <?php endforeach; ?>
                </ul>

                <h6> Étapes</h6>
                <ol>
                    <?php foreach (($jour['etapes'] ?? []) as $etape): ?>
                        <li><?php echo htmlspecialchars($etape); ?></li>
                    <?php endforeach; ?>
                </ol>

                <div class="why-box">
                    <strong>Pourquoi ?</strong><br>
                    <?php echo htmlspecialchars($jour['pourquoi'] ?? ''); ?>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php elseif ($recetteIA): ?>

<div class="alert alert-warning">
    <?php echo nl2br(htmlspecialchars($recetteIA)); ?>
</div>

<?php endif; ?>

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
                                            <input
                                                type="number"
                                                name="quantite"
                                                min="0"
                                                value="<?php echo (int)$produit['quantite']; ?>"
                                                class="form-control form-control-sm rounded-pill"
                                            >
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