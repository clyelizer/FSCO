<?php
require_once __DIR__ . '/../includes/api_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    sendResponse('error', null, 'Email et mot de passe requis', 400);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['motdepasse'])) {
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'nom' => $user['nom'],
        'role' => $user['role'],
        'exp' => time() + (30 * 24 * 60 * 60) // 30 jours
    ];
    
    $token = JWT::encode($payload);
    
    sendResponse('success', [
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], 'Connexion réussie');
} else {
    sendResponse('error', null, 'Identifiants incorrects', 401);
}
