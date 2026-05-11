<?php

require_once __DIR__ . '/../Controller/ControllerIA.php';

if (isset($_GET['id_profil_sante'])) {
    $id_profil_sante = $_GET['id_profil_sante'];
    $date_jour = date('Y-m-d');

    $controllerIA = new ControllerIA();
    $resultat = $controllerIA->analyserSuiviJournalier($id_profil_sante, $date_jour);
} else {
    $resultat = [
        "success" => false,
        "message" => "ID profil santé manquant."
    ];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Analyse IA du suivi santé</title>
</head>
<body>

<h2>Analyse IA du suivi journalier</h2>

<p>
    <?= htmlspecialchars($resultat['message']) ?>
</p>

<?php if ($resultat['success']) : ?>

    <p>
        Points attribués :
        <strong>
            <?= $resultat['points'] > 0 ? '+' . $resultat['points'] : $resultat['points'] ?>
        </strong>
    </p>

    <h3>Détails de l'analyse :</h3>

    <ul>
        <?php foreach ($resultat['commentaires'] as $commentaire) : ?>
            <li><?= htmlspecialchars($commentaire) ?></li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>

<br>

<a href="profil_sante.php">Retour au profil santé</a>

</body>
</html>