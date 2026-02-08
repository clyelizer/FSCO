<?php
/**
 * User Library Management API
 * Permet de gérer la bibliothèque personnelle de l'utilisateur via API
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];
$libraryPath = __DIR__ . '/../../htdocs/pages/admin/data/user_library.json';

switch ($method) {
    case 'GET':
        handleGet($libraryPath);
        break;
    case 'POST':
        handlePost($libraryPath);
        break;
    case 'DELETE':
        handleDelete($libraryPath);
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/user/library
 * Récupère la bibliothèque de l'utilisateur
 */
function handleGet($libraryPath) {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $library = readJsonFile($libraryPath);
    
    // Filtrer par utilisateur
    $userLibrary = array_filter($library, function($item) use ($user) {
        return isset($item['user_id']) && $item['user_id'] == $user['user_id'];
    });
    
    // Filtrer par type si spécifié
    if (isset($_GET['type'])) {
        $type = sanitizeInput($_GET['type']);
        $userLibrary = array_filter($userLibrary, function($item) use ($type) {
            return isset($item['type']) && $item['type'] === $type;
        });
    }
    
    // Trier par date d'ajout
    usort($userLibrary, function($a, $b) {
        return ($b['added_at'] ?? 0) - ($a['added_at'] ?? 0);
    });
    
    sendResponse('success', array_values($userLibrary), 'Bibliothèque récupérée avec succès');
}

/**
 * POST /api/user/library
 * Ajoute un élément à la bibliothèque
 */
function handlePost($libraryPath) {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $data = getJsonInput();
    validateRequired($data, ['item_id', 'item_type', 'title']);
    
    $library = readJsonFile($libraryPath);
    
    // Vérifier si l'élément est déjà dans la bibliothèque
    foreach ($library as $item) {
        if ($item['user_id'] == $user['user_id'] && 
            $item['item_id'] == $data['item_id'] && 
            $item['item_type'] == $data['item_type']) {
            sendResponse('error', null, 'Cet élément est déjà dans votre bibliothèque', 409);
        }
    }
    
    $libraryItem = [
        'id' => time(),
        'user_id' => $user['user_id'],
        'item_id' => intval($data['item_id']),
        'item_type' => sanitizeInput($data['item_type']), // blog, formation, ressource
        'title' => sanitizeInput($data['title']),
        'description' => $data['description'] ?? '',
        'thumbnail' => sanitizeInput($data['thumbnail'] ?? ''),
        'notes' => $data['notes'] ?? '',
        'progress' => intval($data['progress'] ?? 0),
        'completed' => $data['completed'] ?? false,
        'added_at' => time(),
        'updated_at' => time()
    ];
    
    $library[] = $libraryItem;
    
    if (file_put_contents($libraryPath, json_encode($library, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('add_to_library', [
            'user_id' => $user['user_id'],
            'item_id' => $libraryItem['item_id'],
            'item_type' => $libraryItem['item_type']
        ]);
        sendResponse('success', $libraryItem, 'Élément ajouté à la bibliothèque');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

/**
 * DELETE /api/user/library
 * Supprime un élément de la bibliothèque
 */
function handleDelete($libraryPath) {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $data = getJsonInput();
    validateRequired($data, ['item_id', 'item_type']);
    
    $library = readJsonFile($libraryPath);
    $found = false;
    
    foreach ($library as $key => $item) {
        if ($item['user_id'] == $user['user_id'] && 
            $item['item_id'] == $data['item_id'] && 
            $item['item_type'] == $data['item_type']) {
            $found = true;
            unset($library[$key]);
            break;
        }
    }
    
    if (!$found) {
        sendResponse('error', null, 'Élément non trouvé dans la bibliothèque', 404);
    }
    
    $library = array_values($library);
    
    if (file_put_contents($libraryPath, json_encode($library, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('remove_from_library', [
            'user_id' => $user['user_id'],
            'item_id' => $data['item_id'],
            'item_type' => $data['item_type']
        ]);
        sendResponse('success', null, 'Élément supprimé de la bibliothèque');
    } else {
        sendResponse('error', null, 'Erreur lors de la suppression', 500);
    }
}
