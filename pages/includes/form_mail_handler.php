<?php
require_once 'FormMailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING) ?? 'Feedback Test';
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
    ?? filter_input(INPUT_POST, 'besoins', FILTER_SANITIZE_STRING)
    ?? 'Aucun message';
$testTitle = filter_input(INPUT_POST, 'test_title', FILTER_SANITIZE_STRING);
$testScore = filter_input(INPUT_POST, 'test_score', FILTER_SANITIZE_STRING);

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email invalide']);
    exit;
}

$mailer = new FormMailer();
$subject = $testTitle ? "Feedback Test: $testTitle" : "Nouveau contact de $nom";
$body = "<h3>Nouveau message" . ($testTitle ? " - Feedback Test" : " de contact") . "</h3>
         <p><strong>Nom:</strong> $nom</p>
         <p><strong>Email:</strong> $email</p>";

if ($testTitle) {
    $body .= "<p><strong>Test:</strong> $testTitle</p>
              <p><strong>Score obtenu:</strong> $testScore</p>";
}

$body .= "<p><strong>Message:</strong><br>$message</p>";

if ($mailer->send('clyelise1@gmail.com', $subject, $body)) {
    echo json_encode(['status' => 'success', 'message' => 'Message envoyé avec succès']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message']);
}
?>