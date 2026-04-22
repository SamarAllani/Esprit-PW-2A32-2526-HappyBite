<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/PostController.php';
require_once __DIR__ . '/../Controllers/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();

$message = '';
$messageType = '';

// Vérifier le message de succès après redirection
if (isset($_GET['success'])) {
    $message = 'Post publié avec succès !';
    $messageType = 'success';
}

if (isset($_GET['updated'])) {
    $message = 'Post mis à jour avec succès !';
    $messageType = 'success';
}
 
if (isset($_GET['comment_success'])) {
    $message = 'Commentaire ajouté avec succès !';
    $messageType = 'success';
}

if (isset($_GET['comment_updated'])) {
    $message = 'Commentaire mis à jour avec succès !';
    $messageType = 'success';
}

if (isset($_GET['comment_deleted'])) {
    $message = 'Commentaire supprimé avec succès !';
    $messageType = 'success';
}

// Traiter l'ajout de post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    if ($_POST['action'] === 'add_post') {
        $contenu = $_POST['contenu'] ?? '';
        
        if (!empty($contenu)) {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadsDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $image = uniqid() . '-' . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadsDir . $image);
            }
            
            if ($postController->create($contenu, $image)) {
                header('Location: Home.php?success=1');
                exit;
            } else {
                $message = 'Erreur lors de la publication du post.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Le contenu du post ne peut pas être vide.';
            $messageType = 'warning';
        }
    } elseif ($_POST['action'] === 'update_post') {
        $id = (int)$_POST['id'];
        $contenu = $_POST['contenu'] ?? '';
        
        if (!empty($contenu)) {
            if ($postController->update($id, $contenu)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'id' => $id, 'contenu' => $contenu]);
                    exit;
                }
                header('Location: Home.php?updated=1');
                exit;
            } else {
                $message = 'Erreur lors de la mise à jour du post.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Le contenu du post ne peut pas être vide.';
            $messageType = 'warning';
        }
    } elseif ($_POST['action'] === 'delete_post') {
        $id = (int)$_POST['id'];
        
        if ($postController->delete($id)) {
            if ($isAjax) {
                echo json_encode(['success' => true, 'id' => $id]);
                exit;
            }
            $message = 'Post supprimé avec succès !';
            $messageType = 'success';
        } else {
            $message = 'Erreur lors de la suppression du post.';
            $messageType = 'danger';
        }
    } elseif ($_POST['action'] === 'like_post') {
        $id = (int)$_POST['id'];
        $liked = $_POST['liked'] === 'true';
        
        if ($liked) {
            $postController->removeLike($id);
        } else {
            $postController->addLike($id);
        }
        
        // Récupérer le post mis à jour
        $post = $postController->getById($id);
        echo json_encode(['success' => true, 'likes' => $post['nombreLikes']]);
        exit;
    } elseif ($_POST['action'] === 'add_comment') {
        $post_id = (int)$_POST['post_id'];
        $contenu = $_POST['contenu'] ?? '';
        
        if (!empty($contenu)) {
            $commentId = $commentaireController->create($contenu, $post_id);
            if ($commentId !== false) {
                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'id' => $commentId,
                        'post_id' => $post_id,
                        'contenu' => $contenu,
                        'dateCommentaire' => date('d M Y à H:i')
                    ]);
                    exit;
                }
                header('Location: Home.php?comment_success=1');
                exit;
            } else {
                $message = 'Erreur lors de l\'ajout du commentaire.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Le commentaire ne peut pas être vide.';
            $messageType = 'warning';
        }
    } elseif ($_POST['action'] === 'update_comment') {
        $id = (int)$_POST['id'];
        $contenu = $_POST['contenu'] ?? '';
        
        if (!empty($contenu)) {
            if ($commentaireController->update($id, $contenu)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'id' => $id, 'contenu' => $contenu]);
                    exit;
                }
                header('Location: Home.php?comment_updated=1');
                exit;
            } else {
                $message = 'Erreur lors de la mise à jour du commentaire.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Le commentaire ne peut pas être vide.';
            $messageType = 'warning';
        }
    } elseif ($_POST['action'] === 'delete_comment') {
        $id = (int)$_POST['id'];
        
        if ($commentaireController->delete($id)) {
            if ($isAjax) {
                echo json_encode(['success' => true, 'id' => $id]);
                exit;
            }
            header('Location: Home.php?comment_deleted=1');
            exit;
        } else {
            $message = 'Erreur lors de la suppression du commentaire.';
            $messageType = 'danger';
        }
    }
}

$posts = $postController->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - HappyBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ton CSS -->
    <link rel="stylesheet" href="/Views/assets/css/style.css">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .feed-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .post-card {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .post-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .post-content {
            padding: 15px;
        }
        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin: 10px 0;
        }
        .post-actions {
            padding: 10px 15px;
            display: flex;
            gap: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .post-action-btn {
            flex: 1;
            border: none;
            background: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
            color: #65676b;
            font-size: 14px;
        }
        .post-action-btn:hover {
            background-color: #f0f2f5;
        }
        .post-action-btn.liked {
            color: #e74c3c;
        }
        .add-post-form {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .add-post-textarea {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 10px 15px;
            resize: none;
            font-family: inherit;
        }
        .add-post-textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        .post-date {
            font-size: 12px;
            color: #65676b;
        }
        .menu-dots {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #65676b;
        }
        .dropdown-menu {
            min-width: 150px;
        }
        .modal-custom {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-custom.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-custom-content {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        .modal-custom-content h4 {
            margin-bottom: 15px;
            color: #333;
        }
        .modal-custom-content p {
            color: #666;
            margin-bottom: 20px;
        }
        .modal-custom-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .modal-custom-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .error-input {
            border-color: #e74c3c !important;
        }
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }
        .comments-section {
            border-top: 1px solid #e0e0e0;
            padding: 15px;
            background-color: #fafafa;
            max-height: 400px;
            overflow-y: auto;
        }
        .comments-section.hidden {
            display: none;
        }
        .comment-item {
            background-color: white;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .comment-author {
            font-weight: bold;
            font-size: 13px;
        }
        .comment-date {
            font-size: 11px;
            color: #65676b;
        }
        .comment-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .comment-btn {
            background: none;
            border: none;
            color: #0a66c2;
            cursor: pointer;
            font-size: 12px;
            padding: 0;
        }
        .comment-btn:hover {
            text-decoration: underline;
        }
        .comment-form {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
        }
        .comment-textarea {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            resize: none;
        }
        .comment-textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.1);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="javascript:void(0);"><img src="images/logo2.png" alt="HappyBite Logo" height="70"></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarFront" aria-controls="navbarFront" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarFront">
            <ul class="navbar-nav ms-5">
                <li class="nav-item">
                    <a class="nav-link active" href="Home.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Produit.php">Produits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="List-Recette.php">Recettes</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="feed-container">
        <!-- Messages d'alerte -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un post -->
        <div class="add-post-form">
            <h5 class="mb-3">Partager un post</h5>
            <form id="addPostForm" method="POST" enctype="multipart/form-data" onsubmit="return validateAddPost()">
                <input type="hidden" name="action" value="add_post">
                <textarea id="postContent" name="contenu" class="add-post-textarea" placeholder="Qu'est-ce que vous pensez ?" rows="4"></textarea>
                <div id="contentError" class="error-message"></div>
                
                <div class="mt-3">
                    <input type="file" id="postImage" name="image" accept="image/*" class="form-control mb-2">
                    <button type="submit" class="btn btn-success w-100">Publier</button>
                </div>
            </form>
        </div>

        <!-- Flux des posts -->
        <?php if (empty($posts)): ?>
            <div class="alert alert-info text-center">
                <p>Aucun post pour le moment. Soyez le premier à partager !</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card" id="post-card-<?php echo $post['id']; ?>">
                    <div class="post-header">
                        <div>
                            <strong>HappyBite</strong>
                            <div class="post-date"><?php echo date('d M Y à H:i', strtotime($post['datePublication'])); ?></div>
                        </div>
                        <div class="dropdown">
                            <button class="menu-dots" type="button" id="dropdownMenu<?php echo $post['id']; ?>" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu<?php echo $post['id']; ?>">
                                <li>
                                    <button type="button" class="dropdown-item" onclick="openEditPostModal(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" onclick="openDeleteConfirmation(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="post-content">
                        <p id="post-text-<?php echo $post['id']; ?>"><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="/happybite/uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" class="post-image">
                        <?php endif; ?>
                    </div>

                    <div class="post-actions">
                        <button class="post-action-btn like-btn" id="like-btn-<?php echo $post['id']; ?>" onclick="toggleLike(<?php echo $post['id']; ?>, this)">
                            <i class="fas fa-thumbs-up"></i> <span id="like-count-<?php echo $post['id']; ?>">J'aime (<?php echo $post['nombreLikes']; ?>)</span>
                        </button>
                        <button class="post-action-btn" onclick="toggleCommentsSection(<?php echo $post['id']; ?>)">
                            <i class="fas fa-comment"></i> Commenter
                        </button>
                    </div>

                    <!-- Section des commentaires -->
                    <div id="comments-section-<?php echo $post['id']; ?>" class="comments-section hidden">
                        <?php
                        $comments = $commentaireController->getByPostId($post['id']);
                        ?>
                        
                        <?php if (empty($comments)): ?>
                            <p class="text-muted text-center" style="font-size: 13px;">Aucun commentaire pour le moment</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item" id="comment-item-<?php echo $comment['id']; ?>">
                                    <div class="comment-header">
                                        <div>
                                            <div class="comment-author">HappyBite</div>
                                            <div class="comment-date"><?php echo date('d M Y à H:i', strtotime($comment['dateCommentaire'])); ?></div>
                                        </div>
                                        <div class="comment-actions">
                                            <div class="dropdown">
                                                <button class="comment-btn dropdown-toggle" type="button" id="commentMenu<?php echo $comment['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu<?php echo $comment['id']; ?>">
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item" onclick='event.stopPropagation(); openEditCommentModal(<?php echo $comment['id']; ?>, "<?php echo addslashes($comment['contenu']); ?>"); return false;'>
                                                            <i class="fas fa-edit"></i> Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" type="button" onclick="openDeleteCommentConfirmation(<?php echo $comment['id']; ?>)">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <p id="comment-content-<?php echo $comment['id']; ?>" style="margin: 0; font-size: 13px; color: #333;"><?php echo htmlspecialchars($comment['contenu']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Formulaire d'ajout de commentaire -->
                        <div class="comment-form">
                            <form id="commentForm-<?php echo $post['id']; ?>" method="POST" onsubmit="return validateComment(<?php echo $post['id']; ?>)">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <textarea class="comment-textarea" name="contenu" id="commentContent-<?php echo $post['id']; ?>" placeholder="Ajouter un commentaire..." rows="2"></textarea>
                                <div id="commentError-<?php echo $post['id']; ?>" class="error-message"></div>
                                <div style="display: flex; gap: 10px; margin-top: 8px;">
                                    <button type="submit" class="btn btn-sm btn-success">Commenter</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleCommentsSection(<?php echo $post['id']; ?>)">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour modifier un post -->
<div id="editPostModal" class="modal-custom">
    <div class="modal-custom-content">
        <h4>Modifier le post</h4>
        <form id="editPostForm" method="POST" onsubmit="return validateEditPost()">
            <input type="hidden" name="action" value="update_post">
            <input type="hidden" id="postId" name="id" value="">
            <textarea id="editPostContent" name="contenu" class="form-control" rows="4"></textarea>
            <div id="editContentError" class="error-message mt-2"></div>
            <div class="mt-3" style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success flex-grow-1">Enregistrer</button>
                <button type="button" class="btn btn-secondary flex-grow-1" onclick="closeEditPostModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteConfirmationModal" class="modal-custom">
    <div class="modal-custom-content">
        <h4>Êtes-vous sûr ?</h4>
        <p>Êtes-vous sûr de vouloir supprimer ce post ? Cette action ne pourra pas être annulée.</p>
        <div class="modal-custom-buttons">
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Supprimer</button>
            <button type="button" class="btn btn-secondary" onclick="closeDeleteConfirmation()">Annuler</button>
        </div>
    </div>
</div>

<!-- Modal pour modifier un commentaire -->
<div id="editCommentModal" class="modal-custom">
    <div class="modal-custom-content">
        <h4>Modifier le commentaire</h4>
        <form id="editCommentForm" method="POST" onsubmit="return validateEditComment()">
            <input type="hidden" name="action" value="update_comment">
            <input type="hidden" id="commentId" name="id" value="">
            <textarea id="editCommentContent" name="contenu" class="form-control" rows="3"></textarea>
            <div id="editCommentError" class="error-message mt-2"></div>
            <div class="mt-3" style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success flex-grow-1">Enregistrer</button>
                <button type="button" class="btn btn-secondary flex-grow-1" onclick="closeEditCommentModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation de suppression de commentaire -->
<div id="deleteCommentConfirmationModal" class="modal-custom">
    <div class="modal-custom-content">
        <h4>Êtes-vous sûr ?</h4>
        <p>Êtes-vous sûr de vouloir supprimer ce commentaire ? Cette action ne pourra pas être annulée.</p>
        <div class="modal-custom-buttons">
            <button type="button" class="btn btn-danger" onclick="confirmDeleteComment()">Supprimer</button>
            <button type="button" class="btn btn-secondary" onclick="closeDeleteCommentConfirmation()">Annuler</button>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let deletePostId = null;

// Validation du formulaire d'ajout de post
function validateAddPost() {
    const content = document.getElementById('postContent').value.trim();
    const contentError = document.getElementById('contentError');
    
    contentError.textContent = '';
    
    if (content === '') {
        contentError.textContent = 'Le contenu du post est obligatoire.';
        return false;
    }
    
    return true;
}

// Validation du formulaire de modification
function validateEditPost() {
    const content = document.getElementById('editPostContent').value.trim();
    const contentError = document.getElementById('editContentError');
    const postId = document.getElementById('postId').value;
    
    contentError.textContent = '';
    
    if (content === '') {
        contentError.textContent = 'Le contenu du post est obligatoire.';
        return false;
    }
    
    fetch('Home.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_post&ajax=1&id=' + encodeURIComponent(postId) + '&contenu=' + encodeURIComponent(content)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const postText = document.getElementById('post-text-' + data.id);
            if (postText) {
                const safeText = content
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
                postText.innerHTML = safeText;
            }
            closeEditPostModal();
        } else {
            contentError.textContent = 'Erreur lors de la mise à jour du post.';
        }
    })
    .catch(() => {
        contentError.textContent = 'Erreur lors de la mise à jour du post.';
    });
    
    return false;
}

// Ouvrir le modal de modification
function openEditPostModal(postId) {
    const postText = document.getElementById('post-text-' + postId);
    const content = postText.innerHTML.replace(/<br\s*\/?>/gi, '\n');
    document.getElementById('postId').value = postId;
    document.getElementById('editPostContent').value = content;
    document.getElementById('editPostModal').classList.add('show');
}

// Fermer le modal de modification
function closeEditPostModal() {
    document.getElementById('editPostModal').classList.remove('show');
}

// Ouvrir le modal de confirmation de suppression
function openDeleteConfirmation(postId) {
    deletePostId = postId;
    document.getElementById('deleteConfirmationModal').classList.add('show');
}

// Fermer le modal de confirmation
function closeDeleteConfirmation() {
    deletePostId = null;
    document.getElementById('deleteConfirmationModal').classList.remove('show');
}

// Confirmer la suppression
function confirmDelete() {
    if (deletePostId !== null) {
        fetch('Home.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_post&ajax=1&id=' + encodeURIComponent(deletePostId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const postCard = document.getElementById('post-card-' + data.id);
                if (postCard) {
                    postCard.remove();
                }
                closeDeleteConfirmation();
            }
        })
        .catch(() => {
            closeDeleteConfirmation();
        });
    }
}

// Basculer le like
function toggleLike(postId, button) {
    const isLiked = button.classList.contains('liked');
    
    fetch('Home.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=like_post&id=' + postId + '&liked=' + isLiked
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.toggle('liked');
            const likeCountSpan = document.getElementById('like-count-' + postId);
            likeCountSpan.textContent = 'J\'aime (' + data.likes + ')';
        }
    })
    .catch(error => console.error('Error:', error));
}

// Fermer le modal quand on clique en dehors
window.onclick = function(event) {
    const editModal = document.getElementById('editPostModal');
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const editCommentModal = document.getElementById('editCommentModal');
    const deleteCommentModal = document.getElementById('deleteCommentConfirmationModal');
    
    if (event.target === editModal) {
        closeEditPostModal();
    }
    if (event.target === deleteModal) {
        closeDeleteConfirmation();
    }
    if (event.target === editCommentModal) {
        closeEditCommentModal();
    }
    if (event.target === deleteCommentModal) {
        closeDeleteCommentConfirmation();
    }
}

let deleteCommentId = null;

// Basculer la section des commentaires
function toggleCommentsSection(postId) {
    const section = document.getElementById('comments-section-' + postId);
    section.classList.toggle('hidden');
}

// Validation du commentaire
function validateComment(postId) {
    const contentElement = document.getElementById('commentContent-' + postId);
    const content = contentElement.value.trim();
    const errorDiv = document.getElementById('commentError-' + postId);
    
    errorDiv.textContent = '';
    
    if (content === '') {
        errorDiv.textContent = 'Le commentaire ne peut pas être vide.';
        return false;
    }
    
    const postIdField = document.querySelector('#commentForm-' + postId + ' input[name="post_id"]');
    const form = new URLSearchParams();
    form.append('action', 'add_comment');
    form.append('ajax', '1');
    form.append('post_id', postIdField.value);
    form.append('contenu', content);

    fetch('Home.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentsSection = document.getElementById('comments-section-' + postId);
            const placeholder = commentsSection.querySelector('.text-muted.text-center');
            if (placeholder) {
                placeholder.remove();
            }

            const newComment = document.createElement('div');
            newComment.className = 'comment-item';
            newComment.id = 'comment-item-' + data.id;
            newComment.innerHTML = `
                <div class="comment-header">
                    <div>
                        <div class="comment-author">HappyBite</div>
                        <div class="comment-date">${data.dateCommentaire}</div>
                    </div>
                    <div class="comment-actions">
                        <div class="dropdown">
                            <button class="comment-btn dropdown-toggle" type="button" id="commentMenu${data.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu${data.id}">
                                <li>
                                    <button class="dropdown-item" type="button" onclick="openEditCommentModal(${data.id}, '${escapeJsString(data.contenu)}')">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" type="button" onclick="openDeleteCommentConfirmation(${data.id})">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <p id="comment-content-${data.id}" style="margin: 0; font-size: 13px; color: #333;">${escapeHtml(data.contenu)}</p>
            `;
            commentsSection.insertBefore(newComment, commentsSection.querySelector('.comment-form'));
            contentElement.value = '';
        } else {
            errorDiv.textContent = 'Erreur lors de l\'ajout du commentaire.';
        }
    })
    .catch(() => {
        errorDiv.textContent = 'Erreur lors de l\'ajout du commentaire.';
    });

    return false;
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/\n/g, '<br>');
}

function escapeJsString(text) {
    return text
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\"/g, '\\"')
        .replace(/\n/g, '\\n');
}

// Ouvrir le modal de modification de commentaire
function openEditCommentModal(commentId, content) {
    document.getElementById('commentId').value = commentId;
    document.getElementById('editCommentContent').value = content;
    document.getElementById('editCommentModal').classList.add('show');
}

// Fermer le modal de modification de commentaire
function closeEditCommentModal() {
    document.getElementById('editCommentModal').classList.remove('show');
}

// Valider la modification du commentaire
function validateEditComment() {
    const content = document.getElementById('editCommentContent').value.trim();
    const errorDiv = document.getElementById('editCommentError');
    const commentId = document.getElementById('commentId').value;
    
    errorDiv.textContent = '';
    
    if (content === '') {
        errorDiv.textContent = 'Le commentaire ne peut pas être vide.';
        return false;
    }
    
    fetch('Home.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_comment&ajax=1&id=' + encodeURIComponent(commentId) + '&contenu=' + encodeURIComponent(content)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentContent = document.getElementById('comment-content-' + data.id);
            if (commentContent) {
                commentContent.textContent = content;
            }
            closeEditCommentModal();
        } else {
            errorDiv.textContent = 'Erreur lors de la mise à jour du commentaire.';
        }
    })
    .catch(() => {
        errorDiv.textContent = 'Erreur lors de la mise à jour du commentaire.';
    });
    
    return false;
}

// Ouvrir le modal de confirmation de suppression de commentaire
function openDeleteCommentConfirmation(commentId) {
    deleteCommentId = commentId;
    document.getElementById('deleteCommentConfirmationModal').classList.add('show');
}

// Fermer le modal de confirmation de suppression de commentaire
function closeDeleteCommentConfirmation() {
    deleteCommentId = null;
    document.getElementById('deleteCommentConfirmationModal').classList.remove('show');
}

// Confirmer la suppression du commentaire
function confirmDeleteComment() {
    if (deleteCommentId !== null) {
        fetch('Home.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_comment&ajax=1&id=' + encodeURIComponent(deleteCommentId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentItem = document.getElementById('comment-item-' + data.id);
                if (commentItem) {
                    commentItem.remove();
                }
                closeDeleteCommentConfirmation();
            }
        })
        .catch(() => {
            closeDeleteCommentConfirmation();
        });
    }
}
</script>

</body>
</html>
