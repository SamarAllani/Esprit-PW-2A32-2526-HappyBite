<?php
// migrate_db.php
require_once 'db_connection.php';

try {
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) AFTER motDePasse");
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS reset_expires DATETIME NULL");
    echo "Migration terminée avec succès.";
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>