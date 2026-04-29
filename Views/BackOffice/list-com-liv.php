<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/LivraisonController.php';

$commandeCtrl = new CommandeController();
$livCtrl = new LivraisonController();

$vue = $_GET['vue'] ?? 'commande';
if (!in_array($vue, ['commande', 'livraison'], true)) {
    $vue = 'commande';
}

if (isset($_GET['supprimer_livraison'])) {
    $idSuppr = (int) $_GET['supprimer_livraison'];
    if ($idSuppr > 0) {
        $livCtrl->supprimerLivraison($idSuppr);
    }
    header('Location: list-com-liv.php?vue=livraison');
    exit;
}

if (isset($_GET['supprimer_commande'])) {
    $idSupprC = (int) $_GET['supprimer_commande'];
    if ($idSupprC > 0) {
        $commandeCtrl->supprimerCommande($idSupprC);
    }
    header('Location: list-com-liv.php?vue=commande');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_livraison'])) {
    $idMaj = (int) ($_POST['id_livraison'] ?? 0);
    $dateMaj = trim((string) ($_POST['livraison_date'] ?? ''));
    $statutMaj = trim((string) ($_POST['statut'] ?? ''));
    if ($idMaj > 0 && $dateMaj !== '' && $statutMaj !== '') {
        $livCtrl->updateLivraison($idMaj, $dateMaj, $statutMaj);
    }
    header('Location: list-com-liv.php?vue=livraison');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_commande'])) {
    $idMajC = (int) ($_POST['id_commande'] ?? 0);
    $modeMaj = trim((string) ($_POST['mode_paiement'] ?? ''));
    $redStr = str_replace(',', '.', trim((string) ($_POST['reduction'] ?? '0')));
    $reductionMaj = is_numeric($redStr) ? (float) $redStr : 0.0;
    if ($idMajC > 0 && in_array($modeMaj, ['carte', 'cash', 'paypal'], true)) {
        $commandeCtrl->finaliserCommande($idMajC, $modeMaj, $reductionMaj);
    }
    header('Location: list-com-liv.php?vue=commande');
    exit;
}

$idModCom = isset($_GET['modifier_commande']) ? (int) $_GET['modifier_commande'] : 0;
$commandeEdition = null;
if ($idModCom > 0) {
    $commandeEdition = $commandeCtrl->getCommandeById($idModCom);
    if ($commandeEdition === null) {
        $idModCom = 0;
    } else {
        $vue = 'commande';
    }
}

$idModifier = isset($_GET['modifier_livraison']) ? (int) $_GET['modifier_livraison'] : 0;
$livraisonEdition = null;
if ($commandeEdition === null && $idModifier > 0) {
    $livraisonEdition = $livCtrl->getLivraisonById($idModifier);
    if ($livraisonEdition === null) {
        $idModifier = 0;
    }
}

$commandes = $commandeCtrl->listCommandes();
$livraisons = $livCtrl->listLivraisons();

$dateEditValue = '';
$statutEditValue = '';
if ($livraisonEdition !== null) {
    $rawDate = LivraisonController::extraireDatePourAffichage($livraisonEdition);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
        $dateEditValue = $rawDate;
    } else {
        $parsed = DateTimeImmutable::createFromFormat('d/m/Y', $rawDate);
        $dateEditValue = $parsed ? $parsed->format('Y-m-d') : $rawDate;
    }
    $statutEditValue = (string) ($livraisonEdition['statut'] ?? '');
}

$nomsProduitsEditCom = '';
$totalEditComFmt = '';
$reductionEditComVal = '';
$modePaiementEditSel = '';
if ($commandeEdition !== null) {
    $idComEd = (int) $commandeEdition['id_commande'];
    $nomsProduitsEditCom = $commandeCtrl->getNomsProduitsCommande($idComEd);
    $totalEditComFmt = number_format((float) ($commandeEdition['total'] ?? 0), 2, ',', ' ');
    $reductionEditComVal = number_format((float) ($commandeEdition['reduction'] ?? 0), 2, ',', ' ');
    $modeRaw = strtolower(trim((string) ($commandeEdition['modePaiement'] ?? '')));
    $modePaiementEditSel = in_array($modeRaw, ['carte', 'cash', 'paypal'], true) ? $modeRaw : '';
}

$imgModify = is_file(__DIR__ . '/images/modify.png') ? 'images/modify.png' : 'images/modify.svg';
$imgDelete = is_file(__DIR__ . '/images/delete.png') ? 'images/delete.png' : 'images/delete.svg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Commandes &amp; livraisons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <img class="site-logo" src="../FrontOffice/images/logo.png" alt="">
</header>

<nav class="main-nav">
    <div class="nav-links">
        <a href="List-Produit.php">Produits</a>
        <a href="List-Recette.php">Recettes</a>
        <a href="List-Categorie.php">Catégories</a>
        <a href="list-com-liv.php" class="nav-link-active">Commandes &amp; livraison</a>
    </div>
</nav>

<main class="commande-wrap">
    <div class="liste-com-liv-stack">

        <?php if ($commandeEdition !== null) { ?>
            <section class="commande-panel liste-com-liv-form-panel" aria-label="Modifier une commande">
                <h2 class="liste-com-liv-form-title">Modifier la commande #<?php echo (int) $commandeEdition['id_commande']; ?></h2>
                <form method="post" action="list-com-liv.php?vue=commande">
                    <input type="hidden" name="id_commande" value="<?php echo (int) $commandeEdition['id_commande']; ?>">
                    <div class="commande-field">
                        <label for="produit-edit-com">Produit</label>
                        <input type="text" id="produit-edit-com" readonly value="<?php echo htmlspecialchars($nomsProduitsEditCom); ?>">
                    </div>
                    <div class="commande-field">
                        <label for="total-edit-com">Total</label>
                        <input type="text" id="total-edit-com" readonly value="<?php echo htmlspecialchars($totalEditComFmt); ?> DT">
                    </div>
                    <div class="commande-field">
                        <label for="reduction-edit-com">Code de Promo</label>
                        <input type="text" id="reduction-edit-com" name="reduction" placeholder="Montant ou 0" value="<?php echo htmlspecialchars($reductionEditComVal); ?>">
                    </div>
                    <div class="commande-field">
                        <label for="mode-paiement-edit">Mode de paiement</label>
                        <select id="mode-paiement-edit" name="mode_paiement" required>
                            <option value="" disabled<?php echo $modePaiementEditSel === '' ? ' selected' : ''; ?>>Choisir un mode de paiement</option>
                            <option value="carte"<?php echo $modePaiementEditSel === 'carte' ? ' selected' : ''; ?>>Carte</option>
                            <option value="cash"<?php echo $modePaiementEditSel === 'cash' ? ' selected' : ''; ?>>Cash</option>
                            <option value="paypal"<?php echo $modePaiementEditSel === 'paypal' ? ' selected' : ''; ?>>Paypal</option>
                        </select>
                        <div id="carte-paiement-details-edit" class="mode-paiement-details" hidden>
                            <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                            <div class="commande-field commande-field--nested">
                                <label for="carte-titulaire-edit">Titulaire de la carte</label>
                                <input type="text" id="carte-titulaire-edit" autocomplete="off" placeholder="Nom sur la carte">
                            </div>
                            <div class="commande-field commande-field--nested">
                                <label for="carte-numero-edit">Numéro de carte</label>
                                <input type="text" id="carte-numero-edit" inputmode="numeric" autocomplete="off" placeholder="0000 0000 0000 0000" maxlength="19">
                            </div>
                            <div class="commande-field-row">
                                <div class="commande-field commande-field--nested">
                                    <label for="carte-expiration-edit">Expiration</label>
                                    <input type="text" id="carte-expiration-edit" autocomplete="off" placeholder="MM/AA" maxlength="5">
                                </div>
                                <div class="commande-field commande-field--nested">
                                    <label for="carte-cvv-edit">CVV</label>
                                    <input type="password" id="carte-cvv-edit" autocomplete="off" placeholder="•••" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div id="cash-paiement-details-edit" class="mode-paiement-details" hidden>
                            <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                            <div class="commande-field commande-field--nested">
                                <label for="cash-montant-edit">Montant prévu (billets / pièces)</label>
                                <input type="text" id="cash-montant-edit" autocomplete="off" placeholder="Ex. 50 DT">
                            </div>
                            <div class="commande-field commande-field--nested">
                                <label for="cash-contact-edit">Téléphone pour le livreur</label>
                                <input type="tel" id="cash-contact-edit" autocomplete="off" placeholder="+216 …">
                            </div>
                            <div class="commande-field commande-field--nested">
                                <label for="cash-note-edit">Note pour la livraison</label>
                                <input type="text" id="cash-note-edit" autocomplete="off" placeholder="Sonnette, étage…">
                            </div>
                        </div>
                        <div id="paypal-paiement-details-edit" class="mode-paiement-details" hidden>
                            <p class="mode-paiement-hint">Saisie locale uniquement — non enregistrée en base.</p>
                            <div class="commande-field commande-field--nested">
                                <label for="paypal-email-edit">E-mail du compte PayPal</label>
                                <input type="email" id="paypal-email-edit" autocomplete="off" placeholder="vous@exemple.com">
                            </div>
                            <div class="commande-field commande-field--nested">
                                <label for="paypal-nom-edit">Nom affiché sur PayPal</label>
                                <input type="text" id="paypal-nom-edit" autocomplete="off" placeholder="Nom du compte">
                            </div>
                        </div>
                    </div>
                    <div class="commande-actions liste-com-liv-form-actions">
                        <a href="list-com-liv.php?vue=commande" class="btn-commande-outline">Annuler</a>
                        <button type="submit" name="maj_commande" value="1" class="btn-commande-primary">Enregistrer</button>
                    </div>
                </form>
            </section>
        <?php } elseif ($livraisonEdition !== null) { ?>
            <section class="commande-panel liste-com-liv-form-panel" aria-label="Modifier une livraison">
                <h2 class="liste-com-liv-form-title">Modifier la livraison #<?php echo (int) $livraisonEdition['id_livraison']; ?></h2>
                <form method="post" action="list-com-liv.php?vue=livraison">
                    <input type="hidden" name="id_livraison" value="<?php echo (int) $livraisonEdition['id_livraison']; ?>">
                    <div class="commande-field">
                        <label for="livraison_date">Date de livraison</label>
                        <input type="date" id="livraison_date" name="livraison_date" required value="<?php echo htmlspecialchars($dateEditValue); ?>">
                    </div>
                    <div class="commande-field">
                        <label for="statut">Statut</label>
                        <input type="text" id="statut" name="statut" required value="<?php echo htmlspecialchars($statutEditValue); ?>">
                    </div>
                    <div class="commande-actions liste-com-liv-form-actions">
                        <a href="list-com-liv.php?vue=livraison" class="btn-commande-outline">Annuler</a>
                        <button type="submit" name="maj_livraison" value="1" class="btn-commande-primary">Enregistrer</button>
                    </div>
                </form>
            </section>
        <?php } ?>

        <h1 class="liste-com-liv-title">Liste de commande et livraison</h1>

        <div class="mode-buttons">
            <button type="button" id="btn-vue-commande" class="btn-commande-primary btn-vue-toggle<?php echo $vue === 'commande' ? ' is-active' : ''; ?>" data-vue="commande">Commande</button>
            <button type="button" id="btn-vue-livraison" class="btn-commande-outline btn-vue-toggle<?php echo $vue === 'livraison' ? ' is-active' : ''; ?>" data-vue="livraison">Livraison</button>
        </div>

        <section class="commande-panel" aria-label="Tableau">
            <div id="wrap-table-commande" class="table-vue"<?php echo $vue !== 'commande' ? ' hidden' : ''; ?>>
                <div class="table-com-liv-wrapper">
                    <table class="table-com-liv">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Mode paiement</th>
                                <th>Réduction</th>
                                <th>ID livraison</th>
                                <th class="table-com-liv-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($commandes === []) { ?>
                                <tr><td colspan="7">Aucune commande.</td></tr>
                            <?php } else { ?>
                                <?php foreach ($commandes as $c) { ?>
                                    <?php $idC = (int) $c['id_commande']; ?>
                                    <tr>
                                        <td><?php echo $idC; ?></td>
                                        <td><?php echo htmlspecialchars((string) ($c['date'] ?? $c['date_commande'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float) ($c['total'] ?? 0), 2, ',', ' ')); ?> DT</td>
                                        <td><?php echo htmlspecialchars((string) ($c['modePaiement'] ?? '—')); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float) ($c['reduction'] ?? 0), 2, ',', ' ')); ?></td>
                                        <td><?php echo $c['id_livraison'] !== null && $c['id_livraison'] !== '' ? (int) $c['id_livraison'] : '—'; ?></td>
                                        <td class="table-com-liv-actions">
                                            <div class="table-com-liv-actions-inner">
                                                <a href="list-com-liv.php?modifier_commande=<?php echo $idC; ?>&vue=commande"
                                                   class="table-com-liv-icon-link"
                                                   title="Modifier"><img src="<?php echo htmlspecialchars($imgModify); ?>" alt="Modifier" width="24" height="24"></a>
                                                <a href="list-com-liv.php?supprimer_commande=<?php echo $idC; ?>&vue=commande"
                                                   class="table-com-liv-icon-link table-com-liv-delete-link"
                                                   title="Supprimer"><img src="<?php echo htmlspecialchars($imgDelete); ?>" alt="Supprimer" width="24" height="24"></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="wrap-table-livraison" class="table-vue"<?php echo $vue !== 'livraison' ? ' hidden' : ''; ?>>
                <div class="table-com-liv-wrapper">
                    <table class="table-com-liv">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th class="table-com-liv-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($livraisons === []) { ?>
                                <tr><td colspan="4">Aucune livraison.</td></tr>
                            <?php } else { ?>
                                <?php foreach ($livraisons as $liv) { ?>
                                    <?php
                                    $idL = (int) $liv['id_livraison'];
                                    $dateAff = LivraisonController::extraireDatePourAffichage($liv);
                                    $dtAff = $dateAff;
                                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateAff)) {
                                        $dtx = DateTimeImmutable::createFromFormat('Y-m-d', $dateAff);
                                        $dtAff = $dtx ? $dtx->format('d/m/Y') : $dateAff;
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $idL; ?></td>
                                        <td><?php echo htmlspecialchars($dtAff); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($liv['statut'] ?? '')); ?></td>
                                        <td class="table-com-liv-actions">
                                            <div class="table-com-liv-actions-inner">
                                                <a href="list-com-liv.php?modifier_livraison=<?php echo $idL; ?>&vue=livraison"
                                                   class="table-com-liv-icon-link"
                                                   title="Modifier"><img src="<?php echo htmlspecialchars($imgModify); ?>" alt="Modifier" width="24" height="24"></a>
                                                <a href="list-com-liv.php?supprimer_livraison=<?php echo $idL; ?>&vue=livraison"
                                                   class="table-com-liv-icon-link table-com-liv-delete-link"
                                                   title="Supprimer"><img src="<?php echo htmlspecialchars($imgDelete); ?>" alt="Supprimer" width="24" height="24"></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<div id="modal-suppression-liste" class="liste-com-liv-modal" hidden aria-hidden="true">
    <div class="liste-com-liv-modal__backdrop" tabindex="-1"></div>
    <div class="liste-com-liv-modal__box" role="dialog" aria-modal="true" aria-labelledby="modal-suppression-titre">
        <h2 id="modal-suppression-titre" class="liste-com-liv-modal__title">Supprimer cette ligne ?</h2>
        <p class="liste-com-liv-modal__text">Cette action est définitive. Confirmez-vous la suppression ?</p>
        <div class="liste-com-liv-modal__actions">
            <button type="button" class="btn-commande-outline" id="modal-suppression-annuler">Annuler</button>
            <button type="button" class="btn-commande-primary" id="modal-suppression-confirmer">Supprimer</button>
        </div>
    </div>
</div>

<footer>
    © 2026 HappyBite
</footer>

<script>
(function () {
    var vueInit = <?php echo json_encode($vue); ?>;
    var wrapC = document.getElementById('wrap-table-commande');
    var wrapL = document.getElementById('wrap-table-livraison');
    var btnC = document.getElementById('btn-vue-commande');
    var btnL = document.getElementById('btn-vue-livraison');
    if (!wrapC || !wrapL || !btnC || !btnL) return;

    function setVue(v) {
        var isC = v === 'commande';
        wrapC.hidden = !isC;
        wrapL.hidden = isC;
        btnC.classList.toggle('btn-commande-primary', isC);
        btnC.classList.toggle('btn-commande-outline', !isC);
        btnC.classList.toggle('is-active', isC);
        btnL.classList.toggle('btn-commande-primary', !isC);
        btnL.classList.toggle('btn-commande-outline', isC);
        btnL.classList.toggle('is-active', !isC);
        try {
            history.replaceState(null, '', 'list-com-liv.php?vue=' + (isC ? 'commande' : 'livraison'));
        } catch (e) {}
    }

    btnC.addEventListener('click', function () { setVue('commande'); });
    btnL.addEventListener('click', function () { setVue('livraison'); });
    setVue(vueInit);
})();
</script>
<script src="js/controles.js" defer></script>

</body>
</html>
