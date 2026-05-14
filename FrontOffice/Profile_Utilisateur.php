<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Controllers/UtilisateurPhotoSql.php';

$pdo = Database::getConnection();
$uid = (int) ($_SESSION['user_id'] ?? 0);
if ($uid <= 0) {
    header('Location: auth/login.php');
    exit;
}

$pk = utilisateur_table_pk_column($pdo);

$columnExists = static function (PDO $pdoConn, string $table, string $column): bool {
    $stmt = $pdoConn->prepare(
        'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c'
    );
    $stmt->execute(['t' => $table, 'c' => $column]);

    return (int) $stmt->fetchColumn() > 0;
};

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($prenom === '' || $nom === '' || $email === '') {
            $error = 'Prénom, nom et email sont obligatoires.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email AND `{$pk}` != :id");
            $stmt->execute(['email' => $email, 'id' => $uid]);
            if ((int) $stmt->fetchColumn() > 0) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                $sets = ['prenom = :prenom', 'nom = :nom', 'email = :email'];
                $params = [
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'email' => $email,
                    'id' => $uid,
                ];
                if ($columnExists($pdo, 'utilisateur', 'description')) {
                    $sets[] = 'description = :description';
                    $params['description'] = $description;
                }
                if ($columnExists($pdo, 'utilisateur', 'budget')) {
                    $sets[] = 'budget = :budget';
                    $params['budget'] = isset($_POST['budget']) && $_POST['budget'] !== '' ? (float) $_POST['budget'] : 0.0;
                }

                $photoPath = null;
                if (!empty($_FILES['profile_photo']['tmp_name']) && is_uploaded_file((string) $_FILES['profile_photo']['tmp_name'])) {
                    $photoPath = utilisateur_handle_profile_photo_upload($_FILES['profile_photo']);
                    if ($photoPath === null) {
                        $error = 'Photo non enregistrée (format JPEG, PNG, GIF ou Webp, max 2 Mo).';
                    }
                }

                if ($error === '' && $photoPath !== null) {
                    if ($columnExists($pdo, 'utilisateur', 'profil-image')) {
                        $sets[] = '`profil-image` = :profil_image';
                        $params['profil_image'] = $photoPath;
                    } elseif ($columnExists($pdo, 'utilisateur', 'profile_photo')) {
                        $sets[] = 'profile_photo = :profile_photo';
                        $params['profile_photo'] = $photoPath;
                    }
                }

                if ($error === '') {
                    $sql = 'UPDATE utilisateur SET ' . implode(', ', $sets) . " WHERE `{$pk}` = :id";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute($params)) {
                        $_SESSION['user_prenom'] = $prenom;
                        $_SESSION['user_nom'] = $nom;
                        $_SESSION['user_email'] = $email;
                        $message = 'Profil mis à jour.';
                        header('Location: Profile_Utilisateur.php?ok=1');
                        exit;
                    }
                    $error = 'Impossible d’enregistrer les modifications.';
                }
            }
        }
    } elseif ($action === 'update_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare("SELECT motDePasse FROM utilisateur WHERE `{$pk}` = :id LIMIT 1");
        $stmt->execute(['id' => $uid]);
        $hash = (string) ($stmt->fetchColumn() ?: '');

        if ($hash === '' || !password_verify($current, $hash)) {
            $error = 'Mot de passe actuel incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } elseif ($new !== $confirm) {
            $error = 'La confirmation ne correspond pas.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE utilisateur SET motDePasse = :h WHERE `{$pk}` = :id");
            if ($up->execute(['h' => $newHash, 'id' => $uid])) {
                $message = 'Mot de passe modifié.';
            } else {
                $error = 'Impossible de mettre à jour le mot de passe.';
            }
        }
    } elseif ($action === 'delete_account') {
        try {
            $del = $pdo->prepare("DELETE FROM utilisateur WHERE `{$pk}` = :id");
            $del->execute(['id' => $uid]);
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/');
            }
            session_destroy();
            header('Location: Home.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Suppression impossible (données liées au compte). Contactez le support.';
        }
    }
}

if (isset($_GET['ok'])) {
    $message = 'Profil mis à jour.';
}

$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE `{$pk}` = :id LIMIT 1");
$stmt->execute(['id' => $uid]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$userRow) {
    header('Location: auth/login.php');
    exit;
}

$prenom = (string) ($userRow['prenom'] ?? '');
$nom = (string) ($userRow['nom'] ?? '');
$email = (string) ($userRow['email'] ?? '');
$role = (string) ($userRow['role'] ?? '');
$description = (string) ($userRow['description'] ?? '');
$budget = $userRow['budget'] ?? null;

$relPhoto = utilisateur_fetch_profile_relative_path($pdo, $uid);
$photoSrc = $relPhoto !== null ? utilisateur_nav_profile_img_src($relPhoto) : null;

$initials = '';
if ($prenom !== '') {
    $initials .= strtoupper(substr($prenom, 0, 1));
}
if ($nom !== '') {
    $initials .= strtoupper(substr($nom, 0, 1));
}
if ($initials === '') {
    $initials = 'M';
}

$hasBudget = $columnExists($pdo, 'utilisateur', 'budget');
$hasDescription = $columnExists($pdo, 'utilisateur', 'description');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Paramètres</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style-original-views.css">
    <style>
        .profile-page .profile-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e8ecf0;
            box-shadow: 0 8px 28px rgba(19, 30, 23, 0.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .profile-page .profile-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eef2f0;
            font-weight: 700;
            color: #173b2c;
            font-size: 1.05rem;
        }
        .profile-page .profile-card-body {
            padding: 1.25rem;
        }
        .profile-page .profile-photo-wrap {
            text-align: center;
        }
        .profile-page .profile-photo-img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(44, 126, 52, 0.35);
        }
        .profile-page .profile-photo-placeholder {
            width: 160px;
            height: 160px;
            margin: 0 auto;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d6b2a, #43a047);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2rem;
            border: 3px solid rgba(44, 126, 52, 0.35);
        }
        .profile-page .btn-profile-primary {
            background: #2C7E34;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
        }
        .profile-page .btn-profile-primary:hover {
            background: #256d2c;
            color: #fff;
        }
        .profile-page .btn-profile-muted {
            background: #eef2f0;
            border: none;
            color: #334;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
        }
        .profile-page .btn-profile-danger {
            background: #b91c1c;
            border: 2px solid #991b1b;
            color: #fff;
            font-weight: 600;
            padding: 0.6rem 1.25rem;
            border-radius: 12px;
            width: 100%;
        }
        .profile-page .btn-profile-danger:hover {
            background: #991b1b;
            color: #fff;
        }
        .profile-page .edit-panel {
            display: none;
            padding: 1.25rem;
            background: #f8faf8;
            border-top: 1px solid #eef2f0;
        }
        .profile-page .edit-panel.is-open {
            display: block;
        }
        .profile-page .info-row {
            display: flex;
            gap: 1rem;
            padding: 0.65rem 0;
            border-bottom: 1px solid #f0f3f1;
        }
        .profile-page .info-label {
            width: 130px;
            flex-shrink: 0;
            color: #5c6d66;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .profile-page .info-value {
            color: #173b2c;
            font-weight: 600;
        }
        .profile-page .page-lead {
            color: #5c6d66;
            max-width: 640px;
        }
    </style>
</head>
<body class="profile-page">

<?php
$nav_active = 'profile';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap">
    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold" style="color: #173b2c;">Paramètres du compte</h1>
            <p class="page-lead mx-auto">Modifiez vos informations personnelles, votre photo et votre mot de passe.</p>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success shadow-sm"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger shadow-sm"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="profile-card">
                    <div class="profile-card-header">Photo de profil</div>
                    <div class="profile-card-body profile-photo-wrap">
                        <?php if (is_string($photoSrc) && $photoSrc !== ''): ?>
                            <img class="profile-photo-img" src="<?php echo htmlspecialchars($photoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <?php else: ?>
                            <div class="profile-photo-placeholder" aria-hidden="true"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <p class="text-muted small mt-3 mb-0">Utilisez « Modifier » ci-dessous pour envoyer une nouvelle image.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="profile-card">
                    <div class="profile-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>Informations personnelles</span>
                        <button type="button" class="btn btn-sm btn-profile-primary" id="btn-toggle-profile">Modifier</button>
                    </div>
                    <div class="profile-card-body">
                        <div class="info-row">
                            <div class="info-label">Nom</div>
                            <div class="info-value"><?php echo htmlspecialchars(trim($prenom . ' ' . $nom), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Rôle</div>
                            <div class="info-value"><?php echo htmlspecialchars($role !== '' ? $role : 'client', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <?php if ($hasDescription): ?>
                            <div class="info-row border-bottom-0">
                                <div class="info-label">Description</div>
                                <div class="info-value" style="font-weight:500;"><?php echo nl2br(htmlspecialchars($description !== '' ? $description : '—', ENT_QUOTES, 'UTF-8')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($hasBudget): ?>
                            <div class="info-row border-bottom-0">
                                <div class="info-label">Budget</div>
                                <div class="info-value"><?php echo $budget !== null && $budget !== '' ? htmlspecialchars((string) $budget, ENT_QUOTES, 'UTF-8') . ' DT' : '—'; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="edit-panel" id="panel-edit-profile">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label class="form-label">Photo (optionnel)</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" id="input-profile-photo">
                                <div class="mt-2" id="photo-preview-wrap" style="display:none;">
                                    <img id="photo-preview-img" alt="" style="max-width:120px;max-height:120px;border-radius:50%;border:2px solid #ddd;">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom" class="form-control" required value="<?php echo htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom" class="form-control" required value="<?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <?php if ($hasDescription): ?>
                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            <?php endif; ?>
                            <?php if ($hasBudget): ?>
                                <div class="mb-3">
                                    <label class="form-label">Budget (DT)</label>
                                    <input type="number" name="budget" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars((string) ($budget ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-profile-primary">Enregistrer</button>
                                <button type="button" class="btn btn-profile-muted" id="btn-cancel-profile">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>Sécurité</span>
                        <button type="button" class="btn btn-sm btn-profile-primary" id="btn-toggle-password">Changer le mot de passe</button>
                    </div>
                    <div class="profile-card-body">
                        <div class="info-row border-bottom-0">
                            <div class="info-label">Mot de passe</div>
                            <div class="info-value">••••••••</div>
                        </div>
                    </div>
                    <div class="edit-panel" id="panel-password">
                        <form method="post">
                            <input type="hidden" name="action" value="update_password">
                            <div class="mb-2">
                                <label class="form-label">Mot de passe actuel</label>
                                <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nouveau mot de passe</label>
                                <input type="password" name="new_password" class="form-control" required autocomplete="new-password" minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmer</label>
                                <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password" minlength="6">
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-profile-primary">Enregistrer le mot de passe</button>
                                <button type="button" class="btn btn-profile-muted" id="btn-cancel-password">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="profile-card border-danger" style="border-color: #fecaca;">
                    <div class="profile-card-header" style="color:#991b1b;">Zone sensible</div>
                    <div class="profile-card-body">
                        <p class="text-muted small mb-3">La suppression du compte est définitive.</p>
                        <button type="button" class="btn-profile-danger" id="btn-open-delete">Supprimer mon compte</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modal-delete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Supprimer le compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Confirmez-vous la suppression définitive de votre compte ?</p>
            </div>
            <div class="modal-footer border-0">
                <form method="post" class="d-flex gap-2 w-100 justify-content-end">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="button" class="btn btn-profile-muted" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-profile-danger" style="width:auto;">Oui, supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var panelProfile = document.getElementById('panel-edit-profile');
    var panelPwd = document.getElementById('panel-password');
    var btnProfile = document.getElementById('btn-toggle-profile');
    var btnCancelProfile = document.getElementById('btn-cancel-profile');
    var btnPwd = document.getElementById('btn-toggle-password');
    var btnCancelPwd = document.getElementById('btn-cancel-password');

    function closeAll() {
        panelProfile.classList.remove('is-open');
        panelPwd.classList.remove('is-open');
    }

    if (btnProfile) {
        btnProfile.addEventListener('click', function () {
            closeAll();
            panelProfile.classList.toggle('is-open');
        });
    }
    if (btnCancelProfile) {
        btnCancelProfile.addEventListener('click', function () {
            panelProfile.classList.remove('is-open');
        });
    }
    if (btnPwd) {
        btnPwd.addEventListener('click', function () {
            closeAll();
            panelPwd.classList.toggle('is-open');
        });
    }
    if (btnCancelPwd) {
        btnCancelPwd.addEventListener('click', function () {
            panelPwd.classList.remove('is-open');
        });
    }

    var inputPhoto = document.getElementById('input-profile-photo');
    if (inputPhoto) {
        inputPhoto.addEventListener('change', function () {
            var f = inputPhoto.files && inputPhoto.files[0];
            var wrap = document.getElementById('photo-preview-wrap');
            var img = document.getElementById('photo-preview-img');
            if (!f || !f.type.match(/^image\//)) {
                if (wrap) wrap.style.display = 'none';
                return;
            }
            var r = new FileReader();
            r.onload = function (e) {
                img.src = e.target.result;
                wrap.style.display = 'block';
            };
            r.readAsDataURL(f);
        });
    }

    var delModal = document.getElementById('modal-delete');
    var btnOpenDel = document.getElementById('btn-open-delete');
    if (btnOpenDel && delModal && typeof bootstrap !== 'undefined') {
        var m = new bootstrap.Modal(delModal);
        btnOpenDel.addEventListener('click', function () { m.show(); });
    }
})();
</script>
</body>
</html>
