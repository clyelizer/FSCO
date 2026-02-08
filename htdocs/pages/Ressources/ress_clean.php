<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formations - FSCo</title>
  <link rel="stylesheet" href="../../index.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <!-- HEADER -->
  <header class="header">
    <div class="container header__inner">
      <a href="../../index.php" class="logo">
        <i class="fas fa-brain logo-icon"></i>
        <span>FSCo</span>
      </a>
      <nav class="nav">
        <a href="../../index.php" class="nav__link">Accueil</a>
        <a href="../Formations/formations.php" class="nav__link active">Formations</a>
        <a href="../Ressources/ressources.php" class="nav__link">Ressources</a>
        <a href="../Blogs/blogs.php" class="nav__link">Blogs</a>
        <a href="../maBiblio/bibliotheque.php" class="nav__link">Ma Bibliothèque</a>
        <a href="../Suivi/suivi.html" class="nav__link">Suivi</a>
      </nav>
      <div class="actions">
        <a href="../sondage/sondage.php" class="btn btn--outline">
          <i class="fas fa-user"></i> Mon Compte
        </a>
        <a href="#contact" class="btn btn--primary">Commencer</a>
      </div>
    </div>
  </header>

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
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Tous niveaux</option>
            <option>Débutant</option>
            <option>Intermédiaire</option>
            <option>Avancé</option>
          </select>
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Toutes durées</option>
            <option>1-2 semaines</option>
            <option>1 mois</option>
            <option>2-3 mois</option>
          </select>
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Tous prix</option>
            <option>0-500€</option>
            <option>500-1000€</option>
            <option>1000€+</option>
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
      $formations = json_decode(file_get_contents('../../pages/admin/data/formations.json'), true) ?? [];
      $formations = array_filter($formations, function ($f) {
        return ($f['statut'] ?? 'brouillon') === 'publié';
      });
      ?>

      <div class="service-cards">
        <?php if (empty($formations)): ?>
          <div style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
            <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="color: #64748b;">Aucune formation disponible</h3>
            <p style="color: #94a3b8;">Nos formations seront bientôt disponibles. Revenez prochainement !</p>
          </div>
        <?php else: ?>
          <?php foreach ($formations as $formation): ?>
            <div class="card">
              <div style="position: relative;">
                <img
                  src="<?= !empty($formation['image']) ? '../../' . htmlspecialchars($formation['image']) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400' ?>"
                  alt="<?= htmlspecialchars($formation['titre'] ?? '') ?>"
                  style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
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

  <!-- TEMOIGNAGES FORMATIONS -->
  <section class="testimonials">
    <div class="container">
      <h2>Avis de nos apprenants</h2>
      <p class="section-subtitle">Découvrez ce que disent nos participants sur leurs expériences de formation</p>

      <div class="testimonials__grid">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <p class="quote">"Formation exceptionnelle en IA. Les formateurs sont des experts et le contenu est très
            pratique. J'ai pu appliquer immédiatement les concepts appris dans mon travail."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=marie" alt="Marie Dubois" style="border-radius: 50%;" />
            <div>
              <cite>Marie Dubois</cite>
              <small>Data Scientist, TechCorp</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Formation IA - Niveau Avancé
          </div>
        </div>

        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <p class="quote">"La formation cybersécurité m'a ouvert les yeux sur les menaces actuelles. Très pédagogique
            et les exercices pratiques sont excellents."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=thomas" alt="Thomas Martin" style="border-radius: 50%;" />
            <div>
              <cite>Thomas Martin</cite>
              <small>CISO, InnovateLab</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Cybersécurité Avancée
          </div>
        </div>

        <div class="testimonial-card">
          <div class="stars">★★★★☆</div>
          <p class="quote">"Parfait pour quelqu'un qui débute. Les formateurs prennent le temps d'expliquer et le suivi
            personnalisé est top."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=sophie" alt="Sophie Laurent" style="border-radius: 50%;" />
            <div>
              <cite>Sophie Laurent</cite>
              <small>Développeuse, StartUpTech</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Automatisation RPA
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FORMATEURS -->
  <section class="formation-section">
    <div class="container">
      <h2 class="section-title">Nos Formateurs Experts</h2>
      <p class="section-description">Apprenez avec des professionnels reconnus dans leur domaine</p>

      <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/120?u=expert1" alt="Dr. Ahmed Bennani"
            style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Dr. Ahmed Bennani</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">IA & Machine Learning</p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">PhD en IA, 15 ans d'expérience chez Google et
            Microsoft. Auteur de 3 livres sur le ML.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">4.9/5</span>
            <span style="color: #666;">250+ élèves</span>
          </div>
        </div>

        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/120?u=expert2" alt="Sarah El Mansouri"
            style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Sarah El Mansouri</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">Cybersécurité</p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Certifiée CISSP, ancienne consultante en
            sécurité chez Deloitte. Spécialiste en audit et conformité.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">4.8/5</span>
            <span style="color: #666;">180+ élèves</span>
          </div>
        </div>

        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/120?u=expert3" alt="Youssef Tazi"
            style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Youssef Tazi</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">Architecture SI</p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Architecte cloud certifié AWS/Azure, 12 ans
            d'expérience en transformation digitale.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">4.7/5</span>
            <span style="color: #666;">320+ élèves</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="features-section">
    <div class="container">
      <h2 class="section-title">Questions Fréquentes</h2>
      <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
          <div
            style="background: var(--light-bg); padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem;">Comment se déroule l'inscription ?</h4>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div style="padding: 1rem; display: none;">
            <p>L'inscription se fait en ligne via notre plateforme. Après paiement, vous recevez immédiatement vos accès
              à la formation et au groupe d'entraide.</p>
          </div>
        </div>

        <div style="margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
          <div
            style="background: var(--light-bg); padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem;">Les formations sont-elles éligibles au CPF ?</h4>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div style="padding: 1rem; display: none;">
            <p>Oui, toutes nos formations sont éligibles au CPF. Nous vous accompagnons dans les démarches
              administratives.</p>
          </div>
        </div>

        <div style="margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
          <div
            style="background: var(--light-bg); padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem;">Quel est le délai d'accès après inscription ?</h4>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div style="padding: 1rem; display: none;">
            <p>L'accès est immédiat après inscription. Vous pouvez commencer la formation quand vous le souhaitez.</p>
          </div>
        </div>

        <div style="margin-bottom: 1rem; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
          <div
            style="background: var(--light-bg); padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1rem;">Proposez-vous des formations intra-entreprise ?</h4>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div style="padding: 1rem; display: none;">
            <p>Oui, nous proposons des formations sur mesure pour les entreprises. Contactez-nous pour un devis
              personnalisé.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

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
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo">FSCo</div>
          <p>Formation Suivi Conseil - Votre partenaire digital</p>
        </div>
        <div class="footer-links">
          <h4>Liens utiles</h4>
          <ul>
            <li><a href="../../index.php#services">Services</a></li>
            <li><a href="../Formations/formations.php">Formation</a></li>
            <li><a href="../../index.php#methodologie">Méthodologie</a></li>
            <li><a href="../sondage/sondage.php">Sondage</a></li>
          </ul>
        </div>
        <div class="footer-contact">
          <h4>Contact</h4>
          <p>Email: clyelise1@gmail.com</p>
          <p>Téléphone: +212 698771627</p>
          <p>Adresse: Casablanca, Maroc</p>
        </div>
      </div>
    </div>
  </footer>

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