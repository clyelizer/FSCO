<?php
/**
 * Formations Management API - SQL Version
 * CRUD operations for courses/formations
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
 * GET /api/admin/formations
 * List all formations or get a specific one by ID
 */
function handleGet() {
    $user = requireAuth();
    $db = Database::getInstance();
    
    // Get specific formation by ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT f.*, i.nom as instructeur_nom, i.specialite as instructeur_specialite, i.avatar as instructeur_avatar 
                FROM formations f 
                LEFT JOIN instructors i ON f.instructeur_id = i.id 
                WHERE f.id = ?";
        $formation = $db->fetchOne($sql, [$id]);
        
        if (!$formation) {
            sendResponse('error', null, 'Formation non trouvée', 404);
        }
        
        // Decode JSON fields
        $formation['prerequis'] = json_decode($formation['prerequis'], true) ?? [];
        $formation['objectifs'] = json_decode($formation['objectifs'], true) ?? [];
        $formation['curriculum'] = json_decode($formation['curriculum'], true) ?? [];
        
        // Build instructor object
        if ($formation['instructeur_id']) {
            $formation['instructeur'] = [
                'id' => $formation['instructeur_id'],
                'nom' => $formation['instructeur_nom'],
                'specialite' => $formation['instructeur_specialite'],
                'avatar' => $formation['instructeur_avatar']
            ];
        }
        unset($formation['instructeur_nom'], $formation['instructeur_specialite'], $formation['instructeur_avatar']);
        
        sendResponse('success', $formation, 'Formation récupérée avec succès');
    }
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    
    // Build query with filters
    $where = [];
    $params = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "f.categorie = ?";
        $params[] = sanitizeInput($_GET['category']);
    }
    
    if (isset($_GET['level']) && !empty($_GET['level'])) {
        $where[] = "f.niveau = ?";
        $params[] = sanitizeInput($_GET['level']);
    }
    
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "f.statut = ?";
        $params[] = sanitizeInput($_GET['status']);
    }
    
    if (isset($_GET['instructor_id']) && !empty($_GET['instructor_id'])) {
        $where[] = "f.instructeur_id = ?";
        $params[] = intval($_GET['instructor_id']);
    }
    
    if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
        $where[] = "f.is_featured = 1";
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . sanitizeInput($_GET['search']) . '%';
        $where[] = "(f.titre LIKE ? OR f.description LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM formations f $whereClause";
    $totalResult = $db->fetchOne($countSql, $params);
    $total = $totalResult['total'];
    
    // Fetch with instructor join
    $sql = "SELECT f.*, i.nom as instructeur_nom, i.specialite as instructeur_specialite, i.avatar as instructeur_avatar 
            FROM formations f 
            LEFT JOIN instructors i ON f.instructeur_id = i.id 
            $whereClause 
            ORDER BY f.created_at DESC 
            LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $formations = $db->fetchAll($sql, $params);
    
    // Process each formation
    foreach ($formations as &$formation) {
        $formation['prerequis'] = json_decode($formation['prerequis'], true) ?? [];
        $formation['objectifs'] = json_decode($formation['objectifs'], true) ?? [];
        $formation['curriculum'] = json_decode($formation['curriculum'], true) ?? [];
        
        if ($formation['instructeur_id']) {
            $formation['instructeur'] = [
                'id' => $formation['instructeur_id'],
                'nom' => $formation['instructeur_nom'],
                'specialite' => $formation['instructeur_specialite'],
                'avatar' => $formation['instructeur_avatar']
            ];
        }
        unset($formation['instructeur_nom'], $formation['instructeur_specialite'], $formation['instructeur_avatar']);
    }
    
    sendResponse('success', [
        'formations' => $formations,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ], 'Formations récupérées avec succès');
}

/**
 * POST /api/admin/formations
 * Create a new formation
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['titre', 'description', 'duree', 'prix']);
    
    $sql = "INSERT INTO formations (titre, description, description_courte, categorie, niveau, duree, prix, devise, instructeur_id, image, prerequis, objectifs, curriculum, statut, is_featured, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    try {
        $id = $db->insert($sql, [
            sanitizeInput($data['titre']),
            $data['description'],
            sanitizeInput($data['description_courte'] ?? substr(strip_tags($data['description']), 0, 200)),
            sanitizeInput($data['categorie'] ?? 'Général'),
            $data['niveau'] ?? 'Débutant',
            sanitizeInput($data['duree']),
            floatval($data['prix']),
            $data['devise'] ?? 'MAD',
            isset($data['instructeur_id']) ? intval($data['instructeur_id']) : null,
            sanitizeInput($data['image'] ?? ''),
            json_encode($data['prerequis'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($data['objectifs'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($data['curriculum'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['statut'] ?? 'brouillon',
            isset($data['is_featured']) && $data['is_featured'] ? 1 : 0
        ]);
        
        $formation = $db->fetchOne("SELECT * FROM formations WHERE id = ?", [$id]);
        
        logApiAction('create_formation', [
            'entity_type' => 'formation',
            'entity_id' => $id,
            'new_values' => ['titre' => $data['titre'], 'prix' => $data['prix']]
        ]);
        
        sendResponse('success', $formation, 'Formation créée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la création: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/formations
 * Update an existing formation
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    $existing = $db->fetchOne("SELECT * FROM formations WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Formation non trouvée', 404);
    }
    
    $updates = [];
    $params = [];
    
    $fields = [
        'titre' => 'sanitize',
        'description' => 'raw',
        'description_courte' => 'sanitize',
        'categorie' => 'sanitize',
        'niveau' => 'raw',
        'duree' => 'sanitize',
        'prix' => 'float',
        'devise' => 'raw',
        'instructeur_id' => 'int_nullable',
        'image' => 'sanitize',
        'statut' => 'raw',
        'is_featured' => 'bool'
    ];
    
    foreach ($fields as $field => $type) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            switch ($type) {
                case 'sanitize':
                    $params[] = sanitizeInput($data[$field]);
                    break;
                case 'float':
                    $params[] = floatval($data[$field]);
                    break;
                case 'int_nullable':
                    $params[] = !empty($data[$field]) ? intval($data[$field]) : null;
                    break;
                case 'bool':
                    $params[] = $data[$field] ? 1 : 0;
                    break;
                default:
                    $params[] = $data[$field];
            }
        }
    }
    
    // JSON fields
    foreach (['prerequis', 'objectifs', 'curriculum'] as $jsonField) {
        if (isset($data[$jsonField])) {
            $updates[] = "$jsonField = ?";
            $params[] = json_encode($data[$jsonField], JSON_UNESCAPED_UNICODE);
        }
    }
    
    if (empty($updates)) {
        sendResponse('error', null, 'Aucun champ à mettre à jour', 400);
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $id;
    
    $sql = "UPDATE formations SET " . implode(', ', $updates) . " WHERE id = ?";
    
    try {
        $db->update($sql, $params);
        $formation = $db->fetchOne("SELECT * FROM formations WHERE id = ?", [$id]);
        
        logApiAction('update_formation', [
            'entity_type' => 'formation',
            'entity_id' => $id
        ]);
        
        sendResponse('success', $formation, 'Formation mise à jour avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la mise à jour: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE /api/admin/formations
 * Delete a formation
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['id']);
    
    $id = intval($data['id']);
    
    $existing = $db->fetchOne("SELECT * FROM formations WHERE id = ?", [$id]);
    if (!$existing) {
        sendResponse('error', null, 'Formation non trouvée', 404);
    }
    
    try {
        $db->delete("DELETE FROM formations WHERE id = ?", [$id]);
        
        logApiAction('delete_formation', [
            'entity_type' => 'formation',
            'entity_id' => $id
        ]);
        
        sendResponse('success', null, 'Formation supprimée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur lors de la suppression: ' . $e->getMessage(), 500);
    }
}
