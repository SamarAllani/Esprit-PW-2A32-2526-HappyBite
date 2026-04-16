<?php
// reset_password.php
require_once 'db_connection.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "Lien invalide ou expiré.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateur SET motDePasse = ?, reset_token = NULL, reset_expires = NULL WHERE id_utilisateur = ?");
        if ($stmt->execute([$hashedPassword, $user['id_utilisateur']])) {
            $success = "Mot de passe réinitialisé avec succès !";
        } else {
            $error = "Erreur lors de la réinitialisation.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - Happy Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="View/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-illustration">
            <div class="illustration-icon">🔑</div>
            <h1>Nouveau mot de passe</h1>
            <p>Choisissez un mot de passe sécurisé</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <h2>Réinitialisation</h2>
                <p>Entrez votre nouveau mot de passe</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="login.php">Connectez-vous</a></div>
            <?php endif; ?>
            <?php if (!$error && !$success): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="material-symbols-outlined">lock</span>
                            <input type="password" name="password" required placeholder="Au moins 6 caractères">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <div class="input-group">
                            <span class="material-symbols-outlined">lock</span>
                            <input type="password" name="confirm_password" required placeholder="Confirmez">
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Réinitialiser</button>
                </form>
            <?php endif; ?>
            <div class="register-link">
                <p><a href="login.php">Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</body>
</html>