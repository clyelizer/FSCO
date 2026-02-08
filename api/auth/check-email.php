<?php
require_once __DIR__ . '/../includes/api_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$email = sanitizeInput($_POST['email'] ?? '');

if (empty($email)) {
    sendResponse('error', null, 'Email requis', 400);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, nom FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    sendResponse('success', ['exists' => true, 'nom' => $user['nom']]);
} else {
    sendResponse('success', ['exists' => false]);
}
