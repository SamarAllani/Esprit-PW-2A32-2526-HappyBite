<?php
// forgot_password.php
require_once 'db_connection.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expires = ? WHERE id_utilisateur = ?");
        $stmt->execute([$token, $expires, $user['id_utilisateur']]);
        
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/reset_password.php?token=" . $token;
        $message = "Un lien de réinitialisation a été envoyé. (Démonstration: <a href='$resetLink'>$resetLink</a>)";
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Happy Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="View/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-illustration">
            <div class="illustration-icon">🔐</div>
            <h1>Mot de passe oublié ?</h1>
            <p>Nous vous aidons à le réinitialiser</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <h2>Réinitialisation</h2>
                <p>Entrez votre email pour recevoir un lien</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-group">
                        <span class="material-symbols-outlined">email</span>
                        <input type="email" name="email" required placeholder="votre@email.com">
                    </div>
                </div>
                <button type="submit" class="btn-login">Envoyer le lien</button>
                <div class="register-link">
                    <p><a href="login.php">Retour à la connexion</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>