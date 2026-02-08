<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formations Certifiantes - FSCo</title>
  <meta name="description"
    content="Découvrez nos formations en IA, cybersécurité et systèmes d'information. Apprenez avec des experts et obtenez des certifications reconnues.">
  <link rel="stylesheet" href="../../index.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <!-- HEADER -->
  <!-- HEADER -->
  <?php include '../includes/header.php'; ?>



  <!-- HERO SECTION -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-content">
        <h2>Nos Formations</h2>
        <p class="subtitle">Découvrez notre catalogue complet de formations en IA, cybersécurité et systèmes
          d'information. Apprentissage interactif avec certifications reconnues.</p>
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
          <input type="text" placeholder="Rechercher une formation..."
            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <select id="filterLevel" style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Tous niveaux</option>
            <option value="Débutant">Débutant</option>
            <option value="Intermédiaire">Intermédiaire</option>
            <option value="Avancé">Avancé</option>
          </select>
          <select id="filterDuration"
            style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Toutes durées</option>
            <option value="court">Courte (1-2 semaines)</option>
            <option value="moyen">Moyenne (1 mois)</option>
            <option value="long">Longue (2-3 mois)</option>
          </select>
          <select id="filterPrice" style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Tous prix</option>
            <option value="gratuit">Gratuit</option>
            <option value="payant">Payant</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <!-- CATALOGUE FORMATIONS -->
  <section id="catalogue" class="services-section">
    <div class="container">
      <h2 class="section-title">Catalogue de Formations</h2>
      <p class="section-description">Des formations adaptées à tous les niveaux, de l'initiation à l'expertise, avec un
        suivi personnalisé et des certifications reconnues.</p>

      <?php
      // Charger les formations publiées
      $jsonPath = __DIR__ . '/../../pages/admin/data/formations.json';
      $formations = [];
      if (file_exists($jsonPath)) {
        $formations = json_decode(file_get_contents($jsonPath), true) ?? [];
      }
      $formations = array_filter($formations, function ($f) {
        // AFFICHER AUSSI LES BROUILLONS POUR LE DEBUG
        $statut = $f['statut'] ?? 'brouillon';
        return $statut === 'publié' || $statut === 'brouillon';
      });

      // Charger les témoignages
      $testimonialsPath = __DIR__ . '/../../pages/admin/data/testimonials.json';
      $testimonials = file_exists($testimonialsPath) ? (json_decode(file_get_contents($testimonialsPath), true) ?? []) : [];

      // Charger les formateurs
      $instructorsPath = __DIR__ . '/../../pages/admin/data/instructors.json';
      $instructors = file_exists($instructorsPath) ? (json_decode(file_get_contents($instructorsPath), true) ?? []) : [];
      ?>

      <div class="service-cards">
        <?php if (empty($formations)): ?>
          <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
            <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="color: #64748b;">Aucune formation disponible</h3>
            <p style="color: #94a3b8;">Nos formations seront bientôt disponibles. Revenez prochainement !</p>
          </div>
    </section>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('formationSearch');
        const filterLevel = document.getElementById('filterLevel');
        const filterDuration = document.getElementById('filterDuration');
        const filterPrice = document.getElementById('filterPrice');
        const cards = document.querySelectorAll('.formation-card');

        function filterCards() {
          const searchTerm = searchInput.value.toLowerCase();
          const level = filterLevel.value.toLowerCase();
          const duration = filterDuration.value.toLowerCase();
          const price = filterPrice.value.toLowerCase();

          cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const desc = card.getAttribute('data-desc') || '';
            const cardLevel = (card.getAttribute('data-level') || '').toLowerCase();
            const cardDuration = (card.getAttribute('data-duration') || '').toLowerCase();
            const cardPrice = parseFloat(card.getAttribute('data-price') || 0);

            let matchesSearch = title.includes(searchTerm) || desc.includes(searchTerm);
            let matchesLevel = !level || cardLevel === level;

            let matchesDuration = !duration;
            if (duration === 'court') matchesDuration = cardDuration.includes('semaine');
            else if (duration === 'moyen') matchesDuration = cardDuration.includes('1 mois');
            else if (duration === 'long') matchesDuration = cardDuration.includes('mois') && !cardDuration.includes('1 mois');

            let matchesPrice = !price;
            if (price === 'gratuit') matchesPrice = cardPrice === 0;
            else if (price === 'payant') matchesPrice = cardPrice > 0;

            if (matchesSearch && matchesLevel && matchesDuration && matchesPrice) {
              card.style.display = 'block';
            } else {
              card.style.display = 'none';
            }
          });
        }

        searchInput.addEventListener('input', filterCards);
        filterLevel.addEventListener('change', filterCards);
        filterDuration.addEventListener('change', filterCards);
        filterPrice.addEventListener('change', filterCards);
      });
    </script>
  <?php else: ?>
    <?php foreach ($formations as $formation): ?>
      <div class="card formation-card" data-title="<?= htmlspecialchars(strtolower($formation['titre'] ?? '')) ?>"
        data-desc="<?= htmlspecialchars(strtolower($formation['description'] ?? '')) ?>"
        data-level="<?= htmlspecialchars($formation['niveau'] ?? '') ?>"
        data-duration="<?= htmlspecialchars($formation['duree'] ?? '') ?>"
        data-price="<?= htmlspecialchars($formation['prix'] ?? 0) ?>">
        <div style="position: relative; height: 200px; border-radius: 8px 8px 0 0; overflow: hidden;">
          <?php if (!empty($formation['image'])): ?>
            <img src="../../<?= htmlspecialchars($formation['image']) ?>"
              alt="<?= htmlspecialchars($formation['titre'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <div
              style="width: 100%; height: 100%; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-graduation-cap" style="font-size: 4rem; color: var(--primary-color); opacity: 0.5;"></i>
            </div>
          <?php endif; ?>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
          <h3><?= htmlspecialchars($formation['titre'] ?? '') ?></h3>
          <div style="text-align: right;">
            <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">
              <?= number_format($formation['prix'] ?? 0, 0, ',', ' ') ?>€
            </div>
          </div>
        </div>
        <p><?= htmlspecialchars($formation['description'] ?? '') ?></p>
        <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
          <span><i class="fas fa-clock"></i> <?= htmlspecialchars($formation['duree'] ?? 'Non défini') ?></span>
          <span><i class="fas fa-users"></i> <?= htmlspecialchars($formation['niveau'] ?? 'Tous niveaux') ?></span>
        </div>
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
          <span
            style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
            <?= htmlspecialchars($formation['categorie'] ?? '') ?>
          </span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <a href="#" class="btn btn-secondary" style="flex: 1;">Détails</a>
          <a href="#" class="btn btn-primary" style="flex: 1;">S'inscrire</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
  </div>
  </section>

  <!-- TESTS SECTION -->
  <section id="tests" class="services-section">
    <div class="container">
      <h2 class="section-title">Tests Disponibles</h2>
      <p class="section-description">Testez vos connaissances avec nos tests et certifications en ligne.</p>

      <?php
      // Inclure la configuration et la base de données
      // Inclure la configuration et la base de données
      require_once __DIR__ . '/../admin/evaluations/config.php';
      require_once __DIR__ . '/../admin/evaluations/includes/database.php';

      try {
        // Récupérer les tests publics et publiés avec le nombre de questions et la durée totale
        $tests = Database::getInstance()->fetchAll(
          "SELECT e.id, e.titre, e.description, e.created_at, e.duree_minutes, 
                  COUNT(DISTINCT q.id) as question_count,
                  COALESCE(SUM(q.duree_estimee), 0) as total_duration_seconds
           FROM exam_examens e
           LEFT JOIN exam_examen_questions eq ON e.id = eq.examen_id
           LEFT JOIN exam_questions q ON eq.question_id = q.id
           WHERE e.is_public = 1 AND e.statut = 'published'
           GROUP BY e.id, e.titre, e.description, e.created_at, e.duree_minutes
           ORDER BY e.created_at DESC"
        );
      } catch (Exception $e) {
        $tests = [];
        echo '<div style="background:#fee2e2;color:#991b1b;padding:1rem;margin:1rem;border-radius:8px;">';
        echo '<strong>Erreur SQL:</strong> ' . htmlspecialchars($e->getMessage());
        echo '</div>';
      }
      ?>

      <div class="service-cards">
        <?php if (empty($tests)): ?>
          <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
            <i class="fas fa-clipboard-check" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="color: #64748b;">Aucun test disponible</h3>
            <p style="color: #94a3b8;">De nouveaux tests seront bientôt ajoutés.</p>
          </div>
        <?php else: ?>
          <?php foreach ($tests as $test): ?>
            <div class="card">
              <div
                style="position: relative; height: 150px; border-radius: 8px 8px 0 0; overflow: hidden; background: linear-gradient(135deg, #4f46e5, #818cf8); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-alt" style="font-size: 4rem; color: white; opacity: 0.8;"></i>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <h3><?= htmlspecialchars($test['titre']) ?></h3>
              </div>
              <p><?= htmlspecialchars($test['description']) ?></p>
              <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
                <?php
                // Format duration
                $durationSeconds = $test['total_duration_seconds'];
                $durationDisplay = '';
                if ($durationSeconds < 60) {
                  $durationDisplay = $durationSeconds . ' sec';
                } else {
                  $minutes = floor($durationSeconds / 60);
                  $seconds = $durationSeconds % 60;
                  $durationDisplay = $minutes . ' min' . ($seconds > 0 ? ' ' . $seconds . 's' : '');
                }
                ?>
                <span><i class="fas fa-clock"></i> <?= $durationDisplay ?></span>
                <span><i class="fas fa-question-circle"></i> <?= $test['question_count'] ?>
                  Question<?= $test['question_count'] > 1 ? 's' : '' ?></span>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <a href="../admin/evaluations/student/start_exam.php?exam_id=<?= $test['id'] ?>" class="btn btn-primary"
                  style="flex: 1;">Commencer le test</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- TEMOIGNAGES FORMATIONS -->
  <section class="testimonials">
    <div class="container">
      <h2>Avis de nos apprenants</h2>
      <p class="section-subtitle">Découvrez ce que disent nos participants sur leurs expériences de formation</p>

      <div class="testimonials__grid">
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="testimonial-card">
            <div class="stars">
              <?= str_repeat('★', floor($testimonial['rating'])) . str_repeat('☆', 5 - floor($testimonial['rating'])) ?>
            </div>
            <p class="quote">"<?= htmlspecialchars($testimonial['text']) ?>"</p>
            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
              <img src="<?= htmlspecialchars($testimonial['avatar']) ?>"
                alt="<?= htmlspecialchars($testimonial['name']) ?>" style="border-radius: 50%;" />
              <div>
                <cite><?= htmlspecialchars($testimonial['name']) ?></cite>
                <small><?= htmlspecialchars($testimonial['role']) ?></small>
              </div>
            </div>
            <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
              <?= htmlspecialchars($testimonial['context']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FORMATEURS -->
  <section class="formation-section">
    <div class="container">
      <h2 class="section-title">Nos Formateurs Experts</h2>
      <p class="section-description">Apprenez avec des professionnels reconnus dans leur domaine</p>

      <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <?php foreach ($instructors as $instructor): ?>
          <div class="card" style="text-align: center;">
            <img src="<?= htmlspecialchars($instructor['avatar']) ?>" alt="<?= htmlspecialchars($instructor['name']) ?>"
              style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
            <h3><?= htmlspecialchars($instructor['name']) ?></h3>
            <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">
              <?= htmlspecialchars($instructor['specialty']) ?>
            </p>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;"><?= htmlspecialchars($instructor['bio']) ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
              <span
                style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;"><?= htmlspecialchars($instructor['rating']) ?>/5</span>
              <span style="color: #666;"><?= htmlspecialchars($instructor['students']) ?> élèves</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="faq-section">
    <div class="container">
      <h2 class="section-title">Questions Fréquentes</h2>
      <p class="section-description">Tout savoir sur nos formations</p>

      <?php
      $faqPath = __DIR__ . '/../../pages/admin/data/faq_formations.json';
      $faq_items = file_exists($faqPath) ? (json_decode(file_get_contents($faqPath), true) ?? []) : [];
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
  </script>
</body>

</html>