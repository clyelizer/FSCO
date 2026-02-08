<?php
/**
 * User Profile Management API
 * Permet de gérer les profils utilisateurs via API
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'PUT':
        handlePut();
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/user/profile
 * Récupère le profil de l'utilisateur connecté
 */
function handleGet() {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, nom, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user['user_id']]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        sendResponse('error', null, 'Utilisateur non trouvé', 404);
    }
    
    sendResponse('success', $profile, 'Profil récupéré avec succès');
}

/**
 * PUT /api/user/profile
 * Met à jour le profil de l'utilisateur connecté
 */
function handlePut() {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $data = getJsonInput();
    
    $pdo = getDBConnection();
    
    // Champs autorisés à être modifiés
    $allowedFields = ['nom'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = sanitizeInput($data[$field]);
        }
    }
    
    if (empty($updates)) {
        sendResponse('error', null, 'Aucun champ à mettre à jour', 400);
    }
    
    $params[] = $user['user_id'];
    
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        logApiAction('update_profile', ['user_id' => $user['user_id']]);
        sendResponse('success', null, 'Profil mis à jour avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la mise à jour', 500);
    }
}
