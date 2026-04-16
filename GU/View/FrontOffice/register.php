<<?php
// register.php
require_once 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    header('Location: Profil_Utilisateur.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs obligatoires sont requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, motDePasse, telephone, adresse, role) VALUES (?, ?, ?, ?, ?, ?, 'client')");
            if ($stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $adresse])) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Happy Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="View/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-illustration">
            <div class="illustration-icon">🍽️</div>
            <h1>Rejoignez Happy Bite</h1>
            <p>Créez votre compte gratuitement</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <h2>Inscription</h2>
                <p>Créez votre compte Happy Bite</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="login.php">Connectez-vous</a></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></div>
                    <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label>Mot de passe *</label><input type="password" name="password" required></div>
                    <div class="form-group"><label>Confirmer *</label><input type="password" name="confirm_password" required></div>
                </div>
                <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"></div>
                <div class="form-group"><label>Adresse</label><textarea name="adresse" rows="2"><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea></div>
                <button type="submit" class="btn-login">S'inscrire</button>
                <div class="register-link"><p>Déjà un compte ? <a href="login.php">Se connecter</a></p></div>
            </form>
        </div>
    </div>
</body>
</html>