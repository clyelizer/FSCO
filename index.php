<?php
// Empêche la mise en cache et force la revalidation pour charger la dernière version
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

// Load site configuration
$siteConfig = json_decode(file_get_contents('pages/admin/data/site_config.json'), true) ?? [];

// Fallback defaults
$general = $siteConfig['general'] ?? [
    'site_title' => 'FSCo - Formation Suivi Conseil',
    'contact_email' => 'clyelise1@gmail.com',
    'contact_phone' => '+212 698771629',
    'contact_address' => 'Casablanca, Maroc'
];
$hero = $siteConfig['hero'] ?? [
    'title' => 'FSCo - Formation Suivi Conseil',
    'subtitle' => 'Votre partenaire pour la sécurisation numérique',
    'cta_primary' => 'Découvrir nos services',
    'cta_secondary' => 'Nous contacter',
    'background_image' => 'images/hero-bg.jpg'
];
$theme = $siteConfig['theme'] ?? [
    'primary_color' => '#2563eb',
    'secondary_color' => '#1e293b',
    'font_family' => 'Inter',
    'font_size_base' => '16px',
    'font_size_headings' => '2rem'
];
$seo = $siteConfig['seo'] ?? [
    'meta_title' => $general['site_title'],
    'meta_description' => 'Formation, Suivi et Conseil en informatique.',
    'og_image' => $hero['background_image']
];
$sondage = $siteConfig['sondage'] ?? ['enabled' => true];
$services = $siteConfig['services'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="color-scheme" content="light">
    <title><?= htmlspecialchars($seo['meta_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['meta_description']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo['og_image']) ?>">

    <link rel="stylesheet" href="index.css">
    <link
        href="https://fonts.googleapis.com/css2?family=<?= htmlspecialchars($theme['font_family']) ?>:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            color-scheme:
                light
            ;

            --primary-color:
                <?= htmlspecialchars($theme['primary_color']) ?>
            ;
            --secondary-color:
                <?= htmlspecialchars($theme['secondary_color']) ?>
            ;
            --font-main: '<?= htmlspecialchars($theme['font_family']) ?>', sans-serif;
            --font-size-base:
                <?= htmlspecialchars($theme['font_size_base'] ?? '16px') ?>
            ;
            --font-size-headings:
                <?= htmlspecialchars($theme['font_size_headings'] ?? '2rem') ?>
            ;
        }

        body {
            font-family: var(--font-main);
            font-size: var(--font-size-base);
        }

        h1,
        h2,
        h3 {
            font-size: var(--font-size-headings);
        }
    </style>
</head>

<body>
    <!-- HEADER -->

    <?php include 'pages/includes/header.php'; ?>

    <?php
    // Display success or error messages from session
    if (isset($_SESSION['success_message'])): ?>
        <div
            style="position: fixed; top: 80px; right: 20px; z-index: 10000; background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); max-width: 400px; animation: slideIn 0.3s ease-out;">
            <div style="display: flex; align-items: start; gap: 0.75rem;">
                <i class="fas fa-check-circle" style="font-size: 1.5rem; margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <p style="margin: 0; font-weight: 600; font-size: 0.95rem;"><?= $_SESSION['success_message'] ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.style.display='none'"
                    style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">×</button>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div
            style="position: fixed; top: 80px; right: 20px; z-index: 10000; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); max-width: 400px; animation: slideIn 0.3s ease-out;">
            <div style="display: flex; align-items: start; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle" style="font-size: 1.5rem; margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <p style="margin: 0; font-weight: 600; font-size: 0.95rem;"><?= $_SESSION['error_message'] ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.style.display='none'"
                    style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">×</button>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>


    <!-- Hero Section -->
    <section class="hero-section"
        style="background: linear-gradient(rgba(37, 99, 235, 0.9), rgba(37, 99, 235, 0.8)), url('<?= htmlspecialchars($hero['background_image']) ?>'); background-size: cover; background-position: center;">
        <div class="container">
            <div class="hero-content">
                <h2><?= htmlspecialchars($hero['title']) ?></h2>
                <div class="subtitle"><?= html_entity_decode($hero['subtitle']) ?></div>
                <div class="cta-group">
                    <a href="#services" class="btn btn-primary"
                        style="background: linear-gradient(135deg, #001e6e, var(--secondary-color)); border: none;"><?= htmlspecialchars($hero['cta_primary']) ?></a>
                    <a href="#contact" class="btn btn-secondary"><?= htmlspecialchars($hero['cta_secondary']) ?></a>
                </div>
            </div>
        </div>
    </section>
    <!-- Sondage Section -->
    <?php if ($sondage['enabled'] ?? false): ?>
        <section class="sondage-highlight-section">
            <div class="container">
                <div class="sondage-highlight">
                    <div class="sondage-content">
                        <h2><?= htmlspecialchars($sondage['title'] ?? '🗳️ Sondage en Cours') ?></h2>
                        <h3><?= htmlspecialchars($sondage['subtitle'] ?? 'Faites Évoluer Nos Services') ?></h3>
                        <div>
                            <?= html_entity_decode($sondage['description'] ?? 'Aidez-nous à adapter nos objectifs pour mieux répondre aux besoins réels des particuliers et entreprises. Votre avis compte !') ?>
                        </div>
                        <div class="sondage-stats">
                            <div class="stat">
                                <span class="stat-number"><?= htmlspecialchars($sondage['participants'] ?? '20+') ?></span>
                                <span class="stat-label">Participants</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?= htmlspecialchars($sondage['days_left'] ?? '15') ?></span>
                                <span class="stat-label">Jours restants</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?= htmlspecialchars($sondage['satisfaction'] ?? '60%') ?></span>
                                <span class="stat-label">Ont appreciés</span>
                            </div>
                        </div>
                        <a href="pages/sondage/sondage.php" class="btn btn-primary btn-huge">Participer au Sondage</a>
                    </div>
                    <div class="sondage-visual">
                        <div class="survey-icon">📊</div>
                        <div class="floating-elements">
                            <div class="floating-element">💡</div>
                            <div class="floating-element">🎯</div>
                            <div class="floating-element">📈</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>


    <!-- Méthodologie Section -->
    <section id="methodologie" class="methodologie-section">
        <div class="container">
            <h2 class="section-title">Notre Méthodologie</h2>
            <p class="section-description">Formation, Suivi, Conseil : une approche complète pour votre réussite</p>
            <div class="steps">
                <div class="step">
                    <h3>Formation</h3>
                    <p>Apprentissage théorique et pratique adapté à vos besoins.</p>
                </div>
                <div class="step">
                    <h3>Suivi</h3>
                    <p>Accompagnement continu pour assurer votre progression.</p>
                </div>
                <div class="step">
                    <h3>Conseil</h3>
                    <p>Conseils stratégiques pour optimiser vos projets.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FORMATIONS PREVIEW -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Nos Formations</h2>
            <p class="section-description">Découvrez notre catalogue de formations certifiantes en IA, cybersécurité et
                systèmes d'information</p>
            <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));"
                id="formations-preview">
                <!-- Formations will be loaded dynamically -->
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="pages/Formations/formations.php" class="btn btn-xl"
                    style="background: linear-gradient(135deg, var(--primary-color), var(--tertiary-color)); border: none;">Voir
                    toutes nos formations</a>
            </div>
        </div>
    </section>

    <!-- RESSOURCES PREVIEW -->
    <section class="services-section">
        <div class="container">
            <h2 class="section-title">Ressources Gratuites</h2>
            <p class="section-description">Téléchargez nos guides, checklists et outils pour accélérer votre
                apprentissage</p>
            <div class="service-cards" id="ressources-preview">
                <!-- Ressources will be loaded dynamically -->
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="pages/Ressources/ressources.php" class="btn btn-xl"
                    style="background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); border: none;">Explorer
                    toutes les ressources</a>
            </div>
        </div>
    </section>

    <!-- BLOGS PREVIEW -->
    <section class="formation-section">
        <div class="container">
            <h2 class="section-title">Derniers Articles</h2>
            <p class="section-description">Restez informé des dernières tendances et innovations technologiques</p>
            <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));"
                id="blogs-preview">
                <!-- Blogs will be loaded dynamically -->
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="pages/Blogs/blogs.php" class="btn btn-xl"
                    style="background: linear-gradient(135deg, var(--tertiary-color), var(--primary-color)); border: none;">Voir
                    tous les articles</a>
            </div>
        </div>
    </section>


    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            <p class="section-description">Nous accompagnons votre entreprise dans sa transformation digitale</p>
            <div class="service-cards">
                <?php foreach ($services as $service): ?>
                    <div class="card">
                        <img src="<?= htmlspecialchars($service['image'] ?? 'images/education.jpg') ?>"
                            alt="<?= htmlspecialchars($service['title'] ?? '') ?>"
                            style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
                        <h3><?= htmlspecialchars($service['title'] ?? '') ?></h3>
                        <div><?= html_entity_decode($service['description'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ABONNEMENTS SECTION -->
    <section class="services-section" id='abonnements' style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="container">
            <h2 class="section-title" style="color: white;">Abonnements</h2>
            <p class="section-description" style="color: rgba(255,255,255,0.8);">Accédez à l'excellence avec nos offres
                d'abonnement sur mesure</p>

            <div
                style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border-radius: 20px; padding: 3.5rem; margin-top: 3rem; border: 1px solid rgba(255,255,255,0.1); text-align: center;">

                <div style="max-width: 900px; margin: 0 auto;">

                    <h3
                        style="font-size: 2.2rem; font-weight: 700; margin-bottom: 2rem; background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1.3;">
                        Propulsez Votre Carrière 
                    </h3>

                    <p style="font-size: 1.2rem; color: rgba(255,255,255,0.95); line-height: 1.8; margin-bottom: 2.5rem; font-weight: 300;">
                        Tous nos services ne sont pas gratuits.<br>
                        Mais offrez vous le meilleur car le vous meritez !<br>
                        Nos abonnements premium vous donnent un accès complet à nos <strong style="color: #60a5fa;">meilleures formations</strong>, 
                        un accompagnement personnalisé avec nos <strong style="color: #a78bfa;">connaissances & notre equipe </strong>, 
                        et toutes les ressources (pdf,videos,outils...) telechargeables pour accélérer votre montée en compétences.<br>
                        Cliquez sur le bouton  <strong style="color: #10b981;">"Découvrir Nos Offres"</strong> pour avoir tous les details.
                    </p>

                    <div>
                        <a href="#abonnements" class="btn btn-xl"
                            style="background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: none; font-weight: 700; padding: 1.5rem 3rem; font-size: 1.2rem; box-shadow: 0 20px 40px rgba(59, 130, 246, 0.4); transition: all 0.3s;">
                            Découvrir Nos Offres
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>




    <!-- FAQ Section -->
    <section id="faq" class="faq-section">
        <div class="container">
            <h2 class="section-title">Questions Fréquentes</h2>
            <p class="section-description">Tout ce que vous devez savoir sur nos services</p>

            <?php
            $faq_items = json_decode(file_get_contents('pages/admin/data/faq.json'), true) ?? [];
            ?>

            <div class="faq-grid">
                <?php foreach ($faq_items as $index => $item): ?>
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <span><?= htmlspecialchars($item['question']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p><?= htmlspecialchars($item['answer']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Final Section -->
    <section class="cta-final-section">
        <div class="container">
            <h2>Prêt à transformer votre entreprise ?</h2>
            <p>Contactez-nous dès aujourd'hui pour discuter de vos besoins.</p>
            <form class="cta-form"
                style="display: flex; gap: 1rem; justify-content: center; align-items: center; margin-top: 2rem; position: relative; z-index: 10;">
                <input type="email" name="email" placeholder="Votre email professionnel" class="cta-input" required
                    style="padding: 1rem 1.5rem; border: 2px solid rgba(255,255,255,0.7); border-radius: var(--radius-xl); font-size: 1.1rem; width: 300px; background: white; color: #1f2937; outline: none; position: relative; z-index: 11;" />
                <button type="submit" class="btn btn-huge" style="margin: 0; position: relative; z-index: 11;">Commencer
                    maintenant</button>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const faqGrid = document.querySelector('.faq-grid');

            if (faqGrid) {
                faqGrid.addEventListener('click', function (e) {
                    const button = e.target.closest('.faq-question');
                    if (!button) return;

                    const expanded = button.getAttribute('aria-expanded') === 'true';

                    // Close all others
                    const allButtons = faqGrid.querySelectorAll('.faq-question');
                    allButtons.forEach(btn => {
                        btn.setAttribute('aria-expanded', 'false');
                        btn.nextElementSibling.style.maxHeight = null;
                    });

                    // Toggle current if it wasn't expanded
                    if (!expanded) {
                        button.setAttribute('aria-expanded', 'true');
                        const answer = button.nextElementSibling;
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                    }
                });
            }
        });
    </script>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">FSCo</div>
                    <p>Formation Suivi Conseil - Votre partenaire digital</p>
                </div>
                <div class="footer-links">
                    <h4>Liens utiles</h4>
                    <ul>
                        <li><a href="#services">Services</a></li>
                        <li><a href="pages/Formations/formations.php">Formations</a></li>
                        <li><a href="pages/Ressources/ressources.php">Ressources</a></li>
                        <li><a href="pages/Blogs/blogs.php">Blogs</a></li>
                        <li><a href="pages/Suivi/suivi.php">Suivi</a></li>
                        <li><a href="pages/sondage/sondage.php">Sondage</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p>Email: <?= htmlspecialchars($general['contact_email']) ?></p>
                    <p>Téléphone: <?= htmlspecialchars($general['contact_phone']) ?></p>
                    <p>Adresse: <?= htmlspecialchars($general['contact_address']) ?></p>
                </div>
            </div>
        </div>
    </footer>
    <?php
    $formations = json_decode(file_get_contents('pages/admin/data/formations.json'), true) ?? [];
    $formations = array_filter($formations, function ($f) {
        return ($f['statut'] ?? 'brouillon') === 'publié';
    });
    $ressources = json_decode(file_get_contents('pages/admin/data/ressources.json'), true) ?? [];
    $ressources = array_filter($ressources, function ($r) {
        return ($r['statut'] ?? 'brouillon') === 'publié';
    });
    $blogs = json_decode(file_get_contents('pages/admin/data/blogs.json'), true) ?? [];
    $blogs = array_filter($blogs, function ($b) {
        return ($b['statut'] ?? 'brouillon') === 'publié';
    });
    ?>

    <script>
        const formationsData = <?php echo json_encode(array_reverse($formations)); ?> || [];
        const ressourcesData = <?php echo json_encode(array_reverse($ressources)); ?> || [];
        const blogsData = <?php echo json_encode(array_reverse($blogs)); ?> || [];

        // Fonction de validation email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Gestion du formulaire CTA
        const ctaForm = document.querySelector('.cta-form');
        if (ctaForm) {
            ctaForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                const originalText = btn.textContent;
                btn.textContent = 'Envoi...';
                btn.disabled = true;

                const formData = new FormData(this);
                // Add default values if missing
                if (!formData.has('nom')) formData.append('nom', 'Visiteur CTA');
                if (!formData.has('message')) formData.append('message', 'Inscription via CTA Homepage');

                fetch('pages/includes/form_mail_handler.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') this.reset();
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erreur de connexion');
                    })
                    .finally(() => {
                        btn.textContent = originalText;
                        btn.disabled = false;
                    });
            });
        }

        // Validation basique des autres formulaires
        const forms = document.querySelectorAll('form:not(.start-form)');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-error');
                        isValid = false;
                    } else {
                        field.classList.remove('is-error');
                        field.classList.add('is-success');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires.');
                }
            });
        });

        // Fonctionnalité du bouton retour en haut
        // Fonctionnalité du bouton retour en haut
        document.addEventListener('DOMContentLoaded', () => {
            const backToTopButton = document.querySelector('.back-to-top');

            if (backToTopButton) {
                window.addEventListener('scroll', () => {
                    if (window.pageYOffset > 300) {
                        backToTopButton.classList.add('show');
                    } else {
                        backToTopButton.classList.remove('show');
                    }
                });

                backToTopButton.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });

        // Load dynamic content
        function loadPreviews() {
            try {
                // Load formations
                const formations = formationsData;
                const formationsContainer = document.getElementById('formations-preview');

                formations.slice(0, 3).forEach((formation, index) => {
                    const card = document.createElement('div');
                    card.className = 'card';

                    const badges = ['Certification', 'Sécurité', 'Cloud'];
                    const badge = badges[index] || 'Formation';

                    const imageHtml = formation.image
                        ? `<img src="${formation.image}" alt="${formation.titre}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">`
                        : `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
                             <i class="fas fa-graduation-cap" style="font-size: 4rem; color: var(--primary-color); opacity: 0.5;"></i>
                           </div>`;

                    card.innerHTML = `
                        <div style="position: relative;">
                            ${imageHtml}
                            <div style="position: absolute; top: 10px; right: 10px; background: ${index === 0 ? 'var(--primary-color)' : index === 1 ? '#f44336' : '#2196f3'}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">${badge}</div>
                        </div>
                        <h3>${formation.titre}</h3>
                        <p>${formation.description}</p>
                        <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
                            <span><i class="fas fa-clock"></i> ${formation.duree}</span>
                            <span><i class="fas fa-users"></i> ${formation.niveau}</span>
                        </div>
                        <a href="pages/Formations/formations.php" class="btn btn-primary">En savoir plus</a>
                    `;

                    formationsContainer.appendChild(card);
                });

                // Load ressources
                const ressources = ressourcesData;
                const ressourcesContainer = document.getElementById('ressources-preview');

                ressources.slice(0, 4).forEach((ressource, index) => {
                    const card = document.createElement('div');
                    card.className = 'card';

                    const badges = ['Guide Complet', 'Checklist', 'Architecture', 'Vidéo'];
                    const badge = badges[index] || 'Ressource';

                    const downloadCount = Math.floor(Math.random() * 2000) + 200;

                    const imageHtml = ressource.image
                        ? `<img src="${ressource.image}" alt="${ressource.titre}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">`
                        : `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
                             <i class="fas fa-file-alt" style="font-size: 4rem; color: var(--secondary-color); opacity: 0.5;"></i>
                           </div>`;

                    card.innerHTML = `
                        <div style="position: relative;">
                            ${imageHtml}
                            <div style="position: absolute; top: 10px; left: 10px; background: ${index === 0 ? 'var(--primary-color)' : index === 1 ? '#ff9800' : index === 2 ? '#2196f3' : '#4caf50'}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">${badge}</div>
                            <div style="position: absolute; top: 10px; right: 10px; background: #4caf50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Gratuit</div>
                        </div>
                        <h3>${ressource.titre}</h3>
                        <p>${ressource.description}</p>
                        <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
                            <span><i class="fas fa-file-${ressource.format.toLowerCase() === 'pdf' ? 'pdf' : ressource.format.toLowerCase() === 'video' ? 'video' : 'file'}"></i> ${ressource.format} - ${ressource.taille}</span>
                            <span><i class="fas fa-download"></i> ${downloadCount} téléchargements</span>
                        </div>
                        <a href="pages/Ressources/ressources.php" class="btn btn-primary">${ressource.format === 'Video' ? 'Regarder' : 'Voir plus'}</a>
                    `;

                    ressourcesContainer.appendChild(card);
                });

                // Load blogs
                const blogs = blogsData;
                const publishedBlogs = blogs.filter(blog => blog.statut === 'publié');
                const blogsContainer = document.getElementById('blogs-preview');

                publishedBlogs.slice(0, 2).forEach((blog, index) => {
                    const card = document.createElement('div');
                    card.className = 'card';

                    const views = Math.floor(Math.random() * 500) + 50;
                    const rating = (4.5 + Math.random() * 0.5).toFixed(1);

                    const imageHtml = blog.image
                        ? `<img src="${blog.image}" alt="${blog.titre}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">`
                        : `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
                             <i class="fas fa-newspaper" style="font-size: 4rem; color: var(--tertiary-color); opacity: 0.5;"></i>
                           </div>`;

                    card.innerHTML = `
                        <div style="position: relative;">
                            ${imageHtml}
                            <div style="position: absolute; top: 10px; right: 10px; background: ${index === 0 ? 'var(--primary-color)' : '#f44336'}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">${blog.categorie}</div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <h3>${blog.titre}</h3>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 600;">${'★'.repeat(Math.floor(rating))}${'☆'.repeat(5 - Math.floor(rating))} ${rating}</div>
                                <div style="font-size: 0.8rem; color: #666;">${views} vues</div>
                            </div>
                        </div>
                        <p>${blog.extrait}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <div style="font-size: 0.8rem; color: #666;">
                                <i class="fas fa-user"></i> ${blog.auteur} • <i class="fas fa-calendar"></i> ${new Date(blog.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })}
                            </div>
                            <a href="pages/Blogs/article.php?id=${blog.id}" class="btn btn-secondary">Lire la suite</a>
                        </div>
                    `;

                    blogsContainer.appendChild(card);
                });
            } catch (error) {
                console.error('Erreur lors du chargement des aperçus:', error);
            }
        }

        // Load previews on page load
        document.addEventListener('DOMContentLoaded', loadPreviews);

        // Animation d'entrée des cartes
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observer toutes les cartes (avec un délai pour les cartes dynamiques)
        setTimeout(() => {
            document.querySelectorAll('.card').forEach(card => {
                observer.observe(card);
            });
        }, 1000);
    </script>

    <!-- Back to top button -->
    <button class="back-to-top" title="Retour en haut">↑</button>
</body>

</html>