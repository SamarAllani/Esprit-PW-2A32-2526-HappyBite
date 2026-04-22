<?php
include '../../Controllers/FrigoController.php';

$frigoController = new FrigoController();

$idUtilisateur = (int)($_GET['id_utilisateur'] ?? 0);

$detailsFrigo = [];
$nomUtilisateur = '';

if ($idUtilisateur > 0) {
    $detailsFrigo = $frigoController->getDetailFrigoByUtilisateur($idUtilisateur);

    if (!empty($detailsFrigo)) {
        $nomUtilisateur = $detailsFrigo[0]['nom_utilisateur'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du frigo</title>
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
            <a href="List-Produit.php" class="admin-main-link">Produit</a>
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
            </div>
        </div>

        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Détail du frigo</h2>
                    <p class="text-muted mb-0">
                        <?php if (!empty($nomUtilisateur)) { ?>
                            Utilisateur : <?php echo htmlspecialchars($nomUtilisateur); ?> (ID <?php echo $idUtilisateur; ?>)
                        <?php } else { ?>
                            Aucun utilisateur trouvé
                        <?php } ?>
                    </p>
                </div>

                <a href="List-Frigo-Back.php" class="btn btn-outline-success rounded-pill px-4">
                    Retour
                </a>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body">
                    <?php if (empty($detailsFrigo)) { ?>
                        <div class="alert alert-info mb-0 text-center">
                            Ce frigo est vide.
                        </div>
                    <?php } else { ?>
                        <div class="row">
                            <?php foreach ($detailsFrigo as $produit) { ?>
                                <?php
                                $allergenes = array_filter(array_map('trim', explode(',', $produit['allergene'] ?? '')));
                                $benefices = array_filter(array_map('trim', explode(',', $produit['benefices'] ?? '')));
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm border-0 rounded-4">
                                        <div class="card-body d-flex flex-column">

                                            <div class="text-center mb-3">
                                                <?php if (!empty($produit['image'])) { ?>
                                                    <img
                                                        src="/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                                                        style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 15px;"
                                                    >
                                                <?php } else { ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-4"
                                                         style="height: 200px;">
                                                        <span class="text-muted">Aucune image</span>
                                                    </div>
                                                <?php } ?>
                                            </div>

                                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($produit['nom']); ?></h5>

                                            <p class="mb-2"><strong>Catégorie :</strong> <?php echo htmlspecialchars($produit['nom_categorie'] ?? 'Non classé'); ?></p>
                                            <p class="mb-2"><strong>Prix :</strong> <span class="text-success fw-bold"><?php echo htmlspecialchars($produit['prix']); ?> DT</span></p>
                                            <p class="mb-2"><strong>Calories :</strong> <?php echo htmlspecialchars($produit['calories'] ?? 'Non défini'); ?> cal</p>
                                            <p class="mb-2"><strong>Quantité :</strong> <?php echo htmlspecialchars($produit['quantite']); ?></p>
                                            <p class="mb-2"><strong>Date ajout :</strong> <?php echo htmlspecialchars($produit['date_ajout']); ?></p>

                                            <div class="mb-3">
                                                <strong>Allergènes :</strong><br>
                                                <?php if (!empty($allergenes)) { ?>
                                                    <?php foreach ($allergenes as $item) { ?>
                                                        <span class="badge bg-danger me-1 mb-1"><?php echo htmlspecialchars($item); ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">Aucun</span>
                                                <?php } ?>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Bénéfices :</strong><br>
                                                <?php if (!empty($benefices)) { ?>
                                                    <?php foreach ($benefices as $item) { ?>
                                                        <span class="badge bg-success me-1 mb-1"><?php echo htmlspecialchars($item); ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="text-muted">Non précisé</span>
                                                <?php } ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
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