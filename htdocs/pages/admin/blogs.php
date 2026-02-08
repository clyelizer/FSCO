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
                // Gestion de l'upload d'image
                $imagePath = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload($_FILES['image'], '../../images/blogs/');
                    if ($uploadResult['success']) {
                        $imagePath = 'images/blogs/' . $uploadResult['fileName'];
                    } else {
                        $error = $uploadResult['error'];
                    }
                } elseif (!empty($_POST['image_url'])) {
                    $imagePath = sanitizeInput($_POST['image_url']);
                } elseif ($_POST['action'] === 'edit' && !empty($_POST['existing_image'])) {
                    $imagePath = $_POST['existing_image'];
                }
               
                $blog = [
                    'id' => $_POST['action'] === 'edit' ? $_POST['id'] : generateId(),
                    'titre' => sanitizeInput($_POST['titre']),
                    'extrait' => sanitizeInput($_POST['extrait']),
                    'contenu' => $_POST['contenu'], // CKEditor HTML
                    'auteur' => sanitizeInput($_POST['auteur']),
                    'categorie' => sanitizeInput($_POST['categorie']),
                    'tags' => array_map('sanitizeInput', array_filter(explode(',', $_POST['tags'] ?? ''))),
                    'statut' => $_POST['statut'],
                    'image' => $imagePath,
                    'created_at' => $_POST['action'] === 'edit' ? ($_POST['created_at'] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
               
                $blogs = readJsonData(DATA_BLOGS);
               
                if ($_POST['action'] === 'edit') {
                    foreach ($blogs as $key => $b) {
                        if ($b['id'] === $blog['id']) {
                            $blogs[$key] = $blog;
                            break;
                        }
                    }
                    $msg = 'Article mis à jour avec succès';
                } else {
                    $blogs[] = $blog;
                    $msg = 'Article ajouté avec succès';
                }
               
                if (writeJsonData(DATA_BLOGS, $blogs)) {
                    header("Location: blogs.php?success=" . urlencode($msg));
                    exit;
                } else {
                    $error = 'Erreur lors de la sauvegarde';
                }
                break;
               
            case 'delete':
                if (isset($_POST['id'])) {
                    $blogs = readJsonData(DATA_BLOGS);
                    $blogs = array_filter($blogs, function($b) {
                        return $b['id'] !== $_POST['id'];
                    });
                   
                    if (writeJsonData(DATA_BLOGS, array_values($blogs))) {
                        $message = 'Article supprimé avec succès';
                    } else {
                        $error = 'Erreur lors de la suppression';
                    }
                }
                break;
               
            case 'toggle_status':
                if (isset($_POST['id'])) {
                    $blogs = readJsonData(DATA_BLOGS);
                    foreach ($blogs as $key => $b) {
                        if ($b['id'] === $_POST['id']) {
                            $blogs[$key]['statut'] = $blogs[$key]['statut'] === 'publié' ? 'brouillon' : 'publié';
                            $blogs[$key]['updated_at'] = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                   
                    if (writeJsonData(DATA_BLOGS, $blogs)) {
                        $message = 'Statut de l\'article modifié avec succès';
                    } else {
                        $error = 'Erreur lors de la modification du statut';
                    }
                }
                break;
        }
    }
}

if (isset($_GET['success'])) {
    $message = $_GET['success'];
}

// Récupération des données
$blogs = readJsonData(DATA_BLOGS);
// Trier par date de création décroissante
usort($blogs, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$action = $_GET['action'] ?? 'list';
$currentBlog = null;
if (isset($_GET['id'])) {
    foreach ($blogs as $b) {
        if ($b['id'] === $_GET['id']) {
            $currentBlog = $b;
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
    <title><?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvel Article' : 'Modifier Article') : 'Gestion des Articles' ?> - FSCo Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
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
                <li class="nav-item">
                    <a href="ressources.php">
                        <i class="fas fa-download"></i>
                        <span>Ressources</span>
                    </a>
                </li>
                <li class="nav-item active">
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
            <h1><?= $action === 'add' || $action === 'edit' ? ($action === 'add' ? 'Nouvel Article' : 'Modifier Article') : 'Gestion des Articles' ?></h1>
            <div class="header-actions">
                <span class="welcome-text">Bonjour, <?= $_SESSION['admin_username'] ?></span>
            </div>
        </header>
        <div class="dashboard-content">
            <?php if ($message): ?>
                <div class="success-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
           
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($action === 'list'): ?>
                <!-- Liste des articles -->
                <div class="table-header">
                    <h1>Articles (<?= count($blogs) ?>)</h1>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvel Article
                    </a>
                </div>

                <!-- VERSION DESKTOP -->
                <div class="table-view">
                    <div class="table-container">
                        <?php if (empty($blogs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-blog"></i>
                                <p>Aucun article publié pour le moment.</p>
                                <a href="?action=add" class="btn btn-primary">Publier le premier article</a>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> Article </th>
                                        <th>Auteur</th>
                                        <th>Catégorie</th>
                                        <th>Tags</th>
                                        <th>Statut</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <?php if (!empty($blog['image'])): ?>
                                                        <img src="../../<?= htmlspecialchars($blog['image']) ?>" alt="" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                                                    <?php else: ?>
                                                        <div style="width: 50px; height: 35px; background: #f1f5f9; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image" style="color: #64748b;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($blog['titre'] ?? '') ?></strong><br>
                                                        <small style="color: #64748b;"><?= substr(htmlspecialchars($blog['extrait'] ?? ''), 0, 60) ?>...</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($blog['auteur'] ?? '') ?></td>
                                            <td><span class="badge"><?= htmlspecialchars($blog['categorie'] ?? '') ?></span></td>
                                            <td>
                                                <?php if (!empty($blog['tags'])): ?>
                                                    <?php foreach ($blog['tags'] as $tag): ?>
                                                        <span class="badge-tag"><?= htmlspecialchars($tag) ?></span>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'published' : 'draft' ?>">
                                                    <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'Publié' : 'Brouillon' ?>
                                                </span>
                                                                                    </td>
                                            <td><?= date('d/m/Y', strtotime($blog['created_at'] ?? '')) ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?action=edit&id=<?= $blog['id'] ?>" class="btn-xs btn-primary">Modifier</a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                        <button type="submit" class="btn-xs btn-danger">Supprimer</button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                                        <button type="submit" class="btn-xs <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'btn-warning' : 'btn-success' ?>">
                                                            <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'Mettre en brouillon' : 'Publier' ?>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- VERSION MOBILE - CARTES -->
                <div class="cards-view">
                    <?php foreach ($blogs as $blog): ?>
                        <div class="article-card">
                            <?php if (!empty($blog['image'])): ?>
                                <img src="../../<?= htmlspecialchars($blog['image']) ?>" alt="">
                            <?php else: ?>
                                <div style="width:90px;height:90px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div style="padding:1rem;flex:1;">
                                <h3 style="margin:0 0 .5rem;font-size:1.1rem;"><?= htmlspecialchars($blog['titre'] ?? '') ?></h3>
                                <p style="color:#64748b;font-size:.9rem;margin:0 0 1rem;"><?= substr(htmlspecialchars($blog['extrait'] ?? ''),0,100) ?>...</p>
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem;">
                                    <span class="badge"><?= htmlspecialchars($blog['categorie'] ?? '') ?></span>
                                    <span class="badge <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'published' : 'draft' ?>">
                                        <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'Publié' : 'Brouillon' ?>
                                    </span>
                                </div>
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                    <a href="?action=edit&id=<?= $blog['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'btn-warning' : 'btn-success' ?>">
                                            <?= ($blog['statut'] ?? 'brouillon') === 'publié' ? 'Brouillon' : 'Publier' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Formulaire d'ajout/modification -->
                <div class="form-container">
                    <div class="form-header">
                        <h1><?= $action === 'add' ? 'Nouvel Article' : 'Modifier Article' ?></h1>
                        <p><?= $action === 'add' ? 'Créez un nouvel article pour le blog' : 'Modifiez votre article' ?></p>
                    </div>
                   
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $currentBlog['id'] ?? '' ?>">
                            <input type="hidden" name="existing_image" value="<?= $currentBlog['image'] ?? '' ?>">
                            <input type="hidden" name="created_at" value="<?= $currentBlog['created_at'] ?? '' ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="add">
                        <?php endif; ?>
                       
                        <div class="form-group">
                            <label for="titre" class="form-label">Titre de l'article *</label>
                            <input type="text" id="titre" name="titre" class="form-input"
                                   value="<?= htmlspecialchars($currentBlog['titre'] ?? '') ?>" required>
                        </div>
                       
                        <div class="form-group">
                            <label for="extrait" class="form-label">Extrait *</label>
                            <textarea id="extrait" name="extrait" class="form-textarea" rows="3" required
                                      placeholder="Un court résumé de l'article..."><?= htmlspecialchars($currentBlog['extrait'] ?? '') ?></textarea>
                        </div>
                       
                        <div class="form-group">
                            <label for="contenu" class="form-label">Contenu de l'article *</label>
                            <textarea id="contenu" name="contenu" class="form-textarea" required><?= htmlspecialchars($currentBlog['contenu'] ?? '') ?></textarea>
                        </div>
                       
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="auteur" class="form-label">Auteur *</label>
                                <input type="text" id="auteur" name="auteur" class="form-input"
                                       value="<?= htmlspecialchars($currentBlog['auteur'] ?? ($_SESSION['admin_username'] ?? '')) ?>" required>
                            </div>
                           
                            <div class="form-group">
                                <label for="categorie" class="form-label">Catégorie *</label>
                                <select id="categorie" name="categorie" class="form-select" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Intelligence Artificielle" <?= ($currentBlog['categorie'] ?? '') === 'Intelligence Artificielle' ? 'selected' : '' ?>>Intelligence Artificielle</option>
                                    <option value="Cybersécurité" <?= ($currentBlog['categorie'] ?? '') === 'Cybersécurité' ? 'selected' : '' ?>>Cybersécurité</option>
                                    <option value="Cloud & Infrastructure" <?= ($currentBlog['categorie'] ?? '') === 'Cloud & Infrastructure' ? 'selected' : '' ?>>Cloud & Infrastructure</.option>
                                    <option value="Automatisation" <?= ($currentBlog['categorie'] ?? '') === 'Automatisation' ? 'selected' : '' ?>>Automatisation</option>
                                    <option value="Transformation Digitale" <?= ($currentBlog['categorie'] ?? '') === 'Transformation Digitale' ? 'selected' : '' ?>>Transformation Digitale</option>
                                    <option value="Actualités" <?= ($currentBlog['categorie'] ?? '') === 'Actualités' ? 'selected' : '' ?>>Actualités</option>
                                </select>
                            </div>
                        </div>
                       
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" id="tags" name="tags" class="form-input"
                                       value="<?= !empty($currentBlog['tags']) ? htmlspecialchars(implode(', ', $currentBlog['tags'])) : '' ?>"
                                       placeholder="Séparez les tags par des virgules">
                                <small style="color: #64748b; font-size: 0.8rem;">Ex: IA, Machine Learning, Formation</small>
                            </div>
                           
                            <div class="form-group">
                                <label for="statut" class="form-label">Statut de publication *</label>
                                <select id="statut" name="statut" class="form-select" required>
                                    <option value="brouillon" <?= ($currentBlog['statut'] ?? '') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="publié" <?= ($currentBlog['statut'] ?? '') === 'publié' ? 'selected' : '' ?>>Publié</option>
                                </select>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <label class="form-label">Image à la une</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label for="image" class="form-file">
                                        <i class="fas fa-image"></i>
                                        <span>Télécharger une image</span>
                                        <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="image_url" class="form-input"
                                           value="<?= htmlspecialchars($currentBlog['image'] ?? '') ?>"
                                           placeholder="Ou URL de l'image">
                                </div>
                            </div>
                        </div>
                       
                        <div class="form-actions">
                            <a href="blogs.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $action === 'add' ? 'Créer l\'article' : 'Mettre à jour' ?>
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
       
        .badge.published {
            background: #dcfce7;
            color: #166534;
        }
       
        .badge.draft {
            background: #fef3c7;
            color: #92400e;
        }
       
        .badge-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 0.125rem 0.375rem;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-right: 0.25rem;
            margin-bottom: 0.125rem;
            display: inline-block;
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
       
        .form-file {
            border: 2px dashed #e5e7eb;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
       
        .form-file:hover {
            border-color: #2563eb;
        }

        /* RESPONSIVE MOBILE */
        .table-view { display: block; }
        .cards-view { display: none; }

        @media (max-width: 900px) {
            .table-view { display: none !important; }
            .cards-view { display: block; }
            .article-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,.08);
                margin-bottom: 1rem;
                overflow: hidden;
                display: flex;
                flex-direction: row;
            }
            .article-card img {
                width: 90px;
                height: 90px;
                object-fit: cover;
                flex-shrink: 0;
            }
        }
        @media (max-width: 480px) {
            .article-card {
                flex-direction: column;
            }
            .article-card img {
                width: 100%;
                height: 160px;
                border-radius: 12px 12px 0 0;
            }
        }
    </style>
   
    <script>
        // Initialisation de CKEditor
        if (document.getElementById('contenu')) {
            CKEDITOR.replace('contenu', {
                height: 400,
                toolbar: [
                    { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                    { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'] },
                    { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
                    { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
                ]
            });
        }
       
        // Gestion du clic sur les zones de téléchargement
        document.querySelectorAll('.form-file').forEach(zone => {
            zone.addEventListener('click', function() {
                const input = this.querySelector('input[type="file"]');
                if (input) input.click();
            });
           
            const input = zone.querySelector('input[type="file"]');
            if (input) {
                input.addEventListener('change', function() {
                    const span = zone.querySelector('span');
                    if (this.files.length > 0) {
                        span.textContent = 'Fichier sélectionné: ' + this.files[0].name;
                    } else {
                        span.textContent = zone.querySelector('i').nextElementSibling.textContent;
                    }
                });
            }
        });
    </script>
</body>
</html>