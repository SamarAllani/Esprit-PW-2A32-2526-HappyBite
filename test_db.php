<?php
echo "<h1>🔧 TEST DE CONNEXION</h1>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=happybite;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Connexion réussie !</p>";
    
    $stmt = $pdo->query("SELECT id, email, role, statut FROM utilisateur");
    echo "<h2>📋 Utilisateurs :</h2><table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
    while ($user = $stmt->fetch()) {
        echo "<tr><td>{$user['id']}</td><td>{$user['email']}</td><td>{$user['role']}</td><td>{$user['statut']}</td></tr>";
    }
    echo "</table>";
    echo "<hr><a href='View/FrontOffice/auth/login.php'>🔐 Aller à la connexion</a>";
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
