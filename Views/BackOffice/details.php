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
if ($date){
    $suivisFiltered = array_filter($suivisFiltered, function ($s) use ($date) {
        return $s['date_jour'] === $date;
    });
}

/* =========================
   TRI
========================= */
if ($sort){

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

<div class="charts-grid" style="height: 300px;">

    <canvas id="chartPoids"></canvas>
<canvas id="chartCalories"></canvas>
<canvas id="chartSommeil"></canvas>
<canvas id="chartPas"></canvas>
</div>

   

    <br>
    <form id="searchForm" class="search-suivi">

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
           Filtrer
        </button>
<button type="button" id="resetFilter" class="btn-reset">
    Annuler
</button>
<a href="export_pdf.php?id=<?= $id ?>" target="_blank" class="pdf-btn">
        📄 Exporter en PDF
    </a>
    </div>
</form>
   

  

    <br>
 <h2>Suivi de <?= $user['nom'] ?? $user['name'] ?></h2>
    <p><?= $user['email'] ?></p>
<div id="tableContainer">
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
    let charts = {};

document.getElementById("searchForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const url = new URL(window.location.href);

    const formData = new FormData(form);

    formData.forEach((value, key) => {
        url.searchParams.set(key, value);
    });

    fetch(url)
        .then(res => res.text())
        .then(html => {

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            // ✅ تحديث الجدول فقط
            document.getElementById("tableContainer").innerHTML =
                doc.getElementById("tableContainer").innerHTML;

            // ✅ تحديث chart
            updateChart(doc);

            // ✅ تحديث URL بدون reload
            window.history.pushState({}, "", url);
        })
        .catch(err => console.error(err));
});
document.getElementById("resetFilter").addEventListener("click", function () {

    // reset inputs
    document.querySelector("input[name='date']").value = "";
    document.querySelector("select[name='sort']").value = "";

    const url = new URL(window.location.href);

    // 🔥 نحيو كل الفلاتر
    url.searchParams.delete("date");
    url.searchParams.delete("sort");
    url.searchParams.delete("action");

    fetch(url)
        .then(res => res.text())
        .then(html => {

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            document.getElementById("tableContainer").innerHTML =
                doc.getElementById("tableContainer").innerHTML;

            updateChart(doc);

            window.history.pushState({}, "", url);
        })
        .catch(err => console.error(err));
});
document.querySelector("input[name='date']").addEventListener("input", function () {

    // إذا فضّى التاريخ
    if (this.value === "") {

        const url = new URL(window.location.href);

        url.searchParams.delete("date");
        url.searchParams.set("page", 1);

        fetch(url)
            .then(res => res.text())
            .then(html => {

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                document.getElementById("tableContainer").innerHTML =
                    doc.getElementById("tableContainer").innerHTML;

                updateChart(doc);

                window.history.pushState({}, "", url);
            })
            .catch(err => console.error(err));
    }
});
let chart; // نخلي chart global

window.addEventListener("load", () => {
    updateChart(document);
});
function updateChart(doc) {

    const rows = doc.querySelectorAll("#tableContainer tbody tr");

    let labels = [];
    let poids = [];
    let calories = [];
    let sommeil = [];
    let pas = [];

    rows.forEach(row => {
        const cols = row.querySelectorAll("td");

        labels.push(cols[0].textContent);
        poids.push(parseFloat(cols[1].textContent) || 0);
        calories.push(parseFloat(cols[2].textContent) || 0);
        sommeil.push(parseFloat(cols[3].textContent) || 0);
        pas.push(parseFloat(cols[4].textContent) || 0);
    });

    createSingleChart('chartPoids', 'Poids', poids, labels, 'blue');
    createSingleChart('chartCalories', 'Calories', calories, labels, 'red');
    createSingleChart('chartSommeil', 'Sommeil', sommeil, labels, 'green');
    createSingleChart('chartPas', 'Pas', pas, labels, 'orange');
}



function createSingleChart(id, label, data, labels, color) {

    const canvas = document.getElementById(id);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    if (charts[id]) {
        charts[id].destroy();
    }

    charts[id] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                tension: 0.3
            }]
        },
        options: {
            responsive: true
        }
    });
}
</script>

</body>
</html> 