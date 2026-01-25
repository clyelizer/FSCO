<?php
/**
 * Configuration admin FSCo
 */

// Session et sécurité
session_start();

// Configuration de sécurité
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Chemins des données
define('ADMIN_DATA_PATH', __DIR__ . '/../data/');

// Fichiers de données
define('DATA_FORMATIONS', ADMIN_DATA_PATH . 'formations.json');
define('DATA_RESSOURCES', ADMIN_DATA_PATH . 'ressources.json');
define('DATA_BLOGS', ADMIN_DATA_PATH . 'blogs.json');
define('DATA_USERS', ADMIN_DATA_PATH . 'users.json');
define('DATA_SITE_CONFIG', ADMIN_DATA_PATH . 'site_config.json');

// Configuration d'authentification
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', password_hash('fsco_admin_2025', PASSWORD_DEFAULT));

/**
 * Fonctions d'authentification
 */
function isLoggedIn()
{
    // Check if user is logged in AND has admin role
    return isset($_SESSION['user_id']) &&
        isset($_SESSION['user_role']) &&
        $_SESSION['user_role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        // Redirect to main login page if not logged in as admin
        header('Location: ../../auth/login.php');
        exit;
    }
}

// Login/Logout functions are no longer needed here as they are handled by auth_actions.php
// But we keep logout for the admin sidebar link
function logout()
{
    // Nettoyer toutes les variables de session
    $_SESSION = array();

    // Détruire le cookie de session si configuré
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Détruire la session
    session_destroy();

    // Redirection vers la page d'accueil
    header('Location: ../../index.php');
    exit;
}

/**
 * Fonctions de gestion des données
 */
function ensureDataDirectory()
{
    if (!file_exists(ADMIN_DATA_PATH)) {
        mkdir(ADMIN_DATA_PATH, 0755, true);
    }
}

function readJsonData($file)
{
    if (!file_exists($file)) {
        return [];
    }
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}

function writeJsonData($file, $data)
{
    ensureDataDirectory();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $json) !== false;
}

/**
 * Fonctions utilitaires
 */
function generateId()
{
    return 'id_' . uniqid() . '_' . time();
}

function sanitizeInput($input)
{
    return trim($input);
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Gestion des uploads
 */
function handleFileUpload($file, $uploadDir)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors du téléchargement'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Fichier trop volumineux'];
    }

    $fileName = time() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'fileName' => $fileName];
    }

    return ['success' => false, 'error' => 'Impossible de sauvegarder le fichier'];
}

function handleResourceUpload($file, $uploadDir)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors du téléchargement'];
    }

    // Types autorisés pour les ressources
    $allowedTypes = [
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-rar-compressed',
        'video/mp4',
        'video/quicktime'
    ];

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé. Types acceptés : PDF, Excel, Word, PowerPoint, ZIP, RAR, Vidéos.'];
    }

    $maxSize = 50 * 1024 * 1024; // 50MB pour les ressources
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 50MB)'];
    }

    $fileName = time() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'fileName' => $fileName];
    }

    return ['success' => false, 'error' => 'Impossible de sauvegarder le fichier'];
}

// Initialisation
ensureDataDirectory();