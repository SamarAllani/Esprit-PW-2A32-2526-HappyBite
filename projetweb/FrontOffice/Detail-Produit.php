<?php
require_once __DIR__ . '/../Controllers/ProduitController.php';

$produitController = new ProduitController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID du produit manquant.');
}

$id = (int) $_GET['id'];
$produit = $produitController->showProduitDetails($id);

if (!$produit) {
    die('Produit introuvable.');
}

$allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
$benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — <?php echo htmlspecialchars($produit['nom']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
$nav_active = 'produits';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="detail-shell">
        <div class="detail-card">
            <div class="detail-card-head">Détail du produit</div>
            <div class="detail-card-body">
                <h2><?php echo htmlspecialchars($produit['nom']); ?></h2>
                <p class="detail-muted"><?php echo htmlspecialchars($produit['nom_categorie']); ?></p>

                <div style="text-align: center; margin-bottom: 22px;">
                    <?php if (!empty($produit['image'])) { ?>
                        <img
                            src="../uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                            alt=""
                            style="max-width: 260px; max-height: 260px; object-fit: cover; border-radius: 16px; border: 1px solid var(--hb-card-border);"
                        >
                    <?php } else { ?>
                        <p class="detail-muted">Aucune image définie.</p>
                    <?php } ?>
                </div>

                <div class="commande-field-row" style="margin-bottom: 20px;">
                    <div class="commande-panel recette-card-panel" style="margin: 0;">
                        <p style="margin: 0 0 6px; font-weight: 700;">Prix</p>
                        <span class="produit-card-price" style="font-size: 1.2rem;"><?php echo htmlspecialchars((string) $produit['prix']); ?> DT</span>
                    </div>
                    <div class="commande-panel recette-card-panel" style="margin: 0;">
                        <p style="margin: 0 0 6px; font-weight: 700;">Calories</p>
                        <span class="hb-calories" style="font-size: 1.2rem;"><?php echo htmlspecialchars((string) ($produit['calories'] ?? 'Non défini')); ?></span>
                    </div>
                </div>

                <div class="recette-produit-tags" style="margin-bottom: 16px;">
                    <strong>Allergènes / composants sensibles</strong>
                    <?php if (!empty($allergenes)) { ?>
                        <?php foreach ($allergenes as $item) { ?>
                            <span class="recette-tag" style="background: #c62828;"><?php echo htmlspecialchars($item); ?></span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="produit-tag-muted">Aucun allergène précisé.</span>
                    <?php } ?>
                </div>

                <div class="recette-produit-tags" style="margin-bottom: 16px;">
                    <strong>Bénéfices</strong>
                    <?php if (!empty($benefices)) { ?>
                        <?php foreach ($benefices as $item) { ?>
                            <span class="recette-tag"><?php echo htmlspecialchars($item); ?></span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="produit-tag-muted">Aucun bénéfice précisé.</span>
                    <?php } ?>
                </div>

                <p class="detail-muted" style="margin-bottom: 0;">
                    <strong style="color: #333;">Date d'ajout :</strong> <?php echo htmlspecialchars($produit['date_ajout']); ?>
                </p>

                <div class="detail-actions">
                    <a href="List-Produit.php" class="btn-commande-outline">Retour à la liste</a>
                    <a href="#" class="btn-commande-primary">Ajouter au frigo</a>
                </div>
            </div>
        </div>
    </div>
</main>

<footer>
    © 2026 HappyBite
</footer>

</body>
</html>
