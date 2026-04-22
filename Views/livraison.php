<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/LivraisonController.php';
require_once __DIR__ . '/includes/panier_session.php';

panier_ensure_session();

$commandeCtrl = new CommandeController();
$livraisonCtrl = new LivraisonController();

if (isset($_GET['annuler_livraison'])) {
    $idCmd = (int) ($_SESSION['commande_id'] ?? 0);
    if ($idCmd > 0) {
        $cmd = $commandeCtrl->getCommandeById($idCmd);
        if ($cmd !== null && !empty($cmd['id_livraison'])) {
            $livraisonCtrl->supprimerLivraison((int) $cmd['id_livraison']);
        }
    }
    header('Location: commande.php');
    exit;
}

if (isset($_GET['ok'])) {
    unset($_SESSION['commande_id']);
    header('Location: Home.php');
    exit;
}

$idCommande = (int) ($_SESSION['commande_id'] ?? 0);
if ($idCommande < 1) {
    header('Location: panier.php');
    exit;
}

$commande = $commandeCtrl->getCommandeById($idCommande);
if ($commande === null) {
    unset($_SESSION['commande_id']);
    header('Location: panier.php');
    exit;
}

if (empty($commande['id_livraison'])) {
    $livraisonCtrl->creerEtLierCommande($idCommande);
    $commande = $commandeCtrl->getCommandeById($idCommande);
    if ($commande === null || empty($commande['id_livraison'])) {
        die('Erreur lors de la création de la livraison.');
    }
}

$livraison = $livraisonCtrl->getLivraisonById((int) $commande['id_livraison']);
if ($livraison === null) {
    die('Livraison introuvable.');
}

$dateStr = LivraisonController::extraireDatePourAffichage($livraison);
$dateAffiche = $dateStr;
$dt = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
if ($dt instanceof DateTimeImmutable) {
    $dateAffiche = $dt->format('d/m/Y');
}
$statutAffiche = (string) $livraison['statut'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Livraison</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <img class="site-logo" src="images/logo.png" alt="">
</header>

<nav class="main-nav">
    <div class="nav-links">
        <a href="Home.php">Accueil</a>
        <a href="#">Communauté</a>
        <a href="List-Produit.php" class="nav-link-active" aria-current="page">Produits</a>
        <a href="List-Recette.php">Recettes</a>
        <a href="#">Défis</a>
        <a href="#">Classement</a>
    </div>
    <div class="nav-icons">
        <a href="panier.php" class="nav-cart-link" aria-label="Panier">
            <img class="nav-cart-img" src="images/panier.png" alt="" width="40" height="40">
        </a>
        <details class="nav-profile-dropdown">
            <summary class="nav-profile-trigger" aria-label="Compte">
                <img class="nav-profile-img" src="images/profile.png" alt="" width="40" height="40">
            </summary>
            <div class="nav-profile-menu">
                <a href="#" class="nav-profile-btn nav-profile-signup">S'inscrire</a>
                <a href="#" class="nav-profile-btn nav-profile-login">Se connecter</a>
            </div>
        </details>
    </div>
</nav>

<main class="commande-wrap">
    <section class="commande-panel livraison-panel" aria-label="Livraison">
        <div class="livraison-hero">
            <h1 class="livraison-title">Commande confirmée !</h1>
            <img class="livraison-success-icon" src="images/success.svg" alt="" width="60" height="60">
        </div>
        <p class="livraison-line livraison-line--value">
            <span class="livraison-label">Statut :</span>
            <?php echo htmlspecialchars($statutAffiche); ?>
        </p>
        <p class="livraison-line livraison-line--value">
            <span class="livraison-label">Livraison prévue le</span>
            <?php echo htmlspecialchars($dateAffiche); ?>
        </p>

        <div class="commande-actions livraison-actions">
            <a href="livraison.php?annuler_livraison=1" class="btn-commande-outline">Annuler livraison</a>
            <a href="livraison.php?ok=1" class="btn-commande-primary">Ok</a>
        </div>
    </section>
</main>

<footer>
    © 2026 HappyBite
</footer>

</body>
</html>
