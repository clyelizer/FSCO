<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class FormMailer
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Load .env file
        $envFile = __DIR__ . '/../../.env';
        $env = [];

        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
        } else {
            error_log("Le fichier .env est introuvable à : $envFile");
        }

        // Server settings from .env or fallback
        $this->mail->isSMTP();
        $this->mail->Host = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $env['SMTP_USER'] ?? 'clyelise1@gmail.com';
        $this->mail->Password = $env['SMTP_PASS'] ?? 'votre_mot_de_passe_application';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = $env['SMTP_PORT'] ?? 587;
        $this->mail->CharSet = 'UTF-8';

        // Default sender from .env or fallback
        $fromEmail = $env['FROM_EMAIL'] ?? $env['SMTP_USER'] ?? 'clyelise1@gmail.com';
        $fromName = $env['FROM_NAME'] ?? 'FSCo Notification';
        $this->mail->setFrom($fromEmail, $fromName);
    }

    public function send($to, $subject, $body)
    {
        try {
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>