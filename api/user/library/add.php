<?php
require_once __DIR__ . '/../../includes/api_common.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$resource_id = $_POST['resource_id'] ?? '';
$type = $_POST['type'] ?? 'ressource'; // ressource, blog, formation

if (empty($resource_id)) {
    sendResponse('error', null, 'ID de ressource requis', 400);
}

$pdo = getDBConnection();
// Check if already in library
$stmt = $pdo->prepare("SELECT id FROM user_library WHERE user_id = ? AND resource_id = ? AND type = ?");
$stmt->execute([$user['user_id'], $resource_id, $type]);

if ($stmt->fetch()) {
    sendResponse('error', null, 'Cet élément est déjà dans votre bibliothèque', 409);
}

$stmt = $pdo->prepare("INSERT INTO user_library (user_id, resource_id, type, status, created_at) VALUES (?, ?, ?, 'en_cours', NOW())");

if ($stmt->execute([$user['user_id'], $resource_id, $type])) {
    sendResponse('success', ['id' => $pdo->lastInsertId()], 'Élément ajouté avec succès');
} else {
    sendResponse('error', null, 'Erreur lors de l\'ajout', 500);
}
