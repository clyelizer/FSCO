<?php
/**
 * Site Settings Management API
 * Permet de gérer la configuration du site via API
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];
$dataPath = __DIR__ . '/../../htdocs/pages/admin/data/site_config.json';

switch ($method) {
    case 'GET':
        handleGet($dataPath);
        break;
    case 'PUT':
        handlePut($dataPath);
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/admin/settings
 * Récupère la configuration du site
 */
function handleGet($dataPath) {
    $user = requireAuth();
    
    $config = readJsonFile($dataPath);
    
    // Si la config est vide, créer une config par défaut
    if (empty($config)) {
        $config = getDefaultConfig();
    }
    
    sendResponse('success', $config, 'Configuration récupérée avec succès');
}

/**
 * PUT /api/admin/settings
 * Met à jour la configuration du site
 */
function handlePut($dataPath) {
    $user = requirePermission('write');
    
    $data = getJsonInput();
    
    $config = readJsonFile($dataPath);
    
    if (empty($config)) {
        $config = getDefaultConfig();
    }
    
    // Fusionner les données avec la config existante
    $config = array_merge($config, $data);
    
    // Sanitizer les champs texte
    if (isset($config['site_name'])) $config['site_name'] = sanitizeInput($config['site_name']);
    if (isset($config['site_description'])) $config['site_description'] = sanitizeInput($config['site_description']);
    if (isset($config['contact_email'])) $config['contact_email'] = filter_var($config['contact_email'], FILTER_SANITIZE_EMAIL);
    if (isset($config['contact_phone'])) $config['contact_phone'] = sanitizeInput($config['contact_phone']);
    if (isset($config['contact_address'])) $config['contact_address'] = sanitizeInput($config['contact_address']);
    
    $config['updated_at'] = time();
    
    if (file_put_contents($dataPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('update_settings', ['updated_fields' => array_keys($data)]);
        sendResponse('success', $config, 'Configuration mise à jour avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

/**
 * Configuration par défaut du site
 */
function getDefaultConfig() {
    return [
        'site_name' => 'FSCO',
        'site_description' => 'Formation, Sécurité, Consulting, Organisation',
        'site_url' => 'https://fsco.gt.tc',
        'logo' => '',
        'favicon' => '',
        
        // Contact
        'contact_email' => 'contact@fsco.gt.tc',
        'contact_phone' => '+212 5XX XXX XXX',
        'contact_address' => 'Casablanca, Maroc',
        
        // Social Media
        'social_facebook' => '',
        'social_twitter' => '',
        'social_linkedin' => '',
        'social_instagram' => '',
        'social_youtube' => '',
        
        // SEO
        'seo_title' => 'FSCO - Formation, Sécurité, Consulting, Organisation',
        'seo_description' => 'FSCO offre des services de formation, sécurité informatique, consulting et organisation.',
        'seo_keywords' => 'formation, sécurité, consulting, organisation, IA, cybersécurité',
        
        // Features
        'enable_blog' => true,
        'enable_formations' => true,
        'enable_ressources' => true,
        'enable_evaluations' => true,
        'enable_library' => true,
        
        // Maintenance
        'maintenance_mode' => false,
        'maintenance_message' => 'Le site est en maintenance. Merci de revenir plus tard.',
        
        // Registration
        'enable_registration' => true,
        'require_email_verification' => false,
        
        // Analytics
        'google_analytics_id' => '',
        'facebook_pixel_id' => '',
        
        // Dates
        'created_at' => time(),
        'updated_at' => time()
    ];
}
