<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    // Store the requested action for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /pages/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get all available user information from session
$userId = $_SESSION['user_id'] ?? 'N/A';
$userName = $_SESSION['user_name'] ?? $_SESSION['nom'] ?? 'Utilisateur';
$userEmail = $_SESSION['user_email'] ?? 'Non disponible';
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'user';
$userPhone = $_SESSION['phone'] ?? $_SESSION['user_phone'] ?? 'Non renseigné';

// Get request type and plan
$requestType = $_GET['type'] ?? 'free'; // premium, enterprise, services
$plan = $_GET['plan'] ?? '';

// Admin email (from config)
$adminEmail = 'clyelise1@gmail.com';

// Prepare email content based on request type
$subject = '';
$message = '';
$userMessage = '';

switch ($requestType) {
    case 'premium':
        $subject = '🎯 Nouvelle demande d\'abonnement Premium - FSCo';
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
                .info-row { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
                .label { font-weight: bold; color: #1e293b; }
                .value { color: #475569; }
                .highlight { background: #dbeafe; padding: 15px; border-left: 4px solid #2563eb; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0;'>🎯 Nouvelle demande d'abonnement Premium</h2>
                </div>
                <div class='content'>
                    <div class='highlight'>
                        <strong>Plan demandé :</strong> Premium (29€/mois)<br>
                        <strong>Date de la demande :</strong> " . date('d/m/Y à H:i:s') . "
                    </div>
                    
                    <h3>📋 Informations de l'utilisateur</h3>
                    <div class='info-row'>
                        <span class='label'>👤 Nom :</span> <span class='value'>{$userName}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>✉️ Email :</span> <span class='value'><a href='mailto:{$userEmail}'>{$userEmail}</a></span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>📞 Téléphone :</span> <span class='value'>{$userPhone}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>🆔 ID Utilisateur :</span> <span class='value'>{$userId}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>👔 Statut :</span> <span class='value'>" . ucfirst($userRole) . "</span>
                    </div>
                    
                    <p style='margin-top: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;'>
                        ⚠️ <strong>Action requise :</strong> Veuillez contacter cet utilisateur pour finaliser son abonnement Premium.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        $userMessage = 'Votre demande d\'abonnement Premium a été envoyée avec succès ! Notre équipe vous contactera sous peu pour finaliser votre inscription.';
        break;

    case 'enterprise':
        $subject = '🏢 Nouvelle demande de devis Entreprise - FSCo';
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #6b7280, #4b5563); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
                .info-row { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
                .label { font-weight: bold; color: #1e293b; }
                .value { color: #475569; }
                .highlight { background: #f3f4f6; padding: 15px; border-left: 4px solid #6b7280; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0;'>🏢 Nouvelle demande de devis Entreprise</h2>
                </div>
                <div class='content'>
                    <div class='highlight'>
                        <strong>Plan demandé :</strong> Entreprise (Sur mesure)<br>
                        <strong>Date de la demande :</strong> " . date('d/m/Y à H:i:s') . "
                    </div>
                    
                    <h3>📋 Informations de l'utilisateur</h3>
                    <div class='info-row'>
                        <span class='label'>👤 Nom :</span> <span class='value'>{$userName}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>✉️ Email :</span> <span class='value'><a href='mailto:{$userEmail}'>{$userEmail}</a></span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>📞 Téléphone :</span> <span class='value'>{$userPhone}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>🆔 ID Utilisateur :</span> <span class='value'>{$userId}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>👔 Statut :</span> <span class='value'>" . ucfirst($userRole) . "</span>
                    </div>
                    
                    <p style='margin-top: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;'>
                        ⚠️ <strong>Action requise :</strong> Veuillez contacter cet utilisateur pour établir un devis personnalisé adapté aux besoins de son entreprise.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        $userMessage = 'Votre demande de devis Entreprise a été envoyée ! Nous vous contactons sous peu pour établir une offre personnalisée adaptée à vos besoins.';
        break;

    case 'services':
        $subject = '💼 Demande d\'information sur les services - FSCo';
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #001e6e, #1e293b); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
                .info-row { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
                .label { font-weight: bold; color: #1e293b; }
                .value { color: #475569; }
                .highlight { background: #e0e7ff; padding: 15px; border-left: 4px solid #001e6e; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0;'>💼 Demande d'information sur les services</h2>
                </div>
                <div class='content'>
                    <div class='highlight'>
                        <strong>Type de demande :</strong> Découvrir tous les services<br>
                        <strong>Date de la demande :</strong> " . date('d/m/Y à H:i:s') . "
                    </div>
                    
                    <h3>📋 Informations de l'utilisateur</h3>
                    <div class='info-row'>
                        <span class='label'>👤 Nom :</span> <span class='value'>{$userName}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>✉️ Email :</span> <span class='value'><a href='mailto:{$userEmail}'>{$userEmail}</a></span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>📞 Téléphone :</span> <span class='value'>{$userPhone}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>🆔 ID Utilisateur :</span> <span class='value'>{$userId}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>👔 Statut :</span> <span class='value'>" . ucfirst($userRole) . "</span>
                    </div>
                    
                    <p style='margin-top: 20px; padding: 15px; background: #dbeafe; border-left: 4px solid #2563eb; border-radius: 4px;'>
                        💡 <strong>Action suggérée :</strong> Cet utilisateur souhaite en savoir plus sur vos services. Contactez-le pour lui présenter votre offre complète.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        $userMessage = 'Votre demande d\'information a été envoyée ! Nous vous répondons rapidement avec tous les détails sur nos services.';
        break;

    default:
        $_SESSION['error_message'] = 'Type de demande invalide.';
        header('Location: /index.php#abonnements');
        exit;
}


// Send email to admin using the existing mailer system
require_once __DIR__ . '/../../includes/survey_mailer.php';

// sendEmail expects: ($to_email, $to_name, $subject, $message)
$emailSent = sendEmail($adminEmail, 'Admin FSCo', $subject, $message);

if ($emailSent) {
    $_SESSION['success_message'] = $userMessage;
} else {
    $_SESSION['error_message'] = 'Une erreur est survenue lors de l\'envoi de votre demande. Veuillez réessayer ou nous contacter directement.';
}

header('Location: /index.php#abonnements');
exit;


