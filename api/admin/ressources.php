<?php
/**
 * Ressources Management API - SQL Version
 * CRUD operations for downloadable resources
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/admin/ressources
 */
function handleGet() {
    $user = requireAuth();
    $db = Database::getInstance();
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $ressource = $db->fetchOne("SELECT * FROM ressources WHERE id = ?", [$id]);
        
        if (!$ressource) {
            sendResponse('error', null, 'Ressource non trouvée', 404);
        }
        
        sendResponse('success', $ressource, 'Ressource récupérée avec succès');
    }
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "categorie = ?";
        $params[] = sanitizeInput($_GET['category']);
    }
    
    if (isset($_GET['format']) && !empty($_GET['format'])) {
        $where[] = "format = ?";
        $params[] = strtoupper(sanitizeInput($_GET['format']));
    }
    
    if (isset($_GET['level']) && !empty($_GET['level'])) {
        $where[] = "niveau = ?";
        $params[] = sanitizeInput($_GET['level']);
    }
    
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "statut = ?";
        $params[] = sanitizeInput($_GET['status']);
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . sanitizeInput($_GET['search']) . '%';
        $where[] = "(titre LIKE ? OR description LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $totalResult = $db->fetchOne("SELECT COUNT(*) as total FROM ressources $whereClause", $params);
    $total = $totalResult['total'];
    
    $sql = "SELECT * FROM ressources $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $ressources = $db->fetchAll($sql, $params);
    
    sendResponse('success', [
        'ressources' => $ressources,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ], 'Ressources récupérées avec succès');
}

/**
 * POST /api/admin/ressources
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['titre', 'description']);
    
    $sql = "INSERT INTO ressources (titre, description, format, taille, categorie, niveau, image, fichier, url_externe, statut, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['titre']),
            sanitizeInput($data['description']),
            strtoupper($data['format'] ?? 'PDF'),
            !empty($data['taille']) ? sanitizeInput($data['taille']) : null,
            sanitizeInput($data['categorie'] ?? 'Général'),
            $data['niveau'] ?? 'Débutant',
            sanitizeInput($data['image'] ?? ''),
            sanitizeInput($data['fichier'] ?? ''),
            sanitizeInput($data['url_externe'] ?? ''),
            $data['statut'] ?? 'brouillon'
        ]);
        
        $ressource = $db->fetchOne("SELECT * FROM ressources WHERE id = ?", [$id]);
        
        logApiAction('create_ressource', [
            'entity_type' => 'ressource',
            'entity_id' => $id
        ]);
        
        sendResponse('success', $ressource, 'Ressource créée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la création: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/ressources
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    $existing = $db->fetchOne("SELECT * FROM ressources WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Ressource non trouvée', 404);
    }
    
    $updates = [];
    $params = [];
    
    $fields = ['titre', 'description', 'format', 'taille', 'categorie', 'niveau', 'image', 'fichier', 'url_externe', 'statut'];
    
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            if ($field === 'format') {
                $params[] = strtoupper($data[$field]);
            } elseif ($field === 'taille' && empty($data[$field])) {
                $params[] = null;
            } else {
                $params[] = sanitizeInput($data[$field]);
            }
        }
    }
    
    if (empty($updates)) {
        sendResponse('error', null, 'Aucun champ à mettre à jour', 400);
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $id;
    
    $sql = "UPDATE ressources SET " . implode(', ', $updates) . " WHERE id = ?";
    
    try {
        $db->update($sql, $params);
        $ressource = $db->fetchOne("SELECT * FROM ressources WHERE id = ?", [$id]);
        
        logApiAction('update_ressource', [
            'entity_type' => 'ressource',
            'entity_id' => $id
        ]);
        
        sendResponse('success', $ressource, 'Ressource mise à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la mise à jour: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/ressources
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    $existing = $db->fetchOne("SELECT * FROM ressources WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Ressource non trouvée', 404);
    }
    
    try {
        $db->delete("DELETE FROM ressources WHERE id = ?", [$id]);
        
        logApiAction('delete_ressource', [
            'entity_type' => 'ressource',
            'entity_id' => $id
        ]);
        
        sendResponse('success', null, 'Ressource supprimée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la suppression: ' . $e->getMessage(), 500);
    }
}
