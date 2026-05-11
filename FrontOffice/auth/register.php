<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = $_SESSION['errors'] ?? [];
$error = $_SESSION['error'] ?? '';
$pendingFaceEmail = $_SESSION['just_registered_email'] ?? '';
unset($_SESSION['errors'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Inscription</title>
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
                Créez votre compte pour profiter des recettes, commander des produits adaptés
                et rejoindre la communauté. Votre espace personnel vous attend.
            </p>
        </div>
    </aside>
    <main class="auth-panel">
        <div class="auth-card auth-card--scroll">
            <h2 class="auth-card__title">Inscription</h2>

            <?php if ($error !== ''): ?>
                <div class="auth-alert auth-alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="auth-alert auth-alert--list">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars((string) $err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="../../Controllers/AuthProcess.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="register">

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" required placeholder="Prénom" autocomplete="given-name">
                    </div>
                    <div class="auth-field">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" required placeholder="Nom" autocomplete="family-name">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email-register">Adresse email</label>
                    <input type="email" name="email" required id="email-register" autocomplete="username" placeholder="vous@exemple.com">
                </div>

                <div class="auth-field">
                    <label for="photo-preview-file">Photo de profil (optionnel)</label>
                    <p style="margin:0 0 0.35rem;font-size:0.8rem;color:var(--auth-muted,#6b7280);">Enregistrée sous <code>uploads/users&nbsp;pictures/</code> et le chemin est stocké en base (<code>profil-image</code>).</p>
                    <input type="file" name="profile_photo" accept="image/*" id="photo-preview-file">
                    <div id="photo-preview-container" style="margin-top:0.5rem;display:none;">
                        <img id="photo-preview" alt="Aperçu photo" width="120" height="120" style="object-fit:cover;border-radius:50%;border:2px solid var(--auth-border);">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="Au moins 6 caractères">
                </div>

                <div class="auth-field">
                    <label for="role">Rôle</label>
                    <select name="role" id="role" required>
                        <option value="client">Client</option>
                        <option value="nutritionniste">Nutritionniste</option>
                        <option value="fournisseur">Fournisseur</option>
                    </select>
                </div>

                <div class="auth-field">
                    <label for="referral_code">Code de parrainage</label>
                    <input type="text" name="referral_code" id="referral_code" placeholder="Optionnel">
                </div>

                <div class="auth-field">
                    <label for="budget">Budget (€)</label>
                    <input type="number" name="budget" id="budget" step="50" placeholder="Optionnel">
                </div>

                <div class="auth-field">
                    <label for="description">Description</label>
                    <input type="text" name="description" id="description" placeholder="Quelques mots sur vous (optionnel)">
                </div>

                <button type="submit" class="auth-btn-primary">S'inscrire</button>
            </form>

            <?php if ($pendingFaceEmail !== ''): ?>
                <div class="auth-divider">
                    <p>Face ID</p>
                    <div class="auth-banner">
                        Compte lié à <strong><?php echo htmlspecialchars($pendingFaceEmail); ?></strong> — enregistrez votre visage (caméra) pour vous connecter sans mot de passe ensuite.
                    </div>
                    <button type="button" class="auth-btn-faceid" id="face-enroll-pending-reg" data-email="<?php echo htmlspecialchars($pendingFaceEmail, ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="../images/face-id.png" alt="" class="auth-btn-faceid__icon" width="20" height="20">
                        <span class="auth-btn-faceid__label">Enregistrer Face ID</span>
                    </button>
                </div>
            <?php else: ?>
                <p class="auth-hint" style="margin-top:1rem;text-align:center;">
                    Après inscription, sur la page de connexion vous pourrez enregistrer Face ID (caméra), comme pour valider une commande.
                </p>
            <?php endif; ?>

            <p class="auth-footer-links">Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p class="auth-back"><a href="../Home.php">Retour au site</a></p>
        </div>
    </main>
</div>
<?php require __DIR__ . '/../includes/face_scan_modal.php'; ?>
<script src="../js/auth-face.js"></script>
<script>
    document.getElementById('photo-preview-file').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (file && file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function (evt) {
                document.getElementById('photo-preview').src = evt.target.result;
                document.getElementById('photo-preview-container').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    var savedEmail = localStorage.getItem('happybite_faceid_email');
    if (savedEmail) {
        var er = document.getElementById('email-register');
        if (er && !er.value) er.value = savedEmail;
    }
    document.querySelector('form').addEventListener('submit', function () {
        var email = document.getElementById('email-register').value;
        if (email) localStorage.setItem('happybite_faceid_email', email);
    });
    var enrollReg = document.getElementById('face-enroll-pending-reg');
    if (enrollReg && window.HappyBiteAuthFace) {
        enrollReg.addEventListener('click', function (ev) {
            ev.preventDefault();
            var em = (enrollReg.getAttribute('data-email') || '').trim();
            if (!em) return;
            HappyBiteAuthFace.runEnroll(function () { return em; }, function (ok, data) {
                if (ok) {
                    alert('Visage enregistré. Vous pouvez aller sur la page de connexion et utiliser Face ID.');
                    window.location.href = 'login.php';
                } else if (data && data.error) {
                    alert(data.error);
                }
            });
        });
    }
</script>
<footer class="hb-site-footer">© 2026 HappyBite</footer>
</body>
</html>
