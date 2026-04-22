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
            <img src="images/logo.png" alt="HappyBite Logo" height="40" class="me-2">
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

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-comments text-primary me-2"></i>
                Liste des Posts
            </h2>
            <p class="text-muted mb-0">Modérer les publications des utilisateurs</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i>Retour Dashboard
        </a>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="motCle" class="form-label fw-semibold">Rechercher dans les posts</label>
                        <input
                            type="text"
                            class="form-control"
                            id="motCle"
                            name="motCle"
                            placeholder="Contenu du post..."
                            value="<?php echo htmlspecialchars($motCle); ?>"
                        >
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Rechercher
                        </button>
                        <?php if (!empty($motCle)): ?>
                            <a href="list_posts.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Effacer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-list text-muted me-2"></i>
                Posts (<?php echo count($posts); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun post trouvé</h6>
                    <p class="text-muted small mb-0">
                        <?php echo empty($motCle) ? 'Les posts des utilisateurs apparaîtront ici.' : 'Aucun post ne correspond à votre recherche.'; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold">Contenu</th>
                                <th class="border-0 fw-semibold text-center">Image</th>
                                <th class="border-0 fw-semibold text-center">Likes</th>
                                <th class="border-0 fw-semibold text-center">Commentaires</th>
                                <th class="border-0 fw-semibold text-center">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <?php
                                $comments = $commentaireController->getByPostId($post['id']);
                                $commentsCount = count($comments);
                                ?>
                                <tr>
                                    <td class="border-0">
                                        <div class="fw-semibold mb-1">Post #<?php echo $post['id']; ?></div>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($post['contenu']); ?>">
                                            <?php echo htmlspecialchars(substr($post['contenu'], 0, 100)) . (strlen($post['contenu']) > 100 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td class="border-0 text-center">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../../uploads/<?php echo htmlspecialchars($post['image']); ?>" 
                                                 alt="Post image" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-image text-muted" title="Sans image"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 text-center">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-thumbs-up me-1"></i><?php echo $post['nombreLikes']; ?>
                                        </span>
                                    </td>
                                    <td class="border-0 text-center">
                                        <span class="badge bg-info">
                                            <i class="fas fa-comment me-1"></i><?php echo $commentsCount; ?>
                                        </span>
                                    </td>
                                    <td class="border-0 text-center">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($post['datePublication'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Comments Section (if needed for detailed view) -->
    <div id="commentsSection" class="mt-4" style="display: none;">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-comments text-muted me-2"></i>
                    Commentaires du post
                </h5>
            </div>
            <div class="card-body">
                <div id="commentsList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Post Details Modal -->
<div class="modal fade" id="postDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="postDetailsContent">
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
function viewPostDetails(postId) {
    // Fetch post details via AJAX
    fetch('../Controllers/PostController.php?action=get_post&id=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const post = data.post;
                const comments = data.comments || [];

                let content = `
                    <div class="mb-3">
                        <strong>Contenu:</strong><br>
                        <p>${post.contenu.replace(/\n/g, '<br>')}</p>
                    </div>
                `;

                if (post.image) {
                    content += `
                        <div class="mb-3">
                            <strong>Image:</strong><br>
                            <img src="/happybite/uploads/${post.image}" class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                    `;
                }

                content += `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Likes:</strong> ${post.nombreLikes}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> ${new Date(post.datePublication).toLocaleString('fr-FR')}
                        </div>
                    </div>
                `;

                if (comments.length > 0) {
                    content += `
                        <div class="mb-3">
                            <strong>Commentaires (${comments.length}):</strong>
                            <div class="mt-2">
                    `;

                    comments.forEach(comment => {
                        content += `
                            <div class="border rounded p-2 mb-2 bg-light">
                                <small class="text-muted">${new Date(comment.dateCommentaire).toLocaleString('fr-FR')}</small>
                                <p class="mb-1 mt-1">${comment.contenu}</p>
                                <a href="?delete_comment=${comment.id}" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer ce commentaire ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        `;
                    });

                    content += `
                            </div>
                        </div>
                    `;
                }

                document.getElementById('postDetailsContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('postDetailsModal')).show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des détails du post.');
        });
}
</script>

</body>
</html>