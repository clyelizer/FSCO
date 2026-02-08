<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="15;url=https://fsco.gt.tc">
    <title>Merci - FSCo</title>
    <style>
        /* =================================
        Variables Globales (Ajoutées)
        ================================= */
        :root {
            --primary-color: #0d6efd; /* Un bleu plus vif et standard */
            --secondary-color: #6c757d; /* Gris pour les accents secondaires */
            --text-color: #343a40; /* Noir doux pour la lisibilité */
            --light-bg: #f8f9fa; /* Fond très léger pour le contraste */
            --white: #ffffff;
            --success-color: #198754; /* Vert succès */
            --error-color: #dc3545; /* Rouge erreur */
        }

        /* =================================
            BASE & TYPOGRAPHIE
            ================================= */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--light-bg);
            margin: 0; /* Assurez-vous d'enlever la marge par défaut */
            padding: 0;
        }

        a {
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }


        /* =================================
            HEADER ET NAVIGATION (Refonte Légère)
            ================================= */
        .header {
            background: var(--white);
            backdrop-filter: blur(8px); 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
            padding: 15px 0; 
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: none;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8em; 
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: normal;
        }

        .navbar ul {
            display: flex;
            gap: 0;
            list-style: none; /* Ajout pour la propreté */
            margin: 0;
            padding: 0;
        }

        .navbar li a {
            color: var(--text-color);
            padding: 10px 18px;
            font-weight: 500;
            font-size: 0.9em;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 0 2px;
            position: relative;
            display: inline-block;
        }

        .navbar li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar li a:hover::after {
            width: 70%;
        }

        .navbar li a:hover {
            color: var(--primary-color);
            background: rgba(13, 110, 253, 0.08); 
            transform: none;
        }

        /* =================================
            SECTION MERCI (Focus sur la clarté)
            ================================= */
        .merci-section {
            padding: 120px 0 80px; 
            background: var(--light-bg);
            text-align: center;
            min-height: calc(100vh - 300px); 
            display: flex;
            align-items: center;
        }
        .merci-section .container {
            width: 100%;
        }

        .merci-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .success-icon {
            font-size: 4em; 
            margin-bottom: 20px;
            animation: successPulse 1.5s ease-out;
        }

        @keyframes successPulse {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        .merci-message {
            font-size: 1.1em;
            color: var(--secondary-color);
            margin-bottom: 30px; /* Réduit pour laisser place au bouton */
            line-height: 1.7;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .merci-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
        }

        /* Style pour un bouton CTA clair */
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
            display: inline-block;
        }

        .btn-primary:hover {
            background-color: #0a58ca;
            transform: translateY(-2px);
        }


        .merci-stats {
            background: var(--white);
            padding: 30px 40px; 
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07); 
            margin-bottom: 40px;
            border: 1px solid #e9ecef; 
        }

        .merci-stats h3 {
            color: var(--text-color); 
            margin-bottom: 30px;
            font-size: 1.2em;
            font-weight: 600;
            position: relative;
        }

        .merci-stats h3::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            margin: 8px auto 0;
            border-radius: 5px;
        }


        .stats-grid {
            display: flex;
            justify-content: space-around;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
            flex: 1; 
            padding: 10px;
            border-left: 1px solid #e9ecef; 
        }

        .stat-item:first-child {
            border-left: none; 
        }

        .stat-item .stat-label {
            font-size: 1.05em;
            color: var(--text-color);
            font-weight: 500;
            line-height: 1.5;
        }


        /* =================================
            FOOTER (Refonte pour l'Harmonie)
            ================================= */
        .footer {
            background: #212529; 
            color: #f8f9fa;
            padding: 60px 0 40px;
            font-size: 0.9em;
        }

        .footer-grid {
            display: flex;
            justify-content: space-between;
            gap: 30px; 
            margin-bottom: 40px;
        }

        .footer-brand {
            max-width: 300px;
        }

        .footer-brand .logo {
            color: var(--primary-color);
            font-size: 1.6em; 
            margin-bottom: 15px;
        }

        .footer-brand p {
            color: #adb5bd; 
            line-height: 1.6;
        }

        .footer-links h4, .footer-contact h4 {
            margin-bottom: 15px;
            font-size: 1em;
            color: var(--primary-color); 
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .footer-links ul {
            list-style: none; /* Ajout pour la propreté */
            margin: 0;
            padding: 0;
        }

        .footer-links ul a {
            color: #adb5bd;
            display: block;
            padding: 4px 0;
            transition: color 0.3s ease;
        }

        .footer-links ul a:hover {
            color: var(--white);
            transform: none; 
        }

        .footer-contact p {
            margin-bottom: 8px;
            color: #adb5bd;
        }

        /* Media Query pour la responsivité mobile */
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 15px;
            }
            .navbar ul {
                flex-wrap: wrap;
                justify-content: center;
            }
            .navbar li a {
                padding: 8px 12px;
            }
            .merci-section {
                padding: 150px 0 60px;
                min-height: auto;
            }
            .section-title {
                font-size: 2em;
            }
            .footer-grid {
                flex-direction: column;
                gap: 30px;
                text-align: center;
            }
            .stats-grid {
                flex-direction: column;
                gap: 20px;
            }
            .stat-item {
                border-left: none;
                padding: 15px 0;
                border-bottom: 1px dashed #e9ecef;
            }
            .stat-item:last-child {
                border-bottom: none;
            }
            .merci-stats {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="logo">FSCo</div>
            <nav class="navbar">
                <ul>
                    <li><a href="../../index.php#services">Services</a></li>
                    <li><a href="../../index.php#formation">Formation</a></li>
                    <li><a href="../../index.php#methodologie">Méthodologie</a></li>
                    <li><a href="../../index.php#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="merci-section">
        <div class="container">
            <div class="merci-content">
                <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                    <div class="success-icon" style="color: var(--success-color);">✅</div>
                    <h1 class="section-title">Merci pour votre participation !</h1>
                    <p class="merci-message">
                        Votre réponse a été enregistrée avec succès. Vos insights nous aiderons à améliorer nos services et mieux répondre à vos besoins.<br><br>
                        Ceci n'est que notre sondage! <br>Pour mieux decouvrir nos services,<br> Nous vous prions de jeter un coup d'oeil à notre page principale.<br>Merci
                    </p>

                    <div class="merci-actions">
                        <a href="https://fsco.gt.tc" class="btn-primary">Découvrir nos services maintenant</a>
                    </div>
                
                <?php else: ?>
                    <div class="success-icon" style="color: var(--error-color);">⚠️</div>
                    <h1 class="section-title">Une erreur s'est produite</h1>
                    <p class="merci-message">
                        Désolé, une erreur s'est produite lors de l'enregistrement de votre réponse. Veuillez réessayer ou nous contacter directement.
                    </p>
                    <div class="merci-actions">
                        <a href="../../index.php#contact" class="btn-primary" style="background-color: var(--error-color);">Contacter l'assistance</a>
                    </div>
                <?php endif; ?>

                <div class="merci-stats">
                    <h3>FSCo en quelques mots</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-icon">🌟</span>
                            <span class="stat-label">Une équipe engagée pour vous servir avec expertise et réactivité.</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-icon">💡</span>
                            <span class="stat-label">Une vision claire :<br> Faire <strong>GRAND</strong> et Faire <strong>BIEN</strong> .</span>
                        </div>
                    </div>
                </div>            
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">FSCo</div>
                    <p>Formation Suivi Conseil – Votre partenaire pour la transformation et la croissance digitale.</p>
                </div>
                <div class="footer-links">
                    <h4>Liens utiles</h4>
                    <ul>
                        <li><a href="../../index.php#services">Services</a></li>
                        <li><a href="../../index.php#formation">Formation</a></li>
                        <li><a href="../../index.php#methodologie">Méthodologie</a></li>
                        <li><a href="sondage.php">Sondage</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p>Email: contactfsco@gmail.com</p>
                    <p>Téléphone: +212 698771627</p>
                    <p>Adresse: Casablanca, Maroc</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>