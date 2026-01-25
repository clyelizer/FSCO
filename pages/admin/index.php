<?php
require_once 'includes/config.php';
requireLogin();

// Récupération des statistiques
$formations = readJsonData(DATA_FORMATIONS);
$ressources = readJsonData(DATA_RESSOURCES);
$blogs = readJsonData(DATA_BLOGS);

$stats = [
    'formations' => count($formations),
    'ressources' => count($ressources),
    'blogs' => count($blogs),
    'total' => count($formations) + count($ressources) + count($blogs)
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - FSCo</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-brain"></i>
                <span>FSCo Admin</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="formations.php">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Formations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="ressources.php">
                        <i class="fas fa-download"></i>
                        <span>Ressources</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="blogs.php">
                        <i class="fas fa-blog"></i>
                        <span>Blogs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="evaluations/admin/dashboard.php">
                        <i class="fas fa-tasks"></i>
                        <span>Évaluations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Configuration</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../index.php">
                        <i class="fas fa-home"></i>
                        <span>Retour au site</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <h1>Dashboard Administrateur</h1>
            <div class="header-actions">
                <span class="welcome-text">Bonjour, <?= $_SESSION['user_name'] ?? 'Admin' ?></span>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon formations">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['formations'] ?></h3>
                        <p>Formations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon ressources">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['ressources'] ?></h3>
                        <p>Ressources</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blogs">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['blogs'] ?></h3>
                        <p>Articles</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total Contenu</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Actions Rapides</h2>
                <div class="actions-grid">
                    <a href="formations.php?action=add" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Nouvelle Formation</h3>
                        <p>Ajouter une nouvelle formation au catalogue</p>
                    </a>

                    <a href="ressources.php?action=add" class="action-card">
                        <i class="fas fa-upload"></i>
                        <h3>Nouvelle Ressource</h3>
                        <p>Télécharger une nouvelle ressource</p>
                    </a>

                    <a href="blogs.php?action=add" class="action-card">
                        <i class="fas fa-edit"></i>
                        <h3>Nouvel Article</h3>
                        <p>Publier un nouvel article de blog</p>
                    </a>

                    <a href="../index.php" target="_blank" class="action-card">
                        <i class="fas fa-external-link-alt"></i>
                        <h3>Voir le Site</h3>
                        <p>Accéder au site public FSCo</p>
                    </a>
                </div>
            </div>

            <!-- Recent Content -->
            <div class="recent-content">
                <h2>Contenu Récemment Ajouté</h2>

                <?php if (empty($formations) && empty($ressources) && empty($blogs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>Aucun contenu ajouté pour le moment.</p>
                        <p>Commencez par ajouter une formation, une ressource ou un article de blog.</p>
                    </div>
                <?php else: ?>
                    <div class="content-lists">
                        <?php if (!empty($formations)): ?>
                            <div class="content-section">
                                <h3>Dernières Formations</h3>
                                <div class="content-list">
                                    <?php foreach (array_slice($formations, 0, 3) as $formation): ?>
                                        <div class="content-item">
                                            <div class="content-info">
                                                <h4><?= $formation['titre'] ?? 'Titre non défini' ?></h4>
                                                <p><?= $formation['description'] ?? 'Aucune description' ?></p>
                                                <span class="content-date"><?= $formation['created_at'] ?? '' ?></span>
                                            </div>
                                            <div class="content-actions">
                                                <a href="formations.php?action=edit&id=<?= $formation['id'] ?>"
                                                    class="btn-sm">Modifier</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($ressources)): ?>
                            <div class="content-section">
                                <h3>Dernières Ressources</h3>
                                <div class="content-list">
                                    <?php foreach (array_slice($ressources, 0, 3) as $ressource): ?>
                                        <div class="content-item">
                                            <div class="content-info">
                                                <h4><?= $ressource['titre'] ?? 'Titre non défini' ?></h4>
                                                <p><?= $ressource['description'] ?? 'Aucune description' ?></p>
                                                <span class="content-date"><?= $ressource['created_at'] ?? '' ?></span>
                                            </div>
                                            <div class="content-actions">
                                                <a href="ressources.php?action=edit&id=<?= $ressource['id'] ?>"
                                                    class="btn-sm">Modifier</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>