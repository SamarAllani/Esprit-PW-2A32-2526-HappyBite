<?php
require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/../Controllers/CategorieController.php';

$produitController = new ProduitController();
$categorieController = new CategorieController();

$categories = $categorieController->listCategories();

$action = $_GET['action'] ?? 'normal';

$motCle = trim($_GET['motCle'] ?? '');
$idCategorie = trim($_GET['id_categorie'] ?? '');

$allergie = 'Lactose';
$objectif = 'perte de poids';
$budget = 20;

if ($action === 'smart') {
    $produits = $produitController->rechercherProduitsIntelligents(
        $motCle,
        $idCategorie,
        $allergie,
        $objectif,
        $budget
    );
} else {
    $produits = (!empty($motCle) || !empty($idCategorie))
        ? $produitController->rechercherProduits($motCle, $idCategorie)
        : $produitController->listProduits();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Nos produits</title>
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
    <div class="liste-produits-stack">

        <h1 class="liste-produits-title">Nos produits</h1>
        <p class="liste-produits-subtitle">Choisissez selon votre besoin</p>

        <div class="mode-buttons">
            <a href="?action=normal" class="<?php echo $action !== 'smart' ? 'btn-commande-primary is-active-mode' : 'btn-commande-outline'; ?>">Tous les produits</a>
            <a href="?action=smart" class="<?php echo $action === 'smart' ? 'btn-commande-primary is-active-mode' : 'btn-commande-outline'; ?>">Produits personnalisés</a>
        </div>

        <?php if ($action === 'smart') { ?>
            <div class="liste-alert liste-alert--smart">
                <strong>Mode personnalisé activé :</strong>
                Allergie : <?php echo htmlspecialchars($allergie); ?> ·
                Objectif : <?php echo htmlspecialchars($objectif); ?> ·
                Budget : <?php echo htmlspecialchars((string) $budget); ?> DT
            </div>
        <?php } else { ?>
            <div class="liste-alert liste-alert--normal">
                Affichage normal de tous les produits
            </div>
        <?php } ?>

        <section class="commande-panel liste-filter-panel" aria-label="Filtres">
            <form method="GET">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">

                <div class="liste-filter-row">
                    <div class="commande-field liste-filter-field">
                        <label for="motCle">Rechercher un produit</label>
                        <input
                            type="text"
                            id="motCle"
                            name="motCle"
                            placeholder="Nom du produit..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>
                    <div class="commande-field liste-filter-field">
                        <label for="id_categorie">Catégorie</label>
                        <select id="id_categorie" name="id_categorie">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $categorie) { ?>
                                <option
                                    value="<?php echo (int) $categorie->getIdCategorie(); ?>"
                                    <?php echo ($idCategorie == $categorie->getIdCategorie()) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($categorie->getNom()); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="liste-filter-submit">
                        <button type="submit" class="btn-commande-primary liste-filter-btn">Filtrer</button>
                    </div>
                </div>
            </form>
        </section>

        <?php if (empty($produits)) { ?>
            <div class="liste-alert liste-alert--empty">Aucun produit trouvé.</div>
        <?php } else { ?>
            <div class="produits-grid">
                <?php foreach ($produits as $produit) { ?>
                    <?php
                    $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                    $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                    $idProd = (int) $produit['id_produit'];
                    ?>
                    <article class="commande-panel produit-card" aria-label="<?php echo htmlspecialchars($produit['nom']); ?>">
                        <div class="produit-card-media">
                            <?php if (!empty($produit['image'])) { ?>
                                <img
                                    class="produit-card-img"
                                    src="../uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                    alt=""
                                >
                            <?php } else { ?>
                                <div class="produit-card-placeholder">Aucune image</div>
                            <?php } ?>
                        </div>

                        <h2 class="produit-card-title"><?php echo htmlspecialchars($produit['nom']); ?></h2>
                        <p class="produit-card-cat"><?php echo htmlspecialchars($produit['nom_categorie']); ?></p>

                        <p class="produit-card-line">
                            <strong>Prix :</strong>
                            <span class="produit-card-price"><?php echo htmlspecialchars((string) $produit['prix']); ?> DT</span>
                        </p>
                        <p class="produit-card-line">
                            <strong>Calories :</strong>
                            <span class="hb-calories"><?php echo htmlspecialchars((string) ($produit['calories'] ?? 'Non défini')); ?> cal</span>
                        </p>

                        <div class="produit-card-block">
                            <strong>Allergènes :</strong>
                            <div class="produit-tags">
                                <?php if (!empty($allergenes)) { ?>
                                    <?php foreach ($allergenes as $item) { ?>
                                        <span class="produit-tag produit-tag--allergene"><?php echo htmlspecialchars($item); ?></span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="produit-tag-muted">Aucun</span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="produit-card-block">
                            <strong>Bénéfices :</strong>
                            <div class="produit-tags">
                                <?php if (!empty($benefices)) { ?>
                                    <?php foreach ($benefices as $item) { ?>
                                        <span class="produit-tag produit-tag--benefice"><?php echo htmlspecialchars($item); ?></span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="produit-tag-muted">Non précisé</span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="commande-actions produit-card-actions">
                            <a href="Detail-Produit.php?id=<?php echo $idProd; ?>" class="btn-commande-outline produit-action-btn">Voir détails</a>
                            <button type="button" class="btn-commande-primary produit-action-btn" data-ajouter-panier="<?php echo (int) $idProd; ?>">Ajouter au panier</button>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>

    </div>
</main>

<div id="panier-toast" class="panier-toast" role="status" aria-live="polite" hidden></div>

<footer>
    © 2026 HappyBite
</footer>

<script>
(function () {
    var toast = document.getElementById('panier-toast');
    function showToast(text) {
        if (!toast) return;
        toast.textContent = text;
        toast.hidden = false;
        toast.classList.add('panier-toast--visible');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () {
            toast.classList.remove('panier-toast--visible');
            toast.hidden = true;
        }, 2200);
    }
    document.querySelectorAll('[data-ajouter-panier]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-ajouter-panier');
            if (!id) return;
            btn.disabled = true;
            fetch('ajouter_panier.php?id=' + encodeURIComponent(id) + '&ajax=1', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    showToast(data.message || (data.ok ? 'Ajouté au panier' : 'Erreur'));
                })
                .catch(function () {
                    showToast('Impossible d’ajouter au panier.');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
})();
</script>

</body>
</html>
