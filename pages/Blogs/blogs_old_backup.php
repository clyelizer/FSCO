<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blogs - FSCo</title>
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
        <a href="../Formations/formations.php" class="nav__link">Formations</a>
        <a href="../Ressources/ressources.php" class="nav__link">Ressources</a>
        <a href="../Blogs/blogs.php" class="nav__link active">Blogs</a>
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

  <!-- FILTRES ET CATEGORIES -->
  <section class="features-section">
    <div class="container">
      <div style="display: flex; gap: 2rem; margin-bottom: 3rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
          <input type="text" placeholder="Rechercher un article..."
            style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Toutes catégories</option>
            <option>Intelligence Artificielle</option>
            <option>Cybersécurité</option>
            <option>Cloud & Infrastructure</option>
            <option>Automatisation</option>
            <option>Transformation Digitale</option>
          </select>
          <select style="padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px;">
            <option>Tous auteurs</option>
            <option>Marie Dubois</option>
            <option>Thomas Martin</option>
            <option>Sophie Laurent</option>
            <option>Pierre Durand</option>
          </select>
        </div>
      </div>

      <!-- Categories -->
      <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; justify-content: center;">
        <button class="btn btn-secondary" style="background: var(--primary-color); color: white;">Tous</button>
        <button class="btn btn-secondary">IA & ML</button>
        <button class="btn btn-secondary">Sécurité</button>
        <button class="btn btn-secondary">Cloud</button>
        <button class="btn btn-secondary">Automatisation</button>
        <button class="btn btn-secondary">Innovation</button>
      </div>
    </div>
  </section>

  <!-- ARTICLE EN UNE -->
  <section class="hero-section"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1677442136019-21780ecad995?q=80&w=1200'); background-size: cover; background-position: center; min-height: 60vh;">
    <div class="container">
      <div class="hero-content" style="text-align: left; max-width: 800px;">
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
          <span
            style="background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">Article
            à la Une</span>
          <span
            style="background: rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">IA
            & Innovation</span>
        </div>
        <h2 style="font-size: 3rem; margin-bottom: 1rem; color: white;">L'Intelligence Artificielle en 2025 : Révolution
          ou Évolution ?</h2>
        <p style="font-size: 1.3rem; margin-bottom: 2rem; opacity: 0.9;">Analyse approfondie des tendances qui
          façonneront l'IA cette année : AGI, éthique, régulation et impact économique.</p>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
          <img src="https://i.pravatar.cc/50?u=marie" alt="Marie Dubois" style="border-radius: 50%;" />
          <div style="color: white;">
            <div style="font-weight: 600;">Marie Dubois</div>
            <div style="opacity: 0.8; font-size: 0.9rem;">Directrice IA & Innovation • 15 Jan 2025 • 12 min lecture
            </div>
          </div>
        </div>
        <div class="hero-actions">
          <a href="#article-detail" class="btn btn-primary btn-huge">Lire l'article complet</a>
          <a href="#" class="btn btn-secondary"
            style="background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.3); color: white;">Écouter
            (Audio)</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ARTICLES SECTION -->
  <section id="articles" class="services-section">
    <div class="container">
      <h2 class="section-title">Articles Récents</h2>
      <p class="section-description">Plus de 150 articles publiés cette année sur les technologies émergentes et la
        transformation digitale.</p>

      <div class="service-cards">
        <!-- Article 1 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=400" alt="Zero Trust"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #ff6b35; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Tendance</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #ffebee; color: #d32f2f; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Sécurité</span>
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Zero
              Trust</span>
          </div>
          <h3>Zero Trust Architecture : Le Futur de la Cybersécurité</h3>
          <p>Comprendre l'approche Zero Trust et comment l'implémenter dans votre organisation pour une sécurité
            renforcée. Guide pratique avec exemples concrets.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=thomas" alt="Thomas Martin" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Thomas Martin</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 10 Jan • <i class="fas fa-clock"></i> 8 min • <i class="fas fa-eye"></i>
              2.3k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>

        <!-- Article 2 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=400" alt="Cloud Migration"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #4caf50; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Guide</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Cloud</span>
            <span
              style="background: #f3e5f5; color: #7b1fa2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Migration</span>
          </div>
          <h3>Migration Cloud : Stratégie et Bonnes Pratiques 2025</h3>
          <p>Un guide complet pour migrer vos applications vers le cloud en toute sécurité. Méthodologies, outils et
            retours d'expérience d'entreprises.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=sophie" alt="Sophie Laurent" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Sophie Laurent</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 5 Jan • <i class="fas fa-clock"></i> 12 min • <i class="fas fa-eye"></i>
              1.8k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>

        <!-- Article 3 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1555949963-aa79dcee981c?q=80&w=400" alt="RPA"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #9c27b0; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Tutoriel</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">RPA</span>
            <span
              style="background: #fff3e0; color: #f57c00; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Automatisation</span>
          </div>
          <h3>RPA : De l'Idée au Processus Automatisé</h3>
          <p>Tutoriel complet pour créer votre premier robot logiciel. De l'analyse du processus à la mise en production
            avec UiPath et Automation Anywhere.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=pierre" alt="Pierre Durand" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Pierre Durand</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 28 Dec • <i class="fas fa-clock"></i> 15 min • <i class="fas fa-eye"></i>
              3.1k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>

        <!-- Article 4 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?q=80&w=400" alt="RGPD"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #f44336; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Urgent</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #ffebee; color: #d32f2f; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">RGPD</span>
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Conformité</span>
          </div>
          <h3>RGPD 2025 : Les Changements Majeurs à Anticiper</h3>
          <p>Analyse des modifications du règlement général sur la protection des données pour 2025. Nouvelles
            obligations et sanctions renforcées.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=anne" alt="Anne Moreau" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Anne Moreau</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 20 Dec • <i class="fas fa-clock"></i> 10 min • <i class="fas fa-eye"></i>
              4.2k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>

        <!-- Article 5 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=400" alt="Ethics AI"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #607d8b; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Réflexion</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Éthique</span>
            <span
              style="background: #f3e5f5; color: #7b1fa2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">IA</span>
          </div>
          <h3>L'Éthique du Machine Learning : Responsabilité et Transparence</h3>
          <p>Les enjeux éthiques du développement de l'IA. Comment construire des modèles responsables et éviter les
            biais discriminants.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=lucas" alt="Lucas Bernard" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Lucas Bernard</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 15 Dec • <i class="fas fa-clock"></i> 14 min • <i class="fas fa-eye"></i>
              2.7k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>

        <!-- Article 6 -->
        <div class="card">
          <div style="position: relative;">
            <img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?q=80&w=400" alt="Future IA"
              style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px;">
            <div
              style="position: absolute; top: 10px; right: 10px; background: #ff9800; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
              Vision</div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <span
              style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Innovation</span>
            <span
              style="background: #e8f5e8; color: #388e3c; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Futur</span>
          </div>
          <h3>L'IA Générative : Opportunités et Défis pour 2025</h3>
          <p>Analyse des avancées en IA générative (GPT, DALL-E, etc.) et leur impact sur les métiers créatifs et la
            productivité d'entreprise.</p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <img src="https://i.pravatar.cc/30?u=marie" alt="Marie Dubois" style="border-radius: 50%;" />
              <span style="font-size: 0.9rem; color: #666;">Marie Dubois</span>
            </div>
            <div style="font-size: 0.9rem; color: #666;">
              <i class="fas fa-calendar"></i> 12 Dec • <i class="fas fa-clock"></i> 16 min • <i class="fas fa-eye"></i>
              3.8k vues
            </div>
          </div>
          <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <a href="#" class="btn btn-secondary" style="flex: 1;">Lire la suite</a>
            <a href="#" class="btn btn-primary" style="flex: 1;"><i class="fas fa-share"></i> Partager</a>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div style="display: flex; justify-content: center; margin-top: 3rem;">
        <div style="display: flex; gap: 0.5rem; align-items: center;">
          <button class="btn btn-secondary" disabled>&laquo;</button>
          <button class="btn btn-primary">1</button>
          <button class="btn btn-secondary">2</button>
          <button class="btn btn-secondary">3</button>
          <span style="color: #666; margin: 0 1rem;">...</span>
          <button class="btn btn-secondary">12</button>
          <button class="btn btn-secondary">&raquo;</button>
        </div>
      </div>
    </div>
  </section>

  <!-- AUTEURS EN UNE -->
  <section class="formation-section">
    <div class="container">
      <h2 class="section-title">Nos Contributeurs</h2>
      <p class="section-description">Rencontrez les experts qui partagent leur connaissance et leur expérience sur notre
        blog.</p>

      <div class="service-cards" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/100?u=marie" alt="Marie Dubois"
            style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Marie Dubois</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">Directrice IA & Innovation
          </p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">PhD en IA, 12 ans d'expérience chez Google et
            Microsoft. Spécialiste en éthique de l'IA.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">45
              articles</span>
            <span style="color: #666;">12k followers</span>
          </div>
          <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Voir ses articles</a>
        </div>

        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/100?u=thomas" alt="Thomas Martin"
            style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Thomas Martin</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">Expert Cybersécurité</p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">CISSP certifié, ancien CISO de grandes
            entreprises. Consultant en sécurité depuis 15 ans.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">38
              articles</span>
            <span style="color: #666;">8.5k followers</span>
          </div>
          <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Voir ses articles</a>
        </div>

        <div class="card" style="text-align: center;">
          <img src="https://i.pravatar.cc/100?u=sophie" alt="Sophie Laurent"
            style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 1rem; object-fit: cover;" />
          <h3>Sophie Laurent</h3>
          <p style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem;">Architecte Cloud</p>
          <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">AWS Certified Solutions Architect, 10 ans
            d'expérience en cloud computing et DevOps.</p>
          <div style="display: flex; justify-content: center; gap: 0.5rem; font-size: 0.8rem;">
            <span
              style="background: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">52
              articles</span>
            <span style="color: #666;">15k followers</span>
          </div>
          <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Voir ses articles</a>
        </div>
      </div>
    </div>
  </section>

  <!-- STATISTIQUES BLOG -->
  <section class="features-section">
    <div class="container">
      <h2 class="section-title">L'Impact de Notre Blog</h2>
      <div class="stats-grid" style="margin-top: 2rem;">
        <div class="stat-card">
          <i class="fas fa-file-alt"></i>
          <h3>150+</h3>
          <p>Articles publiés</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-eye"></i>
          <h3>250K+</h3>
          <p>Vues totales</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-users"></i>
          <h3>45K+</h3>
          <p>Lecteurs mensuels</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-share-alt"></i>
          <h3>12K+</h3>
          <p>Partages sociaux</p>
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
        <button type="submit" class="btn btn-huge" style="margin: 0;">Je veux recevoir les articles</button>
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
        console.log('Email blogs CTA envoyé aux admins:', email);
        alert('Merci ! Vous êtes maintenant inscrit à notre newsletter. Vous recevrez nos derniers articles.');

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