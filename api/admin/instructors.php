<?php
/**
 * Instructors Management API - SQL Version
 * CRUD operations for course instructors
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
 * GET /api/admin/instructors
 */
function handleGet() {
    $db = Database::getInstance();
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $instructor = $db->fetchOne("SELECT * FROM instructors WHERE id = ?", [$id]);
        
        if (!$instructor) {
            sendResponse('error', null, 'Instructeur non trouvé', 404);
        }
        
        // Get formations count
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM formations WHERE instructeur_id = ?", [$id]);
        $instructor['formations_count'] = $count['count'];
        
        sendResponse('success', $instructor, 'Instructeur récupéré avec succès');
    }
    
    $where = [];
    $params = [];
    
    if (!isset($_GET['show_all'])) {
        $where[] = "is_active = 1";
    }
    
    if (isset($_GET['specialty']) && !empty($_GET['specialty'])) {
        $where[] = "specialite LIKE ?";
        $params[] = '%' . sanitizeInput($_GET['specialty']) . '%';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $instructors = $db->fetchAll(
        "SELECT * FROM instructors $whereClause ORDER BY rating DESC, nom ASC",
        $params
    );
    
    sendResponse('success', [
        'instructors' => $instructors,
        'total' => count($instructors)
    ], 'Instructeurs récupérés avec succès');
}

/**
 * POST /api/admin/instructors
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['nom', 'specialite']);
    
    $sql = "INSERT INTO instructors (nom, specialite, bio, avatar, rating, students_count, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['nom']),
            sanitizeInput($data['specialite']),
            sanitizeInput($data['bio'] ?? ''),
            sanitizeInput($data['avatar'] ?? ''),
            floatval($data['rating'] ?? 0),
            intval($data['students_count'] ?? 0)
        ]);
        
        $instructor = $db->fetchOne("SELECT * FROM instructors WHERE id = ?", [$id]);
        
        logApiAction('create_instructor', ['entity_type' => 'instructor', 'entity_id' => $id]);
        
        sendResponse('success', $instructor, 'Instructeur créé avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/instructors
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    if (!$db->fetchOne("SELECT id FROM instructors WHERE id = ?", [$id])) {
        sendResponse('error', null, 'Instructeur non trouvé', 404);
    }
    
    $updates = [];
    $params = [];
    
    foreach (['nom', 'specialite', 'bio', 'avatar'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = sanitizeInput($data[$field]);
        }
    }
    
    if (isset($data['rating'])) {
        $updates[] = "rating = ?";
        $params[] = floatval($data['rating']);
    }
    if (isset($data['students_count'])) {
        $updates[] = "students_count = ?";
        $params[] = intval($data['students_count']);
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
        $db->update("UPDATE instructors SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        $instructor = $db->fetchOne("SELECT * FROM instructors WHERE id = ?", [$id]);
        
        logApiAction('update_instructor', ['entity_type' => 'instructor', 'entity_id' => $id]);
        
        sendResponse('success', $instructor, 'Instructeur mis à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/instructors
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    if (!$db->fetchOne("SELECT id FROM instructors WHERE id = ?", [$id])) {
        sendResponse('error', null, 'Instructeur non trouvé', 404);
    }
    
    // Check if instructor has formations
    $formations = $db->fetchOne("SELECT COUNT(*) as count FROM formations WHERE instructeur_id = ?", [$id]);
    if ($formations['count'] > 0) {
        sendResponse('error', null, 'Impossible de supprimer: instructeur assigné à ' . $formations['count'] . ' formation(s)', 400);
    }
    
    try {
        $db->delete("DELETE FROM instructors WHERE id = ?", [$id]);
        logApiAction('delete_instructor', ['entity_type' => 'instructor', 'entity_id' => $id]);
        sendResponse('success', null, 'Instructeur supprimé avec succès');
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}
