<?php
require_once __DIR__ . '/../../includes/api_common.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$id = $_POST['id'] ?? '';

if (empty($id)) {
    sendResponse('error', null, 'ID requis', 400);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("DELETE FROM user_library WHERE id = ? AND user_id = ?");

if ($stmt->execute([$id, $user['user_id']])) {
    sendResponse('success', null, 'Élément retiré avec succès');
} else {
    sendResponse('error', null, 'Erreur lors de la suppression', 500);
}
