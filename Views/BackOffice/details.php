<?php
require_once "../../Config.php";
require_once "../../Controllers/SuiviJournalierController.php";

$controller = new SuiviJournalierController();

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

$user = $controller->getUser($id);
$suivis = $controller->getSuiviUser($id);

/* =========================
   PARAMÈTRES
========================= */

$action = $_GET['action'] ?? null;
$date = $_GET['date'] ?? null;
$sort = $_GET['sort'] ?? null;

$suivisFiltered = $suivis;

/* =========================
   FILTRE DATE (RECHERCHE)
========================= */
if ($action === 'search' && $date) {
    $suivisFiltered = array_filter($suivisFiltered, function ($s) use ($date) {
        return $s['date_jour'] === $date;
    });
}

/* =========================
   TRI
========================= */
if ($action === 'apply' && $sort) {

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

/* =========================
   STATS
========================= */

$total = count($suivis);

$sumPoids = 0;
$sumCalories = 0;
$sumSommeil = 0;
$sumPas = 0;
$sumHydratation = 0;

foreach ($suivis as $s) {
    $sumPoids += is_numeric($s['poids']) ? $s['poids'] : 0;
    $sumCalories += is_numeric($s['calories']) ? $s['calories'] : 0;
    $sumSommeil += is_numeric($s['sommeil_heures']) ? $s['sommeil_heures'] : 0;
    $sumPas += is_numeric($s['nbr_pas']) ? $s['nbr_pas'] : 0;
    $sumHydratation += is_numeric($s['hydratation_litre']) ? $s['hydratation_litre'] : 0;
}

$avgPoids = $total ? $sumPoids / $total : 0;
$avgCalories = $total ? $sumCalories / $total : 0;
$avgSommeil = $total ? $sumSommeil / $total : 0;
$avgPas = $total ? $sumPas / $total : 0;
$avgHydratation = $total ? $sumHydratation / $total : 0;

/* SCORES */
$scorePoids = 100;
$scoreCalories = min(100, $avgCalories / 50);
$scoreSommeil = min(100, ($avgSommeil / 8) * 100);
$scorePas = min(100, $avgPas / 100);
$scoreHydratation = min(100, ($avgHydratation / 2) * 100);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails suivi</title>
    <link rel="stylesheet" href="/Views/assets/css/dashboard.css">
</head>

<body>

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

<div class="content">

<div class="card" style="height: 300px;">

    <canvas id="statsChart" height="200"></canvas>
</div>

   

    <br>

   

    <!-- FORMULAIRE -->
    <form method="GET">

        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- DATE -->
        <input type="date"
               name="date"
               value="<?= htmlspecialchars($date ?? '') ?>">

        <button type="submit" name="action" value="search">
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

        <button type="submit" name="action" value="apply" class="btn-filter">
            Appliquer
        </button>
 <a href="export_pdf.php?id=<?= $id ?>" target="_blank" class="pdf-btn">
        📄 Exporter en PDF
    </a>
    </form>

    <br>
 <h2>Suivi de <?= $user['nom'] ?? $user['name'] ?></h2>
    <p><?= $user['email'] ?></p>
    <!-- TABLE -->
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
        <?php foreach ($suivisFiltered as $s): ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('statsChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($suivisFiltered, 'date_jour')) ?>,

        datasets: [
            {
                label: 'Poids (kg)',
                data: <?= json_encode(array_column($suivisFiltered, 'poids')) ?>,
                borderColor: 'blue',
                tension: 0.3
            },
            {
                label: 'Calories',
                data: <?= json_encode(array_column($suivisFiltered, 'calories')) ?>,
                borderColor: 'red',
                tension: 0.3
            },
            {
                label: 'Sommeil (h)',
                data: <?= json_encode(array_column($suivisFiltered, 'sommeil_heures')) ?>,
                borderColor: 'green',
                tension: 0.3
            },
            {
                label: 'Pas',
                data: <?= json_encode(array_column($suivisFiltered, 'nbr_pas')) ?>,
                borderColor: 'orange',
                tension: 0.3
            }
        ]
    },

    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html> centre le catistique