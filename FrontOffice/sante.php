<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId = $loggedIn ? (int) ($_SESSION['user_id'] ?? 0) : 0;

$profilSnippet = null;
if ($loggedIn && $userId > 0) {
    require_once __DIR__ . '/../config/Database.php';
    try {
        $db = Database::getConnection();
        $st = $db->prepare('SELECT objectif, poids_actuel, taille FROM profil_sante WHERE id_utilisateur = :id LIMIT 1');
        $st->execute(['id' => $userId]);
        $profilSnippet = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $profilSnippet = null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBite — Espace santé</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-original-views.css">
</head>
<body>

<?php
$nav_active = 'sante';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="commande-wrap" style="max-width:720px;margin:0 auto;padding:2rem 1.25rem 4rem;">
    <h1 style="font-family:var(--hb-font-main,Poppins,sans-serif);font-size:1.85rem;color:#2C7E34;margin-bottom:0.5rem;font-weight:700;text-align:center;">Espace santé</h1>
    <p style="color:#6b7280;text-align:center;margin-bottom:1.5rem;font-size:0.95rem;">Suivi complet du profil et des habitudes quotidiennes.</p>

    <?php if ($loggedIn): ?>
        <p style="color:#3d5248;line-height:1.6;margin-bottom:1.25rem;text-align:center;">
            Ouvrez votre <a href="user_health_space.php" style="color:#2C7E34;font-weight:400;">tableau de bord santé</a>
            pour créer ou modifier votre profil et vos suivis journaliers.
            Retrouvez aussi les <a href="List-Recette.php">recettes</a> et votre <a href="List-Frigo.php">frigo</a>.
        </p>
        <?php if ($profilSnippet): ?>
            <div style="background:#ecfdf3;border:1px solid #bbf7d0;border-radius:12px;padding:1rem 1.1rem;color:#1a4d22;text-align:center;">
                <strong>Profil</strong> —
                objectif : <?php echo htmlspecialchars((string) ($profilSnippet['objectif'] ?? '—')); ?>
                <?php if (!empty($profilSnippet['poids_actuel']) || !empty($profilSnippet['taille'])): ?>
                    ·
                    <?php if (!empty($profilSnippet['poids_actuel'])): ?>
                        <?php echo htmlspecialchars((string) $profilSnippet['poids_actuel']); ?> kg
                    <?php endif; ?>
                    <?php if (!empty($profilSnippet['taille'])): ?>
                        <?php if (!empty($profilSnippet['poids_actuel'])): ?> / <?php endif; ?>
                        <?php echo htmlspecialchars((string) $profilSnippet['taille']); ?> cm
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <p style="text-align:center;margin-top:1.25rem;">
                <a href="user_health_space.php" style="display:inline-block;padding:10px 20px;background:#2C7E34;color:#fff!important;text-decoration:none;border-radius:10px;font-weight:600;">Voir l’espace santé</a>
            </p>
        <?php else: ?>
            <p style="color:#5c6b62;text-align:center;">Aucun profil santé pour le moment.</p>
            <p style="text-align:center;margin-top:1rem;">
                <a href="create.php" style="display:inline-block;padding:10px 20px;background:#2C7E34;color:#fff!important;text-decoration:none;border-radius:10px;font-weight:600;">Créer profil santé</a>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer style="text-align:center;padding:1rem;color:#2C7E34;font-weight:400;font-family:Poppins,sans-serif;">
    © 2026 HappyBite
</footer>

<?php if (!$loggedIn) {
    require __DIR__ . '/includes/guest_login_gate.php';
} ?>
</body>
</html>
