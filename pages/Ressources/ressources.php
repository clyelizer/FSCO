<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

/*
 * SQL SCHEMA FOR DEPLOYMENT:
 * 
 * CREATE TABLE IF NOT EXISTS user_library (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     user_id INT NOT NULL,
 *     resource_id VARCHAR(255) NOT NULL,
 *     type VARCHAR(50) NOT NULL DEFAULT 'ressource',
 *     status ENUM('favoris', 'en_cours', 'termine') DEFAULT 'favoris',
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     UNIQUE KEY unique_user_resource (user_id, resource_id, type),
 *     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 */
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ressources Gratuites & Outils - FSCo</title>
  <meta name="description"
    content="Accédez à notre bibliothèque de ressources gratuites : guides PDF, checklists, vidéos et outils pour votre transformation digitale.">
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
        <h2>Nos Ressources</h2>
        <p class="subtitle">Découvrez notre bibliothèque complète de ressources gratuites pour accélérer votre
          apprentissage et votre transformation digitale.</p>
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
          <input type="text" id="resourceSearch" placeholder="Rechercher une ressource..."
            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <select id="filterLevel" style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Tous niveaux</option>
            <option value="Débutant">Débutant</option>
            <option value="Intermédiaire">Intermédiaire</option>
            <option value="Avancé">Avancé</option>
          </select>
          <select id="filterCategory"
            style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Toutes catégories</option>
            <option value="IA">IA</option>
            <option value="Cybersécurité">Cybersécurité</option>
            <option value="Développement">Développement</option>
            <option value="Business">Business</option>
          </select>
          <select id="filterFormat"
            style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option value="">Tous formats</option>
            <option value="PDF">PDF</option>
            <option value="Vidéo">Vidéo</option>
            <option value="Outil">Outil</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <!-- CATALOGUE FORMATIONS -->
  <section id="catalogue" class="services-section">
    <div class="container">
      <h2 class="section-title">Bibliothèque de Ressources</h2>
      <p class="section-description">Plus de 50 ressources gratuites : guides, templates, études de cas et outils pour
        accélérer votre transformation digitale.</p>

      <?php
      // Charger les ressources publiées
      $ressources = json_decode(file_get_contents('../../pages/admin/data/ressources.json'), true) ?? [];
      $ressources = array_filter($ressources, function ($r) {
        return ($r['statut'] ?? 'brouillon') === 'publié';
      });
      ?>

      <div class="service-cards">
        <?php if (empty($ressources)): ?>
          <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
            <i class="fas fa-book-open" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="color: #64748b;">Aucune ressource disponible</h3>
            <p style="color: #94a3b8;">Nos ressources seront bientôt disponibles. Revenez prochainement !</p>
          </div>
        <?php else: ?>
          <?php foreach ($ressources as $ressource):
            $lien = !empty($ressource['fichier']) ? '../../' . $ressource['fichier'] : ($ressource['url_externe'] ?? '#');
            ?>
            <div class="card resource-card" data-title="<?= htmlspecialchars(strtolower($ressource['titre'] ?? '')) ?>"
              data-desc="<?= htmlspecialchars(strtolower($ressource['description'] ?? '')) ?>"
              data-level="<?= htmlspecialchars($ressource['niveau'] ?? '') ?>"
              data-category="<?= htmlspecialchars($ressource['categorie'] ?? '') ?>"
              data-format="<?= htmlspecialchars($ressource['format'] ?? '') ?>">
              <div style="position: relative; height: 200px; border-radius: 8px 8px 0 0; overflow: hidden;">
                <?php if (!empty($ressource['image'])): ?>
                  <img src="../../<?= htmlspecialchars($ressource['image']) ?>"
                    alt="<?= htmlspecialchars($ressource['titre'] ?? '') ?>"
                    style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                  <div
                    style="width: 100%; height: 100%; background: linear-gradient(135deg, #f6f9fc, #eef2f7); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-file-alt" style="font-size: 4rem; color: var(--secondary-color); opacity: 0.5;"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <h3><?= htmlspecialchars($ressource['titre'] ?? '') ?></h3>
              </div>
              <p><?= htmlspecialchars($ressource['description'] ?? '') ?></p>
              <div style="display: flex; gap: 16px; margin: 1rem 0; font-size: 0.9rem; color: #666;">
                <span><i class="fas fa-file-pdf"></i> <?= htmlspecialchars($ressource['format'] ?? 'PDF') ?></span>
              </div>
              <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <span
                  style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                  <?= htmlspecialchars($ressource['categorie'] ?? '') ?>
                </span>
              </div>
              <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                  <a href="viewer.php?id=<?= htmlspecialchars($ressource['id']) ?>" class="btn btn-secondary"
                    style="justify-content: center; padding: 0.6rem; font-size: 0.85rem; text-decoration: none;">
                    <i class="fas fa-eye" style="margin-right: 0.5rem;"></i> Consulter
                  </a>
                  <a href="download_handler.php?id=<?= htmlspecialchars($ressource['id']) ?>" class="btn btn-primary"
                    style="justify-content: center; padding: 0.6rem; font-size: 0.85rem; text-decoration: none;">
                    <i class="fas fa-lock" style="margin-right: 0.5rem;"></i> Télécharger
                  </a>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                  <button onclick="addToLibrary('<?= htmlspecialchars($ressource['id']) ?>')" class="btn btn-outline"
                    style="justify-content: center; padding: 0.6rem; font-size: 0.85rem;">
                    <i class="fas fa-bookmark" style="margin-right: 0.5rem;"></i> Ajouter à ma bibliothèque
                  </button>
                  <button
                    onclick="openCommentModal('<?= htmlspecialchars($ressource['id']) ?>', '<?= htmlspecialchars(addslashes($ressource['titre'] ?? '')) ?>')"
                    class="btn btn-outline" style="justify-content: center; padding: 0.6rem; font-size: 0.85rem;">
                    <i class="fas fa-comment" style="margin-right: 0.5rem;"></i> Donner votre avis
                  </button>
                </div>
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
      <p class="section-description">Tout savoir sur nos ressources gratuites</p>

      <?php
      $faq_items = json_decode(file_get_contents('../../pages/admin/data/faq_ressources.json'), true) ?? [];
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

  <!-- MODALS -->

  <!-- Comment Modal -->
  <div id="commentModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div
      style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; position: relative;">
      <button onclick="closeModal('commentModal')"
        style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
      <h3 style="margin-top: 0;">Donner votre avis</h3>
      <p id="commentResourceTitle" style="color: #666; margin-bottom: 1rem;"></p>

      <form id="commentForm">
        <input type="hidden" id="commentResourceId" name="resource_id">
        <input type="hidden" id="commentResourceName" name="resource_title">
        <input type="hidden" name="action" value="send_comment">

        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.5rem;">Votre commentaire</label>
          <textarea name="comment" rows="4"
            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;" required
            placeholder="Ce que vous avez pensé de cette ressource..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Envoyer</button>
      </form>
    </div>
  </div>

  <!-- Login/Subscription Modal -->
  <div id="loginModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div
      style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 400px; position: relative; text-align: center;">
      <button onclick="closeModal('loginModal')"
        style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
      <div
        style="width: 60px; height: 60px; background: #e3f2fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
        <i class="fas fa-lock" style="font-size: 1.5rem; color: var(--primary-color);"></i>
      </div>
      <h3>Accès Réservé</h3>
      <p style="color: #666; margin-bottom: 1.5rem;">La bibliotheque est réservée à nos membres
        connectés<br>Prenez 2 mins pour vous connecter ou creer un compte.</p>

      <div style="display: flex; flex-direction: column; gap: 0.5rem;">
        <a href="../../pages/auth/login.php" class="btn btn-primary">Se connecter</a>
        <a href="../../pages/auth/login.php?mode=register" class="btn btn-outline">Créer un compte gratuit</a>
      </div>
    </div>
  </div>

  <script>
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;

    // Modal functions
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    window.onclick = function (event) {
      if (event.target.id === 'commentModal') closeModal('commentModal');
      if (event.target.id === 'loginModal') closeModal('loginModal');
    }

    // Add to Library
    function addToLibrary(id) {
      if (!isLoggedIn) {
        document.getElementById('loginModal').style.display = 'flex';
        return;
      }

      const btn = event.currentTarget;
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('action', 'add_to_library');
      formData.append('resource_id', id);
      formData.append('type', 'ressource');

      fetch('actions.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            btn.innerHTML = '<i class="fas fa-check"></i> Ajouté';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline');
          } else {
            alert(data.message);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          btn.innerHTML = originalHtml;
          btn.disabled = false;
        });
    }

    // Comment functions
    function openCommentModal(id, title) {
      document.getElementById('commentResourceId').value = id;
      document.getElementById('commentResourceName').value = title;
      document.getElementById('commentResourceTitle').textContent = title;
      document.getElementById('commentModal').style.display = 'flex';
    }

    document.getElementById('commentForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn.textContent;
      btn.textContent = 'Envoi...';
      btn.disabled = true;

      const formData = new FormData(this);

      fetch('actions.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.status === 'success') {
            closeModal('commentModal');
            this.reset();
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Une erreur est survenue.');
        })
        .finally(() => {
          btn.textContent = originalText;
          btn.disabled = false;
        });
    });

    // Download function
    function downloadResource(id, link) {
      if (!isLoggedIn) {
        document.getElementById('loginModal').style.display = 'flex';
        return;
      }

      // Proceed to download
      window.open(link, '_blank');
    }

    // Open Resource in new window and mark as reading
    function openResource(url, id) {
      console.log('🔍 openResource called with:', { url, id, isLoggedIn });

      if (!url || url === '#') {
        console.warn('⚠️ No valid URL provided');
        return;
      }

      // Trigger backend action to mark as reading (if logged in)
      if (isLoggedIn && id) {
        console.log('📚 Marking as reading...');
        const formData = new FormData();
        formData.append('action', 'mark_as_reading');
        formData.append('resource_id', id);
        formData.append('type', 'ressource');

        fetch('actions.php', {
          method: 'POST',
          body: formData
        }).then(response => response.json())
          .then(data => console.log('✅ Marked as reading:', data))
          .catch(err => console.error('❌ Error marking as reading:', err));
      } else {
        console.log('⚠️ Not logged in or no ID, skipping mark as reading');
      }

      // Open in a new tab
      console.log('🚀 Opening URL in new tab:', url);
      const newWindow = window.open(url, '_blank');

      if (newWindow) {
        console.log('✅ New tab opened successfully');
      } else {
        console.error('❌ Failed to open new tab (popup blocker?)');
      }
    }

    // Gestion du formulaire CTA (existant)
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

    // Search and Filter Functionality
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('resourceSearch');
      const filterLevel = document.getElementById('filterLevel');
      const filterCategory = document.getElementById('filterCategory');
      const filterFormat = document.getElementById('filterFormat');
      const cards = document.querySelectorAll('.resource-card');

      function filterCards() {
        const searchTerm = searchInput.value.toLowerCase();
        const level = filterLevel.value.toLowerCase();
        const category = filterCategory.value.toLowerCase();
        const format = filterFormat.value.toLowerCase();

        cards.forEach(card => {
          const title = (card.getAttribute('data-title') || '').toLowerCase();
          const desc = (card.getAttribute('data-desc') || '').toLowerCase();
          const cardLevel = (card.getAttribute('data-level') || '').toLowerCase();
          const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
          const cardFormat = (card.getAttribute('data-format') || '').toLowerCase();

          let matchesSearch = title.includes(searchTerm) || desc.includes(searchTerm);
          let matchesLevel = !level || cardLevel === level;
          let matchesCategory = !category || cardCategory.includes(category);
          let matchesFormat = !format || cardFormat === format;

          if (matchesSearch && matchesLevel && matchesCategory && matchesFormat) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      }

      searchInput.addEventListener('input', filterCards);
      filterLevel.addEventListener('change', filterCards);
      filterCategory.addEventListener('change', filterCards);
      filterFormat.addEventListener('change', filterCards);
    });
  </script>
</body>

</html>