<?php
// Profil_Utilisateur.php
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    
    $stmt = $pdo->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, telephone = ?, adresse = ? WHERE id_utilisateur = ?");
    if ($stmt->execute([$nom, $prenom, $telephone, $adresse, $user_id])) {
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $message = "Profil mis à jour avec succès !";
        $user['nom'] = $nom;
        $user['prenom'] = $prenom;
        $user['telephone'] = $telephone;
        $user['adresse'] = $adresse;
    } else {
        $error = "Erreur lors de la mise à jour.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Happy Bite</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="View/css/Profil_Utilisateur.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Happy Bite</div>
            <div class="nav-links">
                <a href="#">Accueil</a>
                <a href="#">Commandes</a>
                <a href="#">Défis</a>
            </div>
            <div class="nav-icons">
                <span class="material-symbols-outlined icon">notifications</span>
                <span class="material-symbols-outlined icon">settings</span>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </nav>
    <div class="main-container">
        <div class="breadcrumb">
            <a href="#">Accueil</a>
            <span class="separator">/</span>
            <span class="current">Mon Profil</span>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="profile-grid">
            <div class="sidebar-card">
                <div class="profile-avatar">
                    <div class="avatar-image">
                        <span class="material-symbols-outlined">account_circle</span>
                    </div>
                    <h3><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h3>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <span class="profile-badge"><?= ucfirst($user['role']) ?></span>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="#" class="active"><span class="material-symbols-outlined">person</span> Mon profil</a></li>
                    <li><a href="#"><span class="material-symbols-outlined">kitchen</span> Mon frigo</a></li>
                    <li><a href="#"><span class="material-symbols-outlined">emoji_events</span> Mes défis</a></li>
                </ul>
            </div>
            <div class="main-content-card">
                <div class="card-header">
                    <h2><span class="material-symbols-outlined">person</span> Informations personnelles</h2>
                    <button class="edit-btn" onclick="toggleEdit()"><span class="material-symbols-outlined">edit</span> Modifier</button>
                </div>
                <div id="viewMode">
                    <div class="info-row"><span class="info-label">Nom complet</span><span class="info-value"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span></div>
                    <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
                    <div class="info-row"><span class="info-label">Téléphone</span><span class="info-value"><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></span></div>
                    <div class="info-row"><span class="info-label">Adresse</span><span class="info-value"><?= htmlspecialchars($user['adresse'] ?? 'Non renseignée') ?></span></div>
                </div>
                <div id="editMode" style="display:none;">
                    <form method="POST">
                        <div class="form-group"><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required></div>
                        <div class="form-group"><label>Prénom</label><input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required></div>
                        <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>"></div>
                        <div class="form-group"><label>Adresse</label><textarea name="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea></div>
                        <div class="form-actions"><button type="button" class="btn-secondary" onclick="toggleEdit()">Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
                    </form>
                </div>
            </div>
            <div class="stats-card">
                <h3><span class="material-symbols-outlined">insights</span> Mes statistiques</h3>
                <div class="stat-item"><span class="stat-label">Défis complétés</span><span class="stat-number">3</span></div>
                <div class="stat-item"><span class="stat-label">Points gagnés</span><span class="stat-number">430</span></div>
                <div class="stat-item"><span class="stat-label">Recettes postées</span><span class="stat-number">5</span></div>
            </div>
        </div>
    </div>
    <script>
        function toggleEdit() {
            const view = document.getElementById('viewMode');
            const edit = document.getElementById('editMode');
            view.style.display = view.style.display === 'none' ? 'block' : 'none';
            edit.style.display = edit.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>