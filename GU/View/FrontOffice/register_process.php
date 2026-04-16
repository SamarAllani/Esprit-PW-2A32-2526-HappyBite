<?php
// register_process.php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Tous les champs obligatoires sont requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email invalide.";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Cet email est déjà utilisé.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, motDePasse, telephone, adresse, role) VALUES (?, ?, ?, ?, ?, ?, 'client')");
            if ($stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $adresse])) {
                $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription.";
            }
        }
    }
    
    header('Location: register.php');
    exit();
} else {
    header('Location: register.php');
    exit();
}
?>