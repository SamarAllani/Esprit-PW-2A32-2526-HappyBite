<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifier les informations de connexion
    try {
        $stmt = $pdo->prepare("SELECT id, prenom, nom, motDePasse, statut FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['statut'] === 'actif' && password_verify($password, $user['motDePasse'])) {
            // Rediriger vers la page de profil utilisateur
            header("Location: Profil_Utilisateur.php?id=" . $user['id']);
            exit();
        } else {
            // Stocker le message d'erreur dans la session
            $_SESSION['error'] = "Email ou mot de passe incorrect.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur SQL : " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>