<?php
// Utilisation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chargement de l'autoloader de Composer
require '../vendor/autoload.php';

// Configuration de la base de données MySQL
require_once '../config.php';

// Obtenir la connexion à la base de données
$pdo = getDBConnection();


// ===================================================================
// FONCTIONS DE PRÉPARATION DES EMAILS
// ===================================================================

/**
 * Prépare et envoie l'email de confirmation de participation
 */
function sendConfirmationEmail($email, $prenom, $pays)
{
    $subject = "Confirmation de participation à l'enquête FSCo";
    $message = "
    <html>
    <head>
        <title>Confirmation de participation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>✅ Confirmation de participation</h1>
        </div>
        <div class='content'>
            <h2>Bonjour $prenom,</h2>
            <p>Nous avons bien reçu votre participation à notre enquête sur les étudiants, les technologies et l'avenir professionnel.</p>
            <p>Vos réponses ont été enregistrées avec succès et contribueront à améliorer nos services et formations.</p>
            <p><strong>Résumé de vos informations :</strong></p>
            <ul>
                <li><strong>Prénom :</strong> $prenom</li>
                <li><strong>Pays :</strong> $pays</li>
                <li><strong>Date de participation :</strong> " . date('d/m/Y à H:i') . "</li>
            </ul>
            <p>Si vous avez des questions ou souhaitez modifier vos réponses, n'hésitez pas à nous contacter.</p>
            <p>Cordialement,<br>L'équipe FSCo</p>
        </div>
        <div class='footer'>
            <p>FSCo - Formation Suivi Conseil | Contact : contactfsco@gmail.com</p>
        </div>
    </body>
    </html>
    ";

    return sendEmail($email, $prenom, $subject, $message);
}

/**
 * Prépare et envoie l'email de remerciement
 */
function sendThankYouEmail($email, $prenom)
{
    $subject = "🙏 Merci infiniment pour votre participation !";
    $message = "
    <html>
    <head>
        <title>Merci pour votre participation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
            .button { display: inline-block; padding: 10px 20px; background: #f5576c; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🙏 Merci infiniment !</h1>
        </div>
        <div class='content'>
            <h2>Cher(e) $prenom,</h2>
            <p>Nous tenons à vous remercier chaleureusement pour le temps que vous avez consacré à répondre à notre enquête détaillée.</p>
            <p>Vos insights précieux sur l'IA, la data science, et l'avenir professionnel des étudiants nous aideront à :</p>
            <ul>
                <li>Adapter nos programmes de formation</li>
                <li>Développer de nouveaux services</li>
                <li>Mieux comprendre les besoins du marché</li>
                <li>Préparer les étudiants aux défis de demain</li>
            </ul>
            <p>Restez connecté avec nous pour découvrir nos prochaines initiatives !</p>
            <a href='https://fsco.gt.tc' class='button'>Découvrir nos services</a>
            <p>Cordialement,<br>L'équipe FSCo</p>
        </div>
        <div class='footer'>
            <p>FSCo - Formation Suivi Conseil | Contact : contactfsco@gmail.com</p>
            
        </div>
    </body>
    </html>";

    return sendEmail($email, $prenom, $subject, $message);
}


function sendAdminNotificationEmail($prenom, $pays, $email_participant, $date_soumission)
{
    // Récupérer l'email admin depuis les variables d'environnement
    $admin_email = getenv('SMTP_USER');

    $subject = "🔔 Nouvelle réponse au sondage FSCo - $prenom ($pays)";
    $message = "
    <html>
    <head>
        <title>Nouvelle réponse au sondage</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
            .info-box { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #667eea; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🔔 Nouvelle réponse reçue</h1>
        </div>
        <div class='content'>
            <h2>Une nouvelle réponse au sondage a été soumise !</h2>

            <div class='info-box'>
                <h3>Informations du participant :</h3>
                <ul>
                    <li><strong>Prénom :</strong> $prenom</li>
                    <li><strong>Pays :</strong> $pays</li>
                    <li><strong>Email :</strong> $email_participant</li>
                    <li><strong>Date de soumission :</strong> $date_soumission</li>
                </ul>
            </div>

            <p>Vous pouvez consulter les statistiques mises à jour dans le <a href='https://fsco.gt.tc/pages/dashboard.php'>dashboard d'administration</a>.</p>

            <p>Cordialement,<br>Le système de sondage FSCo</p>
        </div>
        <div class='footer'>
            <p>FSCo - Formation Suivi Conseil | Système automatique de notification</p>
        </div>
    </body>
    </html>";

    return sendEmail($admin_email, 'Administrateur FSCo', $subject, $message);
}



// ===================================================================
// FONCTION UTILITAIRE (CONVERSION MONÉTAIRE)
// ===================================================================

/**
 * Convertit une plage de prix (en MAD) dans la devise locale
 */
function convertCurrency($amount_range, $country)
{
    $conversions = [
        'Maroc' => ['MAD', 1], // Base: MAD
        'Algérie' => ['DZD', 13.5],
        'Tunisie' => ['TND', 0.32],
        'France' => ['EUR', 0.088],
        'Canada' => ['CAD', 0.12],
        'Belgique' => ['EUR', 0.088],
        'Suisse' => ['CHF', 0.094],
        'Espagne' => ['EUR', 0.088],
        'Italie' => ['EUR', 0.088],
        'Portugal' => ['EUR', 0.088],
        'États-Unis' => ['USD', 0.099],
        'Royaume-Uni' => ['GBP', 0.077],
        'Allemagne' => ['EUR', 0.088]
    ];

    if (!isset($conversions[$country])) {
        return $amount_range . " MAD"; // Défaut Maroc
    }

    list($currency, $rate) = $conversions[$country];

    // Parser la plage de prix
    if (strpos($amount_range, '-') !== false) {
        list($min, $max) = explode('-', $amount_range);
        $min_converted = intval($min) * $rate;
        $max_converted = intval($max) * $rate;
        return number_format($min_converted, 0) . " - " . number_format($max_converted, 0) . " " . $currency;
    } elseif (strpos($amount_range, '+') !== false) {
        $amount = intval(str_replace('+', '', $amount_range));
        $converted = $amount * $rate;
        return number_format($converted, 0) . "+ " . $currency;
    }

    return $amount_range . " " . $currency;
}

// ===================================================================
// TRAITEMENT DU FORMULAIRE (POST)
// ===================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // PARTIE 1
    $prenom = $_POST['prenom'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $domaine = $_POST['domaine'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $etablissement = $_POST['etablissement'] ?? '';
    $experience = $_POST['experience'] ?? '';

    // PARTIE 2
    $cours_ia = $_POST['cours_ia'] ?? '';
    $explication_ia = $_POST['explication_ia'] ?? '';
    $usage_ia = $_POST['usage_ia'] ?? '';
    $usages_ia = isset($_POST['usages_ia']) ? implode(', ', $_POST['usages_ia']) : '';

    // PARTIE 3
    $problemes = isset($_POST['problemes']) ? implode(', ', $_POST['problemes']) : '';
    $probleme_principal = $_POST['probleme_principal'] ?? '';
    $processus_repetitifs = isset($_POST['processus_repetitifs']) ? implode(', ', $_POST['processus_repetitifs']) : '';
    $processus_automatise = $_POST['processus_automatise'] ?? '';
    $heures_economisees = $_POST['heures_economisees'] ?? '';
    $quantite_donnees = $_POST['quantite_donnees'] ?? '';

    // PARTIE 4
    $competences = isset($_POST['competences']) ? implode(', ', $_POST['competences']) : '';
    $duree_formation = $_POST['duree_formation'] ?? '';
    $format_formation = $_POST['format_formation'] ?? '';
    $prix_formation = $_POST['prix_formation'] ?? '';

    // Convertir le prix selon le pays
    $prix_converti = convertCurrency($prix_formation, $pays);

    // PARTIE 5
    $obstacles = isset($_POST['obstacles']) ? implode(', ', $_POST['obstacles']) : '';
    $equipements = $_POST['equipements'] ?? '';
    $raisons_pas_solutions = isset($_POST['raisons_pas_solutions']) ? implode(', ', $_POST['raisons_pas_solutions']) : '';
    $obstacle_principal = $_POST['obstacle_principal'] ?? '';

    // PARTIE 6
    $formation_securite = $_POST['formation_securite'] ?? '';
    $niveau_securite = $_POST['niveau_securite'] ?? '';
    $pratiques_securite = $_POST['pratiques_securite'] ?? '';
    $risques_cyber = $_POST['risques_cyber'] ?? '';

    // PARTIE 7
    $importance_ia_carriere = $_POST['importance_ia_carriere'] ?? '';
    $secteur_souhaite = $_POST['secteur_souhaite'] ?? '';
    $demande_emploi = $_POST['demande_emploi'] ?? '';
    $competences_importance = isset($_POST['competences_importance']) ? implode(' > ', $_POST['competences_importance']) : '';
    $preparation_emploi = $_POST['preparation_emploi'] ?? '';
    $manque_preparation = $_POST['manque_preparation'] ?? '';
    $entreprises_innovantes = $_POST['entreprises_innovantes'] ?? '';

    // PARTIE 8
    $ia_cours = $_POST['ia_cours'] ?? '';
    $ia_ameliore_enseignement = $_POST['ia_ameliore_enseignement'] ?? '';
    $risques_ia_enseignement = $_POST['risques_ia_enseignement'] ?? '';
    $risques_details = $_POST['risques_details'] ?? '';

    // PARTIE 9
    $recommandation_education = $_POST['recommandation_education'] ?? '';
    $vision_tech = $_POST['vision_tech'] ?? '';
    $interets_communaute = isset($_POST['interets_communaute']) ? implode(', ', $_POST['interets_communaute']) : '';
    $email = $_POST['email'] ?? '';
    $friends_emails = $_POST['friends_emails'] ?? '';

    // Date de soumission
    $date_soumission = date('Y-m-d H:i:s');

    // Préparer la requête SQL
    $sql = "INSERT INTO survey_responses (
        date_soumission, prenom, pays, adresse, sexe, domaine, annee, etablissement, experience,
        cours_ia, explication_ia, usage_ia, usages_ia,
        problemes, probleme_principal, processus_repetitifs, processus_automatise, heures_economisees, quantite_donnees,
        competences, duree_formation, format_formation, prix_formation, prix_converti,
        obstacles, equipements, raisons_pas_solutions, obstacle_principal,
        formation_securite, niveau_securite, pratiques_securite, risques_cyber,
        importance_ia_carriere, secteur_souhaite, demande_emploi, competences_importance, preparation_emploi, manque_preparation, entreprises_innovantes,
        ia_cours, ia_ameliore_enseignement, risques_ia_enseignement, risques_details,
        recommandation_education, vision_tech, interets_communaute, email
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?
    )";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $date_soumission,
            $prenom,
            $pays,
            $adresse,
            $sexe,
            $domaine,
            $annee,
            $etablissement,
            $experience,
            $cours_ia,
            $explication_ia,
            $usage_ia,
            $usages_ia,
            $problemes,
            $probleme_principal,
            $processus_repetitifs,
            $processus_automatise,
            $heures_economisees,
            $quantite_donnees,
            $competences,
            $duree_formation,
            $format_formation,
            $prix_formation,
            $prix_converti,
            $obstacles,
            $equipements,
            $raisons_pas_solutions,
            $obstacle_principal,
            $formation_securite,
            $niveau_securite,
            $pratiques_securite,
            $risques_cyber,
            $importance_ia_carriere,
            $secteur_souhaite,
            $demande_emploi,
            $competences_importance,
            $preparation_emploi,
            $manque_preparation,
            $entreprises_innovantes,
            $ia_cours,
            $ia_ameliore_enseignement,
            $risques_ia_enseignement,
            $risques_details,
            $recommandation_education,
            $vision_tech,
            $interets_communaute,
            $email
        ]);




        // Envoi des emails après succès de la sauvegarde
        // Email de confirmation au participant
        $confirmation_sent = sendConfirmationEmail($email, $prenom, $pays);

        // Email de notification à l'admin
        $admin_notification_sent = sendAdminNotificationEmail($prenom, $pays, $email, $date_soumission);

        // Email de remerciement au participant
        $thankyou_sent = sendThankYouEmail($email, $prenom);

        // Vérifier les erreurs
        $email_errors = [];
        if (!$confirmation_sent) {
            $email_errors[] = "confirmation";
        }
        if (!$thankyou_sent) {
            $email_errors[] = "thank_you";
        }


        // Redirection vers une page de remerciement avec info sur les emails
        $redirect_url = "merci.php?success=1";
        if (!empty($email_errors)) {
            $redirect_url .= "&email_errors=" . urlencode(implode(',', $email_errors));
        }
        header("Location: $redirect_url");
        exit();

    } catch (PDOException $e) {
        // Redirection avec message d'erreur
        header("Location: sondage.php?error=database&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>