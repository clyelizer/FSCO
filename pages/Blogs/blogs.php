<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blog & Actualités Tech - FSCo</title>
  <meta name="description"
    content="Restez informé des dernières tendances en technologie, IA, cybersécurité et transformation digitale avec nos articles d'experts.">
  <link rel="stylesheet" href="../../index.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <!-- HEADER -->
  <?php include '../includes/header.php'; ?>

  <!-- HERO SECTION -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-content">
        <h2>Nos Articles de Blog</h2>
        <p class="subtitle">Découvrez nos dernières publications sur l'IA, la cybersécurité, et la transformation
          digitale. Conseils d'experts et analyses approfondies.</p>
        <div class="hero-actions">
          <a href="#catalogue" class="btn btn-primary">Voir le Catalogue</a>
          <a href="#contact" class="btn btn-secondary">S'inscrire</a>
        </div>
      </div>
    </div>
  </section>

  <!-- FILTRES ET RECHERCHE -->
  <section class="features-section">
    <div class="container">
      <div style="display: flex; gap: 2rem; margin-bottom: 3rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
          <input type="text" id="blogSearch" placeholder="Rechercher un article..."
            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Tous niveaux</option>
            <option>Débutant</option>
            <option>Intermédiaire</option>
            <option>Avancé</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <!-- CATALOGUE FORMATIONS -->
  <section id="catalogue" class="services-section">
    <div class="container">
      <h2 class="section-title">Nos Articles</h2>
      <p class="section-description">Découvrez l'actualité du numerique , du web, de la robotique et bien plus ainsi que nos analyses, guides pratiques et articles </p>

      <?php
      // Charger les blogs publiés
      $blogs = json_decode(file_get_contents('../../pages/admin/data/blogs.json'), true) ?? [];
      $blogs = array_filter($blogs, function ($b) {
        return ($b['statut'] ?? 'brouillon') === 'publié';
      });
     $blogs=array_reverse($blogs);
      ?>

      <div class="service-cards">
        <?php if (empty($blogs)): ?>
          <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
            <i class="fas fa-newspaper" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="color: #64748b;">Aucun article disponible</h3>
            <p style="color: #94a3b8;">Nos articles seront bientôt disponibles. Revenez prochainement !</p>
          </div>
        <?php else: ?>
          <?php foreach ($blogs as $blog): ?>
            <div class="card blog-card" data-title="<?= htmlspecialchars(strtolower($blog['titre'] ?? '')) ?>"
              data-desc="<?= htmlspecialchars(strtolower($blog['description'] ?? '')) ?>">
              <div style="position: relative; height: 200px; border-radius: 8px 8px 0 0; overflow: hidden;">
                <?php if (!empty($blog['image'])): ?>
                  <img src="../../<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['titre'] ?? '') ?>"
                    style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div
                    style="width: 100%; height: 100%; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-newspaper" style="font-size: 4rem; color: var(--tertiary-color); opacity: 0.5;"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <h3><?= htmlspecialchars($blog['titre'] ?? '') ?></h3>
              </div>
              <p><?= htmlspecialchars($blog['description'] ?? '') ?></p>
              <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($blog['auteur'] ?? 'FSCo') ?></span>
                <span><i class="fas fa-calendar"></i> <?= htmlspecialchars($blog['date'] ?? date('d/m/Y')) ?></span>
              </div>
              <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <span
                  style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                  <?= htmlspecialchars($blog['categorie'] ?? '') ?>
                </span>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <a href="article.php?id=<?= htmlspecialchars($blog['id']) ?>" class="btn btn-primary" style="flex: 1;">Lire
                  l'article</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>



  <!-- FAQ -->
  <section class="faq-section">
    <div class="container">
      <h2 class="section-title">Questions Fréquentes</h2>
      <p class="section-description">Tout savoir sur notre blog et nos publications</p>

      <?php
      $faq_items = json_decode(file_get_contents('../../pages/admin/data/faq_blogs.json'), true) ?? [];
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

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const faqQuestions = document.querySelectorAll('.faq-question');

      faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
          const expanded = question.getAttribute('aria-expanded') === 'true';

          // Close all others
          faqQuestions.forEach(q => {
            q.setAttribute('aria-expanded', 'false');
            q.nextElementSibling.style.maxHeight = null;
          });

          // Toggle current
          if (!expanded) {
            question.setAttribute('aria-expanded', 'true');
            const answer = question.nextElementSibling;
            answer.style.maxHeight = answer.scrollHeight + 'px';
          }
        });
      });
    });
  </script>

  <!-- NEWSLETTER -->
  <section class="cta-final-section">
    <div class="container">
      <h2>Ne manquez aucun article</h2>
      <p>Recevez nos nouveaux articles directement dans votre boîte mail et restez à la pointe de l'innovation.</p>
      <form class="cta-form"
        style="display: flex; gap: 1rem; justify-content: center; align-items: center; margin-top: 2rem;">
        <input type="email" placeholder="Votre email professionnel" class="cta-input" required
          style="padding: 1rem 1.5rem; border: 2px solid rgba(255,255,255,0.7); border-radius: var(--radius-xl); font-size: 1.1rem; width: 300px; background: white; color: #1f2937; outline: none;" />
        <button type="submit" class="btn btn-huge" style="margin: 0;">Je veux recevoir les actualités</button>
      </form>
    </div>
  </section>

  <!-- FOOTER -->
  <!-- FOOTER -->
  <?php include '../includes/footer.php'; ?>

  <script>
    // Gestion du formulaire CTA
    const ctaForm = document.querySelector('.cta-form');
    if (ctaForm) {
      ctaForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const emailInput = ctaForm.querySelector('.cta-input');
        const email = emailInput.value.trim();

        if (!email) {
          alert('Veuillez saisir votre email professionnel.');
          emailInput.focus();
          return;
        }

        if (!isValidEmail(email)) {
          alert('Veuillez saisir un email valide.');
          emailInput.focus();
          return;
        }

        // Simulation d'envoi aux admins
        console.log('Email formations CTA envoyé aux admins:', email);
        alert('Merci ! Votre demande a été envoyée. Nos experts vous contacteront sous 24h.');

        // Reset du formulaire
        ctaForm.reset();
      });
    }

    // Fonction de validation email
    function isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }

    // Search Functionality
    document.getElementById('blogSearch').addEventListener('input', function (e) {
      const searchTerm = e.target.value.toLowerCase();
      const cards = document.querySelectorAll('.blog-card');

      cards.forEach(card => {
        const title = card.getAttribute('data-title');
        const desc = card.getAttribute('data-desc');

        if (title.includes(searchTerm) || desc.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>