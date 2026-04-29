<?php
require_once "../../vendor/autoload.php";
require_once "../../Config.php";
require_once "../../Controllers/SuiviJournalierController.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

$controller = new SuiviJournalierController();

$user = $controller->getUser($id);
$suivis = $controller->getSuiviUser($id);

/* ===== calculs ===== */
$total = count($suivis);

$sumPoids = $sumCalories = $sumSommeil = $sumPas = $sumHydratation = 0;

$toFloat = fn($v) => (float) str_replace(',', '.', trim($v ?? 0));

foreach ($suivis as $s) {
    $sumPoids += $toFloat($s['poids']);
    $sumCalories += $toFloat($s['calories']);
    $sumSommeil += $toFloat($s['sommeil_heures']);
    $sumPas += (int) ($s['nbr_pas'] ?? 0);
    $sumHydratation += $toFloat($s['hydratation_litre']);
}

$avgPoids = $total ? $sumPoids / $total : 0;
$avgCalories = $total ? $sumCalories / $total : 0;
$avgSommeil = $total ? $sumSommeil / $total : 0;
$avgPas = $total ? $sumPas / $total : 0;
$avgHydratation = $total ? $sumHydratation / $total : 0;

/* ===== HTML PDF ===== */
$html = "
<style>
body { font-family: Arial; }

.header {
    text-align: center;
    margin-bottom: 20px;
}

h2 {
    color: #2e7d32;
    margin: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #222;
}

th {
    background-color: #4CAF50;
    color: white;
    padding: 8px;
    font-size: 12px;
    border: 1px solid #222;
}

td {
    padding: 6px;
    font-size: 11px;
    text-align: center;
    border: 1px solid #222;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}
</style>

<div class='header'>
    <h2 style='color:#2e7d32;'>Rapport de suivi santé</h2>
</div>

<h2>Suivi de {$user['nom']}</h2>
<p  style='color:#2e7d32; font-weight:bold; color:#27ae60;'>
    Email: {$user['email']}
</p>

<table border='1' cellpadding='5'>
<tr>
<th>Date</th><th>Poids</th><th>Calories</th><th>Sommeil</th><th>Pas</th><th>Sport</th><th>Hydratation</th>
</tr>";

foreach ($suivis as $s) {
    $html .= "
    <tr>
        <td>{$s['date_jour']}</td>
        <td>{$s['poids']}</td>
        <td>{$s['calories']}</td>
        <td>{$s['sommeil_heures']}</td>
        <td>{$s['nbr_pas']}</td>
        <td>{$s['nbr_activites_sport']}</td>
        <td>{$s['hydratation_litre']}</td>
    </tr>";
}

$html .= "</table>";

/* ===== DOMPDF ===== */
$options = new Options();
$options->set("isRemoteEnabled", true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("suivi_sante.pdf", ["Attachment" => true]);