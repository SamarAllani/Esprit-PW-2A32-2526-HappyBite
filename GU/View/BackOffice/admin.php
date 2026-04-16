<?php
require '../FrontOffice/db_connection.php';

// Récupérer tous les utilisateurs depuis la base de données
try {
    $stmt = $pdo->prepare("SELECT id, prenom, nom, email, motDePasse, statut, created_at FROM utilisateur ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// Traiter les actions (DELETE, BLOCK, UNBLOCK)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        
        if ($_POST['action'] === 'delete') {
            try {
                $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = ?");
                $stmt->execute([$user_id]);
                header("Location: admin.php?success=deleted");
                exit;
            } catch (PDOException $e) {
                $error_message = "Erreur lors de la suppression : " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'block') {
            try {
                $stmt = $pdo->prepare("UPDATE utilisateur SET statut = 'bloqué' WHERE id = ?");
                $stmt->execute([$user_id]);
                header("Location: admin.php?success=blocked");
                exit;
            } catch (PDOException $e) {
                $error_message = "Erreur lors du blocage : " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'unblock') {
            try {
                $stmt = $pdo->prepare("UPDATE utilisateur SET statut = 'actif' WHERE id = ?");
                $stmt->execute([$user_id]);
                header("Location: admin.php?success=unblocked");
                exit;
            } catch (PDOException $e) {
                $error_message = "Erreur lors du déblocage : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>

<html class="light" lang="fr"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>GreenBite Admin - Gestion des Utilisateurs</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-error": "#ffffff",
                        "surface-tint": "#006e1c",
                        "on-error-container": "#93000a",
                        "tertiary-fixed": "#bdefbe",
                        "surface-container-lowest": "#ffffff",
                        "primary-container": "#4caf50",
                        "primary-fixed-dim": "#78dc77",
                        "error-container": "#ffdad6",
                        "surface-bright": "#f9f9f9",
                        "on-background": "#1a1c1c",
                        "secondary-container": "#ff9800",
                        "tertiary-container": "#77a67a",
                        "on-tertiary-container": "#0d3b19",
                        "primary-fixed": "#94f990",
                        "inverse-surface": "#2f3131",
                        "secondary": "#8b5000",
                        "inverse-primary": "#78dc77",
                        "on-tertiary-fixed-variant": "#24502c",
                        "surface-variant": "#e2e2e2",
                        "on-tertiary-fixed": "#002109",
                        "inverse-on-surface": "#f1f1f1",
                        "outline": "#6f7a6b",
                        "on-secondary": "#ffffff",
                        "on-secondary-fixed": "#2c1600",
                        "on-primary-fixed-variant": "#005313",
                        "primary": "#006e1c",
                        "error": "#ba1a1a",
                        "on-primary": "#ffffff",
                        "surface-container-low": "#f3f3f3",
                        "on-tertiary": "#ffffff",
                        "surface-container-highest": "#e2e2e2",
                        "secondary-fixed": "#ffdcbe",
                        "on-secondary-container": "#653900",
                        "background": "#f9f9f9",
                        "surface-container-high": "#e8e8e8",
                        "on-surface": "#1a1c1c",
                        "tertiary": "#3c6842",
                        "surface-container": "#eeeeee",
                        "on-primary-fixed": "#002204",
                        "on-primary-container": "#003c0b",
                        "on-secondary-fixed-variant": "#693c00",
                        "secondary-fixed-dim": "#ffb870",
                        "surface": "#f9f9f9",
                        "surface-dim": "#dadada",
                        "on-surface-variant": "#3f4a3c",
                        "tertiary-fixed-dim": "#a2d3a4",
                        "outline-variant": "#becab9"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Manrope', sans-serif;
            background-color: #f9f9f9;
            color: #1a1c1c;
        }
        .editorial-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="bg-surface text-on-surface antialiased">
<!-- SideNavBar -->
<aside class="h-screen w-64 fixed left-0 top-0 bg-zinc-100 dark:bg-zinc-900 shadow-[4px_0_12px_rgba(0,0,0,0.04)] flex flex-col p-4 space-y-2 z-40 overflow-y-auto">
<div class="mb-8 px-2 py-4">
<h1 class="font-['Plus_Jakarta_Sans'] font-bold text-lg text-zinc-900 dark:text-zinc-100">GreenBite Admin</h1>
<p class="text-xs text-zinc-500 dark:text-zinc-400">Gestion Plateforme</p>
</div>
<nav class="flex-1 space-y-1">
<!-- Active Tab: Utilisateurs -->
<a class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-800 text-green-700 dark:text-green-400 rounded-lg shadow-sm font-bold transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="group">group</span>
<span class="font-['Manrope'] text-sm font-medium">Utilisateurs</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="restaurant_menu">restaurant_menu</span>
<span class="font-['Manrope'] text-sm font-medium">Recettes</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="analytics">analytics</span>
<span class="font-['Manrope'] text-sm font-medium">Suivi Global</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="forum">forum</span>
<span class="font-['Manrope'] text-sm font-medium">Modération</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="emoji_events">emoji_events</span>
<span class="font-['Manrope'] text-sm font-medium">Défis</span>
</a>
</nav>
<div class="pt-6 border-t border-outline-variant/10 space-y-1">
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
<span class="font-['Manrope'] text-sm font-medium">Paramètres</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-transform hover:translate-x-1" href="#">
<span class="material-symbols-outlined" data-icon="logout">logout</span>
<span class="font-['Manrope'] text-sm font-medium">Déconnexion</span>
</a>
</div>
</aside>
<!-- Main Content Area -->
<main class="ml-64 p-8 min-h-screen">
<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="mb-8 p-4 bg-green-100 text-green-700 rounded-lg border border-green-300">
        <?php 
            if ($_GET['success'] === 'deleted') echo "Utilisateur supprimé avec succès.";
            elseif ($_GET['success'] === 'blocked') echo "Utilisateur bloqué avec succès.";
            elseif ($_GET['success'] === 'unblocked') echo "Utilisateur débloqué avec succès.";
        ?>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="mb-8 p-4 bg-red-100 text-red-700 rounded-lg border border-red-300">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>
<!-- Header & KPI Bento -->
<header class="mb-12">
<div class="flex justify-between items-end mb-8">
<div>
<span class="text-[0.75rem] font-bold tracking-[0.05em] text-primary uppercase font-label">Administration</span>
<h2 class="text-3xl editorial-title font-extrabold text-on-surface mt-2">Gestion des Utilisateurs</h2>
</div>
<button class="bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-3 rounded-full font-bold flex items-center gap-2 shadow-sm transition-transform active:scale-95">
<span class="material-symbols-outlined text-sm" data-icon="person_add">person_add</span>
                    Nouvel Utilisateur
                </button>
</div>
</header>
<!-- User Table -->
<section class="bg-surface-container-lowest rounded-xl shadow-[0px_20px_40px_rgba(26,28,28,0.06)] overflow-hidden">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low text-outline text-[0.75rem] font-bold font-label uppercase tracking-widest">
<th class="px-6 py-4">Photo</th>
<th class="px-6 py-4">Utilisateur</th>
<th class="px-6 py-4">Email</th>
<th class="px-6 py-4">Mot de passe</th>
<th class="px-6 py-4">Statut</th>
<th class="px-6 py-4">Date d'inscription</th>
<th class="px-6 py-4 text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
<?php if (count($users) > 0): ?>
    <?php foreach ($users as $user): ?>
    <tr class="hover:bg-surface-container-low transition-colors group">
    <td class="px-6 py-5">
    <div class="w-12 h-12 rounded-lg bg-surface-container overflow-hidden">
    <div class="w-full h-full flex items-center justify-center font-bold text-sm text-outline bg-primary/10">
        <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
    </div>
    </div>
    </td>
    <td class="px-6 py-5">
    <div class="flex items-center gap-3">
    <div>
    <p class="text-sm font-bold text-on-surface"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
    </div>
    </div>
    </td>
    <td class="px-6 py-5 text-sm text-on-surface-variant"><?php echo htmlspecialchars($user['email']); ?></td>
    <td class="px-6 py-5 text-sm text-on-surface-variant">
    <span class="font-mono text-xs bg-surface-container px-2 py-1 rounded">
    <?php echo substr($user['motDePasse'], 0, 8) . '...'; ?>
    </span>
    </td>
    <td class="px-6 py-5">
    <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase tracking-tighter <?php echo ($user['statut'] === 'bloqué') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
    <?php echo ($user['statut'] === 'bloqué') ? 'Bloqué' : 'Actif'; ?>
    </span>
    </td>
    <td class="px-6 py-5 text-sm text-on-surface-variant"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
    <td class="px-6 py-5">
    <div class="flex justify-end gap-2">
    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="p-2 text-outline hover:text-primary transition-colors" title="Modifier">
    <span class="material-symbols-outlined" data-icon="edit">edit</span>
    </a>
    <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <button type="submit" class="p-2 text-outline hover:text-error transition-colors" title="Supprimer">
    <span class="material-symbols-outlined" data-icon="delete">delete</span>
    </button>
    </form>
    <form method="POST" style="display:inline;">
    <input type="hidden" name="action" value="<?php echo ($user['statut'] === 'bloqué') ? 'unblock' : 'block'; ?>">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <button type="submit" class="p-2 text-outline hover:text-on-surface transition-colors" title="<?php echo ($user['statut'] === 'bloqué') ? 'Débloquer' : 'Bloquer'; ?>">
    <span class="material-symbols-outlined" data-icon="<?php echo ($user['statut'] === 'bloqué') ? 'lock_open' : 'lock'; ?>">
    <?php echo ($user['statut'] === 'bloqué') ? 'lock_open' : 'lock'; ?>
    </span>
    </button>
    </form>
    </div>
    </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr class="hover:bg-surface-container-low transition-colors">
    <td colspan="7" class="px-6 py-5 text-center text-outline">
    <p>Aucun utilisateur trouvé dans la base de données.</p>
    </td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</section>
</main>
</body></html>