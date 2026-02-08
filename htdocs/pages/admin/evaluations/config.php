<?php
/**
 * Configuration de la Plateforme d'Examens (Intégration FSCo)
 */

// Inclure la configuration racine de FSCo
require_once __DIR__ . '/../../../config.php';

// Configuration de l'application
define('APP_NAME', 'Plateforme d\'Examens');
define('APP_URL', '/pages/admin/evaluations'); // Chemin relatif pour flexibilité
define('APP_VERSION', '1.0.0');

// Chemins des dossiers
define('ROOT_PATH', __DIR__ . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('IMAGE_PATH', UPLOAD_PATH . 'images/');
define('AUDIO_PATH', UPLOAD_PATH . 'audio/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Configuration des uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_AUDIO_TYPES', ['mp3', 'wav', 'ogg', 'm4a']);

// Configuration des sessions (Géré par FSCo, mais on définit les constantes si besoin)
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 3600 * 24); // 24 heures
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Configuration de sécurité
define('CSRF_TOKEN_LENGTH', 32);
if (!defined('PASSWORD_MIN_LENGTH'))
    define('PASSWORD_MIN_LENGTH', 8);
if (!defined('MAX_LOGIN_ATTEMPTS'))
    define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('LOGIN_LOCKOUT_TIME'))
    define('LOGIN_LOCKOUT_TIME', 900);

// Configuration de l'IA (Gemini)
define('GEMINI_API_KEY', 'YOUR_API_KEY_HERE'); // Remplacez par votre clé API
define('ENABLE_AI_CORRECTION', true);

define('DEFAULT_EXAM_DURATION', 60); // minutes
define('AUTO_SAVE_INTERVAL', 30); // secondes
define('SESSION_TIMEOUT_BUFFER', 300); // 5 minutes de tolérance

// Niveaux de difficulté
define('DIFFICULTY_LEVELS', [
    'facile' => 'Facile',
    'moyen' => 'Moyen',
    'difficile' => 'Difficile'
]);

// Types de questions
define('QUESTION_TYPES', [
    'qcm' => 'QCM (Choix Multiple)',
    'ouverte' => 'Question Ouverte',
    'vrai_faux' => 'Vrai/Faux'
]);

// Statuts des examens
define('EXAM_STATUSES', [
    'draft' => 'Brouillon',
    'published' => 'Publié',
    'closed' => 'Fermé'
]);

// Statuts des sessions d'examen
define('SESSION_STATUSES', [
    'en_cours' => 'En cours',
    'termine' => 'Terminé',
    'abandonne' => 'Abandonné',
    'expire' => 'Expiré'
]);

// Messages d'erreur personnalisés
define('ERROR_MESSAGES', [
    'db_connection' => 'Erreur de connexion à la base de données',
    'invalid_credentials' => 'Email ou mot de passe incorrect',
    'access_denied' => 'Accès refusé',
    'file_too_large' => 'Le fichier est trop volumineux',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'upload_failed' => 'Échec de l\'upload',
    'session_expired' => 'Votre session a expiré',
    'exam_not_available' => 'Cet examen n\'est pas disponible',
    'question_not_found' => 'Question introuvable'
]);

// Configuration du débogage
define('DEBUG_MODE', true); // Temporaire pour dev
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>