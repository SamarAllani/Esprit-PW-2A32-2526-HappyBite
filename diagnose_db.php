<?php
/**
 * Fichier: diagnose_db.php
 * Script de diagnostic de la connexion à la base de données
 */

echo "<h1>🔍 Diagnostic Connexion Base de Données</h1>";
echo "<hr>";

// Test 1: PDO disponible
echo "<h2>Test 1: Vérification de PDO</h2>";
if (extension_loaded('pdo')) {
    echo "<p style='color:green'>✅ Extension PDO disponible</p>";
} else {
    echo "<p style='color:red'>❌ Extension PDO non disponible</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p style='color:green'>✅ Pilote PDO MySQL disponible</p>";
} else {
    echo "<p style='color:red'>❌ Pilote PDO MySQL non disponible</p>";
}

echo "<hr>";

// Test 2: Connexion directe avec PDO
echo "<h2>Test 2: Connexion directe à la base</h2>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=happybite;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>✅ Connexion PDO réussie !</p>";
    
    // Vérifier les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Tables trouvées:</strong> " . implode(", ", $tables) . "</p>";
    
    // Vérifier les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateur");
    $result = $stmt->fetch();
    echo "<p style='color:green'>✅ Utilisateurs trouvés: " . $result['total'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erreur PDO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
}

echo "<hr>";

// Test 3: Charger les modèles
echo "<h2>Test 3: Chargement des modèles</h2>";
try {
    require_once 'Model/Database.php';
    require_once 'Model/User.php';
    require_once 'Model/SessionManager.php';
    require_once 'Model/CookieManager.php';
    
    // Utiliser les noms complets avec namespaces
    $db = \Model\Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color:green'>✅ Database singleton fonctionnel</p>";
    
    // Test User model
    $userModel = new \Model\User();
    $admin = $userModel->findByEmail('admin2026@happybite.com');
    
    if ($admin) {
        echo "<p style='color:green'>✅ User model fonctionnel</p>";
        echo "<p>Admin trouvé: " . htmlspecialchars($admin['email']) . "</p>";
        
        // Test password verification
        if ($userModel->verifyPassword('admin123', $admin['motDePasse'])) {
            echo "<p style='color:green'>✅ Vérification du mot de passe réussie</p>";
        } else {
            echo "<p style='color:red'>❌ Le mot de passe 'admin123' ne correspond pas</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Utilisateur admin non trouvé</p>";
    }
    
    echo "<p style='color:green'>✅ Modèles chargés avec succès</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur lors du test des modèles:</p>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";

// Test 4: Vérifier les permissions MySQL
echo "<h2>Test 4: Informations MySQL</h2>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        "root",
        ""
    );
    
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p><strong>Version MySQL:</strong> " . htmlspecialchars($result['version']) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Test 5: Créer un lien de test de login
echo "<h2>Test 5: Lien de Test</h2>";
echo "<ul>";
echo "<li><a href='View/FrontOffice/auth/login.php' target='_blank'>Aller à la page de connexion</a></li>";
echo "<li><a href='test_complete.php' target='_blank'>Test complet du système</a></li>";
echo "</ul>";

?>
