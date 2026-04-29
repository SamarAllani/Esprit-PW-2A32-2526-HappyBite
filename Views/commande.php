<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/includes/panier_session.php';

panier_ensure_session();

$commandeCtrl = new CommandeController();
$produitCtrl = new ProduitController();

if (isset($_GET['annuler'])) {
    $idAnn = (int) ($_SESSION['commande_id'] ?? 0);
    if ($idAnn > 0) {
        $commandeCtrl->supprimerCommande($idAnn);
    }
    unset($_SESSION['commande_id']);
    header('Location: panier.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preparer_commande'])) {
    $items = panier_get_items();
    if ($items === []) {
        header('Location: panier.php');
        exit;
    }
    $lignes = [];
    foreach ($items as $idP => $ent) {
        $p = $produitCtrl->getProduitById($idP);
        if (!$p) {
            continue;
        }
        $lignes[] = [
            'id_produit' => $idP,
            'quantite' => $ent['quantite'],
            'prix_unitaire' => $ent['prix_unitaire'],
            'nom' => (string) $p['nom'],
        ];
    }
    if ($lignes === []) {
        header('Location: panier.php');
        exit;
    }
    $res = $commandeCtrl->creerCommandeDepuisPanier($lignes);
    $_SESSION['commande_id'] = $res['id_commande'];
    header('Location: commande.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terminer_commande'])) {
    $idCmd = (int) ($_SESSION['commande_id'] ?? 0);
    if ($idCmd < 1) {
        header('Location: panier.php');
        exit;
    }
    $mode = trim((string) ($_POST['mode_paiement'] ?? ''));
    if ($mode === '') {
        $_SESSION['flash_erreur_commande'] = 'Veuillez choisir un mode de paiement.';
        header('Location: commande.php');
        exit;
    }
    $redStr = str_replace(',', '.', trim((string) ($_POST['reduction'] ?? '0')));
    $reduction = is_numeric($redStr) ? (float) $redStr : 0.0;

    $commandeCtrl->finaliserCommande($idCmd, $mode, $reduction);
    panier_clear();
    header('Location: livraison.php');
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

$nomsProduits = $commandeCtrl->getNomsProduitsCommande($idCommande);
$totalFormate = number_format((float) $commande['total'], 2, ',', ' ');
$flashErreur = $_SESSION['flash_erreur_commande'] ?? '';
unset($_SESSION['flash_erreur_commande']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Commande</title>
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
    <section class="commande-panel" aria-label="Commande">
        <?php if ($flashErreur !== '') { ?>
            <p class="commande-flash-erreur"><?php echo htmlspecialchars($flashErreur); ?></p>
        <?php } ?>

        <form method="post" action="commande.php" id="form-commande">
            <div class="commande-field">
                <label for="produit">Produit</label>
                <textarea id="produit" name="produit" readonly rows="4"><?php echo htmlspecialchars($nomsProduits); ?></textarea>
            </div>
            <div class="commande-field">
                <label for="total">Total</label>
                <input type="text" id="total" name="total" readonly value="<?php echo htmlspecialchars($totalFormate); ?> DT">
            </div>
            <div class="commande-field">
                <label for="reduction">Code de Promo</label>
                <input type="text" id="reduction" name="reduction" placeholder="Montant ou 0" value="<?php echo htmlspecialchars((string) ($_POST['reduction'] ?? '')); ?>">
            </div>
            <div class="commande-field">
                <label for="mode-paiement">Mode de paiement</label>
                <select id="mode-paiement" name="mode_paiement" required>
                    <option value="" selected disabled>Choisir un mode de paiement</option>
                    <option value="carte">Carte</option>
                    <option value="cash">Cash</option>
                    <option value="paypal">Paypal</option>
                </select>
                <div id="carte-paiement-details" class="mode-paiement-details" hidden>
                    <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                    <div class="commande-field commande-field--nested">
                        <label for="carte-titulaire">Titulaire de la carte</label>
                        <input type="text" id="carte-titulaire" autocomplete="off" placeholder="Nom sur la carte">
                    </div>
                    <div class="commande-field commande-field--nested">
                        <label for="carte-numero">Numéro de carte</label>
                        <input type="text" id="carte-numero" inputmode="numeric" autocomplete="off" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div class="commande-field-row">
                        <div class="commande-field commande-field--nested">
                            <label for="carte-expiration">Expiration</label>
                            <input type="text" id="carte-expiration" autocomplete="off" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="commande-field commande-field--nested">
                            <label for="carte-cvv">CVV</label>
                            <input type="password" id="carte-cvv" autocomplete="off" placeholder="•••" maxlength="4">
                        </div>
                    </div>
                </div>
                <div id="cash-paiement-details" class="mode-paiement-details" hidden>
                    <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                    <div class="commande-field commande-field--nested">
                        <label for="cash-montant">Montant prévu (billets / pièces)</label>
                        <input type="text" id="cash-montant" autocomplete="off" placeholder="Ex. 50 DT">
                    </div>
                    <div class="commande-field commande-field--nested">
                        <label for="cash-contact">Téléphone pour le livreur</label>
                        <input type="tel" id="cash-contact" autocomplete="off" placeholder="+216 …">
                    </div>
                    <div class="commande-field commande-field--nested">
                        <label for="cash-note">Note pour la livraison</label>
                        <input type="text" id="cash-note" autocomplete="off" placeholder="Sonnette, étage…">
                    </div>
                </div>
                <div id="paypal-paiement-details" class="mode-paiement-details" hidden>
                    <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                    <div class="commande-field commande-field--nested">
                        <label for="paypal-email">E-mail du compte PayPal</label>
                        <input type="email" id="paypal-email" autocomplete="off" placeholder="vous@exemple.com">
                    </div>
                    <div class="commande-field commande-field--nested">
                        <label for="paypal-nom">Nom affiché sur PayPal</label>
                        <input type="text" id="paypal-nom" autocomplete="off" placeholder="Nom du compte">
                    </div>
                </div>
            </div>
            <div class="commande-actions">
                <a href="commande.php?annuler=1" class="btn-commande-outline">Annuler</a>
                <button type="submit" name="terminer_commande" value="1" class="btn-commande-primary">Terminer</button>
            </div>
        </form>
    </section>
</main>

<footer>
    © 2026 HappyBite
</footer>

<script src="js/controles.js" defer></script>

</body>
</html>
