<?php
/**
 * API pour appliquer les changements sur le site FSCO
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handleApplyChange();
        break;
    default:
        sendJsonResponse('error', null, 'Méthode non autorisée', 405);
}

function handleApplyChange() {
    verifyApiKey();
    $db = Database::getInstance();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['request_id'])) {
        sendJsonResponse('error', null, 'ID de demande requis', 400);
    }
    
    // Récupérer la demande via SQL
    $request = $db->fetchOne("SELECT * FROM ai_requests WHERE id = ?", [$data['request_id']]);
    
    if (!$request) {
        sendJsonResponse('error', null, 'Demande non trouvée', 404);
    }
    
    // Vérifier que la demande est approuvée
    if ($request['status'] !== 'approved') {
        sendJsonResponse('error', null, 'Cette demande n\'est pas approuvée', 400);
    }
    
    // Décoder les données
    $payloadData = json_decode($request['payload_json'], true);
    
    // Appliquer le changement selon le type
    $result = applyChangeByType($request['type'], $payloadData);
    
    if ($result['success']) {
        // Mettre à jour le statut de la demande en SQL
        $db->update(
            "UPDATE ai_requests SET status = 'applied', applied_at = NOW(), applied_result = ? WHERE id = ?",
            [json_encode($result), $data['request_id']]
        );
        
        logAction('apply_change', [
            'request_id' => $data['request_id'],
            'type' => $request['type'],
            'success' => true
        ]);
        
        sendJsonResponse('success', $result, 'Changement appliqué avec succès');
    } else {
        logAction('apply_change', [
            'request_id' => $data['request_id'],
            'type' => $request['type'],
            'success' => false,
            'error' => $result['error']
        ]);
        
        sendJsonResponse('error', $result['error'], 'Erreur lors de l\'application', 500);
    }
}

/**
 * Appliquer le changement selon le type
 */
function applyChangeByType($type, $data) {
    $apiUrl = FSCO_API_URL;
    $apiKey = FSCO_API_KEY;
    
    switch ($type) {
        case 'create_blog':
            return applyCreateBlog($apiUrl, $apiKey, $data);
        case 'update_blog':
            return applyUpdateBlog($apiUrl, $apiKey, $data);
        case 'delete_blog':
            return applyDeleteBlog($apiUrl, $apiKey, $data);
        case 'create_formation':
            return applyCreateFormation($apiUrl, $apiKey, $data);
        case 'update_formation':
            return applyUpdateFormation($apiUrl, $apiKey, $data);
        case 'delete_formation':
            return applyDeleteFormation($apiUrl, $apiKey, $data);
        case 'create_ressource':
            return applyCreateRessource($apiUrl, $apiKey, $data);
        case 'update_ressource':
            return applyUpdateRessource($apiUrl, $apiKey, $data);
        case 'delete_ressource':
            return applyDeleteRessource($apiUrl, $apiKey, $data);
        case 'update_settings':
            return applyUpdateSettings($apiUrl, $apiKey, $data);
        default:
            return ['success' => false, 'error' => 'Type de changement non supporté'];
    }
}

/**
 * Appliquer la création d'un blog
 */
function applyCreateBlog($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/blogs');
    
    $postData = [
        'title' => $data['title'] ?? '',
        'content' => $data['content'] ?? '',
        'excerpt' => $data['excerpt'] ?? '',
        'category' => $data['category'] ?? 'General',
        'image' => $data['image'] ?? '',
        'tags' => $data['tags'] ?? [],
        'published' => $data['published'] ?? true,
        'featured' => $data['featured'] ?? false
    ];
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la mise à jour d'un blog
 */
function applyUpdateBlog($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/blogs');
    
    $postData = [
        'id' => $data['id'] ?? '',
        'title' => $data['title'] ?? null,
        'content' => $data['content'] ?? null,
        'excerpt' => $data['excerpt'] ?? null,
        'category' => $data['category'] ?? null,
        'image' => $data['image'] ?? null,
        'tags' => $data['tags'] ?? null,
        'published' => $data['published'] ?? null,
        'featured' => $data['featured'] ?? null
    ];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la suppression d'un blog
 */
function applyDeleteBlog($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/blogs');
    
    $postData = ['id' => $data['id'] ?? ''];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la création d'une formation
 */
function applyCreateFormation($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/formations');
    
    $postData = [
        'title' => $data['title'] ?? '',
        'description' => $data['description'] ?? '',
        'short_description' => $data['short_description'] ?? '',
        'category' => $data['category'] ?? 'General',
        'level' => $data['level'] ?? 'Beginner',
        'duration' => $data['duration'] ?? '',
        'price' => $data['price'] ?? 0,
        'instructor_id' => $data['instructor_id'] ?? 0,
        'image' => $data['image'] ?? '',
        'prerequisites' => $data['prerequisites'] ?? [],
        'learning_objectives' => $data['learning_objectives'] ?? [],
        'curriculum' => $data['curriculum'] ?? [],
        'published' => $data['published'] ?? true,
        'featured' => $data['featured'] ?? false
    ];
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la mise à jour d'une formation
 */
function applyUpdateFormation($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/formations');
    
    $postData = [
        'id' => $data['id'] ?? '',
        'title' => $data['title'] ?? null,
        'description' => $data['description'] ?? null,
        'short_description' => $data['short_description'] ?? null,
        'category' => $data['category'] ?? null,
        'level' => $data['level'] ?? null,
        'duration' => $data['duration'] ?? null,
        'price' => $data['price'] ?? null,
        'instructor_id' => $data['instructor_id'] ?? null,
        'image' => $data['image'] ?? null,
        'prerequisites' => $data['prerequisites'] ?? null,
        'learning_objectives' => $data['learning_objectives'] ?? null,
        'curriculum' => $data['curriculum'] ?? null,
        'published' => $data['published'] ?? null,
        'featured' => $data['featured'] ?? null
    ];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la suppression d'une formation
 */
function applyDeleteFormation($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/formations');
    
    $postData = ['id' => $data['id'] ?? ''];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la création d'une ressource
 */
function applyCreateRessource($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/ressources');
    
    $postData = [
        'title' => $data['title'] ?? '',
        'description' => $data['description'] ?? '',
        'file_url' => $data['file_url'] ?? '',
        'file_name' => $data['file_name'] ?? '',
        'file_size' => $data['file_size'] ?? 0,
        'type' => $data['type'] ?? 'document',
        'category' => $data['category'] ?? 'General',
        'thumbnail' => $data['thumbnail'] ?? '',
        'author' => $data['author'] ?? '',
        'tags' => $data['tags'] ?? [],
        'published' => $data['published'] ?? true,
        'featured' => $data['featured'] ?? false
    ];
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la mise à jour d'une ressource
 */
function applyUpdateRessource($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/ressources');
    
    $postData = [
        'id' => $data['id'] ?? '',
        'title' => $data['title'] ?? null,
        'description' => $data['description'] ?? null,
        'file_url' => $data['file_url'] ?? null,
        'file_name' => $data['file_name'] ?? null,
        'file_size' => $data['file_size'] ?? null,
        'type' => $data['type'] ?? null,
        'category' => $data['category'] ?? null,
        'thumbnail' => $data['thumbnail'] ?? null,
        'author' => $data['author'] ?? null,
        'tags' => $data['tags'] ?? null,
        'published' => $data['published'] ?? null,
        'featured' => $data['featured'] ?? null
    ];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la suppression d'une ressource
 */
function applyDeleteRessource($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/ressources');
    
    $postData = ['id' => $data['id'] ?? ''];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}

/**
 * Appliquer la mise à jour des paramètres
 */
function applyUpdateSettings($apiUrl, $apiKey, $data) {
    $ch = curl_init($apiUrl . '/admin/settings');
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        return ['success' => true, 'data' => $responseData['data']];
    }
    
    return ['success' => false, 'error' => $responseData['message'] ?? 'Erreur inconnue'];
}
?>
