<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/../Controllers/RecetteController.php';
require_once __DIR__ . '/../Controllers/PostController.php';

$produitsFeatured = [];
$recettesFeatured = [];
$postsFeatured = [];

try {
    $produitController = new ProduitController();
    $recetteController = new RecetteController();
    $postController = new PostController();

    $produitsFeatured = array_slice($produitController->listProduits(), 0, 4);
    $recettesFeatured = array_slice($recetteController->listRecettes(), 0, 3);
    $postsFeatured = array_slice($postController->getAll(), 0, 4);
} catch (Throwable $e) {
    $produitsFeatured = [];
    $recettesFeatured = [];
    $postsFeatured = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
$nav_active = 'accueil';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="home-wrap home-showcase">
        <section class="home-hero-slider" aria-label="Bannière principale">
            <img class="home-hero-slide is-active" src="images/pic1.webp" alt="Eat healthy - image 1" loading="eager">
            <img class="home-hero-slide" src="images/pic2.png" alt="Eat healthy - image 2" loading="lazy">
            <img class="home-hero-slide" src="images/pic3.webp" alt="Eat healthy - image 3" loading="lazy">
            <div class="home-hero-overlay">
                <h1>Mangez sain.<br>Vivez mieux.</h1>
                <p>Découvrez des repas équilibrés, des produits frais et une meilleure routine avec HappyBite.</p>
                <div class="home-hero-cta">
                    <a class="home-hero-btn home-hero-btn--primary" href="List-Produit.php">Explorer les produits</a>
                    <a class="home-hero-btn home-hero-btn--ghost" href="List-Recette.php">Voir les recettes</a>
                </div>
            </div>
        </section>

        <section class="home-feature-grid" aria-label="Acces rapides">
            <article class="home-feature-card home-feature-card--products">
                <div class="home-feature-head">
                    <span class="home-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 10c0 6 4 10 8 10s8-4 8-10c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2Z"/>
                            <path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </span>
                    <h2>Produits sains</h2>
                </div>
                <p>Achetez des produits nutritifs adaptés à votre style de vie.</p>
                <a href="List-Produit.php" class="home-feature-link">Découvrir <span aria-hidden="true">→</span></a>
            </article>
            <article class="home-feature-card home-feature-card--recipes">
                <div class="home-feature-head">
                    <span class="home-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3h8"/>
                            <path d="M10 3v6a2 2 0 0 1-2 2H6"/>
                            <path d="M14 3v6a2 2 0 0 0 2 2h2"/>
                            <path d="M6 11v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8"/>
                        </svg>
                    </span>
                    <h2>Recettes</h2>
                </div>
                <p>Apprenez à cuisiner équilibré à la maison, simplement.</p>
                <a href="List-Recette.php" class="home-feature-link">Parcourir <span aria-hidden="true">→</span></a>
            </article>
            <article class="home-feature-card home-feature-card--community">
                <div class="home-feature-head">
                    <span class="home-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                        </svg>
                    </span>
                    <h2>Communauté</h2>
                </div>
                <p>Partagez vos plats et découvrez les publications des autres.</p>
                <a href="Communaute.php" class="home-feature-link">Explorer <span aria-hidden="true">→</span></a>
            </article>
            <article class="home-feature-card home-feature-card--tracker">
                <div class="home-feature-head">
                    <span class="home-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V5"/>
                            <path d="M20 19V5"/>
                            <path d="M7 14l3-3 3 2 4-5"/>
                        </svg>
                    </span>
                    <h2>Suivi santé</h2>
                </div>
                <p>Suivez vos progrès au quotidien et améliorez vos objectifs.</p>
                <a href="sante.php" class="home-feature-link">Commencer <span aria-hidden="true">→</span></a>
            </article>
        </section>

        <section class="home-section" aria-label="Produits en vedette">
            <div class="home-section-head">
                <h2 class="home-section-title">Produits en vedette</h2>
                <a class="home-section-more" href="List-Produit.php">Voir tout <span aria-hidden="true">→</span></a>
            </div>
            <div class="home-card-grid">
                <?php if ($produitsFeatured === []) { ?>
                    <article class="home-media-card">
                        <div class="home-media-card__img home-media-card__img--p1" aria-hidden="true"></div>
                        <div class="home-media-card__body">
                            <h3>Aucun produit disponible</h3>
                            <p>Ajoutez des produits en base pour afficher cette section.</p>
                            <a class="home-media-card__btn" href="List-Produit.php">Voir <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                <?php } else { ?>
                    <?php foreach ($produitsFeatured as $idx => $produit) { ?>
                        <?php
                        $idProduit = (int) ($produit['id_produit'] ?? 0);
                        $imgProduit = trim((string) ($produit['image'] ?? ''));
                        $prixProduit = (float) ($produit['prix'] ?? 0);
                        $descProduit = trim((string) ($produit['benefices'] ?? 'Produit healthy disponible sur HappyBite.'));
                        ?>
                        <article class="home-media-card">
                            <?php if ($imgProduit !== '') { ?>
                                <div class="home-media-card__img"><img src="../uploads/<?php echo htmlspecialchars($imgProduit); ?>" alt="<?php echo htmlspecialchars((string) ($produit['nom'] ?? 'Produit')); ?>"></div>
                            <?php } else { ?>
                                <div class="home-media-card__img home-media-card__img--p<?php echo ($idx % 4) + 1; ?>" aria-hidden="true"></div>
                            <?php } ?>
                            <div class="home-media-card__body">
                                <h3><?php echo htmlspecialchars((string) ($produit['nom'] ?? 'Produit')); ?></h3>
                                <p><?php echo htmlspecialchars(substr($descProduit, 0, 90)); ?><?php echo strlen($descProduit) > 90 ? '…' : ''; ?></p>
                                <a class="home-media-card__btn" href="Detail-Produit.php?id=<?php echo $idProduit; ?>">Voir (<?php echo htmlspecialchars(number_format($prixProduit, 2, ',', ' ')); ?> DT) <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>

        <section class="home-section" aria-label="Recettes en vedette">
            <div class="home-section-head">
                <h2 class="home-section-title">Recettes en vedette</h2>
                <a class="home-section-more" href="List-Recette.php">Voir tout <span aria-hidden="true">→</span></a>
            </div>
            <div class="home-card-grid home-card-grid--recipes">
                <?php if ($recettesFeatured === []) { ?>
                    <article class="home-media-card">
                        <div class="home-media-card__img home-media-card__img--r1" aria-hidden="true"></div>
                        <div class="home-media-card__body">
                            <h3>Aucune recette disponible</h3>
                            <p>Ajoutez des recettes en base pour afficher cette section.</p>
                            <a class="home-media-card__btn" href="List-Recette.php">Voir <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                <?php } else { ?>
                    <?php foreach ($recettesFeatured as $idx => $recette) { ?>
                        <?php
                        $idRecette = (int) ($recette['id_recette'] ?? 0);
                        $descRecette = trim((string) ($recette['description'] ?? 'Recette healthy HappyBite.'));
                        ?>
                        <article class="home-media-card">
                            <div class="home-media-card__img home-media-card__img--r<?php echo ($idx % 3) + 1; ?>" aria-hidden="true"></div>
                            <div class="home-media-card__body">
                                <h3><?php echo htmlspecialchars((string) ($recette['nom'] ?? 'Recette')); ?></h3>
                                <p><?php echo htmlspecialchars(substr($descRecette, 0, 90)); ?><?php echo strlen($descRecette) > 90 ? '…' : ''; ?></p>
                                <a class="home-media-card__btn" href="Detail-Recette.php?id=<?php echo $idRecette; ?>">Voir <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    <?php } ?>
                <?php } ?>
            </div>
        </section>

        <section class="home-section" aria-label="Communauté">
            <div class="home-section-head">
                <h2 class="home-section-title">La communauté mange quoi ?</h2>
                <a class="home-section-more" href="Communaute.php">Voir tout <span aria-hidden="true">→</span></a>
            </div>
            <div class="home-community-strip">
                <a class="home-community-card" href="Communaute.php" aria-label="Voir la communauté">
                    <?php if ($postsFeatured === []) { ?>
                        <span class="home-community-card__img home-community-card__img--c1" aria-hidden="true"></span>
                        <span class="home-community-card__img home-community-card__img--c2" aria-hidden="true"></span>
                        <span class="home-community-card__img home-community-card__img--c3" aria-hidden="true"></span>
                        <span class="home-community-card__img home-community-card__img--c4" aria-hidden="true"></span>
                    <?php } else { ?>
                        <?php foreach ($postsFeatured as $idx => $post) { ?>
                            <?php $imgPost = trim((string) ($post['image'] ?? '')); ?>
                            <?php if ($imgPost !== '') { ?>
                                <span class="home-community-card__img"><img src="../uploads/<?php echo htmlspecialchars($imgPost); ?>" alt="Post communautaire"></span>
                            <?php } else { ?>
                                <span class="home-community-card__img home-community-card__img--c<?php echo ($idx % 4) + 1; ?>" aria-hidden="true"></span>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </a>
            </div>
        </section>

        <section class="home-bottom-slider" aria-label="Bannieres bas de page">
            <div class="home-bottom-slider__track" id="home-bottom-track">
                <article class="home-bottom-slide">
                    <img class="home-bottom-slide__bg" src="images/bottom1.png" alt="Suivi sante" onerror="this.onerror=null;this.src='images/bottom1.jpg';">
                    <div class="home-bottom-slide__content home-bottom-slide__content--right home-bottom-slide__content--dark">
                        <h2>Suivez votre santé<br>Chaque jour</h2>
                        <p>Surveillez vos habitudes, vos progrés et avancez vers vos objectifs avec régularité.</p>
                        <a class="home-bottom-slide__btn" href="sante.php">Commancer la suivi</a>
                    </div>
                </article>

                <article class="home-bottom-slide">
                    <img class="home-bottom-slide__bg" src="images/bottom2.png" alt="Suivi commande" onerror="this.onerror=null;this.src='images/bottom2.jpg';">
                    <div class="home-bottom-slide__content home-bottom-slide__content--left home-bottom-slide__content--dark">
                        <h2>Suivez votre commande</h2>
                        <p>Vérifiez facilement le statut et l'avancement de votre commande en temps réel.</p>
                        <a class="home-bottom-slide__btn" href="Track.php">Commancer la suivi</a>
                    </div>
                </article>

                <article class="home-bottom-slide">
                    <img class="home-bottom-slide__bg" src="images/bottom3.png" alt="Parcours sain" onerror="this.onerror=null;this.src='images/bottom3.jpg';">
                    <div class="home-bottom-slide__content home-bottom-slide__content--right">
                        <h2>Commencez votre parcours sain dès aujourd'hui !</h2>
                        <p>Mangez mieux, sentez-vous mieux, vivez mieux.</p>
                        <a class="home-bottom-slide__btn" href="#">S'inscrire</a>
                    </div>
                </article>
            </div>
        </section>
    </div>
</main>

<footer>
    © 2026 HappyBite
</footer>

<script>
(function () {
    var slides = document.querySelectorAll('.home-hero-slide');
    if (!slides || slides.length === 0) return;
    var index = 0;
    setInterval(function () {
        slides[index].classList.remove('is-active');
        index = (index + 1) % slides.length;
        slides[index].classList.add('is-active');
    }, 3000);
})();

(function () {
    var track = document.getElementById('home-bottom-track');
    if (!track) return;
    var slides = track.querySelectorAll('.home-bottom-slide');
    if (!slides || slides.length === 0) return;
    var index = 0;
    setInterval(function () {
        index = (index + 1) % slides.length;
        track.style.transform = 'translateX(-' + (index * 100) + '%)';
    }, 3000);
})();
</script>

</body>
</html>
