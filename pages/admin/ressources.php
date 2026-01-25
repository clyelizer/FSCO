<?php
require_once 'includes/config.php';
requireLogin();
// Traitement des actions
$message = '';
$error = '';
// DEBUG: Afficher les données POST reçues
if ($_POST && isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $message = "DEBUG: fichier_url reçu = '" . ($_POST['fichier_url'] ?? 'NON DÉFINI') . "' | ";
    $message .= "Fichier uploadé error code = " . ($_FILES['fichier']['error'] ?? 'NON DÉFINI');
}
// Display success message from redirect
if (isset($_GET['success'])) {
    $message = $_GET['success'];
}
// Check for POST max size error
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $maxSize = ini_get('post_max_size');
    $error = "⚠️ ERREUR CRITIQUE : Le fichier est trop volumineux !<br>" .
        "La limite actuelle du serveur est de <strong>$maxSize</strong>.<br>" .
        "Votre fichier fait environ " . round($_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 1) . " MB.<br>" .
        "Il faut redémarrer le serveur avec des limites plus élevées.";
}
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                // Gestion de l'upload d'image
                $imagePath = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    // Priority 1: New image upload
                    $uploadResult = handleFileUpload($_FILES['image'], '../../images/admin/');
                    if ($uploadResult['success']) {
                        $imagePath = 'images/admin/' . $uploadResult['fileName'];
                    } else {
                        $error = $uploadResult['error'];
                    }
                } elseif ($_POST['action'] === 'edit' && !empty($_POST['existing_image']) && empty($_POST['image_url'])) {
                    // Priority 2: Preserve existing image if no new upload AND no URL provided
                    $imagePath = $_POST['existing_image'];
                } elseif (!empty($_POST['image_url'])) {
                    // Priority 3: Use URL only if explicitly provided and no upload
                    $imagePath = sanitizeInput($_POST['image_url']);
                }
                // Gestion de l'upload de fichier
                $filePath = '';
                if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    // Priority 1: New file upload
                    $fileResult = handleResourceUpload($_FILES['fichier'], '../../files/resources/');
                    if ($fileResult['success']) {
                        $filePath = 'files/resources/' . $fileResult['fileName'];
                    } else {
                        $error = $fileResult['error'];
                    }
                } elseif ($_POST['action'] === 'edit' && !empty($_POST['existing_fichier']) && empty($_POST['fichier_url'])) {
                    // Priority 2: Preserve existing file if no new upload AND no URL provided
                    $filePath = $_POST['existing_fichier'];
                } elseif (!empty($_POST['fichier_url'])) {
                    // Priority 3: Use URL only if explicitly provided and no upload
                    $filePath = sanitizeInput($_POST['fichier_url']);
                }
                $ressource = [
                    'id' => $_POST['action'] === 'edit' ? $_POST['id'] : generateId(),
                    'titre' => sanitizeInput($_POST['titre']),
                    'description' => sanitizeInput($_POST['description']),
                    'format' => sanitizeInput($_POST['format']),
                    'taille' => sanitizeInput($_POST['taille']),
                    'categorie' => sanitizeInput($_POST['categorie']),
                    'niveau' => sanitizeInput($_POST['niveau']),
                    'image' => $imagePath,
                    'fichier' => $filePath,
                    'url_externe' => sanitizeInput($_POST['url_externe']),
                    'statut' => $_POST['statut'] ?? 'brouillon',
                    'created_at' => $_POST['action'] === 'edit' ? ($_POST['created_at'] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $ressources = readJsonData(DATA_RESSOURCES);
                if ($_POST['action'] === 'edit') {
                    $found = false;
                    foreach ($ressources as $key => $r) {
                        if ($r['id'] === $ressource['id']) {
                            $ressources[$key] = $ressource;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $error = "Erreur: Ressource introuvable (ID: " . $ressource['id'] . ")";
                    }
                    $message = 'Ressource mise à jour avec succès';
                } else {
                    $ressources[] = $ressource;
                    $message = 'Ressource ajoutée avec succès';
                }
                if (writeJsonData(DATA_RESSOURCES, $ressources)) {
                    // Redirect to prevent form resubmission and data reload
                    header('Location: ressources.php?success=' . urlencode($_POST['action'] === 'edit' ? 'Ressource mise à jour avec succès' : 'Ressource ajoutée avec succès'));
                    exit;
                } else {
                    $error = 'Erreur lors de la sauvegarde';
                }
                break;
            case 'delete':
                if (isset($_POST['id'])) {
                    $ressources = readJsonData(DATA_RESSOURCES);
                    $ressources = array_filter($ressources, function ($r) {
                        return $r['id'] !== $_POST['id'];
                    });
                    if (writeJsonData(DATA_RESSOURCES, array_values($ressources))) {
                        $message = 'Ressource supprimée avec succès';
                    } else {
                        $error = 'Erreur lors de la suppression';
                    }
                }
                break;
            case 'toggle_status':
                if (isset($_POST['id'])) {
                    $ressources = readJsonData(DATA_RESSOURCES);
                    foreach ($ressources as $key => $r) {
                        if ($r['id'] === $_POST['id']) {
                            $ressources[$key]['statut'] = $ressources[$key]['statut'] === 'publié' ? 'brouillon' : 'publié';
                            $ressources[$key]['updated_at'] = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                    if (writeJsonData(DATA_RESSOURCES, $ressources)) {
                        $message = 'Statut de la ressource modifié avec succès';
                    } else {
                        $error = 'Erreur lors de la modification du statut';
                    }
                }
                break;
        }
    }
}
// Récupération des données AVANT le traitement POST
$ressources = readJsonData(DATA_RESSOURCES);
$action = $_GET['action'] ?? 'list';
$currentRessource = null;
if (isset($_GET['id'])) {
    foreach ($ressources as $r) {
        if ($r['id'] === $_GET['id']) {
            $currentRessource = $r;
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
    <title>
        <?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvelle Ressource' : 'Modifier Ressource') : 'Gestion des Ressources' ?>
        - FSCo Admin
    </title>
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
                <li class="nav-item">
                    <a href="formations.php">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Formations</span>
                    </a>
                </li>
                <li class="nav-item active">
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
            <h1><?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvelle Ressource' : 'Modifier Ressource') : 'Gestion des Ressources' ?>
            </h1>
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
                <!-- Liste des ressources -->
                <div class="table-header">
                    <h1>Ressources (<?= count($ressources) ?>)</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle Ressource
                    </a>
                </div>
                <?php if (empty($ressources)): ?>
                    <div class="empty-state">
                        <i class="fas fa-download"></i>
                        <p>Aucune ressource ajoutée pour le moment.</p>
                        <a href="?action=add" class="btn btn-primary">Ajouter la première ressource</a>
                    </div>
                <?php else: ?>
                    <!-- VERSION DESKTOP : TABLEAU ORIGINAL -->
                    <div class="table-view">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> Titre </th>
                                        <th>Format</th>
                                        <th>Taille</th>
                                        <th>Catégorie</th>
                                        <th>Niveau</th>
                                        <th>Statut</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ressources as $ressource): ?>
                                        <tr>
                                            <td>
                                                <div class="resource-cell">
                                                    <?php if (!empty($ressource['image'])): ?>
                                                        <img src="../../<?= $ressource['image'] ?>" alt=""
                                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php else: ?>
                                                        <div
                                                            style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-file" style="color: #64748b;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($ressource['titre'] ?? '') ?></strong><br>
                                                        <small
                                                            style="color: #64748b;"><?= substr(htmlspecialchars($ressource['description'] ?? ''), 0, 40) ?>...</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge"><?= htmlspecialchars($ressource['format'] ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($ressource['taille'] ?? '') ?></td>
                                            <td><span class="badge"><?= htmlspecialchars($ressource['categorie'] ?? '') ?></span></td>
                                            <td><span class="badge"><?= htmlspecialchars($ressource['niveau'] ?? '') ?></span></td>
                                            <td>
                                                <?php
                                                $statut = $ressource['statut'] ?? 'brouillon';
                                                $statutClass = $statut === 'publié' ? 'status-published' : 'status-draft';
                                                ?>
                                                <span class="status-badge <?= $statutClass ?>"><?= ucfirst($statut) ?></span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($ressource['created_at'] ?? '')) ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?= $ressource['id'] ?>">
                                                        <button type="submit"
                                                            class="btn-xs <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'btn-warning' : 'btn-success' ?>">
                                                            <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'Dépublier' : 'Publier' ?>
                                                        </button>
                                                    </form>
                                                    <a href="?action=edit&id=<?= $ressource['id'] ?>"
                                                        class="btn-xs btn-primary">Modifier</a>
                                                    <form method="POST" style="display: inline;"
                                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette ressource ?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $ressource['id'] ?>">
                                                        <button type="submit" class="btn-xs btn-danger">Supprimer</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- VERSION MOBILE : CARTES (ajout responsive seulement) -->
                    <div class="cards-view">
                        <?php foreach ($ressources as $ressource): ?>
                            <div class="resource-card">
                                <?php if (!empty($ressource['image'])): ?>
                                    <img src="../../<?= htmlspecialchars($ressource['image']) ?>" alt="Preview" class="resource-img">
                                <?php else: ?>
                                    <div class="resource-img no-image"><i class="fas fa-file"></i></div>
                                <?php endif; ?>

                                <div class="resource-content">
                                    <h3><?= htmlspecialchars($ressource['titre'] ?? '') ?></h3>
                                    <p class="desc"><?= htmlspecialchars(substr($ressource['description'] ?? '', 0, 100)) ?>...</p>

                                    <div class="resource-tags">
                                        <span class="badge"><?= htmlspecialchars($ressource['format'] ?? '') ?></span>
                                        <span class="badge"><?= htmlspecialchars($ressource['categorie'] ?? '') ?></span>
                                        <span class="badge"><?= htmlspecialchars($ressource['niveau'] ?? '') ?></span>
                                        <span class="status-badge <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'published' : 'draft' ?>">
                                            <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'Publié' : 'Brouillon' ?>
                                        </span>
                                    </div>

                                    <div class="resource-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= $ressource['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'btn-warning' : 'btn-success' ?>">
                                                <?= ($ressource['statut'] ?? 'brouillon') === 'publié' ? 'Dépublier' : 'Publier' ?>
                                            </button>
                                        </form>
                                        <a href="?action=edit&id=<?= $ressource['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $ressource['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Formulaire d'ajout/modification (votre original intact) -->
                <div class="form-container">
                    <div class="form-header">
                        <h1><?= $action === 'add' ? 'Nouvelle Ressource' : 'Modifier Ressource' ?></h1>
                        <p><?= $action === 'add' ? 'Ajoutez une nouvelle ressource à télécharger' : 'Modifiez les informations de la ressource' ?>
                        </p>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $currentRessource['id'] ?? '' ?>">
                            <input type="hidden" name="existing_image" value="<?= $currentRessource['image'] ?? '' ?>">
                            <input type="hidden" name="existing_fichier" value="<?= $currentRessource['fichier'] ?? '' ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="add">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="titre" class="form-label">Titre de la ressource *</label>
                            <input type="text" id="titre" name="titre" class="form-input"
                                value="<?= htmlspecialchars($currentRessource['titre'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description" class="form-label">Description *</label>
                            <textarea id="description" name="description" class="form-textarea"
                                required><?= htmlspecialchars($currentRessource['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="format" class="form-label">Format *</label>
                                <select id="format" name="format" class="form-select" required>
                                    <option value="">Sélectionner un format</option>
                                    <option value="PDF" <?= ($currentRessource['format'] ?? '') === 'PDF' ? 'selected' : '' ?>>
                                        PDF</option>
                                    <option value="Video" <?= ($currentRessource['format'] ?? '') === 'Video' ? 'selected' : '' ?>>Vidéo</option>
                                    <option value="Image" <?= ($currentRessource['format'] ?? '') === 'Image' ? 'selected' : '' ?>>Image</option>
                                    <option value="Excel" <?= ($currentRessource['format'] ?? '') === 'Excel' ? 'selected' : '' ?>>Excel</option>
                                    <option value="Word" <?= ($currentRessource['format'] ?? '') === 'Word' ? 'selected' : '' ?>>Word</option>
                                    <option value="PowerPoint" <?= ($currentRessource['format'] ?? '') === 'PowerPoint' ? 'selected' : '' ?>>PowerPoint</option>
                                    <option value="Archive" <?= ($currentRessource['format'] ?? '') === 'Archive' ? 'selected' : '' ?>>Archive (ZIP/RAR)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="taille" class="form-label">Taille du fichier</label>
                                <input type="text" id="taille" name="taille" class="form-input"
                                    value="<?= htmlspecialchars($currentRessource['taille'] ?? '') ?>" placeholder="ex: 2.5 MB">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="categorie" class="form-label">Catégorie *</label>
                                <select id="categorie" name="categorie" class="form-select" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Guides & Tutoriaux" <?= ($currentRessource['categorie'] ?? '') === 'Guides & Tutoriaux' ? 'selected' : '' ?>>Guides & Tutoriaux</option>
                                    <option value="Checklists & Templates" <?= ($currentRessource['categorie'] ?? '') === 'Checklists & Templates' ? 'selected' : '' ?>>Checklists & Templates</option>
                                    <option value="Études de cas" <?= ($currentRessource['categorie'] ?? '') === 'Études de cas' ? 'selected' : '' ?>>Études de cas</option>
                                    <option value="Outils & Logiciels" <?= ($currentRessource['categorie'] ?? '') === 'Outils & Logiciels' ? 'selected' : '' ?>>Outils & Logiciels</option>
                                    <option value="Webinaires" <?= ($currentRessource['categorie'] ?? '') === 'Webinaires' ? 'selected' : '' ?>>Webinaires</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="niveau" class="form-label">Niveau *</label>
                                <select id="niveau" name="niveau" class="form-select" required>
                                    <option value="">Sélectionner un niveau</option>
                                    <option value="Débutant" <?= ($currentRessource['niveau'] ?? '') === 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                                    <option value="Intermédiaire" <?= ($currentRessource['niveau'] ?? '') === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                    <option value="Expert" <?= ($currentRessource['niveau'] ?? '') === 'Expert' ? 'selected' : '' ?>>Expert</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Image de prévisualisation</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label for="image" class="form-file">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Télécharger une image</span>
                                        <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="image_url" class="form-input"
                                        value="<?= htmlspecialchars($currentRessource['image'] ?? '') ?>" placeholder="Ou URL de l'image">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier à télécharger</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label for="fichier" class="form-file">
                                        <i class="fas fa-file-upload"></i>
                                        <span>Télécharger le fichier</span>
                                        <input type="file" id="fichier" name="fichier" style="display: none;">
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="fichier_url" class="form-input"
                                        value="<?= htmlspecialchars($currentRessource['fichier'] ?? '') ?>"
                                        placeholder="Chemin relatif (ex: files/resources/nom.pdf) ou URL complète">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="url_externe" class="form-label">URL externe (optionnel)</label>
                            <input type="url" id="url_externe" name="url_externe" class="form-input"
                                value="<?= htmlspecialchars($currentRessource['url_externe'] ?? '') ?>"
                                placeholder="https://example.com/ressource">
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="statut" class="form-label">Statut *</label>
                                <select id="statut" name="statut" class="form-select" required>
                                    <option value="brouillon" <?= ($currentRessource['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="publié" <?= ($currentRessource['statut'] ?? '') === 'publié' ? 'selected' : '' ?>>Publié</option>
                                </select>
                            </div>
                        </div>
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="created_at" value="<?= $currentRessource['created_at'] ?? '' ?>">
                        <?php endif; ?>
                        <div class="form-actions">
                            <a href="ressources.php" class="btn btn-secondary">Annuler</a>
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
        /* Vos styles originaux */
        .badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        .status-published {
            background: #dcfce7;
            color: #166534;
        }
        .status-draft {
            background: #fef3c7;
            color: #92400e;
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

        /* AJOUT RESPONSIVE : Cartes sur mobile (images à 90px max, pas de débordement) */
        .resource-cell { display: flex; align-items: center; gap: 0.75rem; }
        .table-view { display: block; }
        .cards-view { display: none; }

        @media (max-width: 900px) {
            .table-view { display: none !important; }
            .cards-view { display: block; }

            .resource-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,.1);
                margin-bottom: 1rem;
                overflow: hidden;
                display: flex;
                flex-direction: row;
                align-items: flex-start;
            }
            .resource-img {
                width: 90px;
                height: 90px;
                object-fit: cover;
                flex-shrink: 0;
            }
            .resource-img.no-image {
                background: #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                color: #94a3b8;
            }
            .resource-content {
                padding: 1rem;
                flex: 1;
                display: flex;
                flex-direction: column;
            }
            .resource-content h3 {
                margin: 0 0 0.5rem;
                font-size: 1.1rem;
                line-height: 1.3;
            }
            .desc {
                color: #64748b;
                font-size: 0.9rem;
                margin: 0 0 1rem;
                line-height: 1.4;
            }
            .resource-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
                margin-bottom: 0.75rem;
            }
            .resource-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: auto;
            }
            .resource-actions .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .resource-card { flex-direction: column; }
            .resource-img {
                width: 100%;
                height: 140px;
                border-radius: 12px 12px 0 0;
            }
        }
    </style>
    <script>
        // Gestion du clic sur les zones de téléchargement
        document.querySelectorAll('.form-file').forEach(zone => {
            zone.addEventListener('click', function () {
                const input = this.querySelector('input[type="file"]');
                if (input) input.click();
            });
            const input = zone.querySelector('input[type="file"]');
            if (input) {
                input.addEventListener('change', function () {
                    const span = this.parentElement.querySelector('span');
                    if (this.files.length > 0) {
                        span.textContent = 'Fichier sélectionné: ' + this.files[0].name;
                    } else {
                        span.textContent = this.parentElement.querySelector('i').nextElementSibling.textContent || 'Télécharger';
                    }
                });
            }
        });
    </script>
</body>
</html>