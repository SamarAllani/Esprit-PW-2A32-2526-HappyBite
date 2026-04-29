<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/PostController.php';
require_once __DIR__ . '/../Controllers/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();

$message = '';
$messageType = '';

if (isset($_GET['success'])) { $message = 'Post publié avec succès !'; $messageType = 'success'; }
if (isset($_GET['updated'])) { $message = 'Post mis à jour avec succès !'; $messageType = 'success'; }
if (isset($_GET['comment_success'])) { $message = 'Commentaire ajouté avec succès !'; $messageType = 'success'; }
if (isset($_GET['comment_updated'])) { $message = 'Commentaire mis à jour avec succès !'; $messageType = 'success'; }
if (isset($_GET['comment_deleted'])) { $message = 'Commentaire supprimé avec succès !'; $messageType = 'success'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    if ($_POST['action'] === 'add_post') {
        $contenu = $_POST['contenu'] ?? '';
        if (!empty($contenu)) {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadsDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                $image = uniqid() . '-' . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadsDir . $image);
            }
            if ($postController->create($contenu, $image)) { header('Location: Communaute.php?success=1'); exit; }
            else { $message = 'Erreur lors de la publication du post.'; $messageType = 'danger'; }
        } else { $message = 'Le contenu du post ne peut pas être vide.'; $messageType = 'warning'; }
    } elseif ($_POST['action'] === 'update_post') {
        $id = (int)$_POST['id']; $contenu = $_POST['contenu'] ?? '';
        if (!empty($contenu)) {
            if ($postController->update($id, $contenu)) {
                if ($isAjax) { echo json_encode(['success' => true, 'id' => $id, 'contenu' => $contenu]); exit; }
                header('Location: Communaute.php?updated=1'); exit;
            } else { $message = 'Erreur lors de la mise à jour du post.'; $messageType = 'danger'; }
        } else { $message = 'Le contenu du post ne peut pas être vide.'; $messageType = 'warning'; }
    } elseif ($_POST['action'] === 'delete_post') {
        $id = (int)$_POST['id'];
        if ($postController->delete($id)) {
            if ($isAjax) { echo json_encode(['success' => true, 'id' => $id]); exit; }
            $message = 'Post supprimé avec succès !'; $messageType = 'success';
        } else { $message = 'Erreur lors de la suppression du post.'; $messageType = 'danger'; }
    } elseif ($_POST['action'] === 'like_post') {
        $id = (int)$_POST['id']; $liked = $_POST['liked'] === 'true';
        if ($liked) $postController->removeLike($id); else $postController->addLike($id);
        $post = $postController->getById($id);
        echo json_encode(['success' => true, 'likes' => $post['nombreLikes']]); exit;
    } elseif ($_POST['action'] === 'add_comment') {
        $post_id = (int)$_POST['post_id']; $contenu = $_POST['contenu'] ?? '';
        if (!empty($contenu)) {
            $commentId = $commentaireController->create($contenu, $post_id);
            if ($commentId !== false) {
                if ($isAjax) { echo json_encode(['success' => true, 'id' => $commentId, 'post_id' => $post_id, 'contenu' => $contenu, 'dateCommentaire' => date('d M Y à H:i')]); exit; }
                header('Location: Communaute.php?comment_success=1'); exit;
            } else { $message = "Erreur lors de l'ajout du commentaire."; $messageType = 'danger'; }
        } else { $message = 'Le commentaire ne peut pas être vide.'; $messageType = 'warning'; }
    } elseif ($_POST['action'] === 'update_comment') {
        $id = (int)$_POST['id']; $contenu = $_POST['contenu'] ?? '';
        if (!empty($contenu)) {
            if ($commentaireController->update($id, $contenu)) {
                if ($isAjax) { echo json_encode(['success' => true, 'id' => $id, 'contenu' => $contenu]); exit; }
                header('Location: Communaute.php?comment_updated=1'); exit;
            } else { $message = 'Erreur lors de la mise à jour du commentaire.'; $messageType = 'danger'; }
        } else { $message = 'Le commentaire ne peut pas être vide.'; $messageType = 'warning'; }
    } elseif ($_POST['action'] === 'delete_comment') {
        $id = (int)$_POST['id'];
        if ($commentaireController->delete($id)) {
            if ($isAjax) { echo json_encode(['success' => true, 'id' => $id]); exit; }
            header('Location: Communaute.php?comment_deleted=1'); exit;
        } else { $message = 'Erreur lors de la suppression du commentaire.'; $messageType = 'danger'; }
    }
}

$posts = $postController->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Communaute - HappyBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #2f6f57;
            --green-dark: #1f4d3a;
            --green-light: #eaf4ef;
            --green-mid: #4a9070;
            --accent: #f0a500;
            --bg: #f4f6f9;
            --card-bg: #ffffff;
            --text: #1a1a2e;
            --text-muted: #6b7280;
            --border: #e8ecf0;
            --shadow: 0 4px 24px rgba(47,111,87,0.08);
            --shadow-hover: 0 8px 32px rgba(47,111,87,0.16);
            --radius: 16px;
            --radius-sm: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .main-navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 var(--border), 0 4px 20px rgba(0,0,0,0.04);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .main-navbar .nav-container {
            width: 90%; max-width: 1400px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; gap: 24px;
        }
        .main-navbar .nav-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none !important; flex-shrink: 0;
        }
        .main-navbar .nav-logo img { height: 40px; width: auto; }
        .main-navbar .nav-logo span { font-weight: 700; font-size: 1.2rem; color: var(--green) !important; }
        .main-navbar .nav-links {
            list-style: none; display: flex; align-items: center; gap: 6px; margin: 0; padding: 0;
        }
        .main-navbar .nav-links li a {
            text-decoration: none !important; color: var(--text-muted) !important;
            font-weight: 500; font-size: 0.95rem; padding: 8px 14px; border-radius: 8px;
            transition: all 0.2s ease;
        }
        .main-navbar .nav-links li a:hover { color: var(--green) !important; background: var(--green-light); }
        .main-navbar .nav-links li a.active {
            color: var(--green) !important; font-weight: 700; background: var(--green-light);
        }
        .main-navbar .nav-user { display: flex; align-items: center; gap: 8px; }
        .main-navbar .nav-action {
            text-decoration: none !important; color: var(--text-muted) !important;
            font-weight: 500; padding: 8px 14px; border-radius: 8px; transition: all 0.2s ease; font-size: 0.9rem;
        }
        .main-navbar .nav-action:hover { background: var(--green-light); color: var(--green) !important; }
        @media (max-width: 900px) {
            .main-navbar .nav-container { flex-wrap: wrap; justify-content: center; }
        }

        /* ── HERO BANNER ── */
        .community-hero {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-mid) 60%, #6dbf9e 100%);
            padding: 48px 0 56px;
            position: relative;
            overflow: hidden;
        }
        .community-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .community-hero .hero-inner {
            max-width: 700px; margin: 0 auto; text-align: center; position: relative; padding: 0 20px;
        }
        .community-hero h1 {
            font-size: 2.4rem; font-weight: 800; color: #fff; margin-bottom: 12px; letter-spacing: -0.5px;
        }
        .community-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.85); margin-bottom: 0; }
        .hero-stats {
            display: flex; justify-content: center; gap: 40px; margin-top: 28px;
        }
        .hero-stat { text-align: center; }
        .hero-stat .stat-num { font-size: 1.6rem; font-weight: 800; color: #fff; display: block; }
        .hero-stat .stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── LAYOUT ── */
        .page-layout {
            max-width: 1100px; margin: 0 auto; padding: 36px 20px;
            display: grid; grid-template-columns: 1fr 320px; gap: 28px;
        }
        @media (max-width: 860px) {
            .page-layout { grid-template-columns: 1fr; }
            .sidebar-col { order: -1; }
        }

        /* ── SIDEBAR ── */
        .sidebar-widget {
            background: var(--card-bg); border-radius: var(--radius);
            padding: 22px; box-shadow: var(--shadow); margin-bottom: 20px;
        }
        .sidebar-widget h6 {
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.8px; color: var(--text-muted); margin-bottom: 16px;
        }
        .sidebar-tip {
            display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;
        }
        .sidebar-tip:last-child { margin-bottom: 0; }
        .tip-icon {
            width: 36px; height: 36px; border-radius: 10px; background: var(--green-light);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            color: var(--green); font-size: 0.9rem;
        }
        .tip-text { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }
        .tip-text strong { color: var(--text); display: block; margin-bottom: 2px; }

        /* ── ALERT ── */
        .toast-alert {
            border-radius: var(--radius-sm); border: none; font-size: 0.9rem;
            animation: slideDown 0.4s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── CREATE POST CARD ── */
        .create-post-card {
            background: var(--card-bg); border-radius: var(--radius);
            padding: 22px; box-shadow: var(--shadow); margin-bottom: 24px;
            transition: box-shadow 0.3s ease;
        }
        .create-post-card:hover { box-shadow: var(--shadow-hover); }
        .create-post-header {
            display: flex; align-items: center; gap: 14px; margin-bottom: 16px;
        }
        .avatar-circle {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--green-mid));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 1rem; flex-shrink: 0;
        }
        .create-post-header span { color: var(--text-muted); font-size: 0.9rem; }
        .post-textarea {
            width: 100%; border: 2px solid var(--border); border-radius: var(--radius-sm);
            padding: 14px 16px; resize: none; font-family: inherit; font-size: 0.95rem;
            color: var(--text); background: #fafbfc; transition: all 0.25s ease; line-height: 1.6;
        }
        .post-textarea:focus {
            outline: none; border-color: var(--green);
            background: #fff; box-shadow: 0 0 0 4px rgba(47,111,87,0.08);
        }
        .post-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 14px; flex-wrap: wrap; gap: 10px;
        }
        .file-label {
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            color: var(--text-muted); font-size: 0.88rem; font-weight: 500;
            padding: 8px 14px; border-radius: 8px; border: 1.5px dashed var(--border);
            transition: all 0.2s ease;
        }
        .file-label:hover { border-color: var(--green); color: var(--green); background: var(--green-light); }
        .file-label i { font-size: 1rem; }
        #postImage { display: none; }
        .image-preview-wrap { margin-top: 12px; position: relative; display: inline-block; }
        .image-preview-wrap img {
            max-height: 200px; border-radius: var(--radius-sm); border: 2px solid var(--border);
        }
        .remove-preview {
            position: absolute; top: -8px; right: -8px; width: 24px; height: 24px;
            background: #ef4444; color: #fff; border: none; border-radius: 50%;
            font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .btn-publish {
            background: linear-gradient(135deg, var(--green), var(--green-mid));
            color: #fff; border: none; padding: 10px 28px; border-radius: 10px;
            font-weight: 600; font-size: 0.95rem; cursor: pointer;
            transition: all 0.25s ease; display: flex; align-items: center; gap: 8px;
        }
        .btn-publish:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(47,111,87,0.35); }
        .btn-publish:active { transform: translateY(0); }
        .error-message { color: #ef4444; font-size: 0.8rem; margin-top: 6px; }

        /* ── POST CARD ── */
        .post-card {
            background: var(--card-bg); border-radius: var(--radius);
            margin-bottom: 20px; box-shadow: var(--shadow);
            transition: all 0.3s ease; overflow: hidden;
            animation: fadeUp 0.5s ease both;
        }
        .post-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-2px); }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .post-card:nth-child(1) { animation-delay: 0.05s; }
        .post-card:nth-child(2) { animation-delay: 0.1s; }
        .post-card:nth-child(3) { animation-delay: 0.15s; }
        .post-card:nth-child(4) { animation-delay: 0.2s; }
        .post-card:nth-child(5) { animation-delay: 0.25s; }

        .post-header {
            padding: 18px 20px 14px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .post-author-info { display: flex; align-items: center; gap: 12px; }
        .post-author-name { font-weight: 700; font-size: 0.95rem; color: var(--text); }
        .post-date { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }
        .menu-dots {
            background: none; border: none; width: 34px; height: 34px; border-radius: 8px;
            cursor: pointer; color: var(--text-muted); font-size: 1rem;
            display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        }
        .menu-dots:hover { background: var(--bg); color: var(--text); }

        .post-body { padding: 0 20px 16px; }
        .post-body p { font-size: 0.95rem; line-height: 1.7; color: var(--text); margin: 0; }
        .post-image {
            width: 100%; height: auto; display: block;
            border-radius: var(--radius-sm); margin-top: 14px;
        }

        .post-actions {
            padding: 4px 12px 12px;
            display: flex; gap: 6px; border-top: 1px solid var(--border); padding-top: 12px; margin: 0 8px;
        }
        .post-action-btn {
            flex: 1; border: none; background: none; cursor: pointer;
            padding: 9px 12px; border-radius: 10px; transition: all 0.2s ease;
            color: var(--text-muted); font-size: 0.88rem; font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: 7px;
        }
        .post-action-btn:hover { background: var(--bg); color: var(--text); }
        .post-action-btn.liked { color: #ef4444; }
        .post-action-btn.liked:hover { background: #fef2f2; }
        .post-action-btn i { font-size: 1rem; transition: transform 0.2s ease; }
        .post-action-btn:hover i { transform: scale(1.15); }
        .post-action-btn.liked i { animation: heartPop 0.3s ease; }
        @keyframes heartPop {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.4); }
            100% { transform: scale(1); }
        }

        /* ── COMMENTS ── */
        .comments-section {
            background: #f8faf9; border-top: 1px solid var(--border);
            padding: 16px 20px; max-height: 500px; overflow-y: auto;
        }
        .comments-section.hidden { display: none; }
        .comment-item {
            display: flex; gap: 10px; margin-bottom: 14px;
            animation: fadeUp 0.3s ease both;
        }
        .comment-avatar {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--green-mid), #6dbf9e);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.75rem; font-weight: 700;
        }
        .comment-bubble {
            background: #fff; border-radius: 0 12px 12px 12px;
            padding: 10px 14px; flex: 1; border: 1px solid var(--border);
            position: relative;
        }
        .comment-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .comment-author { font-weight: 700; font-size: 0.82rem; color: var(--text); }
        .comment-date { font-size: 0.75rem; color: var(--text-muted); }
        .comment-text { font-size: 0.88rem; color: var(--text); line-height: 1.5; }
        .comment-actions-row { display: flex; gap: 6px; margin-top: 6px; }
        .comment-action-link {
            background: none; border: none; font-size: 0.75rem; color: var(--text-muted);
            cursor: pointer; padding: 2px 6px; border-radius: 4px; transition: all 0.15s;
        }
        .comment-action-link:hover { background: var(--bg); color: var(--green); }
        .comment-action-link.danger:hover { color: #ef4444; background: #fef2f2; }

        .comment-form-row { display: flex; gap: 10px; align-items: flex-start; margin-top: 12px; }
        .comment-textarea {
            flex: 1; border: 1.5px solid var(--border); border-radius: 10px;
            padding: 9px 13px; font-size: 0.88rem; font-family: inherit; resize: none;
            transition: all 0.2s ease; background: #fff;
        }
        .comment-textarea:focus {
            outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(47,111,87,0.08);
        }
        .btn-comment {
            background: var(--green); color: #fff; border: none; padding: 9px 18px;
            border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s ease; white-space: nowrap;
        }
        .btn-comment:hover { background: var(--green-dark); transform: translateY(-1px); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow);
        }
        .empty-icon {
            width: 80px; height: 80px; border-radius: 50%; background: var(--green-light);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 2rem; color: var(--green);
        }
        .empty-state h5 { font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .empty-state p { color: var(--text-muted); font-size: 0.9rem; }

        /* ── MODALS ── */
        .modal-custom {
            display: none; position: fixed; z-index: 2000; inset: 0;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        .modal-custom.show { display: flex; animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-custom-content {
            background: #fff; border-radius: var(--radius); padding: 32px;
            max-width: 440px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: scaleIn 0.25s ease;
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }
        .modal-custom-content h4 { font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .modal-custom-content p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 22px; }
        .modal-custom-content .form-control {
            border: 1.5px solid var(--border); border-radius: 10px; font-size: 0.9rem; padding: 10px 14px;
        }
        .modal-custom-content .form-control:focus {
            border-color: var(--green); box-shadow: 0 0 0 3px rgba(47,111,87,0.1);
        }
        .modal-footer-btns { display: flex; gap: 10px; margin-top: 18px; }
        .modal-footer-btns .btn { flex: 1; border-radius: 10px; font-weight: 600; padding: 10px; }
        .btn-green { background: var(--green); color: #fff; border: none; }
        .btn-green:hover { background: var(--green-dark); color: #fff; }
        .modal-icon { font-size: 2.5rem; margin-bottom: 14px; }

        /* ── DROPDOWN ── */
        .dropdown-menu {
            border: 1px solid var(--border); border-radius: var(--radius-sm);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1); padding: 6px;
        }
        .dropdown-item {
            border-radius: 7px; font-size: 0.88rem; padding: 8px 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .dropdown-item:hover { background: var(--bg); }
        .dropdown-item.text-danger:hover { background: #fef2f2; }

        /* ── SCROLLBAR ── */
        .comments-section::-webkit-scrollbar { width: 4px; }
        .comments-section::-webkit-scrollbar-track { background: transparent; }
        .comments-section::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    </style>
</head>
<body>

<nav class="main-navbar">
    <div class="nav-container">
        <a href="Home.php" class="nav-logo">
            <img src="assets/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>
        <ul class="nav-links">
            <li><a href="Home.php">Accueil</a></li>
            <li><a href="List-Produit.php">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="Communaute.php" class="active">Communaute</a></li>
        </ul>
        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action">Sante</a>
            <a href="#" class="nav-action">Profil</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="community-hero">
    <div class="hero-inner">
        <h1><i class="fas fa-seedling me-2"></i>Communaute HappyBite</h1>
        <p>Partagez vos decouvertes culinaires, inspirez et soyez inspire par notre communaute.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="stat-num"><?php echo count($posts); ?></span>
                <span class="stat-label">Posts</span>
            </div>
            <div class="hero-stat">
                <span class="stat-num"><?php echo array_sum(array_column($posts, 'nombreLikes')); ?></span>
                <span class="stat-label">J aime</span>
            </div>
            <div class="hero-stat">
                <span class="stat-num"><?php
                    $totalComments = 0;
                    foreach ($posts as $p) $totalComments += count($commentaireController->getByPostId($p['id']));
                    echo $totalComments;
                ?></span>
                <span class="stat-label">Commentaires</span>
            </div>
        </div>
    </div>
</div>

<!-- MAIN LAYOUT -->
<div class="page-layout">
    <!-- FEED COLUMN -->
    <div class="feed-col">

        <?php if (!empty($message)): ?>
        <div class="alert toast-alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- CREATE POST -->
        <div class="create-post-card">
            <div class="create-post-header">
                <div class="avatar-circle">HB</div>
                <span>Quoi de neuf ? Partagez avec la communaute...</span>
            </div>
            <form id="addPostForm" method="POST" enctype="multipart/form-data" onsubmit="return validateAddPost()">
                <input type="hidden" name="action" value="add_post">
                <textarea id="postContent" name="contenu" class="post-textarea" placeholder="Ecrivez quelque chose de savoureux..." rows="3"></textarea>
                <div id="contentError" class="error-message"></div>
                <div id="imagePreviewWrap" class="image-preview-wrap" style="display:none;">
                    <img id="imagePreview" src="" alt="preview">
                    <button type="button" class="remove-preview" onclick="removeImagePreview()"><i class="fas fa-times"></i></button>
                </div>
                <div class="post-toolbar">
                    <label for="postImage" class="file-label">
                        <i class="fas fa-image"></i> Ajouter une photo
                    </label>
                    <input type="file" id="postImage" name="image" accept="image/*" onchange="previewImage(this)">
                    <button type="submit" class="btn-publish">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                </div>
            </form>
        </div>

        <!-- POSTS FEED -->
        <?php if (empty($posts)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-seedling"></i></div>
            <h5>Aucun post pour le moment</h5>
            <p>Soyez le premier a partager quelque chose avec la communaute !</p>
        </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
            <div class="post-card" id="post-card-<?php echo $post['id']; ?>">
                <div class="post-header">
                    <div class="post-author-info">
                        <div class="avatar-circle" style="width:40px;height:40px;font-size:0.85rem;">HB</div>
                        <div>
                            <div class="post-author-name">HappyBite</div>
                            <div class="post-date"><i class="fas fa-clock me-1"></i><?php echo date('d M Y a H:i', strtotime($post['datePublication'])); ?></div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="menu-dots" type="button" id="dropdownMenu<?php echo $post['id']; ?>" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu<?php echo $post['id']; ?>">
                            <li><button type="button" class="dropdown-item" onclick="openEditPostModal(<?php echo $post['id']; ?>)"><i class="fas fa-edit text-primary"></i> Modifier</button></li>
                            <li><button type="button" class="dropdown-item text-danger" onclick="openDeleteConfirmation(<?php echo $post['id']; ?>)"><i class="fas fa-trash"></i> Supprimer</button></li>
                        </ul>
                    </div>
                </div>
                <div class="post-body">
                    <p id="post-text-<?php echo $post['id']; ?>"><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
                    <?php if (!empty($post['image'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" class="post-image">
                    <?php endif; ?>
                </div>
                <div class="post-actions">
                    <button class="post-action-btn like-btn" id="like-btn-<?php echo $post['id']; ?>" onclick="toggleLike(<?php echo $post['id']; ?>, this)">
                        <i class="fas fa-heart"></i>
                        <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['nombreLikes']; ?> J aime</span>
                    </button>
                    <button class="post-action-btn" onclick="toggleCommentsSection(<?php echo $post['id']; ?>)">
                        <i class="fas fa-comment-dots"></i>
                        <span>Commenter</span>
                    </button>
                </div>

                <!-- COMMENTS -->
                <div id="comments-section-<?php echo $post['id']; ?>" class="comments-section hidden">
                    <?php $comments = $commentaireController->getByPostId($post['id']); ?>
                    <?php if (empty($comments)): ?>
                        <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;padding:10px 0;">Aucun commentaire. Soyez le premier !</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-item-<?php echo $comment['id']; ?>">
                            <div class="comment-avatar">HB</div>
                            <div class="comment-bubble">
                                <div class="comment-meta">
                                    <span class="comment-author">HappyBite</span>
                                    <span class="comment-date"><?php echo date('d M Y a H:i', strtotime($comment['dateCommentaire'])); ?></span>
                                </div>
                                <p class="comment-text" id="comment-content-<?php echo $comment['id']; ?>"><?php echo htmlspecialchars($comment['contenu']); ?></p>
                                <div class="comment-actions-row">
                                    <button class="comment-action-link" onclick='openEditCommentModal(<?php echo $comment['id']; ?>, "<?php echo addslashes($comment['contenu']); ?>")'>
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="comment-action-link danger" onclick="openDeleteCommentConfirmation(<?php echo $comment['id']; ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="comment-form-row">
                        <div class="comment-avatar" style="margin-top:2px;">HB</div>
                        <form id="commentForm-<?php echo $post['id']; ?>" style="flex:1;" method="POST" onsubmit="return validateComment(<?php echo $post['id']; ?>)">
                            <input type="hidden" name="action" value="add_comment">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div style="display:flex;gap:8px;align-items:flex-end;">
                                <textarea class="comment-textarea" name="contenu" id="commentContent-<?php echo $post['id']; ?>" placeholder="Ajouter un commentaire..." rows="1"></textarea>
                                <button type="submit" class="btn-comment"><i class="fas fa-paper-plane"></i></button>
                            </div>
                            <div id="commentError-<?php echo $post['id']; ?>" class="error-message"></div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar-col">
        <div class="sidebar-widget">
            <h6><i class="fas fa-lightbulb me-2"></i>Conseils de la communaute</h6>
            <div class="sidebar-tip">
                <div class="tip-icon"><i class="fas fa-camera"></i></div>
                <div class="tip-text"><strong>Ajoutez des photos</strong>Les posts avec images recoivent 3x plus d engagement.</div>
            </div>
            <div class="sidebar-tip">
                <div class="tip-icon"><i class="fas fa-heart"></i></div>
                <div class="tip-text"><strong>Likez et commentez</strong>Encouragez les autres membres de la communaute.</div>
            </div>
            <div class="sidebar-tip">
                <div class="tip-icon"><i class="fas fa-utensils"></i></div>
                <div class="tip-text"><strong>Partagez vos recettes</strong>Inspirez la communaute avec vos creations culinaires.</div>
            </div>
        </div>
        <div class="sidebar-widget">
            <h6><i class="fas fa-fire me-2"></i>Tendances</h6>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <a href="List-Recette.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);padding:10px;border-radius:10px;transition:background 0.2s;" onmouseover="this.style.background='var(--green-light)'" onmouseout="this.style.background='transparent'">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--green-light);display:flex;align-items:center;justify-content:center;color:var(--green);font-size:0.9rem;"><i class="fas fa-book-open"></i></span>
                    <span style="font-size:0.88rem;font-weight:500;">Recettes populaires</span>
                </a>
                <a href="List-Produit.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);padding:10px;border-radius:10px;transition:background 0.2s;" onmouseover="this.style.background='var(--green-light)'" onmouseout="this.style.background='transparent'">
                    <span style="width:32px;height:32px;border-radius:8px;background:#fff3e0;display:flex;align-items:center;justify-content:center;color:#f0a500;font-size:0.9rem;"><i class="fas fa-shopping-bag"></i></span>
                    <span style="font-size:0.88rem;font-weight:500;">Nouveaux produits</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
<div id="editPostModal" class="modal-custom">
    <div class="modal-custom-content">
        <div class="modal-icon">&#9998;</div>
        <h4>Modifier le post</h4>
        <form id="editPostForm" method="POST" onsubmit="return validateEditPost()">
            <input type="hidden" name="action" value="update_post">
            <input type="hidden" id="postId" name="id" value="">
            <textarea id="editPostContent" name="contenu" class="form-control" rows="4"></textarea>
            <div id="editContentError" class="error-message mt-2"></div>
            <div class="modal-footer-btns">
                <button type="submit" class="btn btn-green">Enregistrer</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditPostModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteConfirmationModal" class="modal-custom">
    <div class="modal-custom-content">
        <div class="modal-icon">&#128465;</div>
        <h4>Supprimer ce post ?</h4>
        <p>Cette action est irreversible. Le post sera definitivement supprime.</p>
        <div class="modal-footer-btns">
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">Supprimer</button>
            <button type="button" class="btn btn-secondary" onclick="closeDeleteConfirmation()">Annuler</button>
        </div>
    </div>
</div>

<div id="editCommentModal" class="modal-custom">
    <div class="modal-custom-content">
        <div class="modal-icon">&#128172;</div>
        <h4>Modifier le commentaire</h4>
        <form id="editCommentForm" method="POST" onsubmit="return validateEditComment()">
            <input type="hidden" name="action" value="update_comment">
            <input type="hidden" id="commentId" name="id" value="">
            <textarea id="editCommentContent" name="contenu" class="form-control" rows="3"></textarea>
            <div id="editCommentError" class="error-message mt-2"></div>
            <div class="modal-footer-btns">
                <button type="submit" class="btn btn-green">Enregistrer</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditCommentModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCommentConfirmationModal" class="modal-custom">
    <div class="modal-custom-content">
        <div class="modal-icon">&#128465;</div>
        <h4>Supprimer ce commentaire ?</h4>
        <p>Cette action est irreversible.</p>
        <div class="modal-footer-btns">
            <button type="button" class="btn btn-danger" onclick="confirmDeleteComment()">Supprimer</button>
            <button type="button" class="btn btn-secondary" onclick="closeDeleteCommentConfirmation()">Annuler</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let deletePostId = null;
let deleteCommentId = null;

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewWrap').style.display = 'inline-block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function removeImagePreview() {
    document.getElementById('postImage').value = '';
    document.getElementById('imagePreviewWrap').style.display = 'none';
    document.getElementById('imagePreview').src = '';
}

function validateAddPost() {
    const content = document.getElementById('postContent').value.trim();
    const err = document.getElementById('contentError');
    err.textContent = '';
    if (!content) { err.textContent = 'Le contenu du post est obligatoire.'; return false; }
    return true;
}

function validateEditPost() {
    const content = document.getElementById('editPostContent').value.trim();
    const err = document.getElementById('editContentError');
    const postId = document.getElementById('postId').value;
    err.textContent = '';
    if (!content) { err.textContent = 'Le contenu du post est obligatoire.'; return false; }
    fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update_post&ajax=1&id=' + encodeURIComponent(postId) + '&contenu=' + encodeURIComponent(content)
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const el = document.getElementById('post-text-' + data.id);
            if (el) el.innerHTML = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
            closeEditPostModal();
        } else err.textContent = 'Erreur lors de la mise a jour.';
    }).catch(() => err.textContent = 'Erreur lors de la mise a jour.');
    return false;
}

function openEditPostModal(postId) {
    const el = document.getElementById('post-text-' + postId);
    document.getElementById('postId').value = postId;
    document.getElementById('editPostContent').value = el.innerHTML.replace(/<br\s*\/?>/gi, '\n');
    document.getElementById('editPostModal').classList.add('show');
}
function closeEditPostModal() { document.getElementById('editPostModal').classList.remove('show'); }

function openDeleteConfirmation(postId) { deletePostId = postId; document.getElementById('deleteConfirmationModal').classList.add('show'); }
function closeDeleteConfirmation() { deletePostId = null; document.getElementById('deleteConfirmationModal').classList.remove('show'); }

function confirmDelete() {
    if (deletePostId !== null) {
        fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_post&ajax=1&id=' + encodeURIComponent(deletePostId)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const card = document.getElementById('post-card-' + data.id);
                if (card) { card.style.animation = 'fadeOut 0.3s ease forwards'; setTimeout(() => card.remove(), 300); }
                closeDeleteConfirmation();
            }
        }).catch(() => closeDeleteConfirmation());
    }
}

function toggleLike(postId, button) {
    const isLiked = button.classList.contains('liked');
    fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=like_post&id=' + postId + '&liked=' + isLiked
    }).then(r => r.json()).then(data => {
        if (data.success) {
            button.classList.toggle('liked');
            document.getElementById('like-count-' + postId).textContent = data.likes + ' J aime';
        }
    });
}

function toggleCommentsSection(postId) {
    const section = document.getElementById('comments-section-' + postId);
    section.classList.toggle('hidden');
    if (!section.classList.contains('hidden')) {
        section.style.animation = 'slideDown 0.3s ease';
    }
}

function validateComment(postId) {
    const el = document.getElementById('commentContent-' + postId);
    const content = el.value.trim();
    const err = document.getElementById('commentError-' + postId);
    err.textContent = '';
    if (!content) { err.textContent = 'Le commentaire ne peut pas etre vide.'; return false; }
    const form = new URLSearchParams();
    form.append('action', 'add_comment'); form.append('ajax', '1');
    form.append('post_id', document.querySelector('#commentForm-' + postId + ' input[name="post_id"]').value);
    form.append('contenu', content);
    fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: form.toString() })
    .then(r => r.json()).then(data => {
        if (data.success) {
            const section = document.getElementById('comments-section-' + postId);
            const placeholder = section.querySelector('p[style*="text-align:center"]');
            if (placeholder) placeholder.remove();
            const div = document.createElement('div');
            div.className = 'comment-item'; div.id = 'comment-item-' + data.id;
            div.innerHTML = `<div class="comment-avatar">HB</div><div class="comment-bubble"><div class="comment-meta"><span class="comment-author">HappyBite</span><span class="comment-date">${data.dateCommentaire}</span></div><p class="comment-text" id="comment-content-${data.id}">${escapeHtml(data.contenu)}</p><div class="comment-actions-row"><button class="comment-action-link" onclick="openEditCommentModal(${data.id}, '${escapeJsString(data.contenu)}')"><i class="fas fa-edit"></i> Modifier</button><button class="comment-action-link danger" onclick="openDeleteCommentConfirmation(${data.id})"><i class="fas fa-trash"></i> Supprimer</button></div></div>`;
            section.insertBefore(div, section.querySelector('.comment-form-row'));
            el.value = '';
        } else err.textContent = "Erreur lors de l'ajout.";
    }).catch(() => err.textContent = "Erreur lors de l'ajout.");
    return false;
}

function escapeHtml(t) { return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;').replace(/\n/g,'<br>'); }
function escapeJsString(t) { return t.replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\n/g,'\\n'); }

function openEditCommentModal(id, content) {
    document.getElementById('commentId').value = id;
    document.getElementById('editCommentContent').value = content;
    document.getElementById('editCommentModal').classList.add('show');
}
function closeEditCommentModal() { document.getElementById('editCommentModal').classList.remove('show'); }

function validateEditComment() {
    const content = document.getElementById('editCommentContent').value.trim();
    const err = document.getElementById('editCommentError');
    const id = document.getElementById('commentId').value;
    err.textContent = '';
    if (!content) { err.textContent = 'Le commentaire ne peut pas etre vide.'; return false; }
    fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update_comment&ajax=1&id=' + encodeURIComponent(id) + '&contenu=' + encodeURIComponent(content)
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const el = document.getElementById('comment-content-' + data.id);
            if (el) el.textContent = content;
            closeEditCommentModal();
        } else err.textContent = 'Erreur lors de la mise a jour.';
    }).catch(() => err.textContent = 'Erreur lors de la mise a jour.');
    return false;
}

function openDeleteCommentConfirmation(id) { deleteCommentId = id; document.getElementById('deleteCommentConfirmationModal').classList.add('show'); }
function closeDeleteCommentConfirmation() { deleteCommentId = null; document.getElementById('deleteCommentConfirmationModal').classList.remove('show'); }

function confirmDeleteComment() {
    if (deleteCommentId !== null) {
        fetch('Communaute.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_comment&ajax=1&id=' + encodeURIComponent(deleteCommentId)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const el = document.getElementById('comment-item-' + data.id);
                if (el) el.remove();
                closeDeleteCommentConfirmation();
            }
        }).catch(() => closeDeleteCommentConfirmation());
    }
}

window.onclick = function(e) {
    ['editPostModal','deleteConfirmationModal','editCommentModal','deleteCommentConfirmationModal'].forEach(id => {
        const m = document.getElementById(id);
        if (e.target === m) m.classList.remove('show');
    });
};

// Add fadeOut keyframe dynamically
const style = document.createElement('style');
style.textContent = '@keyframes fadeOut { from { opacity:1; transform:translateY(0); } to { opacity:0; transform:translateY(-10px); } }';
document.head.appendChild(style);
</script>
</body>
</html>
