<?php
require_once "../../vendor/autoload.php";
require_once "../../Config.php";
require_once "../../Controllers/SuiviJournalierController.php";

use Dompdf\Dompdf;
use Dompdf\Options;

ini_set('display_errors', 1);
error_reporting(E_ALL);

$controller = new SuiviJournalierController();

$search = $_GET['search'] ?? null;

$users = !empty($search)
    ? $controller->searchUsersBackoffice($search)
    : $controller->listUsersBackoffice();

function formatList($data) {
    if (empty($data)) return '-';

    if (is_array($data)) {
        return implode(', ', $data);
    }

    $decoded = json_decode($data, true);
    if (is_array($decoded)) {
        return implode(', ', $decoded);
    }

    return $data;
}

$html = '
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

<div class="header">
    <h2>Profil santé des utilisateurs</h2>
</div>

<table>
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
</tr>
';

if (!empty($users)) {
    foreach ($users as $user) {
        $html .= '
        <tr>
            <td>'.($user['id'] ?? '-').'</td>
            <td>'.($user['prenom'] ?? '').' '.($user['nom'] ?? '').'</td>
            <td>'.($user['email'] ?? '-').'</td>
            <td>'.($user['taille'] ?? '-').'</td>
            <td>'.($user['poids_actuel'] ?? '-').'</td>
            <td>'.($user['objectif'] ?? '-').'</td>
            <td>'.formatList($user['allergenes'] ?? '').'</td>
            <td>'.formatList($user['carences'] ?? '').'</td>
            <td>'.formatList($user['maladies'] ?? '').'</td>
        </tr>';
    }
}

$html .= '</table>';

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("utilisateurs.pdf", ["Attachment" => true]);