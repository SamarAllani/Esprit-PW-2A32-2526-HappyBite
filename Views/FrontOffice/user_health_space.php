<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Santé Utilisateur</title>
    <link rel="stylesheet" href="/Views/assets/css/sytle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>


<body>



<nav class="main-navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
           <img src="/Views/assets/logo.png" alt="HappyBite">
            <span>HappyBite</span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="List-Produit.php" class="active">Produits</a></li>
            <li><a href="List-Recette.php">Recettes</a></li>
            <li><a href="#">Commande</a></li>
        </ul>

        <div class="nav-user">
            <a href="List-Frigo.php" class="nav-action">Frigo</a>
            <a href="#" class="nav-action">Commandes</a>
            <a href="#" class="nav-action active">Santé</a>
            <a href="#" class="nav-action">Profil</a>
        </div>
    </div>
</nav>




<div class="page-header">
    <h1>Espace santé de l'utilisateur</h1>
    <p>Suivi complet du profil et des habitudes quotidiennes</p>
</div>

<div class="user-info-grid">

    <div class="info-card">
        <i class="fas fa-id-card"></i>
        <div>
            <span>ID</span>
            <strong><?= htmlspecialchars($user['id'] ?? '') ?></strong>
        </div>
    </div>

    <div class="info-card">
        <i class="fas fa-user"></i>
        <div>
            <span>Prénom</span>
            <strong><?= htmlspecialchars($user['prenom'] ?? '') ?></strong>
        </div>
    </div>

    <div class="info-card">
        <i class="fas fa-user-tag"></i>
        <div>
            <span>Nom</span>
            <strong><?= htmlspecialchars($user['nom'] ?? '') ?></strong>
        </div>
    </div>

    <div class="info-card">
        <i class="fas fa-envelope"></i>
        <div>
            <span>Email</span>
            <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong>
        </div>
    </div>

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

            <a class="btn-edit_profil"
               href="index.php?action=editProfilSante&id_utilisateur=<?= $user['id'] ?>">
                Modifier le profil
            </a>

            <form method="POST"
                  action="index.php?action=deleteProfilSante&id_utilisateur=<?= $user['id'] ?>"
                  onsubmit="return confirm('Supprimer ce profil santé ?');">

                <button type="submit" class="btn-delete_profil">
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
<div class="card">
  <a class="btn-add"
       href="index.php?action=createSuiviJournalier&id_utilisateur=<?= $user['id'] ?>">
        + Ajouter un suivi journalier
    </a>

    <h2>Suivis journaliers</h2>

    <!-- SEARCH -->
    <form method="GET" class="search-suivi">

    <input type="hidden" name="action" value="userHealthSpace">
    <input type="hidden" name="id_utilisateur" value="<?= $user['id'] ?>">

    <div class="search-suivi-box">
        <i class="fas fa-calendar"></i>

        <input type="date"
               name="date"
               value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                  <button type="submit">
        Rechercher
    </button>
  <!-- TRI -->
        <select name="sort">
            <option value="">Tri par défaut (date)</option>

            <option value="poids_asc">Poids ↑</option>
            <option value="poids_desc">Poids ↓</option>

            <option value="calories_asc">Calories ↑</option>
            <option value="calories_desc">Calories ↓</option>

            <option value="sommeil_asc">Sommeil ↑</option>
            <option value="sommeil_desc">Sommeil ↓</option>

            <option value="pas_asc">Pas ↑</option>
            <option value="pas_desc">Pas ↓</option>
        </select>

        <!-- BOUTON UNIQUE -->
        <button type="submit" class="btn-filter">
            Appliquer
        </button>

    </div>
</form>
<?php
$suivisFiltered = $suivis;

/* FILTRE DATE */
$date = $_GET['date'] ?? null;

if ($date) {
    $suivisFiltered = array_filter($suivisFiltered, function ($s) use ($date) {
        return $s['date_jour'] === $date;
    });
}
$sort = $_GET['sort'] ?? null;

if (!$sort) {
    // tri par défaut = date DESC
    usort($suivisFiltered, fn($a, $b) =>
        strtotime($b['date_jour']) <=> strtotime($a['date_jour'])
    );
} else {

    $map = [
        'poids_asc' => fn($a,$b) => $a['poids'] <=> $b['poids'],
        'poids_desc' => fn($a,$b) => $b['poids'] <=> $a['poids'],

        'calories_asc' => fn($a,$b) => $a['calories'] <=> $b['calories'],
        'calories_desc' => fn($a,$b) => $b['calories'] <=> $a['calories'],

        'sommeil_asc' => fn($a,$b) => $a['sommeil_heures'] <=> $b['sommeil_heures'],
        'sommeil_desc' => fn($a,$b) => $b['sommeil_heures'] <=> $a['sommeil_heures'],

        'pas_asc' => fn($a,$b) => $a['nbr_pas'] <=> $b['nbr_pas'],
        'pas_desc' => fn($a,$b) => $b['nbr_pas'] <=> $a['nbr_pas'],
    ];

    if (isset($map[$sort])) {
        usort($suivisFiltered, $map[$sort]);
    }
}
?>

<?php if (!empty($suivisFiltered)): ?>

<div class="suivi-container">

<?php foreach ($suivisFiltered as $suivi): ?>

<div class="card-suivi">

    <!-- HEADER -->
    <div class="card-top">
        <div class="date">
            <i class="fas fa-calendar"></i>
            <?= date("d / m / Y", strtotime($suivi['date_jour'])) ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="card-content">

        <div class="item">
            <i class="fas fa-weight-scale green"></i>
            <span>Poids</span>
            <strong><?= $suivi['poids'] ?> kg</strong>
        </div>

        <div class="item">
            <i class="fas fa-fire orange"></i>
            <span>Calories</span>
            <strong><?= $suivi['calories'] ?> kcal</strong>
        </div>

        <div class="item">
            <i class="fas fa-moon purple"></i>
            <span>Sommeil</span>
            <strong><?= $suivi['sommeil_heures'] ?> h</strong>
        </div>

        <div class="item">
            <i class="fas fa-shoe-prints blue"></i>
            <span>Pas</span>
            <strong><?= number_format($suivi['nbr_pas'],0,',',' ') ?></strong>
        </div>

        <div class="item">
            <i class="fas fa-running green"></i>
            <span>Sport</span>
            <strong><?= $suivi['nbr_activites_sport'] ?> séance</strong>
        </div>

        <div class="item">
            <i class="fas fa-tint cyan"></i>
            <span>Hydratation</span>
            <strong><?= $suivi['hydratation_litre'] ?></strong>
        </div>

    </div>

    <!-- ACTIONS -->
    <div class="card-actions">

        <a class="btn-edit"
           href="index.php?action=editSuiviJournalier&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $user['id'] ?>">
            Modifier
        </a>

        <form method="POST"
              action="index.php?action=deleteSuiviJournalier&id=<?= $suivi['id'] ?>&id_utilisateur=<?= $user['id'] ?>"
              onsubmit="return confirm('Supprimer ?');">

            <button class="btn-delete" type="submit">Supprimer</button>
        </form>

    </div>

</div>

<?php endforeach; ?>

</div>
</div >
<?php else: ?>

<p style="text-align:center; color:red; font-weight:bold;">
<?php
if (empty($suivis)) {
    echo "❌ Aucun suivi journalier";
} elseif ($hasFilter) {
    echo "❌ Aucun résultat pour cette date";
}
?>
</p>

<?php endif; ?>

</div>

</div>



<br><br>

</body>
</html>

</div>

<br><br>



</body>
</html>