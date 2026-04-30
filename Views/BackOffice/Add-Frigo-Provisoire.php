<?php
include '../../Controllers/ProduitController.php';
require_once '../../Config.php';

$produitController = new ProduitController();
$produits = $produitController->listProduits();

$db = Config::getConnexion();

$utilisateurs = $db->query("
    SELECT id, nom, prenom 
    FROM utilisateur 
    WHERE role = 'client'
    ORDER BY id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$message = "";

$idUtilisateurSelectionne = $_GET['id_utilisateur'] ?? ($_POST['id_utilisateur'] ?? '');

if (isset($_POST['save'])) {
    $idUtilisateurSelectionne = $_POST['id_utilisateur'];
    $produitsCoches = $_POST['produits'] ?? [];
    $quantites = $_POST['quantites'] ?? [];

    // Supprimer l'ancien frigo du client
    $delete = $db->prepare("DELETE FROM frigo WHERE id_utilisateur = :id_utilisateur");
    $delete->execute([
        'id_utilisateur' => $idUtilisateurSelectionne
    ]);

    // Réinsérer les produits cochés
    foreach ($produitsCoches as $idProduit => $value) {
        $quantite = $quantites[$idProduit] ?? 1;

        $insert = $db->prepare("
            INSERT INTO frigo (id_utilisateur, id_produit, quantite, date_ajout)
            VALUES (:id_utilisateur, :id_produit, :quantite, NOW())
        ");

        $insert->execute([
            'id_utilisateur' => $idUtilisateurSelectionne,
            'id_produit' => $idProduit,
            'quantite' => $quantite
        ]);
    }

    $message = "Frigo enregistré avec succès.";
}

// Récupérer le frigo actuel du client sélectionné
$frigoActuel = [];

if (!empty($idUtilisateurSelectionne)) {
    $query = $db->prepare("
        SELECT id_produit, quantite
        FROM frigo
        WHERE id_utilisateur = :id_utilisateur
    ");

    $query->execute([
        'id_utilisateur' => $idUtilisateurSelectionne
    ]);

    foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
        $frigoActuel[$ligne['id_produit']] = $ligne['quantite'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Frigo provisoire</title>

    <link rel="stylesheet" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Views/assets/css/style.css">
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="#" class="admin-logo">
                <img src="../assets/images/logo.png" alt="HappyBite">
                <span>HappyBite</span>
            </a>
        </div>

        <nav class="admin-main-menu">
            <a href="List-Produit.php" class="admin-main-link">Produit</a>
            <a href="List-Frigo-Back.php" class="admin-main-link active">Frigo</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="container py-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Frigo provisoire</h2>
                    <p class="text-muted mb-0">
                        Ajouter ou modifier les produits du frigo d’un client
                    </p>
                </div>

                <a href="List-Frigo-Back.php" class="btn btn-outline-success rounded-pill px-4">
                    Retour aux frigos
                </a>
            </div>

            <?php if (!empty($message)) { ?>
                <div class="alert alert-success rounded-4">
                    <?php echo $message; ?>
                </div>
            <?php } ?>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">

                    <form method="GET">
                        <label class="form-label fw-semibold">Choisir un client</label>

                        <div class="row g-3">
                            <div class="col-md-10">
                                <select name="id_utilisateur" class="form-control">
                                    <option value="">-- Choisir un client --</option>

                                    <?php foreach ($utilisateurs as $u) { ?>
                                        <option
                                            value="<?php echo $u['id']; ?>"
                                            <?php echo ($idUtilisateurSelectionne == $u['id']) ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars(trim($u['prenom'] . ' ' . $u['nom'])); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100 rounded-pill">
                                    Charger
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php if (!empty($idUtilisateurSelectionne)) { ?>
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">

                        <form method="POST">
                            <input type="hidden" name="id_utilisateur" value="<?php echo htmlspecialchars($idUtilisateurSelectionne); ?>">

                            <h5 class="fw-bold mb-3">Produits du frigo</h5>

                            <div class="row">
                                <?php foreach ($produits as $p) { ?>
                                    <?php
                                        $idProduit = $p['id_produit'];
                                        $isChecked = isset($frigoActuel[$idProduit]);
                                        $quantite = $isChecked ? $frigoActuel[$idProduit] : 1;
                                    ?>

                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center border rounded-4 p-3 bg-white">
                                            <input
                                                type="checkbox"
                                                class="form-check-input me-3"
                                                name="produits[<?php echo $idProduit; ?>]"
                                                value="1"
                                                <?php echo $isChecked ? 'checked' : ''; ?>
                                            >

                                            <div class="flex-grow-1">
                                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($p['nom_categorie'] ?? 'Sans catégorie'); ?>
                                                </div>
                                            </div>

                                            <input
                                                type="number"
                                                name="quantites[<?php echo $idProduit; ?>]"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($quantite); ?>"
                                                min="1"
                                                style="width: 90px;"
                                            >
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <button type="submit" name="save" class="btn btn-success rounded-pill px-4">
                                Enregistrer le frigo
                            </button>

                        </form>

                    </div>
                </div>
            <?php } ?>

        </div>
    </main>

</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>