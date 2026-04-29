<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Posts - BackOffice HappyBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <img src="/Esprit-PW-2A32-2526-HappyBite-Gestion-post/Views/assets/logo.png" alt="HappyBite Logo" height="40" class="me-2">
            Dashboard
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarBack">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarBack">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="List-Produit.php">
                        <i class="fas fa-box me-1"></i>Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Recette.php">
                        <i class="fas fa-utensils me-1"></i>Recettes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Categorie.php">
                        <i class="fas fa-tags me-1"></i>Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="list_posts.php">
                        <i class="fas fa-comments me-1"></i>Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_commentaires.php">
                        <i class="fas fa-reply me-1"></i>Commentaires
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list-com-liv.php">
                        <i class="fas fa-shopping-cart me-1"></i>Commandes
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
require_once __DIR__ . '/../../Controllers/PostController.php';
require_once __DIR__ . '/../../Controllers/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();

// Suppression sécurisée d'un post
if (isset($_GET['delete_post'])) {
    $id = intval($_GET['delete_post']);
    if ($id > 0) {
        $postController->delete($id);
    }
    header('Location: list_posts.php');
    exit;
}

// Suppression sécurisée d'un commentaire
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    if ($id > 0) {
        $commentaireController->delete($id);
    }
    header('Location: list_posts.php');
    exit;
}

// Recherche
$motCle = trim($_GET['motCle'] ?? '');

if (!empty($motCle)) {
    // Recherche dans les posts
    $posts = array_filter($postController->getAll(), function($post) use ($motCle) {
        return stripos($post['contenu'], $motCle) !== false;
    });
} else {
    $posts = $postController->getAll();
}
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="#" class="admin-logo">
                <img src="../images/logo2.png" alt="HappyBite" style="width: 120px; height: auto; display: block;">
            </a>
        </div>

        <nav class="admin-main-menu">
            <a href="List-Produit.php" class="admin-main-link">Produit</a>
            <a href="Home.php" class="admin-main-link">Commande</a>
            <a href="list_posts.php" class="admin-main-link active">Post</a>
            <a href="#" class="admin-main-link">Utilisateur</a>
            <a href="#" class="admin-main-link">Santé</a>
        </nav>
    </aside>

    <main class="admin-content">

    <!-- Toolbar: search + sort -->
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-muted small text-uppercase">
                        <i class="fas fa-search me-1"></i>Recherche en temps réel
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                               placeholder="Filtrer par contenu du post...">
                        <button class="btn btn-outline-secondary" id="clearSearch" style="display:none;" onclick="document.getElementById('searchInput').value='';filterAndSort();">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">
                        <i class="fas fa-sort me-1"></i>Trier par
                    </label>
                    <select id="sortBy" class="form-select">
                        <option value="date">Date</option>
                        <option value="likes">Likes</option>
                        <option value="comments">Commentaires</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Ordre</label>
                    <select id="sortOrder" class="form-select">
                        <option value="desc">Décroissant</option>
                        <option value="asc">Croissant</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-center gap-2 pt-1">
                        <span class="badge bg-primary fs-6 px-3 py-2" id="resultCount"><?php echo count($posts); ?></span>
                        <span class="text-muted small">résultats</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list text-muted me-2"></i>
                Liste des Posts
            </h5>
            <a href="list_commentaires.php" class="btn btn-sm btn-info">
                <i class="fas fa-reply me-1"></i>Voir les Commentaires
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun post trouvé</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="postsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold">Contenu</th>
                                <th class="border-0 fw-semibold text-center">Image</th>
                                <th class="border-0 fw-semibold text-center sortable" data-col="likes" style="cursor:pointer;">
                                    <i class="fas fa-thumbs-up me-1 text-warning"></i>Likes
                                    <i class="fas fa-sort ms-1 text-muted sort-icon" id="icon-likes"></i>
                                </th>
                                <th class="border-0 fw-semibold text-center sortable" data-col="comments" style="cursor:pointer;">
                                    <i class="fas fa-comment me-1 text-info"></i>Commentaires
                                    <i class="fas fa-sort ms-1 text-muted sort-icon" id="icon-comments"></i>
                                </th>
                                <th class="border-0 fw-semibold text-center sortable" data-col="date" style="cursor:pointer;">
                                    <i class="fas fa-calendar me-1 text-secondary"></i>Date
                                    <i class="fas fa-sort-down ms-1 text-primary sort-icon" id="icon-date"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="postsTableBody">
                            <?php foreach ($posts as $post): ?>
                                <?php
                                $comments = $commentaireController->getByPostId($post['id']);
                                $commentsCount = count($comments);
                                ?>
                                <tr
                                    data-content="<?php echo strtolower(htmlspecialchars($post['contenu'])); ?>"
                                    data-likes="<?php echo (int)$post['nombreLikes']; ?>"
                                    data-comments="<?php echo $commentsCount; ?>"
                                    data-date="<?php echo strtotime($post['datePublication']); ?>"
                                >
                                    <td class="border-0">
                                        <div class="fw-semibold mb-1 text-muted small">Post #<?php echo $post['id']; ?></div>
                                        <div class="text-truncate" style="max-width: 320px;" title="<?php echo htmlspecialchars($post['contenu']); ?>">
                                            <?php echo htmlspecialchars(substr($post['contenu'], 0, 100)) . (strlen($post['contenu']) > 100 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../../uploads/<?php echo htmlspecialchars($post['image']); ?>"
                                                 alt="Post image" class="img-thumbnail"
                                                 style="max-width:56px;max-height:56px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image fa-lg"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                            <?php echo $post['nombreLikes']; ?>
                                        </span>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <span class="badge bg-info px-3 py-2 fs-6">
                                            <?php echo $commentsCount; ?>
                                        </span>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($post['datePublication'])); ?><br>
                                            <span class="text-secondary"><?php echo date('H:i', strtotime($post['datePublication'])); ?></span>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Empty state shown by JS when no results -->
                <div id="noResults" class="text-center py-5" style="display:none;">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun post ne correspond à votre recherche</h6>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Charts -->
    <?php if (!empty($posts)): ?>
    <div class="card shadow-sm border-0 rounded-4 mt-4">
        <div class="card-header bg-light py-2 px-3">
            <span class="fw-semibold text-muted small text-uppercase">
                <i class="fas fa-chart-pie me-2"></i>Statistiques par post
            </span>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">

                <!-- Chart 1: Total interactions -->
                <div class="col-12 col-lg-4">
                    <div class="border rounded-3 p-3" style="background:#fafbfc;">
                        <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fas fa-chart-pie me-1"></i>Total interactions
                        </p>
                        <div class="d-flex justify-content-center mb-3">
                            <canvas id="statsChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                        </div>
                        <div id="chartLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                    </div>
                </div>

                <!-- Chart 2: Likes only -->
                <div class="col-12 col-lg-4">
                    <div class="border rounded-3 p-3" style="background:#fafbfc;">
                        <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fas fa-thumbs-up me-1" style="color:#f0a500;"></i>Likes par post
                        </p>
                        <div class="d-flex justify-content-center mb-3">
                            <canvas id="likesChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                        </div>
                        <div id="likesLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                    </div>
                </div>

                <!-- Chart 3: Comments only -->
                <div class="col-12 col-lg-4">
                    <div class="border rounded-3 p-3" style="background:#fafbfc;">
                        <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fas fa-comment me-1" style="color:#0ea5e9;"></i>Commentaires par post
                        </p>
                        <div class="d-flex justify-content-center mb-3">
                            <canvas id="commentsChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                        </div>
                        <div id="commentsLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">HappyBite - BackOffice</h6>
                <p class="text-muted small mb-0">Panneau d'administration pour la gestion du site</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted small mb-0">© 2026 HappyBite. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Custom JS -->
<script src="js/controles.js"></script>

<script>
// ── Dynamic search + sort ──────────────────────────────────────────────────
let currentSort = 'date';
let currentOrder = 'desc';

const searchInput  = document.getElementById('searchInput');
const sortBySelect = document.getElementById('sortBy');
const sortOrderSel = document.getElementById('sortOrder');
const clearBtn     = document.getElementById('clearSearch');

if (searchInput) {
    searchInput.addEventListener('input', filterAndSort);
    sortBySelect.addEventListener('change', () => { currentSort = sortBySelect.value; filterAndSort(); });
    sortOrderSel.addEventListener('change', () => { currentOrder = sortOrderSel.value; filterAndSort(); });

    // Click on sortable column headers
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (currentSort === col) {
                currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = col;
                currentOrder = col === 'date' ? 'desc' : 'desc';
            }
            sortBySelect.value  = currentSort;
            sortOrderSel.value  = currentOrder;
            filterAndSort();
        });
    });
}

function filterAndSort() {
    const query   = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    const tbody   = document.getElementById('postsTableBody');
    if (!tbody) return;

    clearBtn.style.display = query ? 'inline-block' : 'none';

    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Filter
    let visible = rows.filter(row => {
        const content = row.dataset.content || '';
        const match = !query || content.includes(query);
        row.style.display = match ? '' : 'none';
        return match;
    });

    // Sort
    visible.sort((a, b) => {
        let va, vb;
        if (currentSort === 'likes') {
            va = parseInt(a.dataset.likes); vb = parseInt(b.dataset.likes);
        } else if (currentSort === 'comments') {
            va = parseInt(a.dataset.comments); vb = parseInt(b.dataset.comments);
        } else {
            va = parseInt(a.dataset.date); vb = parseInt(b.dataset.date);
        }
        return currentOrder === 'asc' ? va - vb : vb - va;
    });

    // Re-append in sorted order
    visible.forEach(row => tbody.appendChild(row));

    // Update count
    const countEl = document.getElementById('resultCount');
    if (countEl) countEl.textContent = visible.length;

    // Show/hide empty state
    const noResults = document.getElementById('noResults');
    if (noResults) noResults.style.display = visible.length === 0 ? 'block' : 'none';

    // Update sort icons
    ['likes','comments','date'].forEach(col => {
        const icon = document.getElementById('icon-' + col);
        if (!icon) return;
        if (col === currentSort) {
            icon.className = 'fas ms-1 text-primary sort-icon ' + (currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        } else {
            icon.className = 'fas fa-sort ms-1 text-muted sort-icon';
        }
    });
}

// ── Charts ────────────────────────────────────────────────────────────────
(function buildCharts() {
    const tbody = document.getElementById('postsTableBody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    if (!rows.length) return;

    const palette        = ['#2f6f57','#4a9070','#6dbf9e','#f0a500','#e05c5c','#5b8dee','#a78bfa','#94a3b8'];
    const likePalette    = ['#f0a500','#f5bc3a','#f7cc6a','#e8960a','#c97d00','#ffd966','#ffe8a0','#fbecc8'];
    const commentPalette = ['#0ea5e9','#38bdf8','#0284c7','#7dd3fc','#0369a1','#60a5fa','#3b82f6','#93c5fd'];
    const TOP = 6;

    // Extract data directly from data attributes — no DOM text parsing
    const allData = rows.map(r => ({
        label:    'Post #' + (r.dataset.date ? rows.indexOf(r) + 1 : '?'), // fallback
        likes:    parseInt(r.dataset.likes)    || 0,
        comments: parseInt(r.dataset.comments) || 0,
        combined: (parseInt(r.dataset.likes)||0) + (parseInt(r.dataset.comments)||0)
    }));

    // Get post IDs from the small text inside each row
    rows.forEach((r, i) => {
        const idEl = r.querySelector('td:first-child .text-muted');
        if (idEl) {
            const match = idEl.textContent.match(/#(\d+)/);
            if (match) allData[i].label = 'Post #' + match[1];
        }
    });

    function prepData(key) {
        let sorted = [...allData].sort((a, b) => b[key] - a[key]);
        // Filter out zeros so chart isn't empty
        const nonZero = sorted.filter(d => d[key] > 0);
        const source  = nonZero.length ? nonZero : sorted; // fallback to all if all zero

        if (source.length > TOP) {
            const top = source.slice(0, TOP);
            const rest = source.slice(TOP).reduce((s, d) => s + d[key], 0);
            return {
                labels: [...top.map(d => d.label), 'Autres'],
                values: [...top.map(d => d[key]), rest]
            };
        }
        return { labels: source.map(d => d.label), values: source.map(d => d[key]) };
    }

    function makeChart(canvasId, labels, values, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        // If all values are 0, show a placeholder grey ring
        const hasData = values.some(v => v > 0);
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: hasData ? labels : ['Aucune donnée'],
                datasets: [{
                    data:            hasData ? values : [1],
                    backgroundColor: hasData ? colors.slice(0, labels.length) : ['#e9ecef'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: hasData ? 8 : 0
                }]
            },
            options: {
                responsive: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: hasData,
                        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` }
                    }
                }
            }
        });
    }

    function makeLegend(legendId, labels, values, colors, hasData) {
        const el = document.getElementById(legendId);
        if (!el) return;
        if (!hasData) {
            el.innerHTML = '<span class="text-muted small">Aucune donnée</span>';
            return;
        }
        labels.forEach((label, i) => {
            const pill = document.createElement('div');
            pill.style.cssText = 'display:inline-flex;align-items:center;gap:5px;background:#fff;border-radius:20px;padding:3px 8px 3px 5px;font-size:0.72rem;font-weight:600;border:1px solid #e9ecef;white-space:nowrap;margin:2px;';
            pill.innerHTML = `<span style="width:8px;height:8px;border-radius:50%;background:${colors[i]};flex-shrink:0;display:inline-block;"></span>${label}<span style="background:${colors[i]};color:#fff;border-radius:10px;padding:1px 6px;font-size:0.68rem;margin-left:3px;">${values[i]}</span>`;
            el.appendChild(pill);
        });
    }

    // Chart 1 — combined
    const c = prepData('combined');
    makeChart('statsChart',   c.labels, c.values, palette);
    makeLegend('chartLegend', c.labels, c.values, palette, c.values.some(v=>v>0));

    // Chart 2 — likes
    const l = prepData('likes');
    makeChart('likesChart',   l.labels, l.values, likePalette);
    makeLegend('likesLegend', l.labels, l.values, likePalette, l.values.some(v=>v>0));

    // Chart 3 — comments
    const cm = prepData('comments');
    makeChart('commentsChart',   cm.labels, cm.values, commentPalette);
    makeLegend('commentsLegend', cm.labels, cm.values, commentPalette, cm.values.some(v=>v>0));
})();
</script>

</body>
</html>