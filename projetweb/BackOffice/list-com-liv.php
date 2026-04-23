<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/LivraisonController.php';
require_once __DIR__ . '/includes/bo_layout_start.php';

$commandeCtrl = new CommandeController();
$livCtrl = new LivraisonController();

$vue = $_GET['vue'] ?? 'commande';
if (!in_array($vue, ['commande', 'livraison'], true)) {
    $vue = 'commande';
}

$filtreCommandeId = trim((string) ($_GET['commande_id'] ?? ''));
$filtreCommandeDate = trim((string) ($_GET['commande_date'] ?? ''));
$filtreLivraisonId = trim((string) ($_GET['livraison_id'] ?? ''));
$filtreLivraisonDate = trim((string) ($_GET['livraison_date'] ?? ''));

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

$normalizeYmd = static function (string $raw): string {
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return $raw;
    }
    $d1 = DateTimeImmutable::createFromFormat('d/m/Y', $raw);
    if ($d1 instanceof DateTimeImmutable) {
        return $d1->format('Y-m-d');
    }
    $d2 = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $raw);
    if ($d2 instanceof DateTimeImmutable) {
        return $d2->format('Y-m-d');
    }
    return $raw;
};

if ($filtreCommandeId !== '' || $filtreCommandeDate !== '') {
    $idNeedle = $filtreCommandeId !== '' ? (int) $filtreCommandeId : 0;
    $dateNeedle = $filtreCommandeDate !== '' ? $normalizeYmd($filtreCommandeDate) : '';
    $commandes = array_values(array_filter($commandes, static function (array $c) use ($idNeedle, $dateNeedle, $normalizeYmd): bool {
        if ($idNeedle > 0 && (int) ($c['id_commande'] ?? 0) !== $idNeedle) {
            return false;
        }
        if ($dateNeedle !== '') {
            $raw = (string) ($c['date'] ?? $c['date_commande'] ?? '');
            if ($raw === '') {
                return false;
            }
            $ymd = $normalizeYmd(substr($raw, 0, 10));
            if ($ymd !== $dateNeedle) {
                return false;
            }
        }
        return true;
    }));
}

if ($filtreLivraisonId !== '' || $filtreLivraisonDate !== '') {
    $idNeedle = $filtreLivraisonId !== '' ? (int) $filtreLivraisonId : 0;
    $dateNeedle = $filtreLivraisonDate !== '' ? $normalizeYmd($filtreLivraisonDate) : '';
    $livraisons = array_values(array_filter($livraisons, static function (array $l) use ($idNeedle, $dateNeedle, $normalizeYmd): bool {
        if ($idNeedle > 0 && (int) ($l['id_livraison'] ?? 0) !== $idNeedle) {
            return false;
        }
        if ($dateNeedle !== '') {
            $raw = LivraisonController::extraireDatePourAffichage($l);
            $ymd = $normalizeYmd($raw);
            if ($ymd !== $dateNeedle) {
                return false;
            }
        }
        return true;
    }));
}

$normalizeStatutKey = static function (string $statut): string {
    $statut = trim($statut);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $statut);
    $value = strtolower($ascii !== false ? $ascii : $statut);
    $value = str_replace(['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ù', 'û', 'ç'], ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u', 'c'], $value);
    if (str_contains($value, 'annul')) {
        return 'annulee';
    }
    if (str_contains($value, 'cours')) {
        return 'encours';
    }
    if (str_contains($value, 'livr')) {
        return 'livree';
    }
    if (str_contains($value, 'prepar') || str_contains($value, 'attente')) {
        return 'preparation';
    }
    if (str_starts_with($value, 'en ')) {
        return 'preparation';
    }
    return 'autre';
};

$todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');

$commandeTotal = count($commandes);
$commandeRevenue = 0.0;
$commandeToday = 0;
$commandePending = 0;
$commandesParMois = [];
$modePaiementDist = [];
foreach ($commandes as $c) {
    $commandeRevenue += (float) ($c['total'] ?? 0);

    $rawDate = (string) ($c['date'] ?? $c['date_commande'] ?? '');
    $ymd = $rawDate !== '' ? $normalizeYmd(substr($rawDate, 0, 10)) : '';
    if ($ymd === $todayYmd) {
        $commandeToday++;
    }
    if ($ymd !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
        $monthKey = substr($ymd, 0, 7);
        $commandesParMois[$monthKey] = ($commandesParMois[$monthKey] ?? 0) + 1;
    }

    $modeRaw = strtolower(trim((string) ($c['modePaiement'] ?? '')));
    if ($modeRaw === '') {
        $commandePending++;
    }
    $modeKey = $modeRaw !== '' ? $modeRaw : 'non defini';
    $modePaiementDist[$modeKey] = ($modePaiementDist[$modeKey] ?? 0) + 1;
}
ksort($commandesParMois);
ksort($modePaiementDist);

$livraisonTotal = count($livraisons);
$livraisonPreparation = 0;
$livraisonEnCours = 0;
$livraisonTerminees = 0;
$statutDist = [
    'En attente' => 0,
    'En cours' => 0,
    'Livrée' => 0,
    'Annulée' => 0,
];
$livraisonsParJour = [];
foreach ($livraisons as $l) {
    $statutKey = $normalizeStatutKey((string) ($l['statut'] ?? ''));
    if ($statutKey === 'preparation') {
        $livraisonPreparation++;
        $statutDist['En attente']++;
    } elseif ($statutKey === 'encours') {
        $livraisonEnCours++;
        $statutDist['En cours']++;
    } elseif ($statutKey === 'livree') {
        $livraisonTerminees++;
        $statutDist['Livrée']++;
    } elseif ($statutKey === 'annulee') {
        $statutDist['Annulée']++;
    }

    $dateLiv = $normalizeYmd(LivraisonController::extraireDatePourAffichage($l));
    if ($dateLiv !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateLiv)) {
        $livraisonsParJour[$dateLiv] = ($livraisonsParJour[$dateLiv] ?? 0) + 1;
    }
}
ksort($livraisonsParJour);

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
<body class="page-bo page-list-com-liv">

<?php bo_layout_start('comliv'); ?>

<main class="commande-wrap">
    <div class="liste-com-liv-stack" style="max-width: 1100px; width: 100%;">

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

        <section id="dashboard-commande" class="bo-dashboard"<?php echo $vue !== 'commande' ? ' hidden' : ''; ?>>
            <div class="bo-stats-grid">
                <article class="bo-stat-card"><h3>Total commandes</h3><p><?php echo $commandeTotal; ?></p></article>
                <article class="bo-stat-card"><h3>Chiffre d'affaires</h3><p><?php echo htmlspecialchars(number_format($commandeRevenue, 2, ',', ' ')); ?> DT</p></article>
                <article class="bo-stat-card"><h3>Commandes aujourd'hui</h3><p><?php echo $commandeToday; ?></p></article>
                <article class="bo-stat-card"><h3>Commandes en attente</h3><p><?php echo $commandePending; ?></p></article>
            </div>
            <div class="bo-charts-grid">
                <article class="bo-chart-card">
                    <h3>Évolution des commandes</h3>
                    <canvas id="chart-commandes-mois" height="120"></canvas>
                </article>
                <article class="bo-chart-card">
                    <h3>Répartition par mode de paiement</h3>
                    <canvas id="chart-modes-paiement" height="120"></canvas>
                </article>
            </div>
        </section>

        <section id="dashboard-livraison" class="bo-dashboard"<?php echo $vue !== 'livraison' ? ' hidden' : ''; ?>>
            <div class="bo-stats-grid">
                <article class="bo-stat-card"><h3>Total livraisons</h3><p><?php echo $livraisonTotal; ?></p></article>
                <article class="bo-stat-card"><h3>Livraisons en préparation</h3><p><?php echo $livraisonPreparation; ?></p></article>
                <article class="bo-stat-card"><h3>Livraisons en cours</h3><p><?php echo $livraisonEnCours; ?></p></article>
                <article class="bo-stat-card"><h3>Livraisons terminées</h3><p><?php echo $livraisonTerminees; ?></p></article>
            </div>
            <div class="bo-charts-grid">
                <article class="bo-chart-card">
                    <h3>Statut distribution</h3>
                    <canvas id="chart-statut-livraison" height="120"></canvas>
                </article>
                <article class="bo-chart-card">
                    <h3>Livraisons par jour</h3>
                    <canvas id="chart-livraisons-jour" height="120"></canvas>
                </article>
            </div>
        </section>

        <section class="bo-panel" aria-label="Recherche / filtres">
            <?php if ($vue === 'commande') { ?>
                <form method="get" action="list-com-liv.php">
                    <input type="hidden" name="vue" value="commande">
                    <div class="bo-form-row">
                        <div class="bo-field">
                            <label for="commande_id">Rechercher commande (ID)</label>
                            <input type="number" id="commande_id" name="commande_id" min="1" placeholder="Ex. 12" value="<?php echo htmlspecialchars($filtreCommandeId); ?>">
                        </div>
                        <div class="bo-field">
                            <label for="commande_date">Filtrer commande (date)</label>
                            <input type="date" id="commande_date" name="commande_date" value="<?php echo htmlspecialchars($filtreCommandeDate); ?>">
                        </div>
                        <div class="bo-field bo-field-submit">
                            <button type="submit" class="bo-btn-primary">Filtrer</button>
                        </div>
                    </div>
                </form>
            <?php } else { ?>
                <form method="get" action="list-com-liv.php">
                    <input type="hidden" name="vue" value="livraison">
                    <div class="bo-form-row">
                        <div class="bo-field">
                            <label for="livraison_id">Rechercher livraison (ID)</label>
                            <input type="number" id="livraison_id" name="livraison_id" min="1" placeholder="Ex. 4" value="<?php echo htmlspecialchars($filtreLivraisonId); ?>">
                        </div>
                        <div class="bo-field">
                            <label for="livraison_date_filter">Filtrer livraison (date)</label>
                            <input type="date" id="livraison_date_filter" name="livraison_date" value="<?php echo htmlspecialchars($filtreLivraisonDate); ?>">
                        </div>
                        <div class="bo-field bo-field-submit">
                            <button type="submit" class="bo-btn-primary">Filtrer</button>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </section>

        <section class="bo-table-wrap" aria-label="Tableau">
            <div id="wrap-table-commande" class="table-vue"<?php echo $vue !== 'commande' ? ' hidden' : ''; ?>>
                <div class="bo-table-scroll">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID utilisateur</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Mode paiement</th>
                                <th>Réduction</th>
                                <th>ID livraison</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($commandes === []) { ?>
                                <tr><td colspan="8" class="bo-empty">Aucune commande.</td></tr>
                            <?php } else { ?>
                                <?php foreach ($commandes as $c) { ?>
                                    <?php $idC = (int) $c['id_commande']; ?>
                                    <tr>
                                        <td class="bo-td-center"><?php echo $idC; ?></td>
                                        <td class="bo-td-center"><?php echo 1; ?></td>
                                        <td class="bo-td-center"><?php echo htmlspecialchars((string) ($c['date'] ?? $c['date_commande'] ?? '')); ?></td>
                                        <td class="bo-td-center"><?php echo htmlspecialchars(number_format((float) ($c['total'] ?? 0), 2, ',', ' ')); ?> DT</td>
                                        <td class="bo-td-center"><?php echo htmlspecialchars((string) ($c['modePaiement'] ?? '—')); ?></td>
                                        <td class="bo-td-center"><?php echo htmlspecialchars(number_format((float) ($c['reduction'] ?? 0), 2, ',', ' ')); ?></td>
                                        <td class="bo-td-center"><?php echo $c['id_livraison'] !== null && $c['id_livraison'] !== '' ? (int) $c['id_livraison'] : '—'; ?></td>
                                        <td class="bo-td-center">
                                            <span class="bo-icon-actions">
                                                <a href="Edit-commande.php?id=<?php echo $idC; ?>" title="Modifier" aria-label="Modifier">
                                                    <img src="<?php echo htmlspecialchars($imgModify); ?>" alt="" width="24" height="24">
                                                </a>
                                                <a href="list-com-liv.php?supprimer_commande=<?php echo $idC; ?>&vue=commande" title="Supprimer" aria-label="Supprimer">
                                                    <img src="<?php echo htmlspecialchars($imgDelete); ?>" alt="" width="24" height="24">
                                                </a>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="wrap-table-livraison" class="table-vue"<?php echo $vue !== 'livraison' ? ' hidden' : ''; ?>>
                <div class="bo-table-scroll">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID utilisateur</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($livraisons === []) { ?>
                                <tr><td colspan="5" class="bo-empty">Aucune livraison.</td></tr>
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
                                    $statut = trim((string) ($liv['statut'] ?? ''));
                                    $statutKey = $normalizeStatutKey($statut);
                                    $statutClass = 'bo-statut--muted';
                                    if ($statutKey === 'annulee') {
                                        $statutClass = 'bo-statut--annulee';
                                    } elseif ($statutKey === 'preparation') {
                                        $statutClass = 'bo-statut--preparation';
                                    } elseif ($statutKey === 'encours') {
                                        $statutClass = 'bo-statut--encours';
                                    } elseif ($statutKey === 'livree') {
                                        $statutClass = 'bo-statut--livree';
                                    }
                                    ?>
                                    <tr>
                                        <td class="bo-td-center"><?php echo $idL; ?></td>
                                        <td class="bo-td-center"><?php echo 1; ?></td>
                                        <td class="bo-td-center"><?php echo htmlspecialchars($dtAff); ?></td>
                                        <td class="bo-td-center">
                                            <span class="bo-statut <?php echo htmlspecialchars($statutClass); ?>">
                                                <?php echo htmlspecialchars($statut); ?>
                                            </span>
                                        </td>
                                        <td class="bo-td-center">
                                            <span class="bo-icon-actions">
                                                <a href="Edit-livraison.php?id=<?php echo $idL; ?>" title="Modifier" aria-label="Modifier">
                                                    <img src="<?php echo htmlspecialchars($imgModify); ?>" alt="" width="24" height="24">
                                                </a>
                                                <a href="list-com-liv.php?supprimer_livraison=<?php echo $idL; ?>&vue=livraison" title="Supprimer" aria-label="Supprimer">
                                                    <img src="<?php echo htmlspecialchars($imgDelete); ?>" alt="" width="24" height="24">
                                                </a>
                                            </span>
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

<?php bo_layout_end(); ?>

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var vueInit = <?php echo json_encode($vue); ?>;
    var wrapC = document.getElementById('wrap-table-commande');
    var wrapL = document.getElementById('wrap-table-livraison');
    var dashC = document.getElementById('dashboard-commande');
    var dashL = document.getElementById('dashboard-livraison');
    var btnC = document.getElementById('btn-vue-commande');
    var btnL = document.getElementById('btn-vue-livraison');
    if (!wrapC || !wrapL || !dashC || !dashL || !btnC || !btnL) return;

    var charts = [];
    if (window.Chart) {
        charts.push(new Chart(document.getElementById('chart-commandes-mois'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($commandesParMois)); ?>,
                datasets: [{
                    label: 'Commandes',
                    data: <?php echo json_encode(array_values($commandesParMois)); ?>,
                    borderColor: '#2c7e34',
                    backgroundColor: 'rgba(44,126,52,0.18)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2.2 }
        }));

        charts.push(new Chart(document.getElementById('chart-modes-paiement'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_keys($modePaiementDist))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($modePaiementDist)); ?>,
                    backgroundColor: ['#ef5350', '#42a5f5', '#ffca28', '#66bb6a', '#ab47bc', '#ff7043', '#26c6da']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2.2 }
        }));

        charts.push(new Chart(document.getElementById('chart-statut-livraison'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($statutDist)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($statutDist)); ?>,
                    backgroundColor: ['#ffd54f', '#64b5f6', '#81c784', '#ef5350']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2.2 }
        }));

        charts.push(new Chart(document.getElementById('chart-livraisons-jour'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($livraisonsParJour)); ?>,
                datasets: [{
                    label: 'Livraisons',
                    data: <?php echo json_encode(array_values($livraisonsParJour)); ?>,
                    borderColor: '#1976d2',
                    backgroundColor: 'rgba(25,118,210,0.18)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, aspectRatio: 2.2 }
        }));
    }

    function setVue(v) {
        var isC = v === 'commande';
        wrapC.hidden = !isC;
        wrapL.hidden = isC;
        dashC.hidden = !isC;
        dashL.hidden = isC;
        btnC.classList.toggle('btn-commande-primary', isC);
        btnC.classList.toggle('btn-commande-outline', !isC);
        btnC.classList.toggle('is-active', isC);
        btnL.classList.toggle('btn-commande-primary', !isC);
        btnL.classList.toggle('btn-commande-outline', isC);
        btnL.classList.toggle('is-active', !isC);
        if (charts.length > 0) {
            setTimeout(function () {
                charts.forEach(function (chart) { chart.resize(); });
            }, 0);
        }
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
