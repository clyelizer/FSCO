<?php
require_once __DIR__ . '/../../includes/api_common.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? null;
$is_favorite = isset($_POST['is_favorite']) ? (int)$_POST['is_favorite'] : null;

if (empty($id)) {
    sendResponse('error', null, 'ID requis', 400);
}

$pdo = getDBConnection();
$updates = [];
$params = [];

if ($status !== null) {
    $updates[] = "status = ?";
    $params[] = $status;
}

if ($is_favorite !== null) {
    $updates[] = "is_favorite = ?";
    $params[] = $is_favorite;
}

if (empty($updates)) {
    sendResponse('error', null, 'Aucune donnée à mettre à jour', 400);
}

$params[] = $id;
$params[] = $user['user_id'];
$sql = "UPDATE user_library SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute($params)) {
    sendResponse('success', null, 'Mise à jour réussie');
} else {
    sendResponse('error', null, 'Erreur lors de la mise à jour', 500);
}
