<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Santé Utilisateur</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>


<body>

<?php include __DIR__ . '/../partials/navbar.php'; ?>



<h1>Espace santé de l'utilisateur</h1>

<!-- USER INFO -->
<div class="card">
    <p><strong>ID :</strong> <?= htmlspecialchars($user['id'] ?? '') ?></p>
    <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom'] ?? '') ?></p>
    <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom'] ?? '') ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
</div>
<hr>

<!-- PROFIL SANTÉ -->
<div class="card">
    <h2>Profil santé</h2>

    <?php if ($profil): ?>

<div class="profil-grid">

    <div class="profil-item">
        <i class="fas fa-ruler-vertical icon"></i>
        <h4>Taille</h4>
        <p><?= htmlspecialchars($profil['taille']) ?> cm</p>
    </div>

    <div class="profil-item">
        <i class="fas fa-weight-scale icon"></i>
        <h4>Poids</h4>
        <p><?= htmlspecialchars($profil['poids_actuel']) ?> kg</p>
    </div>

    <div class="profil-item">
        <i class="fas fa-bullseye icon"></i>
        <h4>Objectif</h4>
        <p><?= htmlspecialchars($profil['objectif']) ?></p>
    </div>

    <div class="profil-item">
        <i class="fas fa-utensils icon"></i>
        <h4>Allergènes</h4>
        <p>
            <?= !empty($profil['allergenes']) ? htmlspecialchars(implode(', ', $profil['allergenes'])) : 'Aucun' ?>
        </p>
    </div>

    <div class="profil-item">
        <i class="fas fa-pills icon"></i>
        <h4>Carences</h4>
        <p>
            <?= !empty($profil['carences']) ? htmlspecialchars(implode(', ', $profil['carences'])) : 'Aucune' ?>
        </p>
    </div>

    <div class="profil-item">
        <i class="fas fa-heart-pulse icon"></i>
        <h4>Maladies</h4>
        <p>
            <?= !empty($profil['maladies']) ? htmlspecialchars(implode(', ', $profil['maladies'])) : 'Aucune' ?>
        </p>
    </div>

</div>

        <!-- ACTIONS -->
        <div class="profil-actions">

            <a class="btn-edit"
               href="index.php?action=editProfilSante&id_utilisateur=<?= $user['id'] ?>">
                Modifier le profil
            </a>

            <form method="POST"
                  action="index.php?action=deleteProfilSante&id_utilisateur=<?= $user['id'] ?>"
                  onsubmit="return confirm('Supprimer ce profil santé ?');">

                <button type="submit" class="btn-delete">
                    Supprimer le profil
                </button>

            </form>

        </div>

    <?php else: ?>

        <p>Aucun profil santé pour cet utilisateur.</p>

        <a class="btn-add"
   href="index.php?action=createProfilSante&id_utilisateur=<?= $user['id'] ?>">
    + Créer profil santé
</a>

    <?php endif; ?>

</div>

<hr>

<!-- SUIVI JOURNALIER -->
<div class="card">
<a class="btn-add" href="index.php?action=createSuiviJournalier&id_utilisateur=<?= $user['id'] ?>"> + Ajouter un suivi journalier </a>
    <h2>Suivis journaliers</h2>

    <!-- 🔵 FORM FILTER PHP -->
   <!-- 🔵 SEARCH BAR -->
<form method="GET" class="search-bar">

    <input type="hidden" name="action" value="userHealthSpace">
    <input type="hidden" name="id_utilisateur" value="<?= $user['id'] ?>">

    <input type="date"
           name="date"
           class="search-input"
           value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

    <button type="submit" class="search-btn">
        Rechercher
    </button>

</form>
<?php
$dateFilter = $_GET['date'] ?? '';

$hasFilter = !empty($dateFilter);

// 🔴 si aucun suivi en base
if (empty($suivis)) {
    $suivisFiltered = [];
} else {
    $suivisFiltered = $suivis;

    if ($hasFilter) {
        $suivisFiltered = array_filter($suivis, function ($s) use ($dateFilter) {
            return $s['date_jour'] === $dateFilter;
        });
    }
}
?>

    <!-- 🔵 TABLE -->
    <table border="0" width="100%">

        <thead>
            <tr>
                <th>Date</th>
                <th>Poids</th>
                <th>Calories</th>
                <th>Sommeil</th>
                <th>Pas</th>
                <th>Sport</th>
                <th>Hydratation</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

<?php if (!empty($suivisFiltered)): ?>

    <?php foreach ($suivisFiltered as $suivi): ?>
        <tr>
            <td><?= htmlspecialchars($suivi['date_jour']) ?></td>
            <td><?= htmlspecialchars($suivi['poids']) ?></td>
            <td><?= htmlspecialchars($suivi['calories']) ?></td>
            <td><?= htmlspecialchars($suivi['sommeil_heures']) ?></td>
            <td><?= htmlspecialchars($suivi['nbr_pas']) ?></td>
            <td><?= htmlspecialchars($suivi['nbr_activites_sport']) ?></td>
            <td><?= htmlspecialchars($suivi['hydratation_litre']) ?></td>

            <td>
                <a class="btn-edit-table"
                   href="index.php?action=editSuiviJournalier&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $user['id'] ?>">
                    Modifier
                </a>

                <form method="POST"
                      action="index.php?action=deleteSuiviJournalier&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $user['id'] ?>"
                      style="display:inline;"
                      onsubmit="return confirm('Supprimer ?');">

                    <button class="btn-delete-table" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

<?php else: ?>

    <tr>
        <td colspan="8" style="text-align:center; padding:15px; color:red; font-weight:bold;">
            <?php
if (empty($suivis)) {
    echo "❌ Aucun suivi journalier pour cet utilisateur";
} elseif ($hasFilter) {
    echo "❌ Aucun suivi journalier pour cette date";
}
?>
        </td>
    </tr>

<?php endif; ?>

</tbody>
</table>



<br><br>

</body>
</html>

</div>

<br><br>



</body>
</html>