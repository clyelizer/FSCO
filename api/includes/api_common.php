<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/api_key_helper.php';
require_once __DIR__ . '/../../pages/admin/evaluations/includes/database.php';

header('Content-Type: application/json');

/**
 * Envoie une réponse JSON et termine l'exécution
 */
function sendResponse($status, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'data' => $data,
        'message' => $message,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Récupère l'utilisateur authentifié via JWT ou API Key
 */
function getAuthUser() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    // 1. Essayer d'abord l'authentification par API Key (Agent IA)
    $apiKey = APIKey::extractFromHeader();
    if ($apiKey) {
        $keyData = APIKey::validate($apiKey);
        if ($keyData) {
            return [
                'type' => 'api_key',
                'key' => $apiKey,
                'name' => $keyData['name'],
                'permissions' => $keyData['permissions']
            ];
        }
    }
    
    // 2. Sinon, essayer l'authentification JWT (Utilisateur Web/Mobile)
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        $payload = JWT::decode($token);
        if ($payload && isset($payload['user_id'])) {
            return [
                'type' => 'jwt',
                'user_id' => $payload['user_id'],
                'email' => $payload['email'],
                'nom' => $payload['nom'],
                'role' => $payload['role']
            ];
        }
    }
    
    return null;
}

/**
 * Exige une authentification (JWT ou API Key)
 */
function requireAuth() {
    $user = getAuthUser();
    if (!$user) {
        sendResponse('error', null, 'Non autorisé - Authentification requise', 401);
    }
    return $user;
}

/**
 * Vérifie si l'utilisateur a une permission spécifique
 */
function hasPermission($permission) {
    $user = getAuthUser();
    if (!$user) {
        return false;
    }
    
    // Pour les utilisateurs JWT, vérifier le rôle
    if ($user['type'] === 'jwt') {
        return $user['role'] === 'admin' || $user['role'] === 'moderator';
    }
    
    // Pour les API Keys, vérifier les permissions
    if ($user['type'] === 'api_key') {
        return in_array($permission, $user['permissions']) || in_array('admin', $user['permissions']);
    }
    
    return false;
}

/**
 * Exige une permission spécifique
 */
function requirePermission($permission) {
    $user = requireAuth();
    
    if (!hasPermission($permission)) {
        sendResponse('error', null, 'Non autorisé - Permission insuffisante', 403);
    }
    
    return $user;
}

/**
 * Exige le rôle admin
 */
function requireAdmin() {
    $user = requireAuth();
    
    if ($user['type'] === 'jwt' && $user['role'] !== 'admin') {
        sendResponse('error', null, 'Non autorisé - Rôle admin requis', 403);
    }
    
    if ($user['type'] === 'api_key' && !in_array('admin', $user['permissions'])) {
        sendResponse('error', null, 'Non autorisé - Permission admin requise', 403);
    }
    
    return $user;
}

/**
 * Fonctions utilitaires
 */

/**
 * @deprecated This function is deprecated and should not be used.
 * All data should be fetched from SQL using Database::getInstance()
 * @throws Exception Always throws to prevent usage
 */
function readJsonFile($path) {
    throw new Exception('DEPRECATED: readJsonFile() is no longer supported. Use Database::getInstance() instead. Path attempted: ' . $path);
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

function validateRequired($data, $required) {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendResponse('error', null, 'Champs requis manquants: ' . implode(', ', $missing), 400);
    }
}

/**
 * Log une action API (Audit Log en SQL + Fallback File)
 */
function logApiAction($action, $details = []) {
    $user = getAuthUser();
    $db = Database::getInstance();
    
    $userId = ($user && $user['type'] === 'jwt') ? $user['user_id'] : null;
    $apiKeyId = null;
    
    // Si c'est une API Key, on cherche son ID en base pour l'audit
    if ($user && $user['type'] === 'api_key') {
        $keyInfo = $db->fetchOne("SELECT id FROM api_keys WHERE api_key = ?", [$user['key']]);
        if ($keyInfo) {
            $apiKeyId = $keyInfo['id'];
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // 1. Audit Log en SQL
    try {
        $db->insert(
            "INSERT INTO audit_logs (user_id, api_key_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $userId,
                $apiKeyId,
                $action,
                $details['entity_type'] ?? 'api_call',
                $details['entity_id'] ?? null,
                isset($details['old_values']) ? json_encode($details['old_values']) : null,
                isset($details['new_values']) ? json_encode($details['new_values']) : json_encode($details),
                $ip,
                $userAgent
            ]
        );
    } catch (Exception $e) {
        // En cas d'erreur DB, le fallback fichier prendra le relais
    }

    // 2. Fallback: Log fichier (toujours utile pour le debug système)
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_type' => $user['type'] ?? 'anonymous',
        'user_identifier' => $user['email'] ?? ($user['key'] ? substr($user['key'], 0, 15) . '...' : 'unknown'),
        'details' => $details,
        'ip' => $ip,
        'user_agent' => $userAgent
    ];
    
    $logFile = __DIR__ . '/../../logs/api_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Génère un identifiant unique pour une demande (Format SQL)
 */
function generateRequestId() {
    $db = Database::getInstance();
    $year = date('Y');
    try {
        $result = $db->fetchOne("SELECT COUNT(*) as total FROM ai_requests WHERE id LIKE 'REQ-$year-%'");
        $count = $result['total'] ?? 0;
    } catch (Exception $e) {
        $count = 0;
    }
    return "REQ-{$year}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}
?>
