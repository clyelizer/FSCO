<?php
session_start();
require_once '../admin/includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in -> Redirect to subscription section
    header('Location: ../../index.php#abonnements');
    exit;
}

// Get user plan
$userPlan = $_SESSION['user_plan'] ?? 'free';

// Check if plan allows download (premium or enterprise)
if ($userPlan !== 'premium' && $userPlan !== 'enterprise') {
    // Free plan -> Redirect to subscription section
    header('Location: ../../index.php#abonnements');
    exit;
}

// Get resource ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Ressource non spécifiée.");
}

// Load resources to find file path
$ressources = readJsonData(DATA_RESSOURCES);
$resource = null;

foreach ($ressources as $r) {
    if ($r['id'] === $id) {
        $resource = $r;
        break;
    }
}

if (!$resource) {
    die("Ressource introuvable.");
}

// File path
$filePath = '../../' . $resource['fichier'];

if (!file_exists($filePath)) {
    die("Le fichier n'est pas disponible sur le serveur.");
}

// Serve file
$mimeType = mime_content_type($filePath);
$fileName = basename($filePath);

// Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
