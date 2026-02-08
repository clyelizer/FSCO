<?php
/**
 * Configuration de l'Interface Agent IA FSCO
 */
require_once __DIR__ . '/../htdocs/config.php';
require_once __DIR__ . '/../pages/admin/evaluations/includes/database.php';

// URL du service WhatsApp (Node.js sur Render/Railway)
define('WHATSAPP_SERVICE_URL', 'https://fsco-whatsapp.onrender.com'); // Changez cette URL après déploiement

// Clé API pour communiquer avec le service WhatsApp
define('WHATSAPP_API_KEY', 'fsco_wa_secure_k3y_2026_Xz9Lm');

// URL de l'API FSCO
define('FSCO_API_URL', 'https://fsco.gt.tc/api');
define('FSCO_API_KEY', 'fsco_change_this_to_api_key'); // Clé API créée via /api/admin/api-keys

/**
 * Envoie une réponse JSON
 */
function sendJsonResponse($status, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'data' => $data,
        'message' => $message,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Log une action dans la table audit_logs via SQL
 */
function logAction($action, $details = []) {
    $db = Database::getInstance();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    try {
        $db->insert(
            "INSERT INTO audit_logs (action, entity_type, new_values, ip_address, created_at) 
             VALUES (?, 'interface', ?, ?, NOW())",
            [$action, json_encode($details), $ip]
        );
    } catch (Exception $e) {
        // Fallback discret en cas d'erreur DB
        error_log("Interface log error: " . $e->getMessage());
    }
}

/**
 * Vérifie la clé API X-API-Key (Provenant du service Node.js)
 */
function verifyApiKey() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? '';
    
    if ($apiKey !== WHATSAPP_API_KEY) {
        sendJsonResponse('error', null, 'Non autorisé', 401);
    }
    
    return true;
}

/**
 * Vérifie si le service WhatsApp (Node.js) est accessible
 */
function checkWhatsAppService() {
    $ch = curl_init(WHATSAPP_SERVICE_URL . '/api/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}
