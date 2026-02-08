<?php
/**
 * FAQ Management API - SQL Version
 * CRUD operations for FAQ entries
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
 * GET /api/admin/faq
 */
function handleGet() {
    $db = Database::getInstance();
    
    // Public access for GET, no auth required
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $faq = $db->fetchOne("SELECT * FROM faq WHERE id = ?", [$id]);
        
        if (!$faq) {
            sendResponse('error', null, 'FAQ non trouvée', 404);
        }
        
        sendResponse('success', $faq, 'FAQ récupérée avec succès');
    }
    
    $where = [];
    $params = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "categorie = ?";
        $params[] = sanitizeInput($_GET['category']);
    }
    
    // By default, only show active FAQs for public
    if (!isset($_GET['show_all'])) {
        $where[] = "is_active = 1";
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $faqs = $db->fetchAll("SELECT * FROM faq $whereClause ORDER BY ordre ASC, id ASC", $params);
    
    sendResponse('success', [
        'faqs' => $faqs,
        'total' => count($faqs)
    ], 'FAQs récupérées avec succès');
}

/**
 * POST /api/admin/faq
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['question', 'reponse']);
    
    // Get max ordre
    $maxOrdre = $db->fetchOne("SELECT MAX(ordre) as max_ordre FROM faq");
    $ordre = ($maxOrdre['max_ordre'] ?? 0) + 1;
    
    $sql = "INSERT INTO faq (question, reponse, categorie, ordre, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['question']),
            sanitizeInput($data['reponse']),
            sanitizeInput($data['categorie'] ?? 'general'),
            isset($data['ordre']) ? intval($data['ordre']) : $ordre,
            isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1
        ]);
        
        $faq = $db->fetchOne("SELECT * FROM faq WHERE id = ?", [$id]);
        
        logApiAction('create_faq', ['entity_type' => 'faq', 'entity_id' => $id]);
        
        sendResponse('success', $faq, 'FAQ créée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/faq
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    $existing = $db->fetchOne("SELECT * FROM faq WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'FAQ non trouvée', 404);
    }
    
    $updates = [];
    $params = [];
    
    if (isset($data['question'])) {
        $updates[] = "question = ?";
        $params[] = sanitizeInput($data['question']);
    }
    if (isset($data['reponse'])) {
        $updates[] = "reponse = ?";
        $params[] = sanitizeInput($data['reponse']);
    }
    if (isset($data['categorie'])) {
        $updates[] = "categorie = ?";
        $params[] = sanitizeInput($data['categorie']);
    }
    if (isset($data['ordre'])) {
        $updates[] = "ordre = ?";
        $params[] = intval($data['ordre']);
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
        $db->update("UPDATE faq SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        $faq = $db->fetchOne("SELECT * FROM faq WHERE id = ?", [$id]);
        
        logApiAction('update_faq', ['entity_type' => 'faq', 'entity_id' => $id]);
        
        sendResponse('success', $faq, 'FAQ mise à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/faq
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    if (!$db->fetchOne("SELECT id FROM faq WHERE id = ?", [$id])) {
        sendResponse('error', null, 'FAQ non trouvée', 404);
    }
    
    try {
        $db->delete("DELETE FROM faq WHERE id = ?", [$id]);
        logApiAction('delete_faq', ['entity_type' => 'faq', 'entity_id' => $id]);
        sendResponse('success', null, 'FAQ supprimée avec succès');
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}
