<?php
// forgot_password_process.php
require_once 'db_connection.php';

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
        $_SESSION['message'] = "Lien de réinitialisation: " . $resetLink;
    } else {
        $_SESSION['error'] = "Aucun compte trouvé avec cet email.";
    }
    
    header('Location: forgot_password.php');
    exit();
} else {
    header('Location: forgot_password.php');
    exit();
}
?>