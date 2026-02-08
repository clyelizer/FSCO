<?php
// Charger les dépendances dans le bon ordre
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Vérifier que c'est un professeur
requireProf();

$user = getCurrentUser();
$pageTitle = $pageTitle ?? 'Administration des Tests';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - FSCo Admin</title>
    <!-- CSS Principal Admin FSCo -->
    <link rel="stylesheet" href="/pages/admin/assets/admin.css">
    <!-- CSS Spécifique Examens (si besoin, pour l'instant on utilise admin.css) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Overrides spécifiques pour le module d'examen si nécessaire */
        .admin-sidebar {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
        }

        /* Ajustements pour les tables d'examen */
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>

<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-brain"></i>
                <span>FSCo Exams</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li
                    class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'tests.php' || basename($_SERVER['PHP_SELF']) == 'create_test.php' || basename($_SERVER['PHP_SELF']) == 'manage_test_questions.php' ? 'active' : '' ?>">
                    <a href="tests.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Tests</span>
                    </a>
                </li>
                <li
                    class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'questions.php' || basename($_SERVER['PHP_SELF']) == 'add_question.php' || basename($_SERVER['PHP_SELF']) == 'edit_question.php' ? 'active' : '' ?>">
                    <a href="questions.php">
                        <i class="fas fa-question-circle"></i>
                        <span>Banque de Questions</span>
                    </a>
                </li>
                <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Rapports</span>
                    </a>
                </li>

                <li class="nav-item"
                    style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <a href="/pages/admin/index.php">
                        <i class="fas fa-arrow-left"></i>
                        <span>Retour Admin FSCo</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Voir le site</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/auth/logout.php">
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
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <div class="header-actions">
                <span class="welcome-text">Professeur : <?= htmlspecialchars($user['nom']) ?></span>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-<?= $_GET['type'] ?? 'info' ?>"
                    style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background: <?= $_GET['type'] == 'error' ? '#fee2e2' : '#dcfce7' ?>; color: <?= $_GET['type'] == 'error' ? '#991b1b' : '#166534' ?>;">
                    <?= htmlspecialchars($_GET['message']) ?>
                </div>
            <?php endif; ?>