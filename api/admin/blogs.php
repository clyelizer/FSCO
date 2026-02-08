<?php
/**
 * Blogs Management API - SQL Version
 * CRUD operations for blog articles
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
 * GET /api/admin/blogs
 * List all blogs or get a specific one by ID
 */
function handleGet() {
    $user = requireAuth();
    $db = Database::getInstance();
    
    // Get specific blog by ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $blog = $db->fetchOne("SELECT * FROM blogs WHERE id = ?", [$id]);
        
        if (!$blog) {
            sendResponse('error', null, 'Blog non trouvé', 404);
        }
        
        // Decode JSON fields
        $blog['tags'] = json_decode($blog['tags'], true) ?? [];
        
        sendResponse('success', $blog, 'Blog récupéré avec succès');
    }
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    
    // Build query with filters
    $where = [];
    $params = [];
    
    // Filter by category
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "categorie = ?";
        $params[] = sanitizeInput($_GET['category']);
    }
    
    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "statut = ?";
        $params[] = sanitizeInput($_GET['status']);
    }
    
    // Search in title and content
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . sanitizeInput($_GET['search']) . '%';
        $where[] = "(titre LIKE ? OR contenu LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM blogs $whereClause";
    $totalResult = $db->fetchOne($countSql, $params);
    $total = $totalResult['total'];
    
    // Fetch paginated results
    $sql = "SELECT * FROM blogs $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $blogs = $db->fetchAll($sql, $params);
    
    // Decode JSON fields for each blog
    foreach ($blogs as &$blog) {
        $blog['tags'] = json_decode($blog['tags'], true) ?? [];
    }
    
    sendResponse('success', [
        'blogs' => $blogs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ], 'Blogs récupérés avec succès');
}

/**
 * POST /api/admin/blogs
 * Create a new blog
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['titre', 'contenu']);
    
    $sql = "INSERT INTO blogs (titre, extrait, contenu, auteur, categorie, tags, image, statut, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $tags = isset($data['tags']) ? json_encode($data['tags'], JSON_UNESCAPED_UNICODE) : '[]';
    $extrait = $data['extrait'] ?? substr(strip_tags($data['contenu']), 0, 200);
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['titre']),
            sanitizeInput($extrait),
            $data['contenu'], // HTML content, don't sanitize
            $user['nom'] ?? $user['name'] ?? 'Admin',
            sanitizeInput($data['categorie'] ?? 'Général'),
            $tags,
            sanitizeInput($data['image'] ?? ''),
            $data['statut'] ?? 'brouillon'
        ]);
        
        // Fetch the created blog
        $blog = $db->fetchOne("SELECT * FROM blogs WHERE id = ?", [$id]);
        $blog['tags'] = json_decode($blog['tags'], true) ?? [];
        
        logApiAction('create_blog', [
            'entity_type' => 'blog',
            'entity_id' => $id,
            'new_values' => ['titre' => $data['titre']]
        ]);
        
        sendResponse('success', $blog, 'Blog créé avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la création: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/blogs
 * Update an existing blog
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    // Check if blog exists
    $existing = $db->fetchOne("SELECT * FROM blogs WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Blog non trouvé', 404);
    }
    
    // Build dynamic update query
    $updates = [];
    $params = [];
    
    if (isset($data['titre'])) {
        $updates[] = "titre = ?";
        $params[] = sanitizeInput($data['titre']);
    }
    if (isset($data['contenu'])) {
        $updates[] = "contenu = ?";
        $params[] = $data['contenu'];
        
        // Auto-update extrait if not provided
        if (!isset($data['extrait'])) {
            $updates[] = "extrait = ?";
            $params[] = sanitizeInput(substr(strip_tags($data['contenu']), 0, 200));
        }
    }
    if (isset($data['extrait'])) {
        $updates[] = "extrait = ?";
        $params[] = sanitizeInput($data['extrait']);
    }
    if (isset($data['categorie'])) {
        $updates[] = "categorie = ?";
        $params[] = sanitizeInput($data['categorie']);
    }
    if (isset($data['image'])) {
        $updates[] = "image = ?";
        $params[] = sanitizeInput($data['image']);
    }
    if (isset($data['tags'])) {
        $updates[] = "tags = ?";
        $params[] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
    }
    if (isset($data['statut'])) {
        $updates[] = "statut = ?";
        $params[] = $data['statut'];
    }
    
    if (empty($updates)) {
        sendResponse('error', null, 'Aucun champ à mettre à jour', 400);
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $id;
    
    $sql = "UPDATE blogs SET " . implode(', ', $updates) . " WHERE id = ?";
    
    try {
        $db->update($sql, $params);
        
        // Fetch updated blog
        $blog = $db->fetchOne("SELECT * FROM blogs WHERE id = ?", [$id]);
        $blog['tags'] = json_decode($blog['tags'], true) ?? [];
        
        logApiAction('update_blog', [
            'entity_type' => 'blog',
            'entity_id' => $id,
            'old_values' => $existing,
            'new_values' => $data
        ]);
        
        sendResponse('success', $blog, 'Blog mis à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la mise à jour: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/blogs
 * Delete a blog
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    // Check if blog exists
    $existing = $db->fetchOne("SELECT * FROM blogs WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Blog non trouvé', 404);
    }
    
    try {
        $db->delete("DELETE FROM blogs WHERE id = ?", [$id]);
        
        logApiAction('delete_blog', [
            'entity_type' => 'blog',
            'entity_id' => $id,
            'old_values' => $existing
        ]);
        
        sendResponse('success', null, 'Blog supprimé avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la suppression: ' . $e->getMessage(), 500);
    }
}
