<?php
// reset_password_process.php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($token)) {
        $_SESSION['error'] = "Token invalide.";
        header('Location: forgot_password.php');
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "Lien invalide ou expiré.";
        header('Location: forgot_password.php');
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateur SET motDePasse = ?, reset_token = NULL, reset_expires = NULL WHERE id_utilisateur = ?");
        if ($stmt->execute([$hashedPassword, $user['id_utilisateur']])) {
            $_SESSION['success'] = "Mot de passe réinitialisé avec succès !";
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la réinitialisation.";
        }
    }
    
    header('Location: reset_password.php?token=' . $token);
    exit();
} else {
    header('Location: forgot_password.php');
    exit();
}
?>