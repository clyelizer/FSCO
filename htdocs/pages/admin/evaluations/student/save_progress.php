<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

requireStudent();

$user = getCurrentUser();

// Récupérer les données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$sessionId = (int) ($data['session_id'] ?? 0);
$answers = $data['answers'] ?? [];

// Vérifier que la session appartient à l'utilisateur
$session = Database::getInstance()->fetchOne(
    "SELECT id FROM exam_sessions WHERE id = ? AND user_id = ? AND statut = 'en_cours'",
    [$sessionId, $user['id']]
);

if (!$session) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session invalide']);
    exit;
}

try {
    // Mettre à jour les réponses
    Database::getInstance()->update(
        "UPDATE exam_sessions SET reponses = ?, dernier_progress = NOW() WHERE id = ?",
        [json_encode($answers), $sessionId]
    );

    echo json_encode(['success' => true, 'message' => 'Progression sauvegardée']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
}
?>