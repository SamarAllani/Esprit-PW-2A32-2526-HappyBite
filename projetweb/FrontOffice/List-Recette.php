<?php
require_once __DIR__ . '/../Controllers/RecetteController.php';

$recetteController = new RecetteController();

$action = $_GET['action'] ?? 'normal';

$motCle = trim($_GET['motCle'] ?? '');

$allergie = 'Lactose';
$objectif = 'perte de poids';
$budget = 20;

if ($action === 'smart') {
    $recettes = $recetteController->rechercherRecettesIntelligentes(
        $motCle,
        $allergie,
        $objectif,
        $budget
    );
} else {
    $recettes = !empty($motCle)
        ? $recetteController->rechercherRecettes($motCle)
        : $recetteController->listRecettes();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Nos recettes</title>
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
    <div class="liste-recettes-stack">

        <h1 class="liste-produits-title">Nos Recettes</h1>
        <p class="liste-produits-subtitle">Choisissez selon votre besoin</p>

        <div class="mode-buttons">
            <a href="?action=normal" class="<?php echo $action !== 'smart' ? 'btn-commande-primary is-active-mode' : 'btn-commande-outline'; ?>">Toutes les recettes</a>
            <a href="?action=smart" class="<?php echo $action === 'smart' ? 'btn-commande-primary is-active-mode' : 'btn-commande-outline'; ?>">Recettes personnalisées</a>
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
                Affichage normal de toutes les recettes
            </div>
        <?php } ?>

        <div class="hb-search-shell">
            <form method="GET" class="hb-search-inner" action="">
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <input
                    type="text"
                    name="motCle"
                    placeholder="Rechercher une recette..."
                    value="<?php echo htmlspecialchars($motCle); ?>"
                    aria-label="Rechercher une recette"
                >
                <button type="submit" class="hb-search-btn">Rechercher</button>
            </form>
        </div>

        <?php if (empty($recettes)) { ?>
            <div class="liste-alert liste-alert--empty">Aucune recette trouvée.</div>
        <?php } else { ?>
            <div class="recettes-grid">
                <?php foreach ($recettes as $recette) { ?>
                    <?php
                    $produitsRecette = $recetteController->getProduitsByRecette($recette['id_recette']);
                    $idsPanier = array_values(array_filter(array_map(
                        static function ($p) {
                            return isset($p['id_produit']) ? (string) (int) $p['id_produit'] : '';
                        },
                        $produitsRecette
                    )));
                    $idsAttr = htmlspecialchars(implode(',', $idsPanier), ENT_QUOTES, 'UTF-8');
                    ?>
                    <article class="commande-panel recette-card-panel" aria-label="<?php echo htmlspecialchars($recette['nom']); ?>">
                        <h2 class="recette-card-title"><?php echo htmlspecialchars($recette['nom']); ?></h2>

                        <?php if (!empty($recette['description'])) { ?>
                            <p class="recette-card-desc"><?php echo htmlspecialchars($recette['description']); ?></p>
                        <?php } ?>

                        <p class="recette-card-cal">
                            <strong>Calories :</strong>
                            <span class="hb-calories"><?php echo htmlspecialchars((string) $recette['calories']); ?> cal</span>
                        </p>

                        <div class="recette-produit-tags">
                            <strong>Produits :</strong>
                            <?php foreach ($produitsRecette as $produit) { ?>
                                <span class="recette-tag"><?php echo htmlspecialchars($produit['nom']); ?></span>
                            <?php } ?>
                        </div>

                        <div class="recette-card-actions">
                            <a href="Detail-Recette.php?id=<?php echo (int) $recette['id_recette']; ?>" class="btn-commande-outline">Voir détails</a>
                            <button
                                type="button"
                                class="btn-commande-primary"
                                data-ajouter-recette="<?php echo $idsAttr; ?>"
                                <?php echo $idsPanier === [] ? ' disabled' : ''; ?>
                            >Ajouter au panier</button>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>

    </div>
</main>

<div id="panier-toast" class="panier-toast" role="status" aria-live="polite" hidden></div>

<style>
    .panier-toast {
        background: #fb8c00;
        border-color: #fb8c00;
        color: #fff;
    }
    .panier-toast.panier-toast--visible {
        background: #ef6c00;
        border-color: #ef6c00;
    }
</style>

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
    function addIdsSequential(ids, i, btn) {
        if (i >= ids.length) {
            showToast('Ingrédients ajoutés au panier');
            btn.disabled = false;
            return;
        }
        fetch('ajouter_panier.php?id=' + encodeURIComponent(ids[i]) + '&ajax=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showToast(data.message || 'Erreur lors de l’ajout');
                    btn.disabled = false;
                    return;
                }
                addIdsSequential(ids, i + 1, btn);
            })
            .catch(function () {
                showToast('Impossible d’ajouter au panier.');
                btn.disabled = false;
            });
    }
    document.querySelectorAll('[data-ajouter-recette]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var raw = btn.getAttribute('data-ajouter-recette') || '';
            var ids = raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            if (ids.length === 0) {
                showToast('Aucun produit lié à cette recette.');
                return;
            }
            btn.disabled = true;
            addIdsSequential(ids, 0, btn);
        });
    });
})();
</script>

</body>
</html>
