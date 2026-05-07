<?php
/**
 * Fichier: test_complete.php
 * Test complet du système d'authentification
 */

echo "<h1>🧪 Test Complet du Système d'Authentification HappyBite</h1>";
echo "<hr>";

// Test 1: Connexion à la base de données
echo "<h2>Test 1: Connexion à la base de données</h2>";
try {
    require_once 'View/FrontOffice/db/db_connection.php';
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p style='color:green'>✅ Connexion réussie !</p>";
    echo "<p><strong>Tables trouvées:</strong> " . implode(", ", $tables) . "</p>";
    
    // Vérifier les utilisateurs
    $stmt = $pdo->query("SELECT id, email, role FROM utilisateur LIMIT 5");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<p style='color:green'>✅ Utilisateurs trouvés: " . count($users) . "</p>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>" . htmlspecialchars($user['email']) . " (" . $user['role'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'>⚠️ Aucun utilisateur trouvé</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 2: Vérifier les fichiers PHP requis
echo "<h2>Test 2: Vérification des fichiers requis</h2>";
$files = [
    'Controller/auth_controller.php' => 'Contrôleur d\'authentification',
    'Model/Database.php' => 'Modèle Database',
    'Model/User.php' => 'Modèle User',
    'Model/SessionManager.php' => 'Gestionnaire de sessions',
    'Model/CookieManager.php' => 'Gestionnaire de cookies',
    'View/FrontOffice/auth/login.php' => 'Page de connexion',
    'View/FrontOffice/auth/register.php' => 'Page d\'inscription',
    'View/FrontOffice/auth/login_process.php' => 'Traitement de la connexion',
    'View/FrontOffice/auth/register_process.php' => 'Traitement de l\'inscription',
    'View/FrontOffice/auth/logout.php' => 'Page de déconnexion'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color:green'>✅ $description ($file)</p>";
    } else {
        echo "<p style='color:red'>❌ Manquant: $description ($file)</p>";
    }
}

echo "<hr>";

// Test 3: Vérifier les classes PHP
echo "<h2>Test 3: Vérification des classes PHP</h2>";
try {
    require_once 'Model/Database.php';
    require_once 'Model/User.php';
    require_once 'Model/SessionManager.php';
    require_once 'Model/CookieManager.php';
    
    use Model\Database;
    use Model\User;
    use Model\SessionManager;
    use Model\CookieManager;
    
    echo "<p style='color:green'>✅ Toutes les classes PHP chargées avec succès</p>";
    
    // Tester la récupération des utilisateurs
    $userModel = new User();
    $testUser = $userModel->findByEmail('admin2026@happybite.com');
    
    if ($testUser) {
        echo "<p style='color:green'>✅ Utilisateur admin trouvé: " . htmlspecialchars($testUser['email']) . "</p>";
        
        // Tester la vérification du mot de passe
        if ($userModel->verifyPassword('admin123', $testUser['motDePasse'])) {
            echo "<p style='color:green'>✅ Vérification du mot de passe réussie pour admin123</p>";
        } else {
            echo "<p style='color:red'>❌ Mot de passe incorrect ou hash invalide</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Utilisateur admin non trouvé</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur lors du test des classes: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Test 4: Liens de test
echo "<h2>Liens de Test</h2>";
echo "<ul>";
echo "<li><a href='View/FrontOffice/auth/login.php' target='_blank'>Accéder à la page de connexion</a></li>";
echo "<li><a href='View/FrontOffice/auth/register.php' target='_blank'>Accéder à la page d'inscription</a></li>";
echo "<li><a href='test_login.php' target='_blank'>Test de connexion (ancien)</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p>Test terminé. Vérifiez les résultats ci-dessus.</p>";
?>
