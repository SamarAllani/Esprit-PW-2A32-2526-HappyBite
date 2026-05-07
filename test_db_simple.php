<?php
echo "<h1>🔧 Test de connexion à la base happybite</h1>";

try {
    // Test de connexion simple
    $pdo = new PDO("mysql:host=localhost;dbname=happybite;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>✅ Connexion réussie à la base 'happybite'</p>";
    
    // Lister les utilisateurs
    $stmt = $pdo->query("SELECT id, email, role, statut FROM utilisateur");
    $users = $stmt->fetchAll();
    
    echo "<h2>📋 Utilisateurs dans la base :</h2>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
    
    foreach ($users as $user) {
        echo "<table>";
        echo " enpresak" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . $user['statut'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tester la connexion avec le compte admin
    echo "<h2>🔐 Test du compte admin :</h2>";
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => 'admin123@happybite.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color:green'>✅ Compte admin trouvé !</p>";
        
        // Tester le mot de passe
        if (password_verify('admin123', $admin['motDePasse'])) {
            echo "<p style='color:green'>✅ Mot de passe 'admin123' valide</p>";
        } else {
            echo "<p style='color:red'>❌ Mot de passe incorrect - besoin de réinitialisation</p>";
            
            // Réinitialiser le mot de passe
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE utilisateur SET motDePasse = :hash WHERE email = 'admin123@happybite.com'");
            $update->execute(['hash' => $newHash]);
            echo "<p style='color:green'>✅ Mot de passe réinitialisé à 'admin123'</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Compte admin non trouvé - création...</p>";
        
        // Créer le compte admin
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("
            INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut) 
            VALUES ('Admin', 'HappyBite', 'admin123@happybite.com', :hash, 'admin', 'actif')
        ");
        $insert->execute(['hash' => $hash]);
        echo "<p style='color:green'>✅ Compte admin créé avec mot de passe 'admin123'</p>";
    }
    
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
