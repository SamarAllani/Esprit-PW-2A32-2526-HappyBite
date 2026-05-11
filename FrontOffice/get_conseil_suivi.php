<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'ID invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId < 1) {
    http_response_code(401);
    echo json_encode(['error' => 'Session invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Controllers/SuiviJournalierController.php';

$pdo = Database::getConnection();
$chk = $pdo->prepare(
    'SELECT sj.id FROM suivi_journalier sj
     INNER JOIN profil_sante ps ON ps.id = sj.id_profil_sante
     WHERE sj.id = :sid AND ps.id_utilisateur = :uid
     LIMIT 1'
);
$chk->execute(['sid' => $id, 'uid' => $userId]);
if (!$chk->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé'], JSON_UNESCAPED_UNICODE);
    exit;
}

$controller = new SuiviJournalierController();
$controller->getConseil($id);
