<?php
require_once 'Model/Database.php';

use Model\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    $deletedTokens = $db->exec("DELETE FROM user_tokens WHERE expires_at < NOW()");
    echo "✅ Tokens expirés supprimés : $deletedTokens\n";
    
    $deletedLogs = $db->exec("DELETE FROM login_logs WHERE login_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    echo "✅ Anciens logs supprimés : $deletedLogs\n";
    
    echo "\n✨ Nettoyage terminé !\n";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
