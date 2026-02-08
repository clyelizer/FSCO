<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure composer autoloader is loaded
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }
}

// --- CONFIGURATION DES EMAILS (FALLBACK) ---
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'votre-email@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'votre-mot-de-passe');
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'contactfsco@gmail.com');
define('FROM_NAME', getenv('FROM_NAME') ?: 'FSCo - Formation Suivi Conseil');

/**
 * Envoie un email en utilisant PHPMailer et les configurations du .env
 */
function sendEmail($to_email, $to_name, $subject, $message)
{

    // --- Étape 1 : Chargement du fichier .env ---
    // Try to find .env relative to this file
    $envFile = __DIR__ . '/../.env';

    if (!file_exists($envFile)) {
        // Fallback if not found (e.g. if called from nested dir and __DIR__ is weird, but __DIR__ is absolute)
        error_log("Le fichier .env est introuvable à : $envFile");
        // We continue, maybe env vars are already set in server
    } else {
        $env = parse_ini_file($envFile);
    }

    // --- Étape 2 : Initialisation de PHPMailer ---
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'error_log';

        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'] ?? SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'] ?? SMTP_USER;
        $mail->Password = $env['SMTP_PASS'] ?? SMTP_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port = $env['SMTP_PORT'] ?? SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // --- Étape 3 : Définition des adresses ---
        $mail->setFrom($env['FROM_EMAIL'] ?? FROM_EMAIL, $env['FROM_NAME'] ?? FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // --- Étape 4 : Contenu du message ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        // --- Étape 5 : Envoi ---
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Erreur PHPMailer : {$mail->ErrorInfo}");
        return false;
    }
}
