<?php
/**
 * Site Config Management API - SQL Version
 * CRUD operations for site configuration (key/value store)
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
    case 'POST':
        handlePost();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/admin/site-config
 * Get all config or specific keys
 */
function handleGet() {
    $db = Database::getInstance();
    
    // Get specific key
    if (isset($_GET['key'])) {
        $key = sanitizeInput($_GET['key']);
        $config = $db->fetchOne("SELECT * FROM site_config WHERE config_key = ?", [$key]);
        
        if (!$config) {
            sendResponse('error', null, 'Configuration non trouvée', 404);
        }
        
        // Parse value based on type
        $config['parsed_value'] = parseConfigValue($config['config_value'], $config['config_type']);
        
        sendResponse('success', $config, 'Configuration récupérée');
    }
    
    // Get by category
    $where = [];
    $params = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "category = ?";
        $params[] = sanitizeInput($_GET['category']);
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $configs = $db->fetchAll("SELECT * FROM site_config $whereClause ORDER BY category, config_key", $params);
    
    // Group by category and parse values
    $grouped = [];
    foreach ($configs as $config) {
        $category = $config['category'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [];
        }
        $config['parsed_value'] = parseConfigValue($config['config_value'], $config['config_type']);
        $grouped[$category][$config['config_key']] = $config;
    }
    
    sendResponse('success', [
        'config' => $grouped,
        'total' => count($configs)
    ], 'Configuration récupérée');
}

/**
 * POST /api/admin/site-config
 * Create a new config key
 */
function handlePost() {
    $user = requirePermission('admin');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['config_key', 'config_value']);
    
    $key = sanitizeInput($data['config_key']);
    
    // Check if key already exists
    if ($db->fetchOne("SELECT id FROM site_config WHERE config_key = ?", [$key])) {
        sendResponse('error', null, 'Cette clé de configuration existe déjà', 400);
    }
    
    $value = $data['config_value'];
    $type = $data['config_type'] ?? 'string';
    
    // Encode JSON if needed
    if ($type === 'json' && is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    
    $sql = "INSERT INTO site_config (config_key, config_value, config_type, category, description, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    try {
        $id = $db->insert($sql, [
            $key,
            $value,
            $type,
            sanitizeInput($data['category'] ?? 'general'),
            sanitizeInput($data['description'] ?? '')
        ]);
        
        $config = $db->fetchOne("SELECT * FROM site_config WHERE id = ?", [$id]);
        
        logApiAction('create_config', ['entity_type' => 'site_config', 'entity_id' => $key]);
        
        sendResponse('success', $config, 'Configuration créée avec succès');
        
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT /api/admin/site-config
 * Update config values (single or bulk)
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    
    // Single key update
    if (isset($data['config_key'])) {
        $key = sanitizeInput($data['config_key']);
        
        $existing = $db->fetchOne("SELECT * FROM site_config WHERE config_key = ?", [$key]);
        if (!$existing) {
            sendResponse('error', null, 'Configuration non trouvée', 404);
        }
        
        $value = $data['config_value'] ?? $existing['config_value'];
        $type = $data['config_type'] ?? $existing['config_type'];
        
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        $db->update(
            "UPDATE site_config SET config_value = ?, config_type = ?, updated_at = NOW() WHERE config_key = ?",
            [$value, $type, $key]
        );
        
        $config = $db->fetchOne("SELECT * FROM site_config WHERE config_key = ?", [$key]);
        
        logApiAction('update_config', ['entity_type' => 'site_config', 'entity_id' => $key]);
        
        sendResponse('success', $config, 'Configuration mise à jour');
    }
    
    // Bulk update
    if (isset($data['configs']) && is_array($data['configs'])) {
        $updated = 0;
        
        foreach ($data['configs'] as $key => $value) {
            $existing = $db->fetchOne("SELECT * FROM site_config WHERE config_key = ?", [$key]);
            if ($existing) {
                $storeValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                $db->update(
                    "UPDATE site_config SET config_value = ?, updated_at = NOW() WHERE config_key = ?",
                    [$storeValue, $key]
                );
                $updated++;
            }
        }
        
        logApiAction('bulk_update_config', ['count' => $updated]);
        
        sendResponse('success', ['updated' => $updated], "$updated configuration(s) mise(s) à jour");
    }
    
    sendResponse('error', null, 'Données invalides', 400);
}

/**
 * DELETE /api/admin/site-config
 * Delete a config key (admin only)
 */
function handleDelete() {
    $user = requirePermission('admin');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    validateRequired($data, ['config_key']);
    
    $key = sanitizeInput($data['config_key']);
    
    if (!$db->fetchOne("SELECT id FROM site_config WHERE config_key = ?", [$key])) {
        sendResponse('error', null, 'Configuration non trouvée', 404);
    }
    
    try {
        $db->delete("DELETE FROM site_config WHERE config_key = ?", [$key]);
        logApiAction('delete_config', ['entity_type' => 'site_config', 'entity_id' => $key]);
        sendResponse('success', null, 'Configuration supprimée');
    } catch (Exception $e) {
        sendResponse('error', null, 'Erreur: ' . $e->getMessage(), 500);
    }
}

/**
 * Parse config value based on type
 */
function parseConfigValue($value, $type) {
    switch ($type) {
        case 'json':
            return json_decode($value, true);
        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'number':
            return is_numeric($value) ? (strpos($value, '.') !== false ? floatval($value) : intval($value)) : 0;
        default:
            return $value;
    }
}
