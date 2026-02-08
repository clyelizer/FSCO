<?php
/**
 * API pour gérer les demandes de l'agent IA
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequests();
        break;
    case 'POST':
        handlePostRequest();
        break;
    case 'PUT':
        handlePutRequest();
        break;
    case 'DELETE':
        handleDeleteRequest();
        break;
    default:
        sendJsonResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * Lister les demandes
 */
function handleGetRequests() {
    verifyApiKey();
    $db = Database::getInstance();
    
    $query = "SELECT * FROM ai_requests";
    $params = [];
    $where = [];

    // Filtrer par statut si spécifié
    if (isset($_GET['status'])) {
        $where[] = "status = ?";
        $params[] = $_GET['status'];
    }
    
    // Filtrer par type si spécifié
    if (isset($_GET['type'])) {
        $where[] = "type = ?";
        $params[] = $_GET['type'];
    }
    
    // Filtrer par ID si spécifié
    if (isset($_GET['id'])) {
        $where[] = "id = ?";
        $params[] = $_GET['id'];
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $requests = $db->fetchAll($query, $params);
    
    // Formater pour la rétrocompatibilité si nécessaire
    foreach ($requests as &$req) {
        $req['data'] = json_decode($req['payload_json'], true);
        $req['applied_result'] = json_decode($req['applied_result'], true);
        // Assurer que les dates sont au bon format
        $req['created_at_ts'] = strtotime($req['created_at']);
    }
    
    sendJsonResponse('success', $requests, 'Demandes récupérées');
}

/**
 * Créer une demande
 */
function handlePostRequest() {
    verifyApiKey();
    $db = Database::getInstance();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendJsonResponse('error', null, 'Données invalides', 400);
    }
    
    if (!isset($data['type']) || !isset($data['data'])) {
        sendJsonResponse('error', null, 'Type et data requis', 400);
    }
    
    $requestId = generateRequestId();
    
    try {
        $db->insert(
            "INSERT INTO ai_requests (id, type, status, created_by, payload_json, created_at) 
             VALUES (?, ?, 'pending_confirmation', ?, ?, NOW())",
            [
                $requestId,
                $data['type'],
                $data['created_by'] ?? 'agent_ia',
                json_encode($data['data'])
            ]
        );

        $request = [
            'id' => $requestId,
            'type' => $data['type'],
            'status' => 'pending_confirmation',
            'created_by' => $data['created_by'] ?? 'agent_ia',
            'data' => $data['data']
        ];
        
        logAction('create_request', ['request_id' => $requestId, 'type' => $data['type']]);
        sendJsonResponse('success', $request, 'Demande créée avec succès');

    } catch (Exception $e) {
        sendJsonResponse('error', null, 'Erreur lors de la création : ' . $e->getMessage(), 500);
    }
}

/**
 * Mettre à jour une demande (validation)
 */
function handlePutRequest() {
    verifyApiKey();
    $db = Database::getInstance();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['id'])) {
        sendJsonResponse('error', null, 'Données ou ID invalides', 400);
    }
    
    try {
        $fields = [];
        $params = [];

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($fields)) {
            sendJsonResponse('error', null, 'Aucun champ à mettre à jour', 400);
        }

        $params[] = $data['id'];
        $db->update("UPDATE ai_requests SET " . implode(", ", $fields) . " WHERE id = ?", $params);
        
        logAction('update_request', ['request_id' => $data['id'], 'status' => $data['status'] ?? null]);
        sendJsonResponse('success', null, 'Demande mise à jour');

    } catch (Exception $e) {
        sendJsonResponse('error', null, 'Erreur lors de la mise à jour : ' . $e->getMessage(), 500);
    }
}

/**
 * Supprimer une demande
 */
function handleDeleteRequest() {
    verifyApiKey();
    $db = Database::getInstance();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['id'])) {
        sendJsonResponse('error', null, 'ID de demande requis', 400);
    }
    
    try {
        $db->delete("DELETE FROM ai_requests WHERE id = ?", [$data['id']]);
        logAction('delete_request', ['request_id' => $data['id']]);
        sendJsonResponse('success', null, 'Demande supprimée');
    } catch (Exception $e) {
        sendJsonResponse('error', null, 'Erreur lors de la suppression : ' . $e->getMessage(), 500);
    }
}
?>
