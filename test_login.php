<?php
require_once 'View/FrontOffice/db/db_connection.php';

echo "<h1>🔧 Test de connexion à la base de données</h1>";

try {
    // Tester la connexion
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateur");
    $count = $stmt->fetch();
    echo "<p style='color:green'>✅ Connexion réussie ! " . $count['total'] . " utilisateurs trouvés</p>";
    
    // Lister les utilisateurs
    $stmt = $pdo->query("SELECT id, email, role, statut FROM utilisateur");
    echo "<h2>📋 Utilisateurs existants :</h2>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
    while ($user = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['statut'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tester si l'admin existe (admin123@happybite.com)
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => 'admin123@happybite.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color:green'>✅ Admin trouvé !</p>";
        
        // Tester le mot de passe 12345678
        if (password_verify('12345678', $admin['motDePasse'])) {
            echo "<p style='color:green'>✅ Mot de passe '12345678' valide</p>";
        } else {
            echo "<p style='color:red'>❌ Mot de passe incorrect</p>";
            
            // Réinitialiser le mot de passe
            $newHash = password_hash('12345678', PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE utilisateur SET motDePasse = :hash WHERE email = 'admin123@happybite.com'");
            $update->execute(['hash' => $newHash]);
            echo "<p style='color:green'>✅ Mot de passe réinitialisé à '12345678'</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Admin non trouvé - création...</p>";
        
        // Créer le compte admin
        $hash = password_hash('12345678', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("
            INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut) 
            VALUES ('Admin', 'HappyBite', 'admin123@happybite.com', :hash, 'admin', 'actif')
        ");
        $insert->execute(['hash' => $hash]);
        echo "<p style='color:green'>✅ Compte admin créé avec succès !</p>";
        echo "<p>📧 Email: admin123@happybite.com</p>";
        echo "<p>🔑 Mot de passe: 12345678</p>";
    }
    
    echo "<hr>";
    echo "<a href='View/FrontOffice/auth/login.php' style='background: #006e1c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Aller à la connexion</a>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>MySQL est démarré dans XAMPP</li>";
    echo "<li>La base 'happybite' existe</li>";
    echo "<li>Les identifiants sont root / (vide)</li>";
    echo "</ul>";
}
?>
