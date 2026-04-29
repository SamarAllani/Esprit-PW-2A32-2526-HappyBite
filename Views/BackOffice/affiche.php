<?php

require_once "../../Config.php";
require_once "../../Controllers/SuiviJournalierController.php";

$controller = new SuiviJournalierController();

$search = isset($_GET['search']) ? trim($_GET['search']) : null;

if (!empty($search)) {
    $users = $controller->searchUsersBackoffice($search);
} else {
    $users = $controller->listUsersBackoffice();
}

$pieStats = $controller->getStatsProfilsVsNon();


function formatList($data) {
    if (empty($data)) return [];

    // déjà array
    if (is_array($data)) return $data;

    // JSON array string
    $decoded = json_decode($data, true);
    if (is_array($decoded)) return $decoded;

    // string simple "a,b,c"
    return explode(',', $data);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Utilisateurs</title>

    <link rel="stylesheet" href="/Views/assets/css/dashboard.css">

    <style>
        .badge {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-green {
            background-color: #024a0d;
            color: white;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="logo">
        <img src="/Views/assets/logo.png">
        <span>HappyBite</span>
    </div>

    <a href="#">Communauté</a>
    <a href="#">Post</a>
    <a href="#">Utilisateur</a>
    <a href="#" class="active">Santé</a>
</div>

<div class="content">

    <!-- SEARCH -->
    <div class="search-container">
        <form method="GET" class="search-form">
            <input type="text" name="search"
                   placeholder="Rechercher (nom, email, ID)"
                   value="<?= htmlspecialchars($search ?? '') ?>"
                   oninput="checkEmpty(this)">
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- CHART -->
    <div class="card chart-card">

        <h3>Répartition des utilisateurs</h3>
       
 

        <canvas id="pieChart"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card">
         <a href="export_users_pdf.php?search=<?= urlencode($search ?? '') ?>" 
   class="pdf-btn">
    📄 Export PDF</a> 
        <h2>Profil santé des utilisateurs</h2>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Taille</th>
                <th>Poids</th>
                <th>Objectif</th>
                <th>Allergènes</th>
                <th>Carences</th>
                <th>Maladies</th>
                <th>Date MAJ</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>

                        <td>
                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                        </td>

                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['taille'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['poids_actuel'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['objectif'] ?? '') ?></td>

                        <!-- ALLERGENES -->
                        <td>
                            <?php
                            $allergenes = formatList($user['allergenes'] ?? '');
                            foreach ($allergenes as $a) {
                                if (!empty(trim($a))) {
                                    echo '<span class="badge badge-green">' . htmlspecialchars(trim($a)) . '</span>';
                                }
                            }
                            ?>
                        </td>

                        <!-- CARENCES -->
                        <td>
                            <?php
                            $carences = formatList($user['carences'] ?? '');
                            foreach ($carences as $c) {
                                if (!empty(trim($c))) {
                                    echo '<span class="badge badge-green">' . htmlspecialchars(trim($c)) . '</span>';
                                }
                            }
                            ?>
                        </td>

                        <!-- MALADIES -->
                        <td>
                            <?php
                            $maladies = formatList($user['maladies'] ?? '');
                            foreach ($maladies as $m) {
                                if (!empty(trim($m))) {
                                    echo '<span class="badge badge-green">' . htmlspecialchars(trim($m)) . '</span>';
                                }
                            }
                            ?>
                        </td>

                        <td><?= htmlspecialchars($user['date_mise_a_jour'] ?? '') ?></td>

                        <td>
                            <a class="btn" href="details.php?id=<?= $user['id'] ?>">
                                Détail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11">
                        Aucun utilisateur trouvé
                        <?= $search ? 'pour "' . htmlspecialchars($search) . '"' : '' ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

    </div>

</div>

<script>
function checkEmpty(input) {
    if (input.value.trim() === '') {
        window.location.href = window.location.pathname;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctxPie = document.getElementById('pieChart');

new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Avec profil santé', 'Sans profil santé'],
        datasets: [{
            data: [
                <?= $pieStats['avec'] ?>,
                <?= $pieStats['sans'] ?>
            ],
            backgroundColor: ['#18741e', '#bdc3c7'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});
</script>

</body>
</html>