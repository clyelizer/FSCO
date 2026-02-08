<?php
require_once 'includes/config.php';
requireLogin();

$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'general' => [
            'site_title' => sanitizeInput($_POST['site_title']),
            'contact_email' => sanitizeInput($_POST['contact_email']),
            'contact_phone' => sanitizeInput($_POST['contact_phone']),
            'contact_address' => sanitizeInput($_POST['contact_address'])
        ],
        'hero' => [
            'title' => sanitizeInput($_POST['hero_title']),
            'subtitle' => $_POST['hero_subtitle'], // Allow HTML from TinyMCE
            'cta_primary' => sanitizeInput($_POST['hero_cta_primary']),
            'cta_secondary' => sanitizeInput($_POST['hero_cta_secondary']),
            'background_image' => sanitizeInput($_POST['hero_background_image'])
        ],
        'sondage' => [
            'enabled' => isset($_POST['sondage_enabled']),
            'title' => sanitizeInput($_POST['sondage_title']),
            'subtitle' => sanitizeInput($_POST['sondage_subtitle']),
            'description' => $_POST['sondage_description'], // Allow HTML
            'participants' => sanitizeInput($_POST['sondage_participants']),
            'days_left' => sanitizeInput($_POST['sondage_days_left']),
            'satisfaction' => sanitizeInput($_POST['sondage_satisfaction'])
        ],
        'theme' => [
            'primary_color' => sanitizeInput($_POST['primary_color']),
            'secondary_color' => sanitizeInput($_POST['secondary_color']),
            'font_family' => sanitizeInput($_POST['font_family']),
            'font_size_base' => sanitizeInput($_POST['font_size_base']),
            'font_size_headings' => sanitizeInput($_POST['font_size_headings'])
        ],
        'pages' => [
            'formations' => [
                'title' => sanitizeInput($_POST['formations_title']),
                'intro' => sanitizeInput($_POST['formations_intro'])
            ],
            'ressources' => [
                'title' => sanitizeInput($_POST['ressources_title']),
                'intro' => sanitizeInput($_POST['ressources_intro'])
            ],
            'blogs' => [
                'title' => sanitizeInput($_POST['blogs_title']),
                'intro' => sanitizeInput($_POST['blogs_intro'])
            ]
        ],
        'seo' => [
            'meta_title' => sanitizeInput($_POST['meta_title']),
            'meta_description' => sanitizeInput($_POST['meta_description']),
            'og_image' => sanitizeInput($_POST['og_image'])
        ],
        'services' => [],
        'pricing' => []
    ];

    // Handle Services
    if (isset($_POST['service_title'])) {
        for ($i = 0; $i < count($_POST['service_title']); $i++) {
            if (!empty($_POST['service_title'][$i])) {
                $config['services'][] = [
                    'title' => sanitizeInput($_POST['service_title'][$i]),
                    'description' => $_POST['service_description'][$i], // Allow HTML
                    'image' => sanitizeInput($_POST['service_image'][$i])
                ];
            }
        }
    }

    if (writeJsonData(DATA_SITE_CONFIG, $config)) {
        $message = 'Configuration sauvegardée avec succès';
    } else {
        $error = 'Erreur lors de la sauvegarde de la configuration';
    }
}

// Chargement de la configuration
$config = readJsonData(DATA_SITE_CONFIG);
if (empty($config)) {
    $config = [
        'general' => ['site_title' => '', 'contact_email' => '', 'contact_phone' => '', 'contact_address' => ''],
        'hero' => ['title' => '', 'subtitle' => '', 'cta_primary' => '', 'cta_secondary' => '', 'background_image' => ''],
        'sondage' => ['enabled' => false, 'title' => '', 'subtitle' => '', 'description' => '', 'participants' => '', 'days_left' => '', 'satisfaction' => ''],
        'theme' => ['primary_color' => '#2563eb', 'secondary_color' => '#1e293b', 'font_family' => 'Inter', 'font_size_base' => '16px', 'font_size_headings' => '2rem'],
        'pages' => [
            'formations' => ['title' => 'Nos Formations', 'intro' => ''],
            'ressources' => ['title' => 'Ressources', 'intro' => ''],
            'blogs' => ['title' => 'Blog', 'intro' => '']
        ],
        'seo' => ['meta_title' => '', 'meta_description' => '', 'og_image' => ''],
        'services' => []
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Admin - FSCo</title>
    <link rel="stylesheet" href="assets/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/1zj011wjentlk66gfvsj75ixkpwovq4lt3c3k1kfjbkrwjjg/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '.wysiwyg',
            height: 200,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link',
            content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
        });
    </script>
    <style>
        .settings-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 1rem;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .service-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
        }

        .remove-service {
            color: #ef4444;
            cursor: pointer;
            float: right;
        }

        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        input[type="color"] {
            width: 50px;
            height: 50px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
        }
    </style>
</head>

<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-brain"></i>
                <span>FSCo CMS</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item">
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                </li>
                <li class="nav-item">
                    <a href="formations.php"><i class="fas fa-graduation-cap"></i> <span>Formations</span></a>
                </li>
                <li class="nav-item">
                    <a href="ressources.php"><i class="fas fa-download"></i> <span>Ressources</span></a>
                </li>
                <li class="nav-item">
                    <a href="blogs.php"><i class="fas fa-blog"></i> <span>Blogs</span></a>
                </li>
                <li class="nav-item active">
                    <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuration</span></a>
                </li>
                <li class="nav-item">
                    <a href="../../index.php"><i class="fas fa-home"></i> <span>Retour au site</span></a>
                </li>
                <li class="nav-item">
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Configuration du Site (CMS)</h1>
            <div class="header-actions">
                <span class="welcome-text">Bonjour, <?= $_SESSION['admin_username'] ?></span>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($message): ?>
                <div class="success-message"
                    style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message"
                    style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fecaca;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-container">
                <div class="settings-tabs">
                    <button type="button" class="tab-btn active" onclick="openTab('general')">Général</button>
                    <button type="button" class="tab-btn" onclick="openTab('hero')">Accueil (Hero)</button>
                    <button type="button" class="tab-btn" onclick="openTab('theme')">🎨 Apparence</button>
                    <button type="button" class="tab-btn" onclick="openTab('seo')">🔍 SEO</button>
                    <button type="button" class="tab-btn" onclick="openTab('pages')">📄 Pages</button>
                    <button type="button" class="tab-btn" onclick="openTab('sondage')">Sondage</button>
                    <button type="button" class="tab-btn" onclick="openTab('services')">Services</button>
                </div>

                <!-- General Tab -->
                <div id="general" class="tab-content active">
                    <div class="form-group">
                        <label class="form-label">Titre du site</label>
                        <input type="text" name="site_title" class="form-input"
                            value="<?= $config['general']['site_title'] ?? '' ?>">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Email de contact</label>
                            <input type="email" name="contact_email" class="form-input"
                                value="<?= $config['general']['contact_email'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="contact_phone" class="form-input"
                                value="<?= $config['general']['contact_phone'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="contact_address" class="form-input"
                            value="<?= $config['general']['contact_address'] ?? '' ?>">
                    </div>
                </div>

                <!-- Hero Tab -->
                <div id="hero" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">Titre Principal</label>
                        <input type="text" name="hero_title" class="form-input"
                            value="<?= $config['hero']['title'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sous-titre (Rich Text)</label>
                        <textarea name="hero_subtitle"
                            class="form-textarea wysiwyg"><?= $config['hero']['subtitle'] ?? '' ?></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Texte Bouton 1</label>
                            <input type="text" name="hero_cta_primary" class="form-input"
                                value="<?= $config['hero']['cta_primary'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Texte Bouton 2</label>
                            <input type="text" name="hero_cta_secondary" class="form-input"
                                value="<?= $config['hero']['cta_secondary'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Image de fond (URL)</label>
                        <input type="text" name="hero_background_image" class="form-input"
                            value="<?= $config['hero']['background_image'] ?? '' ?>">
                    </div>
                </div>

                <!-- Theme Tab -->
                <div id="theme" class="tab-content">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Couleur Primaire</label>
                            <div class="color-picker-wrapper">
                                <input type="color" name="primary_color"
                                    value="<?= $config['theme']['primary_color'] ?? '#2563eb' ?>">
                                <span>Couleur principale (boutons, liens, accents)</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Couleur Secondaire</label>
                            <div class="color-picker-wrapper">
                                <input type="color" name="secondary_color"
                                    value="<?= $config['theme']['secondary_color'] ?? '#1e293b' ?>">
                                <span>Couleur secondaire (titres, footer)</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Police d'écriture</label>
                        <select name="font_family" class="form-input">
                            <option value="Inter" <?= ($config['theme']['font_family'] ?? '') === 'Inter' ? 'selected' : '' ?>>Inter (Moderne)</option>
                            <option value="Poppins" <?= ($config['theme']['font_family'] ?? '') === 'Poppins' ? 'selected' : '' ?>>Poppins (Géométrique)</option>
                            <option value="Roboto" <?= ($config['theme']['font_family'] ?? '') === 'Roboto' ? 'selected' : '' ?>>Roboto (Classique)</option>
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Taille de police de base</label>
                            <input type="text" name="font_size_base" class="form-input"
                                value="<?= $config['theme']['font_size_base'] ?? '16px' ?>" placeholder="16px">
                            <small style="color: #64748b;">Ex: 14px, 16px, 18px</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Taille des titres</label>
                            <input type="text" name="font_size_headings" class="form-input"
                                value="<?= $config['theme']['font_size_headings'] ?? '2rem' ?>" placeholder="2rem">
                            <small style="color: #64748b;">Ex: 1.5rem, 2rem, 2.5rem</small>
                        </div>
                    </div>
                </div>

                <!-- Pages Tab -->
                <div id="pages" class="tab-content">
                    <h3 style="margin-bottom: 1.5rem;">Personnalisation des Pages</h3>

                    <div class="service-item">
                        <h4>📚 Page Formations</h4>
                        <div class="form-group">
                            <label class="form-label">Titre de la page</label>
                            <input type="text" name="formations_title" class="form-input"
                                value="<?= $config['pages']['formations']['title'] ?? 'Nos Formations' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Introduction</label>
                            <textarea name="formations_intro" class="form-textarea"
                                rows="3"><?= $config['pages']['formations']['intro'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="service-item">
                        <h4>📄 Page Ressources</h4>
                        <div class="form-group">
                            <label class="form-label">Titre de la page</label>
                            <input type="text" name="ressources_title" class="form-input"
                                value="<?= $config['pages']['ressources']['title'] ?? 'Ressources' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Introduction</label>
                            <textarea name="ressources_intro" class="form-textarea"
                                rows="3"><?= $config['pages']['ressources']['intro'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="service-item">
                        <h4>✍️ Page Blog</h4>
                        <div class="form-group">
                            <label class="form-label">Titre de la page</label>
                            <input type="text" name="blogs_title" class="form-input"
                                value="<?= $config['pages']['blogs']['title'] ?? 'Blog' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Introduction</label>
                            <textarea name="blogs_intro" class="form-textarea"
                                rows="3"><?= $config['pages']['blogs']['intro'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div id="seo" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">Meta Title (Google)</label>
                        <input type="text" name="meta_title" class="form-input"
                            value="<?= $config['seo']['meta_title'] ?? '' ?>">
                        <small style="color: #64748b;">Titre affiché dans les résultats de recherche (max 60
                            caractères)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description"
                            class="form-textarea"><?= $config['seo']['meta_description'] ?? '' ?></textarea>
                        <small style="color: #64748b;">Description courte pour les moteurs de recherche (max 160
                            caractères)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Image de partage (OG:Image)</label>
                        <input type="text" name="og_image" class="form-input"
                            value="<?= $config['seo']['og_image'] ?? '' ?>">
                        <small style="color: #64748b;">URL de l'image affichée lors du partage sur les réseaux
                            sociaux</small>
                    </div>
                </div>

                <!-- Sondage Tab -->
                <div id="sondage" class="tab-content">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="sondage_enabled" <?= ($config['sondage']['enabled'] ?? false) ? 'checked' : '' ?>>
                            Activer la section Sondage
                        </label>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Titre</label>
                            <input type="text" name="sondage_title" class="form-input"
                                value="<?= $config['sondage']['title'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sous-titre</label>
                            <input type="text" name="sondage_subtitle" class="form-input"
                                value="<?= $config['sondage']['subtitle'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Rich Text)</label>
                        <textarea name="sondage_description"
                            class="form-textarea wysiwyg"><?= $config['sondage']['description'] ?? '' ?></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Participants</label>
                            <input type="text" name="sondage_participants" class="form-input"
                                value="<?= $config['sondage']['participants'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jours restants</label>
                            <input type="text" name="sondage_days_left" class="form-input"
                                value="<?= $config['sondage']['days_left'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satisfaction</label>
                            <input type="text" name="sondage_satisfaction" class="form-input"
                                value="<?= $config['sondage']['satisfaction'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Services Tab -->
                <div id="services" class="tab-content">
                    <div id="services-list">
                        <?php foreach ($config['services'] as $index => $service): ?>
                            <div class="service-item">
                                <span class="remove-service" onclick="this.parentElement.remove()"><i
                                        class="fas fa-trash"></i></span>
                                <div class="form-group">
                                    <label class="form-label">Titre du service</label>
                                    <input type="text" name="service_title[]" class="form-input"
                                        value="<?= $service['title'] ?? '' ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description (Rich Text)</label>
                                    <textarea name="service_description[]"
                                        class="form-textarea wysiwyg"><?= $service['description'] ?? '' ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Image (URL)</label>
                                    <input type="text" name="service_image[]" class="form-input"
                                        value="<?= $service['image'] ?? '' ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addService()">
                        <i class="fas fa-plus"></i> Ajouter un service
                    </button>
                </div>

                <div class="form-actions" style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer la configuration
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function openTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function addService() {
            const container = document.getElementById('services-list');
            // Note: TinyMCE won't auto-init on dynamically added elements without extra JS, 
            // keeping it simple for now or we'd need to call tinymce.init() again.
            const template = `
                <div class="service-item">
                    <span class="remove-service" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></span>
                    <div class="form-group">
                        <label class="form-label">Titre du service</label>
                        <input type="text" name="service_title[]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="service_description[]" class="form-textarea wysiwyg"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Image (URL)</label>
                        <input type="text" name="service_image[]" class="form-input">
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);

            // Re-init TinyMCE for the new textarea
            tinymce.init({
                selector: '.wysiwyg',
                height: 200,
                menubar: false,
                plugins: 'lists link',
                toolbar: 'undo redo | bold italic underline | bullist numlist | link',
                content_style: 'body { font-family:Inter,sans-serif; font-size:14px }'
            });
        }
    </script>
    </main>
</body>

</html>