<?php
require '../FrontOffice/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = isset($_POST['password']) && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if (empty($prenom) || empty($nom) || empty($email)) {
        $_SESSION['error'] = "Le prenom, le nom et l'email sont obligatoires.";
        header("Location: edit_user.php?id=$user_id");
        exit;
    }

    try {
        if ($password) {
            $stmt = $pdo->prepare("UPDATE utilisateur SET prenom = ?, nom = ?, email = ?, motDePasse = ? WHERE id = ?");
            $stmt->execute([$prenom, $nom, $email, $password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateur SET prenom = ?, nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$prenom, $nom, $email, $user_id]);
        }
        $_SESSION['success'] = "Utilisateur modifié avec succès.";
        header("Location: admin.php?success=edited");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
        header("Location: edit_user.php?id=$user_id");
        exit;
    }
} else {
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($user_id === 0) {
        die("ID utilisateur invalide.");
    }

    try {
        $stmt = $pdo->prepare("SELECT id, prenom, nom, email FROM utilisateur WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("Utilisateur non trouvé.");
        }
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Modifier Utilisateur - GreenBite Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-error": "#ffffff",
                        "surface-tint": "#006e1c",
                        "primary": "#006e1c",
                        "primary-container": "#4caf50",
                        "error": "#ba1a1a",
                        "surface": "#f9f9f9",
                        "on-surface": "#1a1c1c",
                        "outline": "#6f7a6b"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-surface text-on-surface">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-on-surface mb-8 text-center">Modifier Utilisateur</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Prenom</label>
                <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required class="w-full px-4 py-2 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Nom</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required class="w-full px-4 py-2 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-4 py-2 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Nouveau mot de passe (optionnel)</label>
                <input type="password" name="password" placeholder="Laisser vide pour ne pas changer" class="w-full px-4 py-2 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <div class="flex gap-2 pt-4">
                <button type="submit" class="flex-1 bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary/90 transition">
                    Enregistrer
                </button>
                <a href="admin.php" class="flex-1 bg-outline/20 text-on-surface font-bold py-2 px-4 rounded-lg hover:bg-outline/30 transition text-center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
