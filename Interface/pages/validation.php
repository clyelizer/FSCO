<?php
/**
 * Page de validation des demandes de l'agent IA
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Charger les demandes
$requests = loadRequests();

// Filtrer les demandes si nécessaire
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($status_filter !== 'all') {
    $requests = array_filter($requests, function($req) use ($status_filter) {
        return $req['status'] === $status_filter;
    });
}

if ($type_filter !== 'all') {
    $requests = array_filter($requests, function($req) use ($type_filter) {
        return $req['type'] === $type_filter;
    });
}

if (!empty($search)) {
    $requests = array_filter($requests, function($req) use ($search) {
        $search_lower = strtolower($search);
        return strpos(strtolower($req['id']), $search_lower) !== false ||
               strpos(strtolower($req['summary'] ?? ''), $search_lower) !== false;
    });
}

// Trier par date (plus récent en premier)
usort($requests, function($a, $b) {
    return $b['created_at'] <=> $a['created_at'];
});

// Calculer les statistiques
$stats = [
    'pending_validation' => 0,
    'approved' => 0,
    'rejected' => 0,
    'applied' => 0
];

foreach ($requests as $req) {
    if (isset($stats[$req['status']])) {
        $stats[$req['status']]++;
    }
}

// Types de demandes
$request_types = array_unique(array_column($requests, 'type'));
sort($request_types);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des Demandes - Agent IA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/validation.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-robot"></i> Agent IA</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="config.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuration</span>
                </a>
                <a href="validation.php" class="nav-item active">
                    <i class="fas fa-check-circle"></i>
                    <span>Validation</span>
                </a>
                <a href="logs.php" class="nav-item">
                    <i class="fas fa-history"></i>
                    <span>Logs</span>
                </a>
                <a href="whatsapp.php" class="nav-item">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="validation-container">
                <!-- Header -->
                <div class="validation-header">
                    <h1><i class="fas fa-check-circle"></i> Validation des Demandes</h1>
                    <button class="btn-refresh" onclick="loadRequests()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card pending">
                        <div class="label">En attente</div>
                        <div class="value"><?php echo $stats['pending_validation']; ?></div>
                    </div>
                    <div class="stat-card approved">
                        <div class="label">Approuvées</div>
                        <div class="value"><?php echo $stats['approved']; ?></div>
                    </div>
                    <div class="stat-card rejected">
                        <div class="label">Rejetées</div>
                        <div class="value"><?php echo $stats['rejected']; ?></div>
                    </div>
                    <div class="stat-card applied">
                        <div class="label">Appliquées</div>
                        <div class="value"><?php echo $stats['applied']; ?></div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="filters-bar">
                    <div class="filters">
                        <div class="filter-group">
                            <label for="status-filter">Statut</label>
                            <select id="status-filter" onchange="applyFilters()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tous</option>
                                <option value="pending_validation" <?php echo $status_filter === 'pending_validation' ? 'selected' : ''; ?>>En attente</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approuvées</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejetées</option>
                                <option value="applied" <?php echo $status_filter === 'applied' ? 'selected' : ''; ?>>Appliquées</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="type-filter">Type</label>
                            <select id="type-filter" onchange="applyFilters()">
                                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>Tous</option>
                                <?php foreach ($request_types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $type_filter === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search-input">Rechercher</label>
                            <input type="text" id="search-input" placeholder="ID ou résumé..." value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key === 'Enter') applyFilters()">
                        </div>
                    </div>
                </div>

                <!-- Liste des demandes -->
                <div class="requests-list" id="requests-list">
                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Aucune demande trouvée</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <div class="request-card" onclick="viewRequest('<?php echo htmlspecialchars($request['id']); ?>')">
                                <div class="request-header">
                                    <div class="request-id"><?php echo htmlspecialchars($request['id']); ?></div>
                                    <div class="request-status status-<?php echo htmlspecialchars($request['status']); ?>">
                                        <?php echo getStatusLabel($request['status']); ?>
                                    </div>
                                </div>
                                <div class="request-body">
                                    <div class="request-type">
                                        <i class="fas fa-tag"></i>
                                        <span><?php echo htmlspecialchars($request['type']); ?></span>
                                    </div>
                                    <div class="request-summary">
                                        <?php echo htmlspecialchars($request['summary'] ?? 'Sans résumé'); ?>
                                    </div>
                                </div>
                                <div class="request-footer">
                                    <div class="request-date">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo formatDate($request['created_at']); ?></span>
                                    </div>
                                    <div class="request-actions">
                                        <?php if ($request['status'] === 'pending_validation'): ?>
                                            <button class="btn-validate" onclick="event.stopPropagation(); quickValidate('<?php echo htmlspecialchars($request['id']); ?>')">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-view">
                                            <i class="fas fa-eye"></i> Voir détails
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de détails -->
    <div class="modal-overlay" id="request-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modal-title">Détails de la demande</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Contenu dynamique -->
            </div>
            <div class="modal-footer" id="modal-footer">
                <!-- Boutons d'action -->
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script src="../assets/js/validation.js"></script>
</body>
</html>

<?php
/**
 * Fonctions utilitaires
 */

function getStatusLabel($status) {
    $labels = [
        'pending_validation' => 'En attente',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'applied' => 'Appliquée'
    ];
    return $labels[$status] ?? $status;
}

function formatDate($timestamp) {
    $date = new DateTime('@' . $timestamp);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->days === 0) {
        if ($diff->h === 0) {
            return "Il y a " . $diff->i . " min";
        }
        return "Il y a " . $diff->h . "h";
    } elseif ($diff->days === 1) {
        return "Hier";
    } elseif ($diff->days < 7) {
        return "Il y a " . $diff->days . " jours";
    } else {
        return $date->format('d/m/Y');
    }
}
?>
