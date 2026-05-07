<?php
/**
 * Fichier: reset_admin_password.php
 * Script pour réinitialiser le mot de passe admin
 */

echo "<h1>🔑 Réinitialisation du mot de passe Admin</h1>";
echo "<hr>";

try {
    // Connexion directe
    $pdo = new PDO(
        "mysql:host=localhost;dbname=happybite;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Générer le hash pour 'admin123'
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "<p><strong>Nouveau mot de passe:</strong> admin123</p>";
    echo "<p><strong>Hash généré:</strong> " . htmlspecialchars($hashedPassword) . "</p>";
    echo "<hr>";
    
    // Mettre à jour le mot de passe
    $stmt = $pdo->prepare("UPDATE utilisateur SET motDePasse = :hash WHERE email = 'admin2026@happybite.com'");
    $stmt->execute(['hash' => $hashedPassword]);
    
    echo "<p style='color:green'>✅ Mot de passe admin réinitialisé !</p>";
    echo "<p><strong>Utilisateur:</strong> admin2026@happybite.com</p>";
    echo "<p><strong>Nouveau mot de passe:</strong> admin123</p>";
    
    // Vérifier la mise à jour
    $stmt = $pdo->prepare("SELECT email, motDePasse FROM utilisateur WHERE email = 'admin2026@happybite.com'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    // Tester la vérification du mot de passe
    if (password_verify('admin123', $user['motDePasse'])) {
        echo "<p style='color:green'>✅ Vérification du mot de passe réussie !</p>";
    } else {
        echo "<p style='color:red'>❌ Erreur lors de la vérification</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='View/FrontOffice/auth/login.php' style='background: #006e1c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>🔐 Allez à la connexion</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
