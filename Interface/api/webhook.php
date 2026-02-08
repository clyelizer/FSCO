<?php
/**
 * Webhook pour recevoir les notifications du service WhatsApp
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        handleWebhook();
        break;
    default:
        sendJsonResponse('error', null, 'Méthode non autorisée', 405);
}

function handleWebhook() {
    verifyApiKey();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendJsonResponse('error', null, 'Données invalides', 400);
    }
    
    $event = $data['event'] ?? '';
    $payload = $data['data'] ?? [];
    
    switch ($event) {
        case 'whatsapp_connected':
            handleWhatsAppConnected($payload);
            break;
        case 'whatsapp_disconnected':
            handleWhatsAppDisconnected($payload);
            break;
        case 'whatsapp_credentials_updated':
            handleCredentialsUpdated($payload);
            break;
        case 'new_request':
            handleNewRequest($payload);
            break;
        default:
            logAction('unknown_webhook', ['event' => $event, 'payload' => $payload]);
    }
    
    sendJsonResponse('success', null, 'Webhook traité');
}

/**
 * WhatsApp connecté - Mise à jour SQL de la config
 */
function handleWhatsAppConnected($payload) {
    logAction('whatsapp_connected', $payload);
    $db = Database::getInstance();
    
    $db->update(
        "INSERT INTO site_config (config_key, config_value, config_type, category) 
         VALUES ('whatsapp_status', 'connected', 'string', 'system')
         ON DUPLICATE KEY UPDATE config_value = 'connected'",
        []
    );
}

/**
 * WhatsApp déconnecté - Mise à jour SQL de la config
 */
function handleWhatsAppDisconnected($payload) {
    logAction('whatsapp_disconnected', $payload);
    $db = Database::getInstance();
    
    $db->update(
        "INSERT INTO site_config (config_key, config_value, config_type, category) 
         VALUES ('whatsapp_status', 'disconnected', 'string', 'system')
         ON DUPLICATE KEY UPDATE config_value = 'disconnected'",
        []
    );
}

function handleCredentialsUpdated($payload) {
    logAction('credentials_updated', $payload);
}

/**
 * Nouvelle demande / message reçu - Enregistrement SQL
 */
function handleNewRequest($payload) {
    $db = Database::getInstance();
    
    $waId = $payload['phone_number'] ?? $payload['from'] ?? '';
    $body = $payload['message'] ?? $payload['body'] ?? '';
    $waMessageId = $payload['message_id'] ?? $payload['id'] ?? null;
    $mediaUrl = $payload['media_url'] ?? null;
    $messageType = $payload['type'] ?? 'text';

    if (empty($waId)) return;

    logAction('new_whatsapp_message', [
        'from' => $waId,
        'body' => substr($body, 0, 100)
    ]);

    try {
        // 1. Chat (trouver ou créer)
        $chat = $db->fetchOne("SELECT id FROM whatsapp_chats WHERE wa_id = ?", [$waId]);
        
        if (!$chat) {
            $chatId = $db->insert(
                "INSERT INTO whatsapp_chats (wa_id, status, last_message_at) VALUES (?, 'open', NOW())",
                [$waId]
            );
        } else {
            $chatId = $chat['id'];
            $db->update(
                "UPDATE whatsapp_chats SET last_message_at = NOW(), status = 'open' WHERE id = ?",
                [$chatId]
            );
        }

        // 2. Message
        $db->insert(
            "INSERT INTO whatsapp_messages (chat_id, direction, message_type, body, media_url, wa_message_id, status, created_at) 
             VALUES (?, 'in', ?, ?, ?, ?, 'delivered', NOW())",
            [
                $chatId,
                $messageType,
                $body,
                $mediaUrl,
                $waMessageId
            ]
        );

    } catch (Exception $e) {
        logAction('error_sql_webhook', ['error' => $e->getMessage()]);
    }
}
