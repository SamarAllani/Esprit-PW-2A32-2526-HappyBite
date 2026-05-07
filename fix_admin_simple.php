<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=happybite;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Supprimer l'ancien admin
    $pdo->exec("DELETE FROM utilisateur WHERE email = 'admin123@happybite.com'");
    
    // Créer le nouvel admin
    $hash = password_hash('12345678', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut) 
        VALUES ('Admin', 'HappyBite', 'admin123@happybite.com', :hash, 'admin', 'actif')
    ");
    $stmt->execute(['hash' => $hash]);
    
    echo "<h1 style='color:green'>✅ Administrateur créé avec succès !</h1>";
    echo "<p><strong>📧 Email :</strong> admin123@happybite.com</p>";
    echo "<p><strong>🔑 Mot de passe :</strong> 12345678</p>";
    echo "<hr>";
    echo "<a href='View/FrontOffice/auth/login.php'>🔐 Aller à la connexion</a>";
    
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
