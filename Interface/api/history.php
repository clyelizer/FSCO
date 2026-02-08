<?php
/**
 * API History Endpoint
 * Récupère l'historique des messages pour un utilisateur WhatsApp spécifique.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Vérifier la clé API X-API-Key
verifyApiKey();

$db = Database::getInstance();

// Récupérer le wa_id depuis l'URL (Interface/api/history.php?wa_id=...)
$waId = $_GET['wa_id'] ?? null;

if (!$waId) {
    // Essayer de parser l'URL si on utilise du rewriting (Interface/api/history/phone)
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $lastPart = end($parts);
    if (strpos($lastPart, '@') !== false || is_numeric($lastPart)) {
        $waId = $lastPart;
    }
}

if (!$waId) {
    sendJsonResponse('error', null, 'wa_id requis', 400);
}

try {
    // 1. Trouver le chat
    $chat = $db->fetchOne("SELECT id FROM whatsapp_chats WHERE wa_id = ?", [$waId]);
    
    if (!$chat) {
        sendJsonResponse('success', ['history' => []], 'Aucun historique trouvé pour cet utilisateur');
    }

    // 2. Récupérer les 20 derniers messages
    $messages = $db->fetchAll(
        "SELECT direction, message_type, body, created_at 
         FROM whatsapp_messages 
         WHERE chat_id = ? 
         ORDER BY created_at ASC 
         LIMIT 20",
        [$chat['id']]
    );

    // 3. Formater pour l'IA (role: user/assistant)
    $history = array_map(function($msg) {
        return [
            'role' => ($msg['direction'] === 'in' ? 'user' : 'assistant'),
            'content' => $msg['body'],
            'timestamp' => $msg['created_at']
        ];
    }, $messages);

    sendJsonResponse('success', ['history' => history], 'Historique récupéré');

} catch (Exception $e) {
    sendJsonResponse('error', null, 'Erreur lors de la récupération de l\'historique : ' . $e->getMessage(), 500);
}
