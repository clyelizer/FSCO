<?php
session_start();
require_once '../admin/includes/config.php';

// Get resource ID
$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(403);
    die("Accès refusé.");
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

if (!$resource || empty($resource['fichier'])) {
    http_response_code(404);
    die("Ressource introuvable.");
}

// File path (absolute)
$filePath = __DIR__ . '/../../' . $resource['fichier'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die("Fichier introuvable sur le serveur.");
}

// Security: Verify user session
/* if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Vous devez être connecté pour accéder à cette ressource.");
} */

// Get file info
$mimeType = mime_content_type($filePath);
$fileSize = filesize($filePath);
$fileName = basename($filePath);

// Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for streaming (not download)
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Accept-Ranges: bytes');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Prevent caching
header('X-Content-Type-Options: nosniff');

// Stream the file in chunks to prevent memory issues
$handle = fopen($filePath, 'rb');
if ($handle === false) {
    http_response_code(500);
    die("Erreur lors de la lecture du fichier.");
}

// Stream in 8KB chunks
while (!feof($handle)) {
    echo fread($handle, 8192);
    flush();
}

fclose($handle);
exit;
