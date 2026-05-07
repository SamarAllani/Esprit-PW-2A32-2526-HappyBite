<?php
/**
 * Fichier: index.php
 * Page d'accueil du projet HappyBite
 */

// Vérifier si l'utilisateur est connecté
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userRole = $_SESSION['user_role'] ?? null;

// Redirection automatique selon le rôle
if ($isLoggedIn) {
    if ($userRole === 'admin') {
        header("Location: View/BackOffice/admin.php");
        exit();
    } else {
        header("Location: View/FrontOffice/user/Profil_Utilisateur.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite - Accueil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #006e1c 0%, #4caf50 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            color: #006e1c;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        
        .tagline {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .logo {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .welcome-message {
            color: #333;
            margin: 2rem 0;
            font-size: 1.1rem;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #006e1c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #004d12;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 110, 28, 0.3);
        }
        
        .btn-secondary {
            background: #e8f5e9;
            color: #006e1c;
            border: 2px solid #006e1c;
        }
        
        .btn-secondary:hover {
            background: #c8e6c9;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        
        .user-info {
            background: #e8f5e9;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin: 2rem 0;
            border-left: 4px solid #006e1c;
        }
        
        .user-info p {
            color: #333;
            margin: 0.5rem 0;
        }
        
        .navigation {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .nav-item {
            color: #006e1c;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-item:hover {
            color: #004d12;
            text-decoration: underline;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        .feature {
            text-align: center;
        }
        
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-title {
            color: #006e1c;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🥗</div>
        <h1>HappyBite</h1>
        <p class="tagline">Votre plateforme de nutrition et bien-être</p>
        
        <div class="welcome-message">
            <p>Rejoignez-nous pour un mode de vie plus sain !</p>
        </div>
            
            <div class="buttons">
                <a href="View/FrontOffice/auth/login.php" class="btn btn-primary">🔐 Se connecter</a>
                <a href="View/FrontOffice/auth/register.php" class="btn btn-secondary">📝 S'inscrire</a>
            </div>
            
            <div class="navigation">
                <a href="test_complete.php" class="nav-item">🧪 Test du système</a>
            </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">💪</div>
                <div class="feature-title">Fitness</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🥗</div>
                <div class="feature-title">Nutrition</div>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Suivi</div>
            </div>
            <div class="feature">
                <div class="feature-icon">👨‍⚕️</div>
                <div class="feature-title">Experts</div>
            </div>
        </div>
    </div>
</body>
</html>
