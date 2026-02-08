<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Suivi - FSCo</title>
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
        <h2>Suivi Personnalisé</h2>
        <p class="subtitle">Demandez un accompagnement personnalisé pour votre transformation digitale. Coaching,
          conseil stratégique et suivi de projet adapté à vos besoins.</p>
        <div class="hero-actions">
          <a href="#demande-suivi" class="btn btn-primary">Demander un Suivi</a>
          <a href="#services-suivi" class="btn btn-secondary">Nos Services</a>
        </div>
      </div>
    </div>
  </section>

  <!-- FORFAITS SUIVI -->
  <section class="features-section">
    <div class="container">
      <h2 class="section-title">Nos Forfaits de Suivi Personnalisé</h2>
      <p class="section-description">Choisissez le forfait adapté à vos besoins et à votre budget pour un accompagnement
        optimal.</p>

      <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-top: 3rem;">
        <!-- Forfait Essentiel -->
        <div class="card" style="border: 2px solid #e0e0e0; position: relative;">
          <div
            style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #666; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
            Essentiel</div>
          <div style="text-align: center; padding-top: 2rem;">
            <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">450€
            </div>
            <div style="color: #666; margin-bottom: 2rem;">par mois</div>
            <ul style="text-align: left; margin-bottom: 2rem;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>2 sessions de coaching/mois</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Email support prioritaire</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Ressources personnalisées</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Plan d'action mensuel</li>
              <li style="padding: 0.5rem 0;"><i class="fas fa-times"
                  style="color: #ccc; margin-right: 0.5rem;"></i>Support téléphonique</li>
            </ul>
            <a href="#demande-suivi" class="btn btn-primary" style="width: 100%;">Choisir ce forfait</a>
          </div>
        </div>

        <!-- Forfait Professionnel -->
        <div class="card" style="border: 2px solid var(--primary-color); position: relative; transform: scale(1.05);">
          <div
            style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
            Professionnel <span
              style="background: #ff9800; padding: 0.1rem 0.3rem; border-radius: 10px; font-size: 0.7rem; margin-left: 0.5rem;">POPULAIRE</span>
          </div>
          <div style="text-align: center; padding-top: 2rem;">
            <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">850€
            </div>
            <div style="color: #666; margin-bottom: 2rem;">par mois</div>
            <ul style="text-align: left; margin-bottom: 2rem;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>4 sessions de coaching/mois</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Support téléphonique illimité</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Accès plateforme collaborative</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Reporting d'avancement</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Formation d'équipe (2j/an)</li>
            </ul>
            <a href="#demande-suivi" class="btn btn-primary"
              style="width: 100%; background: var(--primary-color); border: 2px solid var(--primary-color);">Choisir ce
              forfait</a>
          </div>
        </div>

        <!-- Forfait Enterprise -->
        <div class="card" style="border: 2px solid #e0e0e0; position: relative;">
          <div
            style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #9c27b0; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
            Enterprise</div>
          <div style="text-align: center; padding-top: 2rem;">
            <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">Sur
              mesure</div>
            <div style="color: #666; margin-bottom: 2rem;">devis personnalisé</div>
            <ul style="text-align: left; margin-bottom: 2rem;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Sessions illimitées</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Équipe dédiée de consultants</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Intervention sur site</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>Formation équipe complète</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;"><i class="fas fa-check"
                  style="color: #4caf50; margin-right: 0.5rem;"></i>SLA garanti 24/7</li>
            </ul>
            <a href="#demande-suivi" class="btn btn-secondary" style="width: 100%;">Demander un devis</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES DETAILLES -->
  <section id="services-suivi" class="services-section">
    <div class="container">
      <h2 class="section-title">Services de Suivi Détaillés</h2>
      <p class="section-description">Découvrez en détail nos services d'accompagnement personnalisé pour votre
        transformation digitale.</p>

      <div class="service-cards">
        <!-- Service 1 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=400" alt="Coaching IA"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              IA & ML</div>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <h3>Coaching IA & Automatisation</h3>
            <div style="text-align: right;">
              <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">À partir de 450€/mois
              </div>
              <div style="font-size: 0.8rem; color: #666;">Forfait personnalisable</div>
            </div>
          </div>
          <p>Accompagnement personnalisé pour intégrer l'IA dans vos processus métier et optimiser votre productivité.
            De l'audit initial à la mise en production.</p>
          <div style="display: flex; gap: 0.5rem; margin: 1rem 0;">
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">IA</span>
            <span
              style="background: #f3e5f5; color: #7b1fa2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Automatisation</span>
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Coaching</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <h4 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">Ce qui est inclus :</h4>
            <ul style="font-size: 0.9rem; color: #666;">
              <li>• Sessions de coaching hebdomadaires</li>
              <li>• Audit IA de vos processus</li>
              <li>• Développement de POC</li>
              <li>• Formation équipe</li>
            </ul>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <a href="#demande-suivi" class="btn btn-secondary" style="flex: 1;">En savoir plus</a>
            <a href="#demande-suivi" class="btn btn-primary" style="flex: 1;">Demander un devis</a>
          </div>
        </div>

        <!-- Service 2 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=400" alt="Audit Sécurité"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #f44336; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Sécurité</div>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <h3>Audit & Conseil Cybersécurité</h3>
            <div style="text-align: right;">
              <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">À partir de 650€</div>
              <div style="font-size: 0.8rem; color: #666;">Mission unique</div>
            </div>
          </div>
          <p>Évaluation complète de votre sécurité numérique et recommandations stratégiques pour vous protéger contre
            les menaces actuelles.</p>
          <div style="display: flex; gap: 0.5rem; margin: 1rem 0;">
            <span
              style="background: #ffebee; color: #d32f2f; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Audit</span>
            <span
              style="background: #fff3e0; color: #f57c00; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Sécurité</span>
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">RGPD</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <h4 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">Ce qui est inclus :</h4>
            <ul style="font-size: 0.9rem; color: #666;">
              <li>• Audit de sécurité complet</li>
              <li>• Analyse des vulnérabilités</li>
              <li>• Plan d'action priorisé</li>
              <li>• Formation sensibilisation</li>
            </ul>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <a href="#demande-suivi" class="btn btn-secondary" style="flex: 1;">En savoir plus</a>
            <a href="#demande-suivi" class="btn btn-primary" style="flex: 1;">Demander un audit</a>
          </div>
        </div>

        <!-- Service 3 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=400" alt="Conseil SI"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #2196f3; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Cloud</div>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <h3>Conseil Systèmes d'Information</h3>
            <div style="text-align: right;">
              <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">À partir de 550€/mois
              </div>
              <div style="font-size: 0.8rem; color: #666;">Accompagnement continu</div>
            </div>
          </div>
          <p>Accompagnement stratégique pour optimiser votre architecture SI et votre transformation digitale. De la
            stratégie à l'implémentation.</p>
          <div style="display: flex; gap: 0.5rem; margin: 1rem 0;">
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Architecture</span>
            <span
              style="background: #f3e5f5; color: #7b1fa2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Cloud</span>
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">DevOps</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <h4 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">Ce qui est inclus :</h4>
            <ul style="font-size: 0.9rem; color: #666;">
              <li>• Architecture SI stratégique</li>
              <li>• Roadmap de transformation</li>
              <li>• Accompagnement migration cloud</li>
              <li>• Optimisation coûts</li>
            </ul>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <a href="#demande-suivi" class="btn btn-secondary" style="flex: 1;">En savoir plus</a>
            <a href="#demande-suivi" class="btn btn-primary" style="flex: 1;">Demander conseil</a>
          </div>
        </div>

        <!-- Service 4 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=400" alt="Formation Suivi"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #4caf50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Formation</div>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <h3>Suivi Post-Formation</h3>
            <div style="text-align: right;">
              <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">À partir de 350€/mois
              </div>
              <div style="font-size: 0.8rem; color: #666;">Support continu</div>
            </div>
          </div>
          <p>Soutien continu après vos formations pour assurer l'application pratique des compétences acquises et
            maximiser le ROI.</p>
          <div style="display: flex; gap: 0.5rem; margin: 1rem 0;">
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Support</span>
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Accompagnement</span>
            <span
              style="background: #f3e5f5; color: #7b1fa2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pratique</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <h4 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">Ce qui est inclus :</h4>
            <ul style="font-size: 0.9rem; color: #666;">
              <li>• Sessions de suivi mensuelles</li>
              <li>• Support technique continu</li>
              <li>• Accès communauté alumni</li>
              <li>• Mises à jour formations</li>
            </ul>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <a href="#demande-suivi" class="btn btn-secondary" style="flex: 1;">En savoir plus</a>
            <a href="#demande-suivi" class="btn btn-primary" style="flex: 1;">Souscrire</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TEMOIGNAGES SUIVI -->
  <section class="testimonials">
    <div class="container">
      <h2>Témoignages Clients</h2>
      <p class="section-subtitle">Découvrez comment nos services de suivi ont aidé nos clients à réussir leur
        transformation digitale</p>

      <div class="testimonials__grid">
        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <p class="quote">"L'accompagnement IA de FSCo a transformé notre approche métier. Leur coach nous a aidés à
            identifier les bonnes opportunités et à implémenter des solutions concrètes qui ont augmenté notre
            productivité de 40%."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=client1" alt="Marc Dubois" style="border-radius: 50%;" />
            <div>
              <cite>Marc Dubois</cite>
              <small>DG, TechStart SAS</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Service : Coaching IA & Automatisation • 8 mois d'accompagnement
          </div>
        </div>

        <div class="testimonial-card">
          <div class="stars">★★★★★</div>
          <p class="quote">"L'audit cybersécurité réalisé par FSCo a été une révélation. Leur approche méthodique et
            leurs recommandations pratiques nous ont permis de renforcer significativement notre sécurité tout en
            respectant notre budget."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=client2" alt="Sophie Martin" style="border-radius: 50%;" />
            <div>
              <cite>Sophie Martin</cite>
              <small>CISO, InnoCorp</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Service : Audit & Conseil Cybersécurité • Mission de 3 mois
          </div>
        </div>

        <div class="testimonial-card">
          <div class="stars">★★★★☆</div>
          <p class="quote">"Le suivi post-formation nous a permis d'appliquer concrètement les concepts appris. L'équipe
            de FSCo a été disponible et leurs conseils pratiques ont fait toute la différence dans notre projet."</p>
          <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
            <img src="https://i.pravatar.cc/60?u=client3" alt="Jean-Pierre Laurent" style="border-radius: 50%;" />
            <div>
              <cite>Jean-Pierre Laurent</cite>
              <small>CTO, DataFlow</small>
            </div>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
            Service : Suivi Post-Formation • 6 mois de support
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STATISTIQUES SUIVI -->
  <section class="features-section">
    <div class="container">
      <h2 class="section-title">Notre Impact</h2>
      <div class="stats-grid" style="margin-top: 2rem;">
        <div class="stat-card">
          <i class="fas fa-users"></i>
          <h3>85+</h3>
          <p>Clients accompagnés</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-chart-line"></i>
          <h3>35%</h3>
          <p>ROI moyen client</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-clock"></i>
          <h3>98%</h3>
          <p>Satisfaction client</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-trophy"></i>
          <h3>24/7</h3>
          <p>Support disponible</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FORMULAIRE DEMANDE SUIVI -->
  <section id="demande-suivi" class="formation-section">
    <div class="container">
      <h2 class="section-title">Demander un Suivi Personnalisé</h2>
      <p class="section-description">Remplissez ce formulaire pour bénéficier d'un accompagnement sur mesure adapté à
        vos besoins spécifiques.</p>

      <div class="auth-form" style="max-width: 600px; margin: 2rem auto;">
        <form action="#" method="post">
          <div class="form-group">
            <label for="nom">Nom complet *</label>
            <input type="text" id="nom" name="nom" required>
          </div>

          <div class="form-group">
            <label for="email">Email professionnel *</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="form-group">
            <label for="entreprise">Entreprise</label>
            <input type="text" id="entreprise" name="entreprise">
          </div>

          <div class="form-group">
            <label for="service">Service souhaité *</label>
            <select id="service" name="service" required>
              <option value="">Choisir un service</option>
              <option value="ia">Coaching IA & Automatisation</option>
              <option value="securite">Audit & Conseil Cybersécurité</option>
              <option value="si">Conseil Systèmes d'Information</option>
              <option value="formation">Suivi Post-Formation</option>
              <option value="autre">Autre (préciser)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="besoins">Décrivez vos besoins spécifiques *</label>
            <textarea id="besoins" name="besoins" rows="5" required
              placeholder="Expliquez votre projet, vos objectifs et les challenges que vous rencontrez..."></textarea>
          </div>

          <div class="form-group">
            <label for="delai">Délai souhaité</label>
            <select id="delai" name="delai">
              <option value="">Choisir un délai</option>
              <option value="urgent">Urgent (moins de 1 semaine)</option>
              <option value="bientot">Dans les 2-4 semaines</option>
              <option value="normal">Dans le mois</option>
              <option value="flexible">Flexible</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%;">Envoyer ma demande</button>
        </form>
      </div>

      <script>
        document.querySelector('.auth-form form').addEventListener('submit', function (e) {
          e.preventDefault();
          const btn = this.querySelector('button[type="submit"]');
          const originalText = btn.textContent;
          btn.textContent = 'Envoi en cours...';
          btn.disabled = true;

          const formData = new FormData(this);

          fetch('../../pages/includes/form_mail_handler.php', {
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
      </script>
    </div>

    <div
      style="text-align: center; margin-top: 2rem; padding: 2rem; background: var(--dark-bg); border-radius: 12px; border: 1px solid var(--border-color);">
      <h3 style="color: var(--text-color); margin-bottom: 1rem;">Contact Direct</h3>
      <p style="color: #666; margin-bottom: 1rem;">Préférez nous contacter directement ?</p>
      <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="https://wa.me/212698771627" class="btn btn-secondary" target="_blank">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <a href="tel:+212698771627" class="btn btn-secondary">
          <i class="fas fa-phone"></i> Téléphone
        </a>
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
        console.log('Email suivi CTA envoyé aux admins:', email);
        alert('Merci ! Vous serez  inscrit(e) à notre newsletter. Vous recevrez nos dernières actualités sur nos services de suivi.');

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