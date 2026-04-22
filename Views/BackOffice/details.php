<?php
require_once "../../Config.php";
require_once "../../Controllers/SuiviJournalierController.php";

$controller = new SuiviJournalierController();

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

$user = $controller->getUser($id);
$suivis = $controller->getSuiviUser($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails suivi</title>

    <link rel="stylesheet" href="/Views/assets/css/dashboard.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
          <img src="/Views/assets/logo.png" alt="HappyBite">
        <span>HappyBite</span>
    </div>

    
    <a href="#">Communauté</a>
    <a href="#">Post</a>
    <a href="#">Utilisateur</a>
    <a href="#" class="active">Santé</a>
</div>

<!-- CONTENT -->
<div class="content">

    <div class="card">

        <h2>Suivi de <?= $user['nom'] ?? $user['name'] ?></h2>
        <p><?= $user['email'] ?></p>

        <br>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Poids</th>
                    <th>Calories</th>
                    <th>Sommeil</th>
                    <th>Pas</th>
                    <th>Sport</th>
                    <th>Hydratation</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($suivis as $s): ?>
                <tr>
                    <td><?= $s['date_jour'] ?></td>
                    <td><?= $s['poids'] ?></td>
                    <td><?= $s['calories'] ?></td>
                    <td><?= $s['sommeil_heures'] ?></td>
                    <td><?= $s['nbr_pas'] ?></td>
                    <td><?= $s['nbr_activites_sport'] ?></td>
                    <td><?= $s['hydratation_litre'] ?></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>