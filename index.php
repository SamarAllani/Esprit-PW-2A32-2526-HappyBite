<?php

require_once __DIR__ . '/Controllers/ProfilSanteController.php';
require_once __DIR__ . '/Controllers/SuiviJournalierController.php';
require_once __DIR__ . '/Controllers/UserController.php';

$action = $_GET['action'] ?? 'home';
$id_utilisateur = $_GET['id_utilisateur'] ?? 1;
$id = $_GET['id'] ?? null;

$profilController = new ProfilSanteController();
$suiviController = new SuiviJournalierController();
$userController = new UserController();

switch ($action) {
    case 'showProfilSante':
        $profilController->show($id_utilisateur);
        break;

    case 'createProfilSante':
        $profilController->create($id_utilisateur);
        break;

    case 'editProfilSante':
        $profilController->edit($id_utilisateur);
        break;

    case 'deleteProfilSante':
        $profilController->delete($id_utilisateur);
        break;

  case 'listSuiviJournalier':
    $suiviController->list($id_utilisateur);
    break;

case 'createSuiviJournalier':
    $suiviController->create($id_utilisateur);
    break;

case 'editSuiviJournalier':
    $suiviController->edit($id, $id_utilisateur);
    break;

case 'updateSuivi':
    $suiviController->update($id);
    break;

case 'deleteSuiviJournalier':
    $suiviController->delete($id, $id_utilisateur);
    break;

    case 'listUsersHealth':
        $userController->listUsersHealth();
        break;

    case 'userHealthSpace':
        $userController->userHealthSpace($id_utilisateur);
        break;
    case 'searchSuiviAjax':
    $suiviController->searchSuiviAjax($id_utilisateur, $_GET['date'] ?? null);
    break;
    case 'listSuiviJournalierAjax':
    $suiviController->listAjax($id_utilisateur);
    break;

    default:
        echo "<h1>Page d'accueil</h1>";
        echo "<a href='index.php?action=listUsersHealth'>Voir tous les utilisateurs</a><br><br>";
        echo "<a href='index.php?action=createProfilSante&id_utilisateur=1'>Créer profil santé utilisateur 1</a><br><br>";
        echo "<a href='index.php?action=showProfilSante&id_utilisateur=1'>Voir profil santé utilisateur 1</a><br><br>";
        echo "<a href='index.php?action=createSuiviJournalier&id_utilisateur=1'>Ajouter suivi utilisateur 1</a><br><br>";
        echo "<a href='index.php?action=listSuiviJournalier&id_utilisateur=1'>Voir suivis utilisateur 1</a>";
        break;
}