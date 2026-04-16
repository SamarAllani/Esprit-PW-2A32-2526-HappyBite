<!DOCTYPE html>
<html lang="fr" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBite - Connexion</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#006e1c",
                        "primary-container": "#4caf50",
                        "secondary-container": "#ff9800",
                        "surface": "#f9f9f9",
                        "on-surface": "#1a1c1c",
                        "surface-container-low": "#f3f3f3",
                        "outline": "#6f7a6b",
                        "error": "#ba1a1a"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface font-body text-on-surface">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black text-primary italic font-headline">GreenBite</h1>
            </div>

            <!-- Login Card -->
            <div class="bg-surface-container-low p-8 rounded-xl shadow-md">
                <h2 class="text-2xl font-bold text-on-surface mb-6 text-center font-headline">Connexion</h2>
                
                <?php 
                session_start();
                if (isset($_SESSION['error'])): 
                ?>
                    <div class="mb-4 p-4 bg-error/10 border border-error rounded-lg">
                        <p class="text-error font-semibold"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form action="login_process.php" method="POST" class="space-y-4">
                    <div class="form-group">
                        <label for="email" class="block text-sm font-semibold text-on-surface mb-2">Email :</label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-4 py-2 border border-outline rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="block text-sm font-semibold text-on-surface mb-2">Mot de passe :</label>
                        <input type="password" id="password" name="password" required 
                               class="w-full px-4 py-2 border border-outline rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white font-bold py-2 rounded-lg hover:bg-opacity-90 transition-all duration-200">
                        Se connecter
                    </button>
                </form>

                <!-- Forgot Password Link -->
                <div class="mt-4 text-center">
                    <a href="forgot_password.php" class="text-primary hover:underline text-sm font-semibold">
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Divider -->
                <div class="my-6 text-center text-outline text-sm">
                    ou
                </div>

                <!-- Sign Up Link -->
                <a href="register.php" class="block w-full bg-primary-container text-white font-bold py-2 rounded-lg text-center hover:bg-opacity-90 transition-all duration-200">
                    Créer un compte
                </a>
            </div>
        </div>
    </div>
</body>
</html>