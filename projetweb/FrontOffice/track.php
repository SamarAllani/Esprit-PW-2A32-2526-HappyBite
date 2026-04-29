<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/LivraisonController.php';
require_once __DIR__ . '/includes/panier_session.php';

panier_ensure_session();

$commandeCtrl = new CommandeController();
$livraisonCtrl = new LivraisonController();

$idCommande = 0;
$commande = null;
if (isset($_GET['id_commande'])) {
    $idCommande = (int) $_GET['id_commande'];
    $commande = $idCommande > 0 ? $commandeCtrl->getCommandeById($idCommande) : null;
} else {
    $latest = $commandeCtrl->listCommandes();
    foreach ($latest as $row) {
        if (!empty($row['id_livraison'])) {
            $commande = $row;
            break;
        }
    }
    if ($commande === null) {
        $commande = $commandeCtrl->getLatestCommande();
    }
    $idCommande = (int) ($commande['id_commande'] ?? 0);
}
$livraison = null;
if ($commande !== null && !empty($commande['id_livraison'])) {
    $livraison = $livraisonCtrl->getLivraisonById((int) $commande['id_livraison']);
}

$statusRaw = (string) ($livraison['statut'] ?? '');
$statusNorm = strtolower(trim($statusRaw));
$statusNorm = str_replace(
    ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ù', 'û', 'ç'],
    ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u', 'c'],
    $statusNorm
);

$statusKey = 'preparation';
if (str_contains($statusNorm, 'livr')) {
    $statusKey = 'livree';
} elseif (str_contains($statusNorm, 'cours')) {
    $statusKey = 'encours';
} elseif (str_contains($statusNorm, 'annul')) {
    $statusKey = 'annulee';
}

$dateStr = $livraison ? LivraisonController::extraireDatePourAffichage($livraison) : '';
$dateAffiche = $dateStr;
$dt = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
if ($dt instanceof DateTimeImmutable) {
    $dateAffiche = $dt->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HappyBite — Suivi de commande</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .track-wrap {
            padding: 14px;
            display: flex;
            justify-content: center;
        }
        .track-shell {
            position: relative;
            width: min(1100px, 100%);
            height: min(78vh, 760px);
            min-height: 520px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #d4e6d6;
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.11);
            background: #edf5ee;
        }
        #track-map {
            width: 100%;
            height: 100%;
        }
        .track-legend {
            position: absolute;
            top: 18px;
            left: 18px;
            z-index: 700;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #e2e2e2;
            border-radius: 12px;
            padding: 12px 14px;
            min-width: 210px;
        }
        .track-legend-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
            color: #2c2c2c;
            font-weight: 500;
        }
        .track-legend-icon {
            width: 26px;
            height: 26px;
            object-fit: contain;
        }
        .track-card {
            position: absolute;
            right: 18px;
            bottom: 18px;
            z-index: 700;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #dde6de;
            border-radius: 12px;
            padding: 14px 16px;
            min-width: 270px;
        }
        .track-card h2 {
            margin: 0 0 8px;
            color: #2e7d32;
            font-size: 1.25rem;
        }
        .track-card-line {
            margin: 0 0 10px;
            color: #2d2d2d;
            font-weight: 500;
        }
        .track-progress {
            height: 10px;
            background: #e5e5e5;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }
        .track-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #43a047, #2e7d32);
            border-radius: 999px;
        }
        .track-progress-label {
            margin-top: 8px;
            text-align: right;
            font-weight: 700;
            color: #2e7d32;
        }
        .track-notif {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 710;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #2e7d32;
            color: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            box-shadow: 0 10px 24px rgba(46, 125, 50, 0.32);
            font-weight: 600;
        }
        .track-notif img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
        .track-empty {
            padding: 24px;
            text-align: center;
            color: #2c3f32;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php
$nav_active = 'panier';
require __DIR__ . '/includes/nav_front.php';
?>

<main class="track-wrap">
    <?php if ($commande === null || $livraison === null) { ?>
        <section class="commande-panel track-empty">
            Aucune livraison en cours à suivre.
        </section>
    <?php } else { ?>
        <section class="track-shell" aria-label="Suivi de livraison">
            <div id="track-map"></div>

            <aside class="track-legend">
                <div class="track-legend-row"><img src="images/store.png" alt="" class="track-legend-icon"><span>Magasin (Départ)</span></div>
                <div class="track-legend-row"><img src="images/order.png" alt="" class="track-legend-icon"><span>Livraison en cours</span></div>
                <div class="track-legend-row"><img src="images/house.png" alt="" class="track-legend-icon"><span>Votre adresse</span></div>
            </aside>

            <?php if ($statusKey === 'livree') { ?>
                <div class="track-notif">
                    <img src="images/success.svg" alt="">
                    <span>Votre commande est arrivée</span>
                </div>
            <?php } ?>

            <article class="track-card">
                <h2><?php echo htmlspecialchars($statusRaw); ?></h2>
                <p class="track-card-line">Commande #<?php echo (int) $idCommande; ?> - Livraison prévue : <?php echo htmlspecialchars($dateAffiche !== '' ? $dateAffiche : 'N/A'); ?></p>
                <?php
                $progress = 10;
                if ($statusKey === 'encours') {
                    $progress = 60;
                } elseif ($statusKey === 'livree') {
                    $progress = 100;
                } elseif ($statusKey === 'annulee') {
                    $progress = 0;
                }
                ?>
                <div class="track-progress"><div class="track-progress-bar" style="width: <?php echo $progress; ?>%"></div></div>
                <div class="track-progress-label"><?php echo $progress; ?>%</div>
            </article>
        </section>
    <?php } ?>
</main>

<footer>
    © 2026 HappyBite
</footer>

<?php if ($commande !== null && $livraison !== null) { ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var status = <?php echo json_encode($statusKey); ?>;
    var map = L.map('track-map', {
        zoomControl: true,
        attributionControl: true
    }).setView([36.8065, 10.1815], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Two points kept intentionally far apart.
    var storeLatLng = [36.8182, 10.1322];
    var houseLatLng = [36.7662, 10.1880];

    var storeIcon = L.icon({
        iconUrl: 'images/store.png',
        iconSize: [40, 40],
        iconAnchor: [20, 36]
    });
    var houseIcon = L.icon({
        iconUrl: 'images/house.png',
        iconSize: [40, 40],
        iconAnchor: [20, 36]
    });
    var orderIcon = L.icon({
        iconUrl: 'images/order.png',
        iconSize: [42, 42],
        iconAnchor: [21, 37]
    });

    L.marker(storeLatLng, { icon: storeIcon }).addTo(map);
    L.marker(houseLatLng, { icon: houseIcon }).addTo(map);

    var routeLine = L.polyline([storeLatLng, houseLatLng], {
        color: '#5f6368',
        weight: 4,
        opacity: 0.85,
        dashArray: '9,9'
    }).addTo(map);

    map.fitBounds(routeLine.getBounds().pad(0.25));

    var orderLatLng = storeLatLng;
    if (status === 'livree') {
        orderLatLng = houseLatLng;
    } else if (status === 'encours') {
        // Midpoint for initial placement before animation.
        orderLatLng = [
            storeLatLng[0] + (houseLatLng[0] - storeLatLng[0]) * 0.58,
            storeLatLng[1] + (houseLatLng[1] - storeLatLng[1]) * 0.58
        ];
    }

    var orderMarker = L.marker(orderLatLng, { icon: orderIcon }).addTo(map);

    if (status === 'encours') {
        var t = 0.55;
        var target = 0.92;
        var step = 0.004;
        var timer = setInterval(function () {
            t += step;
            if (t >= target) {
                clearInterval(timer);
                t = target;
            }
            var lat = storeLatLng[0] + (houseLatLng[0] - storeLatLng[0]) * t;
            var lng = storeLatLng[1] + (houseLatLng[1] - storeLatLng[1]) * t;
            orderMarker.setLatLng([lat, lng]);
        }, 120);
    }
})();
</script>
<?php } ?>
</body>
</html>
