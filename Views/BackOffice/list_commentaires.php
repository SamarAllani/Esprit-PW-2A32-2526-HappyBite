<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Commentaires - BackOffice HappyBite</title>
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
                    <a class="nav-link" href="list_posts.php">
                        <i class="fas fa-comments me-1"></i>Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="list_commentaires.php">
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

// Suppression sécurisée d'un commentaire
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    if ($id > 0) {
        $commentaireController->delete($id);
    }
    header('Location: list_commentaires.php');
    exit;
}

// Recherche
$motCle = trim($_GET['motCle'] ?? '');

// Récupérer tous les commentaires
$allComments = $commentaireController->getAll();

if (!empty($motCle)) {
    // Recherche dans les commentaires
    $comments = array_filter($allComments, function($comment) use ($motCle) {
        return stripos($comment['contenu'], $motCle) !== false;
    });
} else {
    $comments = $allComments;
}

// Trier par date décroissante
usort($comments, function($a, $b) {
    return strtotime($b['dateCommentaire']) - strtotime($a['dateCommentaire']);
});
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
            <a href="list_posts.php" class="admin-main-link">Post</a>
            <a href="list_commentaires.php" class="admin-main-link active">Commentaire</a>
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
                               placeholder="Filtrer par contenu du commentaire...">
                        <button class="btn btn-outline-secondary" id="clearSearch" style="display:none;"
                                onclick="document.getElementById('searchInput').value='';filterAndSort();">
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
                        <option value="post">Post ID</option>
                        <option value="content">Contenu (A→Z)</option>
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
                        <span class="badge bg-primary fs-6 px-3 py-2" id="resultCount"><?php echo count($comments); ?></span>
                        <span class="text-muted small">résultats</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commentaires Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-list text-muted me-2"></i>
                Liste des Commentaires
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($comments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun commentaire trouvé</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="commentsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold">Contenu</th>
                                <th class="border-0 fw-semibold text-center">Image du post</th>
                                <th class="border-0 fw-semibold text-center sortable" data-col="post" style="cursor:pointer;">
                                    Post ID <i class="fas fa-sort ms-1 text-muted sort-icon" id="icon-post"></i>
                                </th>
                                <th class="border-0 fw-semibold text-center sortable" data-col="date" style="cursor:pointer;">
                                    Date <i class="fas fa-sort-down ms-1 text-primary sort-icon" id="icon-date"></i>
                                </th>
                                <th class="border-0 fw-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="commentsTableBody">
                            <?php foreach ($comments as $comment): ?>
                                <tr
                                    data-content="<?php echo strtolower(htmlspecialchars($comment['contenu'])); ?>"
                                    data-post="<?php echo (int)$comment['post_id']; ?>"
                                    data-date="<?php echo strtotime($comment['dateCommentaire']); ?>"
                                >
                                    <td class="border-0">
                                        <div class="fw-semibold mb-1 text-muted small">Commentaire #<?php echo $comment['id']; ?></div>
                                        <div class="text-truncate" style="max-width: 400px;" title="<?php echo htmlspecialchars($comment['contenu']); ?>">
                                            <?php echo htmlspecialchars(substr($comment['contenu'], 0, 100)) . (strlen($comment['contenu']) > 100 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <?php
                                        $post = $postController->getById($comment['post_id']);
                                        if ($post && !empty($post['image'])): ?>
                                            <img src="../../uploads/<?php echo htmlspecialchars($post['image']); ?>"
                                                 alt="Post image" class="img-thumbnail"
                                                 style="max-width:56px;max-height:56px;object-fit:cover;border-radius:8px;">
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-image fa-lg"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <span class="badge bg-secondary px-3 py-2">
                                            Post #<?php echo $comment['post_id']; ?>
                                        </span>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($comment['dateCommentaire'])); ?><br>
                                            <span class="text-secondary"><?php echo date('H:i', strtotime($comment['dateCommentaire'])); ?></span>
                                        </small>
                                    </td>
                                    <td class="border-0 text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="viewCommentDetails('<?php echo htmlspecialchars(addslashes($comment['contenu'])); ?>', '<?php echo date('d/m/Y H:i', strtotime($comment['dateCommentaire'])); ?>')"
                                                title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="?delete_comment=<?php echo $comment['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer ce commentaire ?');"
                                               title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="text-center py-5" style="display:none;">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun commentaire ne correspond à votre recherche</h6>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Comment Details Modal -->
<div class="modal fade" id="commentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du Commentaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
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
<!-- Custom JS -->
<script src="js/controles.js"></script>

<script>
// ── Dynamic search + sort ──────────────────────────────────────────────────
let currentSort  = 'date';
let currentOrder = 'desc';

const searchInput  = document.getElementById('searchInput');
const sortBySelect = document.getElementById('sortBy');
const sortOrderSel = document.getElementById('sortOrder');
const clearBtn     = document.getElementById('clearSearch');

if (searchInput) {
    searchInput.addEventListener('input', filterAndSort);
    sortBySelect.addEventListener('change', () => { currentSort = sortBySelect.value; filterAndSort(); });
    sortOrderSel.addEventListener('change', () => { currentOrder = sortOrderSel.value; filterAndSort(); });

    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (currentSort === col) {
                currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = col;
                currentOrder = 'desc';
            }
            sortBySelect.value = currentSort;
            sortOrderSel.value = currentOrder;
            filterAndSort();
        });
    });
}

function filterAndSort() {
    const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    const tbody = document.getElementById('commentsTableBody');
    if (!tbody) return;

    clearBtn.style.display = query ? 'inline-block' : 'none';

    const rows = Array.from(tbody.querySelectorAll('tr'));

    let visible = rows.filter(row => {
        const content = row.dataset.content || '';
        const match = !query || content.includes(query);
        row.style.display = match ? '' : 'none';
        return match;
    });

    visible.sort((a, b) => {
        let va, vb;
        if (currentSort === 'post') {
            va = parseInt(a.dataset.post); vb = parseInt(b.dataset.post);
        } else if (currentSort === 'content') {
            va = (a.dataset.content || ''); vb = (b.dataset.content || '');
            return currentOrder === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        } else {
            va = parseInt(a.dataset.date); vb = parseInt(b.dataset.date);
        }
        return currentOrder === 'asc' ? va - vb : vb - va;
    });

    visible.forEach(row => tbody.appendChild(row));

    const countEl = document.getElementById('resultCount');
    if (countEl) countEl.textContent = visible.length;

    const noResults = document.getElementById('noResults');
    if (noResults) noResults.style.display = visible.length === 0 ? 'block' : 'none';

    ['post','date'].forEach(col => {
        const icon = document.getElementById('icon-' + col);
        if (!icon) return;
        if (col === currentSort) {
            icon.className = 'fas ms-1 text-primary sort-icon ' + (currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        } else {
            icon.className = 'fas fa-sort ms-1 text-muted sort-icon';
        }
    });
}

function viewCommentDetails(contenu, date) {
    const content = `
        <div class="mb-3"><strong>Contenu :</strong><br><p class="mt-2">${contenu.replace(/\n/g, '<br>')}</p></div>
        <div class="mb-3"><strong>Date :</strong><br><p class="mt-1 text-muted">${date}</p></div>
    `;
    document.getElementById('commentDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('commentDetailsModal')).show();
}
</script>

</body>
</html>
