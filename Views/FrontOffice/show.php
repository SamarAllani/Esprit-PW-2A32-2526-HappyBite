<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil Santé</title>
</head>
<body>
    <h1>Mon profil santé</h1>

    <?php if ($profil): ?>
        <p><strong>Taille :</strong> <?= htmlspecialchars($profil['taille']) ?> cm</p>
        <p><strong>Poids actuel :</strong> <?= htmlspecialchars($profil['poids_actuel']) ?> kg</p>
        <p><strong>Objectif :</strong> <?= htmlspecialchars($profil['objectif']) ?></p>

        <p><strong>Allergènes :</strong>
            <?= !empty($profil['allergenes']) ? htmlspecialchars(implode(', ', $profil['allergenes'])) : 'Aucun' ?>
        </p>

        <p><strong>Carences :</strong>
            <?= !empty($profil['carences']) ? htmlspecialchars(implode(', ', $profil['carences'])) : 'Aucune' ?>
        </p>

        <p><strong>Maladies :</strong>
            <?= !empty($profil['maladies']) ? htmlspecialchars(implode(', ', $profil['maladies'])) : 'Aucune' ?>
        </p>

        <hr>

        <a href="index.php?action=editProfilSante&id_utilisateur=<?= $profil['id_utilisateur'] ?>">
            Modifier mon profil
        </a>

        <br><br>

        <form method="POST" action="index.php?action=deleteProfilSante&id_utilisateur=<?= $profil['id_utilisateur'] ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre profil santé ?');">
            <button type="submit">Supprimer mon profil</button>
        </form>

        <br>

        <a href="index.php">Retour à l'accueil</a>

    <?php else: ?>
        <p>Aucun profil santé trouvé.</p>

        <a href="index.php?action=createProfilSante&id_utilisateur=<?= $id_utilisateur ?>">
            Créer mon profil santé
        </a>

        <br><br>
        <a href="index.php">Retour à l'accueil</a>
        <br><br>
<a href="index.php?action=listUsersHealth">← Retour à la liste des utilisateurs</a>
    <?php endif; ?>
</body>
</html>