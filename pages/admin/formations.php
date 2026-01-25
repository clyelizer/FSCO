<?php
require_once 'includes/config.php';
requireLogin();

// Traitement des actions
$message = '';
$error = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $formation = [
                    'id' => $_POST['action'] === 'edit' ? $_POST['id'] : generateId(),
                    'titre' => sanitizeInput($_POST['titre']),
                    'description' => sanitizeInput($_POST['description']),
                    'prix' => floatval($_POST['prix']),
                    'duree' => sanitizeInput($_POST['duree']),
                    'niveau' => sanitizeInput($_POST['niveau']),
                    'categorie' => sanitizeInput($_POST['categorie']),
                    'image' => sanitizeInput($_POST['image']),
                    'statut' => $_POST['statut'] ?? 'brouillon',
                    'created_at' => $_POST['action'] === 'edit' ? ($_POST['created_at'] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $formations = readJsonData(DATA_FORMATIONS);
                
                if ($_POST['action'] === 'edit') {
                    foreach ($formations as $key => $f) {
                        if ($f['id'] === $formation['id']) {
                            $formations[$key] = $formation;
                            break;
                        }
                    }
                    $message = 'Formation mise à jour avec succès';
                } else {
                    $formations[] = $formation;
                    $message = 'Formation ajoutée avec succès';
                }
                
                if (writeJsonData(DATA_FORMATIONS, $formations)) {
                    $message = 'Formation sauvegardée avec succès';
                } else {
                    $error = 'Erreur lors de la sauvegarde';
                }
                break;
                
            case 'delete':
                if (isset($_POST['id'])) {
                    $formations = readJsonData(DATA_FORMATIONS);
                    $formations = array_filter($formations, function($f) {
                        return $f['id'] !== $_POST['id'];
                    });
                    
                    if (writeJsonData(DATA_FORMATIONS, array_values($formations))) {
                        $message = 'Formation supprimée avec succès';
                    } else {
                        $error = 'Erreur lors de la suppression';
                    }
                }
                break;
                
            case 'toggle_status':
                if (isset($_POST['id'])) {
                    $formations = readJsonData(DATA_FORMATIONS);
                    foreach ($formations as $key => $f) {
                        if ($f['id'] === $_POST['id']) {
                            $formations[$key]['statut'] = $formations[$key]['statut'] === 'publié' ? 'brouillon' : 'publié';
                            $formations[$key]['updated_at'] = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                    
                    if (writeJsonData(DATA_FORMATIONS, $formations)) {
                        $message = 'Statut de la formation modifié avec succès';
                    } else {
                        $error = 'Erreur lors de la modification du statut';
                    }
                }
                break;
        }
    }
}

// Récupération des données
$formations = readJsonData(DATA_FORMATIONS);
$action = $_GET['action'] ?? 'list';
$currentFormation = null;

if (isset($_GET['id'])) {
    foreach ($formations as $f) {
        if ($f['id'] === $_GET['id']) {
            $currentFormation = $f;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvelle Formation' : 'Modifier Formation') : 'Gestion des Formations' ?> - FSCo Admin</title>
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
                <li class="nav-item">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item active">
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
        <header class="admin-header">
            <h1><?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvelle Formation' : 'Modifier Formation') : 'Gestion des Formations' ?></h1>
            <div class="header-actions">
                <span class="welcome-text">Bonjour, <?= $_SESSION['admin_username'] ?></span>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($message): ?>
                <div class="success-message"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Liste des formations -->
                <div class="table-container">
                    <div class="table-header">
                        <h1>Formations (<?= count($formations) ?>)</h1>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle Formation
                        </a>
                    </div>
                    
                    <?php if (empty($formations)): ?>
                        <div class="empty-state">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Aucune formation ajoutée pour le moment.</p>
                            <a href="?action=add" class="btn btn-primary">Ajouter la première formation</a>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Prix</th>
                                    <th>Durée</th>
                                    <th>Niveau</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($formations as $formation): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $formation['titre'] ?? '' ?></strong><br>
                                            <small style="color: #64748b;"><?= substr($formation['description'] ?? '', 0, 50) ?>...</small>
                                        </td>
                                        <td><?= $formation['prix'] ? number_format($formation['prix'], 2) . '€' : 'Non défini' ?></td>
                                        <td><?= $formation['duree'] ?? 'Non défini' ?></td>
                                        <td><span class="badge"><?= $formation['niveau'] ?? 'Non défini' ?></span></td>
                                        <td><span class="badge"><?= $formation['categorie'] ?? 'Non défini' ?></span></td>
                                        <td>
                                            <?php 
                                            $statut = $formation['statut'] ?? 'brouillon';
                                            $statutClass = $statut === 'publié' ? 'status-published' : 'status-draft';
                                            ?>
                                            <span class="status-badge <?= $statutClass ?>"><?= ucfirst($statut) ?></span>
                                        </td>
                                        <td><?= $formation['created_at'] ?? '' ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?= $formation['id'] ?>">
                                                    <button type="submit" class="btn-xs <?= ($formation['statut'] ?? 'brouillon') === 'publié' ? 'btn-warning' : 'btn-success' ?>">
                                                        <?= ($formation['statut'] ?? 'brouillon') === 'publié' ? 'Dépublier' : 'Publier' ?>
                                                    </button>
                                                </form>
                                                <a href="?action=edit&id=<?= $formation['id'] ?>" class="btn-xs btn-primary">Modifier</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette formation ?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $formation['id'] ?>">
                                                    <button type="submit" class="btn-xs btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Formulaire d'ajout/modification -->
                <div class="form-container">
                    <div class="form-header">
                        <h1><?= $action === 'add' ? 'Nouvelle Formation' : 'Modifier Formation' ?></h1>
                        <p><?= $action === 'add' ? 'Ajoutez une nouvelle formation au catalogue' : 'Modifiez les informations de la formation' ?></p>
                    </div>
                    
                    <form method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $currentFormation['id'] ?? '' ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="add">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="titre" class="form-label">Titre de la formation *</label>
                                <input type="text" id="titre" name="titre" class="form-input" 
                                       value="<?= $currentFormation['titre'] ?? '' ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="prix" class="form-label">Prix (€) *</label>
                                <input type="number" id="prix" name="prix" class="form-input" 
                                       value="<?= $currentFormation['prix'] ?? '' ?>" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Description *</label>
                            <textarea id="description" name="description" class="form-textarea" required><?= $currentFormation['description'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="duree" class="form-label">Durée *</label>
                                <select id="duree" name="duree" class="form-select" required>
                                    <option value="">Sélectionner une durée</option>
                                    <option value="20h" <?= ($currentFormation['duree'] ?? '') === '20h' ? 'selected' : '' ?>>20h</option>
                                    <option value="25h" <?= ($currentFormation['duree'] ?? '') === '25h' ? 'selected' : '' ?>>25h</option>
                                    <option value="30h" <?= ($currentFormation['duree'] ?? '') === '30h' ? 'selected' : '' ?>>30h</option>
                                    <option value="35h" <?= ($currentFormation['duree'] ?? '') === '35h' ? 'selected' : '' ?>>35h</option>
                                    <option value="40h" <?= ($currentFormation['duree'] ?? '') === '40h' ? 'selected' : '' ?>>40h</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="niveau" class="form-label">Niveau *</label>
                                <select id="niveau" name="niveau" class="form-select" required>
                                    <option value="">Sélectionner un niveau</option>
                                    <option value="Débutant" <?= ($currentFormation['niveau'] ?? '') === 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                                    <option value="Intermédiaire" <?= ($currentFormation['niveau'] ?? '') === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                    <option value="Avancé" <?= ($currentFormation['niveau'] ?? '') === 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="categorie" class="form-label">Catégorie *</label>
                                <select id="categorie" name="categorie" class="form-select" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Intelligence Artificielle" <?= ($currentFormation['categorie'] ?? '') === 'Intelligence Artificielle' ? 'selected' : '' ?>>Intelligence Artificielle</option>
                                    <option value="Cybersécurité" <?= ($currentFormation['categorie'] ?? '') === 'Cybersécurité' ? 'selected' : '' ?>>Cybersécurité</option>
                                    <option value="Systèmes d'Information" <?= ($currentFormation['categorie'] ?? '') === 'Systèmes d\'Information' ? 'selected' : '' ?>>Systèmes d'Information</option>
                                    <option value="Automatisation" <?= ($currentFormation['categorie'] ?? '') === 'Automatisation' ? 'selected' : '' ?>>Automatisation</option>
                                    <option value="Cloud" <?= ($currentFormation['categorie'] ?? '') === 'Cloud' ? 'selected' : '' ?>>Cloud</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="image" class="form-label">URL de l'image</label>
                                <input type="url" id="image" name="image" class="form-input" 
                                       value="<?= $currentFormation['image'] ?? '' ?>" 
                                       placeholder="https://example.com/image.jpg">
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="statut" class="form-label">Statut *</label>
                                <select id="statut" name="statut" class="form-select" required>
                                    <option value="brouillon" <?= ($currentFormation['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="publié" <?= ($currentFormation['statut'] ?? '') === 'publié' ? 'selected' : '' ?>>Publié</option>
                                </select>
                            </div>
                        </div>
                        
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="created_at" value="<?= $currentFormation['created_at'] ?? '' ?>">
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <a href="formations.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $action === 'add' ? 'Ajouter' : 'Mettre à jour' ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <style>
        .badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        
        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #bbf7d0;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fecaca;
        }
    </style>
</body>
</html>