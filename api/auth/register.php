<?php
require_once __DIR__ . '/../includes/api_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$email = sanitizeInput($_POST['email'] ?? '');
$nom = sanitizeInput($_POST['nom'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($nom) || empty($password)) {
    sendResponse('error', null, 'Tous les champs sont requis', 400);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendResponse('error', null, 'Cet email est déjà utilisé', 409);
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (nom, email, motdepasse, role, statut, plan) VALUES (?, ?, ?, 'student', 'active', 'free')");

if ($stmt->execute([$nom, $email, $hashed_password])) {
    $user_id = $pdo->lastInsertId();
    
    $payload = [
        'user_id' => $user_id,
        'email' => $email,
        'nom' => $nom,
        'role' => 'student',
        'exp' => time() + (30 * 24 * 60 * 60)
    ];
    
    $token = JWT::encode($payload);
    
    sendResponse('success', [
        'token' => $token,
        'user' => [
            'id' => $user_id,
            'nom' => $nom,
            'email' => $email,
            'role' => 'student'
        ]
    ], 'Inscription réussie');
} else {
    sendResponse('error', null, 'Erreur lors de l\'inscription', 500);
}
