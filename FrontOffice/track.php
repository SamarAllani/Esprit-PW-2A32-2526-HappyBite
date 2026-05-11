<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/CommandeController.php';
require_once __DIR__ . '/../Controllers/LivraisonController.php';
require_once __DIR__ . '/includes/panier_session.php';

panier_ensure_session();

$loggedIn = !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId = $loggedIn ? (int) ($_SESSION['user_id'] ?? 0) : 0;

if (!$loggedIn || $userId < 1) {
    header('Location: auth/login.php');
    exit;
}

$commandeCtrl = new CommandeController();
$livraisonCtrl = new LivraisonController();

$commandesSuivi = $commandeCtrl->listCommandesAvecLivraisonPourUtilisateur($userId);

$idCommande = 0;
$commande = null;

if (isset($_GET['id_commande'])) {
    $want = (int) $_GET['id_commande'];
    if ($want > 0 && $commandeCtrl->commandeAppartientAUtilisateur($want, $userId)) {
        $row = $commandeCtrl->getCommandeById($want);
        if (is_array($row) && !empty($row['id_livraison'])) {
            $commande = $row;
            $idCommande = $want;
        }
    }
}

if ($commande === null) {
    $commande = $commandeCtrl->getDerniereCommandeAvecLivraisonPourUtilisateur($userId);
    $idCommande = (int) ($commande['id_commande'] ?? 0);
}

if ($commande === null && $commandesSuivi !== []) {
    $commande = $commandesSuivi[0];
    $idCommande = (int) ($commande['id_commande'] ?? 0);
}

$livraison = null;
if ($commande !== null && !empty($commande['id_livraison'])) {
    $livraison = $livraisonCtrl->getLivraisonById((int) $commande['id_livraison']);
}

$trackSelectOptions = [];
foreach ($commandesSuivi as $rowC) {
    $idc = (int) ($rowC['id_commande'] ?? 0);
    $idl = (int) ($rowC['id_livraison'] ?? 0);
    if ($idc < 1 || $idl < 1) {
        continue;
    }
    $livOpt = $livraisonCtrl->getLivraisonById($idl);
    $statLabel = $livOpt !== null ? (string) ($livOpt['statut'] ?? '—') : '—';
    $dateRaw = $livOpt !== null ? LivraisonController::extraireDatePourAffichage($livOpt) : '';
    $dateLabel = $dateRaw;
    $dtOpt = DateTimeImmutable::createFromFormat('Y-m-d', $dateRaw);
    if ($dtOpt instanceof DateTimeImmutable) {
        $dateLabel = $dtOpt->format('d/m/Y');
    }
    $trackSelectOptions[] = [
        'id_commande' => $idc,
        'label' => sprintf(
            'Commande #%d — %s — %s',
            $idc,
            $dateLabel !== '' ? $dateLabel : 'N/A',
            $statLabel
        ),
        'selected' => ($idc === $idCommande),
    ];
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

// Magasin fixe : Esprit prépa, Pôle Technologique (zone Chotrana II / 2083) — coordonnées alignées zone tertiaire OSM
$shopLat = 36.8996184;
$shopLng = 10.1929178;
$shopLatLngJson = json_encode([(float) $shopLat, (float) $shopLng]);
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
        .track-stack {
            width: min(1100px, 100%);
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
        .track-back-home {
            display: inline-block;
            text-decoration: none;
            white-space: nowrap;
            margin-bottom: 10px;
        }
        .track-legend {
            position: absolute;
            top: 18px;
            left: 18px;
            z-index: 700;
            background: rgba(255, 255, 255, 0.30);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 226, 226, 0.75);
            border-radius: 12px;
            padding: 12px 14px;
            min-width: 210px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
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
            background: rgba(255, 255, 255, 0.30);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(221, 230, 222, 0.75);
            border-radius: 12px;
            padding: 14px 16px;
            min-width: 270px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
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
        .track-geo-status {
            margin-top: 10px;
            font-size: 0.78rem;
            line-height: 1.35;
            color: #1b5e20;
            font-weight: 600;
        }
        .track-geo-status--err {
            color: #c62828;
        }
        /* À gauche du contrôle zoom Leaflet (+/-) en haut à droite */
        .track-commande-toolbar {
            position: absolute;
            top: 10px;
            right: 56px;
            z-index: 1000;
            pointer-events: auto;
        }
        .track-commande-select {
            min-width: 196px;
            max-width: min(50vw, 320px);
            padding: 8px 12px;
            font-family: "Poppins", system-ui, sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1b3a1f;
            border: 1px solid rgba(210, 222, 214, 0.9);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.28);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
            cursor: pointer;
        }
        .track-commande-select:focus {
            outline: 2px solid rgba(46, 125, 50, 0.45);
            outline-offset: 2px;
        }
        @media (max-width: 520px) {
            .track-commande-toolbar {
                right: 52px;
                left: 12px;
                max-width: none;
            }
            .track-commande-select {
                width: 100%;
                max-width: none;
            }
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
            Aucune livraison à suivre pour votre compte. Finalisez une commande avec paiement pour créer une livraison, puis revenez sur cette page.
        </section>
    <?php } else { ?>
        <div class="track-stack">
            <div>
                <a href="Home.php" class="btn-commande-outline track-back-home">Retourner à l'accueil</a>
            </div>
            <section class="track-shell" aria-label="Suivi de livraison">
                <div id="track-map"></div>

                <?php if ($trackSelectOptions !== []) { ?>
                <div class="track-commande-toolbar">
                    <select id="track-commande-select" class="track-commande-select" aria-label="Choisir une commande à suivre" title="Choisir une commande à suivre">
                        <?php foreach ($trackSelectOptions as $opt) { ?>
                            <option value="<?php echo (int) $opt['id_commande']; ?>"<?php echo !empty($opt['selected']) ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>

                <aside class="track-legend">
                    <div class="track-legend-row"><img src="images/store.png" alt="" class="track-legend-icon"><span>Magasin (départ)</span></div>
                    <div class="track-legend-row"><img src="images/order.png" alt="" class="track-legend-icon"><span>Livraison : <?php echo htmlspecialchars($statusRaw !== '' ? $statusRaw : '—'); ?></span></div>
                    <div class="track-legend-row"><img src="images/house.png" alt="" class="track-legend-icon"><span>Votre position</span></div>
                    <p id="track-geo-status" class="track-geo-status" hidden></p>
                </aside>

                <?php if ($statusKey === 'livree') { ?>
                    <div class="track-notif">
                        <img src="images/success.svg" alt="">
                        <span>Votre commande est arrivée</span>
                    </div>
                <?php } ?>

                <article class="track-card">
                    <h2><?php echo htmlspecialchars($statusRaw); ?></h2>
                    <p class="track-card-line">Livraison prévue : <?php echo htmlspecialchars($dateAffiche !== '' ? $dateAffiche : 'N/A'); ?></p>
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
        </div>
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
    var storeLatLng = <?php echo $shopLatLngJson; ?>;
    var geoStatusEl = document.getElementById('track-geo-status');

    function setGeoStatus(msg, isErr) {
        if (!geoStatusEl) return;
        geoStatusEl.hidden = false;
        geoStatusEl.textContent = msg;
        geoStatusEl.classList.toggle('track-geo-status--err', !!isErr);
    }

    var map = L.map('track-map', {
        zoomControl: false,
        attributionControl: true
    }).setView(storeLatLng, 14);

    L.control.zoom({ position: 'topright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

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

    var storeMarker = L.marker(storeLatLng, { icon: storeIcon })
        .addTo(map)
        .bindPopup('Magasin (départ)');

    function haversineMeters(lat1, lon1, lat2, lon2) {
        var R = 6371000;
        var toRad = Math.PI / 180;
        var dLat = (lat2 - lat1) * toRad;
        var dLon = (lon2 - lon1) * toRad;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * toRad) * Math.cos(lat2 * toRad) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    /** @param {number[][]} coords Leaflet [lat,lng][] */
    function pointAlongRoute(coords, t) {
        if (!coords || coords.length === 0) return storeLatLng;
        if (coords.length === 1 || t <= 0) return coords[0];
        if (t >= 1) return coords[coords.length - 1];
        var segs = [];
        var total = 0;
        for (var i = 0; i < coords.length - 1; i++) {
            var d = haversineMeters(coords[i][0], coords[i][1], coords[i + 1][0], coords[i + 1][1]);
            segs.push({ i0: i, len: d, start: total });
            total += d;
        }
        if (total <= 0) return coords[Math.floor(t * (coords.length - 1))];
        var dist = t * total;
        for (var j = 0; j < segs.length; j++) {
            var s = segs[j];
            var end = s.start + s.len;
            if (dist <= end || j === segs.length - 1) {
                var along = s.len > 0 ? (dist - s.start) / s.len : 0;
                if (!isFinite(along)) along = 0;
                var a = coords[s.i0];
                var b = coords[s.i0 + 1];
                return [a[0] + (b[0] - a[0]) * along, a[1] + (b[1] - a[1]) * along];
            }
        }
        return coords[coords.length - 1];
    }

    function fetchOsrmRoute(fromLL, toLL) {
        var url = 'https://router.project-osrm.org/route/v1/driving/' +
            fromLL[1] + ',' + fromLL[0] + ';' + toLL[1] + ',' + toLL[0] +
            '?overview=full&geometries=geojson';
        return fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.routes || !data.routes[0] || !data.routes[0].geometry) {
                    return null;
                }
                var g = data.routes[0].geometry;
                if (!g.coordinates || !g.coordinates.length) return null;
                return g.coordinates.map(function (c) {
                    return [c[1], c[0]];
                });
            })
            .catch(function () { return null; });
    }

    function straightFallback(fromLL, toLL) {
        return [fromLL, toLL];
    }

    function buildMap(houseLatLng, routeCoords) {
        var houseMarker = L.marker(houseLatLng, { icon: houseIcon })
            .addTo(map)
            .bindPopup('Votre position (géolocalisation)');

        var routeLine = L.polyline(routeCoords, {
            color: '#5f6368',
            weight: 5,
            opacity: 0.88,
            dashArray: '10,8'
        }).addTo(map);

        try {
            map.fitBounds(routeLine.getBounds().pad(0.18));
        } catch (e) {
            map.setView(storeLatLng, 13);
        }

        var startT = 0;
        var orderLatLng;
        if (status === 'livree') {
            startT = 1;
            orderLatLng = pointAlongRoute(routeCoords, 1);
        } else if (status === 'encours') {
            startT = 0.52;
            orderLatLng = pointAlongRoute(routeCoords, startT);
        } else {
            startT = 0;
            orderLatLng = pointAlongRoute(routeCoords, 0);
        }

        var orderMarker = L.marker(orderLatLng, { icon: orderIcon }).addTo(map);

        if (status === 'encours') {
            var t = startT;
            var target = 0.96;
            var step = 0.0035;
            var timer = setInterval(function () {
                t += step;
                if (t >= target) {
                    clearInterval(timer);
                    t = target;
                }
                orderMarker.setLatLng(pointAlongRoute(routeCoords, t));
            }, 110);
        }
    }

    setGeoStatus('Recherche de votre position…', false);

    if (!navigator.geolocation) {
        setGeoStatus('Géolocalisation non supportée par ce navigateur.', true);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            var houseLatLng = [pos.coords.latitude, pos.coords.longitude];
            setGeoStatus(
                'Itinéraire calculé depuis le magasin vers votre position (précision ~' +
                Math.round(pos.coords.accuracy || 0) + ' m).',
                false
            );

            fetchOsrmRoute(storeLatLng, houseLatLng).then(function (routeCoords) {
                if (!routeCoords || routeCoords.length < 2) {
                    setGeoStatus('Itinéraire routier indisponible : ligne droite affichée.', false);
                    routeCoords = straightFallback(storeLatLng, houseLatLng);
                } else {
                    var first = routeCoords[0];
                    var last = routeCoords[routeCoords.length - 1];
                    if (haversineMeters(storeLatLng[0], storeLatLng[1], first[0], first[1]) > 35) {
                        routeCoords = [storeLatLng].concat(routeCoords);
                    }
                    if (haversineMeters(last[0], last[1], houseLatLng[0], houseLatLng[1]) > 35) {
                        routeCoords = routeCoords.concat([houseLatLng]);
                    }
                }
                buildMap(houseLatLng, routeCoords);
            });
        },
        function (err) {
            var msg = 'Impossible d’obtenir votre position. Autorisez la géolocalisation pour voir l’itinéraire.';
            if (err && err.code === 1) {
                msg = 'Géolocalisation refusée. Activez-la dans les paramètres du site pour afficher votre trajet.';
            }
            var fallback = [
                storeLatLng[0] - 0.004,
                storeLatLng[1] + 0.006
            ];
            setGeoStatus(msg + ' (aperçu avec un trajet fictif.)', true);
            fetchOsrmRoute(storeLatLng, fallback).then(function (routeCoords) {
                if (!routeCoords || routeCoords.length < 2) {
                    routeCoords = straightFallback(storeLatLng, fallback);
                } else {
                    var firstE = routeCoords[0];
                    var lastE = routeCoords[routeCoords.length - 1];
                    if (haversineMeters(storeLatLng[0], storeLatLng[1], firstE[0], firstE[1]) > 35) {
                        routeCoords = [storeLatLng].concat(routeCoords);
                    }
                    if (haversineMeters(lastE[0], lastE[1], fallback[0], fallback[1]) > 35) {
                        routeCoords = routeCoords.concat([fallback]);
                    }
                }
                buildMap(fallback, routeCoords);
            });
        },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }
    );
})();
</script>
<?php if ($trackSelectOptions !== []) { ?>
<script>
(function () {
    var sel = document.getElementById('track-commande-select');
    if (!sel) return;
    sel.addEventListener('change', function () {
        var v = sel.value;
        if (v) {
            window.location.href = 'track.php?id_commande=' + encodeURIComponent(v);
        }
    });
})();
</script>
<?php } ?>
<?php } ?>
</body>
</html>
