<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bo_require_admin.php';
require_once __DIR__ . '/includes/bo_layout_start.php';
require_once __DIR__ . '/../Controllers/PostController.php';
require_once __DIR__ . '/../Controllers/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();
$posts = $postController->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Posts - BackOffice HappyBite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-dashboard-posts .commande-wrap { padding-top: 8px; }
    </style>
</head>
<body class="page-bo page-list-com-liv page-dashboard-posts">
<?php bo_layout_start('post'); ?>

<main class="commande-wrap">
    <div class="liste-com-liv-stack" style="max-width: 1100px; width: 100%;">
        <div class="liste-com-liv-topbar">
            <div class="mode-buttons">
                <a href="list_posts.php" class="btn-commande-outline btn-vue-toggle">Post</a>
                <a href="list_commentaires.php" class="btn-commande-outline btn-vue-toggle">Commentaire</a>
                <a href="dashboard_posts.php" class="btn-commande-primary is-active btn-vue-toggle">Dashboard</a>
            </div>
        </div>

        <div class="liste-com-liv-title-row">
            <div>
                <h1 class="liste-com-liv-title">Dashboard des posts</h1>
                <p class="liste-com-liv-subtitle">Statistiques et graphiques</p>
            </div>
        </div>

        <?php if (!empty($posts)) { ?>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-3 p-3" style="background:#fafbfc;">
                                <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                                    Total interactions
                                </p>
                                <div class="d-flex justify-content-center mb-3">
                                    <canvas id="statsChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                                </div>
                                <div id="chartLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-3 p-3" style="background:#fafbfc;">
                                <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                                    Likes par post
                                </p>
                                <div class="d-flex justify-content-center mb-3">
                                    <canvas id="likesChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                                </div>
                                <div id="likesLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="border rounded-3 p-3" style="background:#fafbfc;">
                                <p class="text-muted fw-semibold mb-3" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;">
                                    Commentaires par post
                                </p>
                                <div class="d-flex justify-content-center mb-3">
                                    <canvas id="commentsChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas>
                                </div>
                                <div id="commentsLegend" class="d-flex flex-wrap gap-1 justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <table id="postsDataTable" style="display:none;">
                <tbody>
                <?php foreach ($posts as $post) { ?>
                    <?php $commentsCount = count($commentaireController->getByPostId($post['id'])); ?>
                    <tr
                        data-id="<?php echo (int) $post['id']; ?>"
                        data-likes="<?php echo (int) $post['nombreLikes']; ?>"
                        data-comments="<?php echo $commentsCount; ?>"
                    ></tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucune donnée disponible</h6>
                </div>
            </div>
        <?php } ?>
    </div>
</main>

<?php bo_layout_end(); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function buildCharts() {
    const table = document.getElementById('postsDataTable');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tr'));
    if (!rows.length) return;

    const palette = ['#2f6f57','#4a9070','#6dbf9e','#f0a500','#e05c5c','#5b8dee','#a78bfa','#94a3b8'];
    const likePalette = ['#f0a500','#f5bc3a','#f7cc6a','#e8960a','#c97d00','#ffd966','#ffe8a0','#fbecc8'];
    const commentPalette = ['#0ea5e9','#38bdf8','#0284c7','#7dd3fc','#0369a1','#60a5fa','#3b82f6','#93c5fd'];
    const TOP = 6;

    const allData = rows.map((r) => {
        const id = r.dataset.id || '?';
        const likes = parseInt(r.dataset.likes, 10) || 0;
        const comments = parseInt(r.dataset.comments, 10) || 0;
        return {
            label: 'Post #' + id,
            likes: likes,
            comments: comments,
            combined: likes + comments
        };
    });

    function prepData(key) {
        const sorted = [...allData].sort((a, b) => b[key] - a[key]);
        const nonZero = sorted.filter((d) => d[key] > 0);
        const source = nonZero.length ? nonZero : sorted;

        if (source.length > TOP) {
            const top = source.slice(0, TOP);
            const rest = source.slice(TOP).reduce((sum, d) => sum + d[key], 0);
            return {
                labels: [...top.map((d) => d.label), 'Autres'],
                values: [...top.map((d) => d[key]), rest]
            };
        }
        return {
            labels: source.map((d) => d.label),
            values: source.map((d) => d[key])
        };
    }

    function makeChart(canvasId, labels, values, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const hasData = values.some((v) => v > 0);
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: hasData ? labels : ['Aucune donnee'],
                datasets: [{
                    data: hasData ? values : [1],
                    backgroundColor: hasData ? colors.slice(0, labels.length) : ['#e9ecef'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: hasData ? 8 : 0
                }]
            },
            options: {
                responsive: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: hasData,
                        callbacks: {
                            label: function (ctx) {
                                return ' ' + ctx.label + ': ' + ctx.parsed;
                            }
                        }
                    }
                }
            }
        });
    }

    function makeLegend(legendId, labels, values, colors, hasData) {
        const el = document.getElementById(legendId);
        if (!el) return;
        if (!hasData) {
            el.innerHTML = '<span class="text-muted small">Aucune donnee</span>';
            return;
        }
        labels.forEach((label, i) => {
            const pill = document.createElement('div');
            pill.style.cssText = 'display:inline-flex;align-items:center;gap:5px;background:#fff;border-radius:20px;padding:3px 8px 3px 5px;font-size:0.72rem;font-weight:600;border:1px solid #e9ecef;white-space:nowrap;margin:2px;';
            pill.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:' + colors[i] + ';flex-shrink:0;display:inline-block;"></span>' + label + '<span style="background:' + colors[i] + ';color:#fff;border-radius:10px;padding:1px 6px;font-size:0.68rem;margin-left:3px;">' + values[i] + '</span>';
            el.appendChild(pill);
        });
    }

    const c = prepData('combined');
    makeChart('statsChart', c.labels, c.values, palette);
    makeLegend('chartLegend', c.labels, c.values, palette, c.values.some((v) => v > 0));

    const l = prepData('likes');
    makeChart('likesChart', l.labels, l.values, likePalette);
    makeLegend('likesLegend', l.labels, l.values, likePalette, l.values.some((v) => v > 0));

    const cm = prepData('comments');
    makeChart('commentsChart', cm.labels, cm.values, commentPalette);
    makeLegend('commentsLegend', cm.labels, cm.values, commentPalette, cm.values.some((v) => v > 0));
})();
</script>
</body>
</html>
