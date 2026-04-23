<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/includes/panier_session.php';

panier_ensure_session();
$produitController = new ProduitController();

if (isset($_GET['supprimer'])) {
    $idSuppr = (int) $_GET['supprimer'];
    if ($idSuppr > 0) {
        panier_remove_product($idSuppr);
    }
    header('Location: panier.php');
    exit;
}

$items = panier_get_items();
$iconeSuppr = is_file(__DIR__ . '/images/delete.png') ? 'images/delete.png' : 'images/delete.svg';

$lignesPanier = [];
foreach ($items as $idProduit => $entry) {
    $p = $produitController->getProduitById($idProduit);
    if (!$p) {
        panier_remove_product($idProduit);
        continue;
    }
    $lignesPanier[] = [
        'id_produit' => $idProduit,
        'nom' => (string) $p['nom'],
        'prix_unitaire' => $entry['prix_unitaire'],
        'quantite' => $entry['quantite'],
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Panier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
$nav_active = 'panier';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="panier-stack">
        <h1 class="panier-page-title">Panier</h1>
        <section class="commande-panel panier-panel" aria-label="Panier">
            <?php if ($lignesPanier === []) { ?>
                <p class="panier-vide">Votre panier est vide.</p>
            <?php } else { ?>
                <ul class="panier-lignes">
                    <?php foreach ($lignesPanier as $ligne) { ?>
                        <li class="panier-ligne">
                            <div class="panier-ligne-infos">
                                <span class="panier-ligne-nom"><?php echo htmlspecialchars($ligne['nom']); ?></span>
                                <span class="panier-ligne-meta">
                                    <?php echo htmlspecialchars(number_format($ligne['prix_unitaire'], 2, ',', ' ')); ?> DT
                                    × <?php echo (int) $ligne['quantite']; ?>
                                </span>
                            </div>
                            <a href="panier.php?supprimer=<?php echo (int) $ligne['id_produit']; ?>"
                               class="panier-ligne-suppr"
                               aria-label="Retirer du panier"
                               title="Retirer">
                                <img src="<?php echo htmlspecialchars($iconeSuppr); ?>" alt="" width="24" height="24">
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <div class="commande-actions panier-panel-actions">
                <a href="List-Produit.php" class="btn-commande-outline">Ajouter un autre produit</a>
                <?php if ($lignesPanier !== []) { ?>
                    <form method="post" action="commande.php" class="panier-form-commander">
                        <input type="hidden" name="preparer_commande" value="1">
                        <button type="submit" class="btn-commande-primary">Commander</button>
                    </form>
                <?php } else { ?>
                    <span class="panier-commander-disabled" title="Panier vide">Commander</span>
                <?php } ?>
            </div>
        </section>
    </div>
</main>

<footer>
    © 2026 HappyBite
</footer>

</body>
</html>
