<?php
/**
 * API Keys Management Endpoint
 * Permet de gérer les clés API pour les agents IA et intégrations externes
 */
require_once __DIR__ . '/../includes/api_common.php';

// Vérifier la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Routes basées sur la méthode HTTP
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
 * GET /api/admin/api-keys
 * Liste toutes les clés API (admin uniquement)
 */
function handleGet() {
    $user = requireAdmin();
    
    $keys = APIKey::listAll();
    
    // Masquer les clés partiellement pour la sécurité
    $safeKeys = array_map(function($key) {
        return [
            'key' => substr($key['key'], 0, 12) . '...' . substr($key['key'], -4),
            'name' => $key['name'],
            'permissions' => $key['permissions'],
            'created_at' => date('Y-m-d H:i:s', $key['created_at']),
            'last_used' => $key['last_used'] ? date('Y-m-d H:i:s', $key['last_used']) : null,
            'expires_at' => $key['expires_at'] ? date('Y-m-d H:i:s', $key['expires_at']) : null,
            'is_active' => $key['is_active'],
            'usage_count' => $key['usage_count']
        ];
    }, $keys);
    
    logApiAction('list_api_keys', ['count' => count($keys)]);
    sendResponse('success', $safeKeys, 'Clés API récupérées avec succès');
}

/**
 * POST /api/admin/api-keys
 * Crée une nouvelle clé API (admin uniquement)
 */
function handlePost() {
    $user = requireAdmin();
    
    $data = getJsonInput();
    validateRequired($data, ['name']);
    
    $name = sanitizeInput($data['name']);
    $permissions = $data['permissions'] ?? ['read', 'write'];
    $expiresIn = $data['expires_in'] ?? null; // en secondes
    
    // Valider les permissions
    $validPermissions = ['read', 'write', 'admin', 'delete'];
    foreach ($permissions as $perm) {
        if (!in_array($perm, $validPermissions)) {
            sendResponse('error', null, "Permission invalide: $perm", 400);
        }
    }
    
    $apiKey = APIKey::generate($name, $permissions, $expiresIn);
    
    logApiAction('create_api_key', [
        'name' => $name,
        'permissions' => $permissions,
        'expires_in' => $expiresIn
    ]);
    
    sendResponse('success', [
        'key' => $apiKey['key'],
        'name' => $apiKey['name'],
        'permissions' => $apiKey['permissions'],
        'created_at' => date('Y-m-d H:i:s', $apiKey['created_at']),
        'expires_at' => $apiKey['expires_at'] ? date('Y-m-d H:i:s', $apiKey['expires_at']) : null
    ], 'Clé API créée avec succès');
}

/**
 * PUT /api/admin/api-keys
 * Met à jour une clé API existante (admin uniquement)
 */
function handlePut() {
    $user = requireAdmin();
    
    $data = getJsonInput();
    validateRequired($data, ['key']);
    
    $key = $data['key'];
    
    // Vérifier si la clé existe
    $keyDetails = APIKey::getDetails($key);
    if (!$keyDetails) {
        sendResponse('error', null, 'Clé API non trouvée', 404);
    }
    
    // Mettre à jour les permissions si fournies
    if (isset($data['permissions'])) {
        $validPermissions = ['read', 'write', 'admin', 'delete'];
        foreach ($data['permissions'] as $perm) {
            if (!in_array($perm, $validPermissions)) {
                sendResponse('error', null, "Permission invalide: $perm", 400);
            }
        }
        
        APIKey::updatePermissions($key, $data['permissions']);
    }
    
    // Révoquer ou activer la clé
    if (isset($data['is_active'])) {
        if ($data['is_active']) {
            // Réactiver la clé
            $keyDetails['is_active'] = true;
        } else {
            // Révoquer la clé
            APIKey::revoke($key);
        }
    }
    
    logApiAction('update_api_key', [
        'key' => substr($key, 0, 12) . '...',
        'permissions' => $data['permissions'] ?? null,
        'is_active' => $data['is_active'] ?? null
    ]);
    
    sendResponse('success', null, 'Clé API mise à jour avec succès');
}

/**
 * DELETE /api/admin/api-keys
 * Supprime une clé API (admin uniquement)
 */
function handleDelete() {
    $user = requireAdmin();
    
    $data = getJsonInput();
    validateRequired($data, ['key']);
    
    $key = $data['key'];
    
    if (!APIKey::delete($key)) {
        sendResponse('error', null, 'Clé API non trouvée', 404);
    }
    
    logApiAction('delete_api_key', [
        'key' => substr($key, 0, 12) . '...'
    ]);
    
    sendResponse('success', null, 'Clé API supprimée avec succès');
}
