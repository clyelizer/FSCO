<?php
require_once __DIR__ . '/../includes/api_common.php';

// Optionnel: Vérification d'une API Key pour éviter le spam externe
$apiKey = getallheaders()['X-API-KEY'] ?? '';
if ($apiKey !== 'FSCO_MOBILE_APP_2026') {
    // On pourrait renvoyer un 401, mais ici on reste souple ou on log l'erreur
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée', 405);
}

$event_type = sanitizeInput($_POST['event_type'] ?? '');
$resource_id = sanitizeInput($_POST['resource_id'] ?? '');
$page_number = (int)($_POST['page_number'] ?? 0);
$duration = (int)($_POST['duration'] ?? 0);
$device_id = sanitizeInput($_POST['device_id'] ?? 'anonymous');
$user_id = getAuthUser()['user_id'] ?? null;

if (empty($event_type)) {
    sendResponse('error', null, 'Event type requis', 400);
}

// Log dans la base de données (Table à créer si elle n'existe pas)
$pdo = getDBConnection();

try {
    $stmt = $pdo->prepare("INSERT INTO anonymous_tracking (event_type, resource_id, page_number, duration, device_id, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$event_type, $resource_id, $page_number, $duration, $device_id, $user_id]);
    sendResponse('success', null, 'Log enregistré');
} catch (PDOException $e) {
    // Si la table n'existe pas, on la crée à la volée pour simplifier le déploiement de l'API
    if ($e->getCode() == '42S02') {
        $pdo->exec("CREATE TABLE anonymous_tracking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50),
            resource_id VARCHAR(50),
            page_number INT,
            duration INT,
            device_id VARCHAR(100),
            user_id INT NULL,
            created_at DATETIME
        )");
        $stmt = $pdo->prepare("INSERT INTO anonymous_tracking (event_type, resource_id, page_number, duration, device_id, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$event_type, $resource_id, $page_number, $duration, $device_id, $user_id]);
        sendResponse('success', null, 'Log enregistré (Table créée)');
    } else {
        sendResponse('error', null, 'Erreur de tracking: ' . $e->getMessage(), 500);
    }
}
