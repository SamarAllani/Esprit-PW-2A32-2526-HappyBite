<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/ProduitController.php';
require_once __DIR__ . '/../Controllers/RecetteController.php';
require_once __DIR__ . '/../Controllers/PostController.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Controllers/UtilisateurPhotoSql.php';

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

/**
 * Titres courts pour les cartes (contenu des avis depuis data.txt / feedback_home.txt).
 */
$homeFeedbackTitles = [
    2 => 'Communauté & motivation',
    3 => 'Tout personnalisé',
    4 => 'Un outil du quotidien',
    5 => 'Suivi ultra complet',
    6 => 'Objectifs sur mesure',
];

/**
 * Charge les textes « user N : » depuis le fichier projet ou, en secours, Downloads\data.txt.
 *
 * @return array<int, string> id utilisateur => texte de l’avis
 */
function home_load_feedback_quotes(): array
{
    $candidates = [
        __DIR__ . '/data/feedback_home.txt',
        __DIR__ . '/../data/feedback_home.txt',
        'C:\\Users\\samar\\Downloads\\data.txt',
    ];
    $path = null;
    foreach ($candidates as $p) {
        if (is_readable($p)) {
            $path = $p;
            break;
        }
    }
    if ($path === null) {
        return [];
    }
    $raw = (string) file_get_contents($path);
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    if (!preg_match_all('/user\s*(\d+)\s*:\s*(.*?)(?=\s*user\s*\d+\s*:|$)/si', $raw, $m, PREG_SET_ORDER)) {
        return [];
    }
    $out = [];
    foreach ($m as $row) {
        $uid = (int) ($row[1] ?? 0);
        $text = trim(preg_replace('/\s+/u', ' ', (string) ($row[2] ?? '')));
        if ($uid >= 2 && $uid <= 6 && $text !== '') {
            $out[$uid] = $text;
        }
    }
    ksort($out, SORT_NUMERIC);
    return $out;
}

/**
 * @return array<int, array{prenom: string, nom: string}>
 */
function home_load_utilisateur_names_by_ids(array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0)));
    if ($ids === []) {
        return [];
    }
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c'
        );
        $stmt->execute(['t' => 'utilisateur', 'c' => 'id']);
        $pk = ((int) $stmt->fetchColumn() > 0) ? 'id' : 'id_utilisateur';
        $stmt->execute(['t' => 'utilisateur', 'c' => 'id_utilisateur']);
        if ($pk === 'id_utilisateur' && (int) $stmt->fetchColumn() === 0) {
            $pk = 'id';
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $q = $pdo->prepare("SELECT `{$pk}` AS uid, prenom, nom FROM utilisateur WHERE `{$pk}` IN ({$placeholders})");
        $q->execute($ids);
        $map = [];
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $uid = (int) ($row['uid'] ?? 0);
            if ($uid > 0) {
                $map[$uid] = [
                    'prenom' => trim((string) ($row['prenom'] ?? '')),
                    'nom' => trim((string) ($row['nom'] ?? '')),
                ];
            }
        }
        return $map;
    } catch (Throwable $e) {
        return [];
    }
}

$homeFeedbackQuotes = home_load_feedback_quotes();
if ($homeFeedbackQuotes === []) {
    $homeFeedbackQuotes = [
        2 => "J'adore Happybite, en plus de la personnalisation selon le profil santé et des conseils quotidiens et des analyses des plats et du chefbot, on a un espace communauté où on peut interagir avec d autre personnes, poster des post et des commentaires, s'encourager, c'est vrai un réseau social basé sur la nutrition !  Ça m'arrive de challenger mes amis pour savoir qui va gagner le plus de points en 1 semaine haha !",
        3 => "TOUT EST PERSONNALISÉ ! Recettes, produits, conseils. Et on suivre les commandes en temps réel! Aussi c'est hyper sécurisé avec des vérifications au face ID, je conseille!",
        4 => "Ce site est une incroyable découverte, c'est un véritable outil du quotidien. J'adore le Chef or qui planifie mes repas pour la semaine. Et aussi la track de mes courses!",
        5 => "Grâce à ce site, jed peux suivre ma nutrition dans les moindres détails, il est tellement complet! Il y a vraiment toute les fonctionnalités IA nécessaires et pertinentes.",
        6 => "Ça n'a jamais été plus simple de suivre mes objectifs nutritionnels! Incroyable d'avoir des conseils personnalisés et des recettes et produits correspondants à mon profil santé.",
    ];
}

$homeFeedbackIds = [2, 3, 4, 5, 6];
$homeFeedbackNames = home_load_utilisateur_names_by_ids($homeFeedbackIds);

$homeFeedbackItems = [];
foreach ($homeFeedbackIds as $uid) {
    $quote = $homeFeedbackQuotes[$uid] ?? '';
    if ($quote === '') {
        continue;
    }
    $prenom = $homeFeedbackNames[$uid]['prenom'] ?? '';
    $nom = $homeFeedbackNames[$uid]['nom'] ?? '';
    $fullName = trim($prenom . ' ' . $nom);
    if ($fullName === '') {
        $fullName = 'Membre #' . $uid;
    }
    $ini = '';
    if ($prenom !== '') {
        $ini .= strtoupper(substr($prenom, 0, 1));
    }
    if ($nom !== '') {
        $ini .= strtoupper(substr($nom, 0, 1));
    }
    if ($ini === '') {
        $ini = (string) $uid;
    }
    $homeFeedbackItems[] = [
        'id' => $uid,
        'title' => $homeFeedbackTitles[$uid] ?? 'Avis membre',
        'body' => $quote,
        'name' => $fullName,
        'role' => 'Membre HappyBite',
        'initials' => $ini,
        'photoSrc' => '',
    ];
}

try {
    $pdoFb = Database::getConnection();
    foreach ($homeFeedbackItems as $k => $row) {
        $rel = utilisateur_fetch_profile_relative_path($pdoFb, (int) ($row['id'] ?? 0));
        $src = $rel !== null ? utilisateur_nav_profile_img_src($rel) : null;
        $homeFeedbackItems[$k]['photoSrc'] = is_string($src) && $src !== '' ? $src : '';
    }
} catch (Throwable $e) {
    // garder photoSrc vide
}

try {
    $homeFeedbackJson = json_encode(
        $homeFeedbackItems,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS
    );
} catch (Throwable $e) {
    $homeFeedbackJson = '[]';
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
                        $imgRecette = trim((string) ($recette['image'] ?? ''));
                        $descRecette = trim((string) ($recette['description'] ?? 'Recette healthy HappyBite.'));
                        ?>
                        <article class="home-media-card">
                            <?php if ($imgRecette !== '') { ?>
                                <div class="home-media-card__img"><img src="../uploads/<?php echo htmlspecialchars($imgRecette); ?>" alt="<?php echo htmlspecialchars((string) ($recette['nom'] ?? 'Recette')); ?>"></div>
                            <?php } else { ?>
                                <div class="home-media-card__img home-media-card__img--r<?php echo ($idx % 3) + 1; ?>" aria-hidden="true"></div>
                            <?php } ?>
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

        <?php if (count($homeFeedbackItems) >= 2) { ?>
        <section class="home-feedback" aria-label="Avis membres">
            <div class="home-feedback__inner">
                <div class="home-feedback__intro">
                    <p class="home-feedback__label">Feedback</p>
                    <h2 class="home-feedback__title">Ce que nos membres disent.</h2>
                    <div class="home-feedback__nav" role="group" aria-label="Navigation des avis">
                        <button type="button" class="home-feedback__arrow" id="homeFeedbackPrev" aria-label="Avis précédent">&#8592;</button>
                        <button type="button" class="home-feedback__arrow" id="homeFeedbackNext" aria-label="Avis suivant">&#8594;</button>
                    </div>
                </div>
                <div class="home-feedback__cards" id="homeFeedbackCards">
                    <article class="home-feedback-card" id="homeFeedbackCard0" aria-live="polite"></article>
                    <article class="home-feedback-card" id="homeFeedbackCard1" aria-live="polite"></article>
                </div>
            </div>
        </section>
        <?php } ?>

        <section class="home-bottom-slider" aria-label="Bannieres bas de page">
            <div class="home-bottom-slider__track" id="home-bottom-track">
                <article class="home-bottom-slide">
                    <img class="home-bottom-slide__bg" src="images/bottom1.png" alt="Suivi sante" onerror="this.onerror=null;this.src='images/bottom1.jpg';">
                    <div class="home-bottom-slide__content home-bottom-slide__content--right home-bottom-slide__content--dark">
                        <h2>Suivez votre santé<br>Chaque jour</h2>
                        <p>Surveillez vos habitudes, vos progrés et avancez vers vos objectifs avec régularité.</p>
                        <a class="home-bottom-slide__btn" href="user_health_space.php">Commancer la suivi</a>
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
                        <a class="home-bottom-slide__btn" href="register.php">S'inscrire</a>
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

(function () {
    var items = <?php echo $homeFeedbackJson; ?>;
    if (!Array.isArray(items) || items.length < 2) {
        return;
    }
    var wrap = document.getElementById('homeFeedbackCards');
    var c0 = document.getElementById('homeFeedbackCard0');
    var c1 = document.getElementById('homeFeedbackCard1');
    var btnPrev = document.getElementById('homeFeedbackPrev');
    var btnNext = document.getElementById('homeFeedbackNext');
    if (!wrap || !c0 || !c1) {
        return;
    }
    var n = items.length;
    var start = 0;
    var timer = null;

    function fillCard(el, item) {
        el.textContent = '';
        if (!item) {
            return;
        }
        var h3 = document.createElement('h3');
        h3.className = 'home-feedback-card__title';
        h3.textContent = item.title || '';

        var body = document.createElement('p');
        body.className = 'home-feedback-card__body';
        body.textContent = item.body || '';

        var foot = document.createElement('div');
        foot.className = 'home-feedback-card__foot';

        var av = document.createElement('span');
        av.className = 'home-feedback-card__avatar';
        av.setAttribute('aria-hidden', 'true');
        var photo = item.photoSrc && String(item.photoSrc).trim();
        if (photo) {
            av.classList.add('home-feedback-card__avatar--img');
            var img = document.createElement('img');
            img.src = String(item.photoSrc);
            img.alt = '';
            img.loading = 'lazy';
            img.decoding = 'async';
            img.onerror = function () {
                av.classList.remove('home-feedback-card__avatar--img');
                img.remove();
                av.textContent = item.initials || '';
            };
            av.appendChild(img);
        } else {
            av.textContent = item.initials || '';
        }

        var who = document.createElement('div');
        who.className = 'home-feedback-card__who';
        var nameEl = document.createElement('strong');
        nameEl.className = 'home-feedback-card__name';
        nameEl.textContent = item.name || '';
        var roleEl = document.createElement('span');
        roleEl.className = 'home-feedback-card__role';
        roleEl.textContent = item.role || '';
        who.appendChild(nameEl);
        who.appendChild(roleEl);

        foot.appendChild(av);
        foot.appendChild(who);

        el.appendChild(h3);
        el.appendChild(body);
        el.appendChild(foot);
    }

    function renderPair() {
        fillCard(c0, items[start % n]);
        fillCard(c1, items[(start + 1) % n]);
    }

    function getUser2Item() {
        for (var i = 0; i < items.length; i++) {
            if (Number(items[i].id) === 2) {
                return items[i];
            }
        }
        return items[0];
    }

    function applyUser2CardHeight() {
        var u2 = getUser2Item();
        if (!u2) {
            return;
        }
        var probe = document.createElement('article');
        probe.className = 'home-feedback-card';
        probe.setAttribute('aria-hidden', 'true');
        var w = c0.offsetWidth;
        if (w < 80) {
            var rect = wrap.getBoundingClientRect();
            var gap = 22;
            w = Math.max(160, (rect.width - gap) / 2);
        }
        probe.style.cssText =
            'position:absolute;left:-9999px;top:0;width:' +
            w +
            'px;visibility:hidden;pointer-events:none;';
        document.body.appendChild(probe);
        fillCard(probe, u2);
        var h = probe.offsetHeight;
        document.body.removeChild(probe);
        if (h > 40) {
            wrap.style.setProperty('--home-feedback-card-h', h + 'px');
        }
    }

    function bump(delta) {
        var dir = delta > 0 ? 'home-feedback__cards--slide-next' : 'home-feedback__cards--slide-prev';
        wrap.classList.add(dir);
        setTimeout(function () {
            start = (start + delta + n * 10) % n;
            renderPair();
            wrap.classList.remove('home-feedback__cards--slide-next');
            wrap.classList.remove('home-feedback__cards--slide-prev');
        }, 300);
    }

    function next() {
        bump(1);
    }

    function prev() {
        bump(-1);
    }

    function armTimer() {
        if (timer) {
            clearInterval(timer);
        }
        timer = setInterval(next, 5000);
    }

    renderPair();
    applyUser2CardHeight();
    requestAnimationFrame(function () {
        requestAnimationFrame(applyUser2CardHeight);
    });

    var resizeT = null;
    window.addEventListener('resize', function () {
        if (resizeT) {
            clearTimeout(resizeT);
        }
        resizeT = setTimeout(function () {
            applyUser2CardHeight();
        }, 120);
    });
    window.addEventListener('load', function () {
        applyUser2CardHeight();
    });

    armTimer();

    if (btnNext) {
        btnNext.addEventListener('click', function () {
            next();
            armTimer();
        });
    }
    if (btnPrev) {
        btnPrev.addEventListener('click', function () {
            prev();
            armTimer();
        });
    }
})();
</script>

</body>
</html>
