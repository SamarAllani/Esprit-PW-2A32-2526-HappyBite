<?php
require_once __DIR__ . '/../Controllers/RecetteController.php';

$recetteController = new RecetteController();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de la recette manquant.');
}

$id = (int) $_GET['id'];
$recette = $recetteController->showRecetteDetails($id);

if (!$recette) {
    die('Recette introuvable.');
}

$produitsRecette = $recetteController->getProduitsByRecette($id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — <?php echo htmlspecialchars($recette['nom']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
$nav_active = 'recettes';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="detail-shell">
        <div class="detail-card">
            <div class="detail-card-head">Détail de la recette</div>
            <div class="detail-card-body">
                <h2><?php echo htmlspecialchars($recette['nom']); ?></h2>
                <p class="detail-muted"><?php echo htmlspecialchars($recette['description']); ?></p>

                <div class="commande-panel recette-card-panel" style="margin-bottom: 20px;">
                    <p style="margin: 0 0 8px; font-weight: 700;">Calories totales</p>
                    <span class="hb-calories" style="font-size: 1.25rem;"><?php echo htmlspecialchars((string) ($recette['calories'] ?? 0)); ?> cal</span>
                </div>

                <h3 style="margin: 0 0 14px; font-size: 1rem; font-weight: 700; color: var(--hb-forest);">Produits composant la recette</h3>

                <?php if (!empty($produitsRecette)) { ?>
                    <div class="recettes-grid" style="grid-template-columns: 1fr;">
                        <?php foreach ($produitsRecette as $produit) { ?>
                            <?php
                            $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                            $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                            ?>
                            <div class="commande-panel recette-card-panel">
                                <h4 class="recette-card-title" style="font-size: 1rem;"><?php echo htmlspecialchars($produit['nom']); ?></h4>
                                <p class="recette-card-cal" style="margin-bottom: 8px;">
                                    <strong>Prix :</strong> <?php echo htmlspecialchars((string) $produit['prix']); ?> DT
                                </p>
                                <p class="recette-card-cal" style="margin-bottom: 8px;">
                                    <strong>Calories :</strong> <span class="hb-calories"><?php echo htmlspecialchars((string) ($produit['calories'] ?? 0)); ?> cal</span>
                                </p>
                                <div class="recette-produit-tags">
                                    <strong>Allergènes</strong>
                                    <?php if (!empty($allergenes)) { ?>
                                        <?php foreach ($allergenes as $item) { ?>
                                            <span class="recette-tag" style="background: #c62828;"><?php echo htmlspecialchars($item); ?></span>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="produit-tag-muted">Aucun</span>
                                    <?php } ?>
                                </div>
                                <div class="recette-produit-tags">
                                    <strong>Bénéfices</strong>
                                    <?php if (!empty($benefices)) { ?>
                                        <?php foreach ($benefices as $item) { ?>
                                            <span class="recette-tag"><?php echo htmlspecialchars($item); ?></span>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="produit-tag-muted">Non précisé</span>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="detail-muted">Aucun produit associé à cette recette.</p>
                <?php } ?>

                <div class="detail-actions">
                    <a href="List-Recette.php" class="btn-commande-outline">Retour à la liste</a>
                    <a href="#" class="btn-commande-primary">Essayer cette recette</a>
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
