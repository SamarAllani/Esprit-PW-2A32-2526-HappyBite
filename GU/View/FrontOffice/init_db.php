<?php
require 'db_connection.php';

try {
    // D'abord, vérifier si la table users existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        // Créer la table users
        $pdo->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                profile_picture VARCHAR(255),
                reset_token VARCHAR(255),
                token_expiry DATETIME,
                status ENUM('active', 'blocked') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Table 'users' créée avec succès.";
    } else {
        // Vérifier et ajouter les colonnes manquantes
        $result = $pdo->query("SHOW COLUMNS FROM users");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        
        if (!in_array('status', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'blocked') DEFAULT 'active'");
            echo "✓ Colonne 'status' ajoutée à la table 'users'.";
        } else {
            echo "✓ Table 'users' est à jour.";
        }
        
        if (!in_array('reset_token', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255)");
            echo "<br>✓ Colonne 'reset_token' ajoutée à la table 'users'.";
        }
        
        if (!in_array('token_expiry', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN token_expiry DATETIME");
            echo "<br>✓ Colonne 'token_expiry' ajoutée à la table 'users'.";
        }
    }
    
    echo "<br><br><a href='../BackOffice/admin.php' class='text-blue-500 underline'>Retour au Admin</a>";
} catch (PDOException $e) {
    echo "✗ Erreur : " . $e->getMessage();
}
?>
