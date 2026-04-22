<?php
include '../../Controllers/FrigoController.php';

$frigoController = new FrigoController();

$recherche = trim($_GET['recherche'] ?? '');
$motCle = $recherche;
$idUtilisateur = ctype_digit($recherche) ? $recherche : '';

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
| - Si recherche active : export des résultats affichés
| - Sinon : export de toute la liste
*/
if (isset($_GET['export_excel']) && $_GET['export_excel'] == '1') {
    $frigosExport = $frigoController->getResumeFrigos($motCle, $idUtilisateur);

    $nomFichier = !empty($recherche)
        ? 'frigos_recherche.xls'
        : 'frigos.xls';

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nomFichier\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF";

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID Utilisateur</th>";
    echo "<th>Utilisateur</th>";
    echo "<th>Nombre de produits</th>";
    echo "<th>Quantité totale</th>";
    echo "<th>Contenu du frigo</th>";
    echo "<th>Dernier ajout</th>";
    echo "</tr>";

    foreach ($frigosExport as $frigo) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($frigo['id_utilisateur'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($frigo['nom_utilisateur'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($frigo['nombre_produits'] ?? 0) . "</td>";
        echo "<td>" . htmlspecialchars($frigo['quantite_totale'] ?? 0) . "</td>";
        echo "<td>" . htmlspecialchars($frigo['liste_produits'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($frigo['derniere_date_ajout'] ?? '') . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    exit;
}

$frigos = $frigoController->getResumeFrigos($motCle, $idUtilisateur);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des frigos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="/Views/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/Views/assets/css/style.css">
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
            <a href="List-Produit.php" class="admin-main-link active">Produit</a>
            <a href="#" class="admin-main-link">Communauté</a>
            <a href="#" class="admin-main-link">Post</a>
            <a href="#" class="admin-main-link">Utilisateur</a>
            <a href="#" class="admin-main-link">Santé</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="container mt-4">
            <div class="d-flex justify-content-end flex-wrap gap-3">

                <a href="List-Produit.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Produits
                </a>

                <a href="List-Recette.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Recettes
                </a>

                <a href="List-Categorie.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Catégories
                </a>

                <a href="List-Frigo-Back.php" class="btn btn-success rounded-pill px-4 py-2">
                    Frigo
                </a>

                <a href="Dashboard-Produit.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                    Dashboard
                </a>

            </div>
        </div>

        <div class="container py-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Liste des frigos</h2>
                    <p class="text-muted mb-0">Consultation des frigos par utilisateur</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="recherche" class="form-label fw-semibold">Recherche</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="recherche"
                                    name="recherche"
                                    placeholder="Produit, utilisateur ou ID utilisateur..."
                                    value="<?php echo htmlspecialchars($recherche); ?>"
                                >
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100 rounded-pill">
                                    Rechercher
                                </button>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button
                                    type="submit"
                                    name="export_excel"
                                    value="1"
                                    class="btn btn-outline-success w-100 rounded-pill"
                                >
                                    Exporter Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body">
                    <?php if (empty($frigos)) { ?>
                        <div class="alert alert-info mb-0 text-center">
                            Aucun frigo trouvé.
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>ID Utilisateur</th>
                                        <th>Utilisateur</th>
                                        <th>Nombre de produits</th>
                                        <th>Quantité totale</th>
                                        <th>Contenu du frigo</th>
                                        <th>Dernier ajout</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($frigos as $frigo) { ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php echo htmlspecialchars($frigo['id_utilisateur'] ?? ''); ?>
                                            </td>

                                            <td class="text-center">
                                                <strong><?php echo htmlspecialchars($frigo['nom_utilisateur'] ?? ''); ?></strong>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($frigo['nombre_produits'] ?? 0); ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($frigo['quantite_totale'] ?? 0); ?>
                                            </td>

                                            <td>
                                                <?php
                                                $produits = explode(' | ', $frigo['liste_produits'] ?? '');
                                                foreach ($produits as $produit) {
                                                    if (trim($produit) !== '') {
                                                        echo '<span class="badge bg-success me-1 mb-1">' . htmlspecialchars($produit) . '</span>';
                                                    }
                                                }
                                                ?>
                                            </td>

                                            <td class="text-center">
                                                <?php echo htmlspecialchars($frigo['derniere_date_ajout'] ?? ''); ?>
                                            </td>

                                            <td class="text-center">
                                                <a
                                                    href="Detail-Frigo-Back.php?id_utilisateur=<?php echo urlencode($frigo['id_utilisateur']); ?>"
                                                    class="btn btn-info btn-sm me-1 mb-1"
                                                >
                                                    Voir détail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="/Views/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>