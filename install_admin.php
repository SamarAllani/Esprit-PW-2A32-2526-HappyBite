<?php
require_once 'Model/Database.php';
require_once 'Model/User.php';

use Model\Database;
use Model\User;

try {
    $db = Database::getInstance()->getConnection();
    $userModel = new User();
    
    echo "<h1>🔧 Installation de l'administrateur unique</h1>";
    
    // Vérifier si la table existe
    $tableExists = $db->query("SHOW TABLES LIKE 'utilisateur'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p>⚠️ Création de la table utilisateur...</p>";
        $db->exec("
            CREATE TABLE utilisateur (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prenom VARCHAR(100) NOT NULL,
                nom VARCHAR(100) NOT NULL,
                email VARCHAR(120) NOT NULL UNIQUE,
                motDePasse VARCHAR(255) NOT NULL,
                role ENUM('admin', 'client', 'nutritionniste', 'fournisseur') DEFAULT 'client',
                statut ENUM('actif', 'bloqué', 'inactif') DEFAULT 'actif',
                poid DECIMAL(5,2) DEFAULT 0,
                objectif VARCHAR(50) DEFAULT 'maintenir',
                allergie TEXT,
                carence TEXT,
                budget DECIMAL(10,2) DEFAULT 0,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<p style='color: green;'>✅ Table créée</p>";
    }
    
    // Supprimer tous les anciens admins
    $db->exec("DELETE FROM utilisateur WHERE role = 'admin'");
    
    // Créer l'admin unique
    $email = 'admin123@happybite.com';
    $password = '12345678';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO utilisateur (prenom, nom, email, motDePasse, role, statut, objectif, description) 
        VALUES (:prenom, :nom, :email, :motDePasse, 'admin', 'actif', 'administrer', 'Administrateur principal')
    ");
    
    $stmt->execute([
        'prenom' => 'Super',
        'nom' => 'Admin',
        'email' => $email,
        'motDePasse' => $hashedPassword
    ]);
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ Administrateur unique créé avec succès !</h3>";
    echo "<p><strong>📧 Email :</strong> <span style='color: #006e1c; font-weight: bold;'>admin123@happybite.com</span></p>";
    echo "<p><strong>🔑 Mot de passe :</strong> <span style='color: #006e1c; font-weight: bold;'>12345678</span></p>";
    echo "<p><strong>⚠️ Note :</strong> Seul cet administrateur peut accéder au BackOffice.</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<a href='View/BackOffice/admin.php' style='background: #006e1c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Accéder au BackOffice</a>";
    echo "<a href='View/FrontOffice/auth/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🔐 Page de connexion</a>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px;'>";
    echo "<h3 style='color: #721c24;'>❌ Erreur</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>