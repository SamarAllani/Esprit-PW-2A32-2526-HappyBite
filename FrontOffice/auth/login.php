<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../Home.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$pendingFaceEmail = $_SESSION['just_registered_email'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth-layout.css">
</head>
<body class="auth-page">
<div class="auth-split">
    <aside class="auth-brand" aria-hidden="true">
        <div class="auth-brand__bg"></div>
        <div class="auth-brand__inner">
            <h1>Bienvenue sur HappyBite</h1>
            <p>
                Connectez-vous pour accéder aux produits, aux recettes et à la communauté.
                Gérez votre panier et suivez vos habitudes alimentaires en un seul endroit.
            </p>
        </div>
    </aside>
    <main class="auth-panel">
        <div class="auth-card">
            <h2 class="auth-card__title">Connexion</h2>

            <?php if ($error !== ''): ?>
                <div class="auth-alert auth-alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="auth-alert auth-alert--success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($pendingFaceEmail !== ''): ?>
                <div class="auth-banner">
                    Enregistrez votre visage pour <strong><?php echo htmlspecialchars($pendingFaceEmail); ?></strong>
                    afin de vous connecter plus tard avec Face ID (caméra, comme sur la commande).
                </div>
                <button type="button" class="auth-btn-faceid" id="face-enroll-pending" data-email="<?php echo htmlspecialchars($pendingFaceEmail, ENT_QUOTES, 'UTF-8'); ?>">
                    <img src="../images/face-id.png" alt="" class="auth-btn-faceid__icon" width="20" height="20">
                    <span class="auth-btn-faceid__label">Enregistrer Face ID maintenant</span>
                </button>
            <?php endif; ?>

            <form method="POST" action="../../Controllers/AuthProcess.php">
                <input type="hidden" name="action" value="login">
                <div class="auth-field">
                    <label for="email-pwd">Adresse email</label>
                    <input type="email" name="email" required id="email-pwd" autocomplete="username" placeholder="vous@exemple.com">
                </div>
                <div class="auth-field">
                    <label for="pwd">Mot de passe</label>
                    <input type="password" name="password" required id="pwd" autocomplete="current-password" placeholder="Votre mot de passe">
                </div>
                <button type="submit" class="auth-btn-primary">Se connecter</button>
            </form>

            <div class="auth-divider">
                <p>Connexion avec Face ID (optionnel)</p>
                <div class="auth-field">
                    <label for="email-faceid">Email pour Face ID</label>
                    <input type="email" id="email-faceid" placeholder="vous@exemple.com" autocomplete="username">
                </div>
                <button type="button" class="auth-btn-faceid" id="face-login">
                    <img src="../images/face-id.png" alt="" class="auth-btn-faceid__icon" width="20" height="20">
                    <span class="auth-btn-faceid__label">Se connecter avec Face ID</span>
                </button>
                <p class="auth-hint">Si Face ID ne fonctionne pas, utilisez votre mot de passe ci-dessus.</p>
            </div>

            <p class="auth-footer-links">Pas de compte ? <a href="register.php">Créer un compte</a></p>
            <p class="auth-back"><a href="../Home.php">Retour au site</a></p>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../includes/face_scan_modal.php'; ?>
<script src="../js/auth-face.js"></script>
<script>
    (function () {
        var savedEmail = localStorage.getItem('happybite_faceid_email');
        if (savedEmail) {
            var ef = document.getElementById('email-faceid');
            var ep = document.getElementById('email-pwd');
            if (ef) ef.value = savedEmail;
            if (ep && !ep.value) ep.value = savedEmail;
        }
        document.querySelector('form').addEventListener('submit', function () {
            var email = document.getElementById('email-pwd').value;
            if (email) localStorage.setItem('happybite_faceid_email', email);
        });
        document.getElementById('face-login').addEventListener('click', function (e) {
            e.preventDefault();
            var email = document.getElementById('email-faceid').value.trim();
            if (!email) {
                alert('Entrez votre email pour Face ID');
                return;
            }
            localStorage.setItem('happybite_faceid_email', email);
            if (window.HappyBiteAuthFace) {
                HappyBiteAuthFace.runLogin(function () {
                    return document.getElementById('email-faceid').value.trim();
                });
            }
        });
        var enrollBtn = document.getElementById('face-enroll-pending');
        if (enrollBtn && window.HappyBiteAuthFace) {
            enrollBtn.addEventListener('click', function (ev) {
                ev.preventDefault();
                var em = (enrollBtn.getAttribute('data-email') || '').trim();
                if (!em) {
                    return;
                }
                HappyBiteAuthFace.runEnroll(function () {
                    return em;
                }, function (ok, data) {
                    if (ok) {
                        alert('Visage enregistré. Vous pouvez vous connecter avec Face ID.');
                        enrollBtn.closest('.auth-card').querySelector('.auth-banner').style.display = 'none';
                        enrollBtn.style.display = 'none';
                    } else if (data && data.error) {
                        alert(data.error);
                    }
                });
            });
        }
    })();
</script>
</body>
</html>
