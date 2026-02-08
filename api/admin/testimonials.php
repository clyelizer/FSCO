<?php
/**
 * Testimonials Management API - SQL Version
 * CRUD operations for client testimonials
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
 * GET /api/admin/testimonials
 */
function handleGet() {
    $db = Database::getInstance();
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
        
        if (!$testimonial) {
            sendResponse('error', null, 'Témoignage non trouvé', 404);
        }
        
        sendResponse('success', $testimonial, 'Témoignage récupéré avec succès');
    }
    
    $where = [];
    $params = [];
    
    if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
        $where[] = "is_featured = 1";
    }
    
    if (!isset($_GET['show_all'])) {
        $where[] = "is_active = 1";
    }
    
    if (isset($_GET['min_rating'])) {
        $where[] = "rating >= ?";
        $params[] = floatval($_GET['min_rating']);
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $limit = isset($_GET['limit']) ? min(50, intval($_GET['limit'])) : 10;
    
    $testimonials = $db->fetchAll(
        "SELECT * FROM testimonials $whereClause ORDER BY is_featured DESC, rating DESC, created_at DESC LIMIT ?",
        array_merge($params, [$limit])
    );
    
    sendResponse('success', [
        'testimonials' => $testimonials,
        'total' => count($testimonials)
    ], 'Témoignages récupérés avec succès');
}

/**
 * POST /api/admin/testimonials
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['nom', 'texte']);
    
    $sql = "INSERT INTO testimonials (nom, role, avatar, rating, texte, contexte, is_featured, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['nom']),
            sanitizeInput($data['role'] ?? ''),
            sanitizeInput($data['avatar'] ?? ''),
            floatval($data['rating'] ?? 5),
            sanitizeInput($data['texte']),
            sanitizeInput($data['contexte'] ?? ''),
            isset($data['is_featured']) && $data['is_featured'] ? 1 : 0
        ]);
        
        $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
        
        logApiAction('create_testimonial', ['entity_type' => 'testimonial', 'entity_id' => $id]);
        
        sendResponse('success', $testimonial, 'Témoignage créé avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/testimonials
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    if (!$db->fetchOne("SELECT id FROM testimonials WHERE id = ?", [$id])) {
        sendResponse('error', null, 'Témoignage non trouvé', 404);
    }
    
    $updates = [];
    $params = [];
    
    foreach (['nom', 'role', 'avatar', 'texte', 'contexte'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = sanitizeInput($data[$field]);
        }
    }
    
    if (isset($data['rating'])) {
        $updates[] = "rating = ?";
        $params[] = floatval($data['rating']);
    }
    if (isset($data['is_featured'])) {
        $updates[] = "is_featured = ?";
        $params[] = $data['is_featured'] ? 1 : 0;
    }
    if (isset($data['is_active'])) {
        $updates[] = "is_active = ?";
        $params[] = $data['is_active'] ? 1 : 0;
    }
    
    if (empty($updates)) {
        sendResponse('error', null, 'Aucun champ à mettre à jour', 400);
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $id;
    
    try {
        $db->update("UPDATE testimonials SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
        
        logApiAction('update_testimonial', ['entity_type' => 'testimonial', 'entity_id' => $id]);
        
        sendResponse('success', $testimonial, 'Témoignage mis à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/testimonials
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    if (!$db->fetchOne("SELECT id FROM testimonials WHERE id = ?", [$id])) {
        sendResponse('error', null, 'Témoignage non trouvé', 404);
    }
    
    try {
        $db->delete("DELETE FROM testimonials WHERE id = ?", [$id]);
        logApiAction('delete_testimonial', ['entity_type' => 'testimonial', 'entity_id' => $id]);
        sendResponse('success', null, 'Témoignage supprimé avec succès');
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}
