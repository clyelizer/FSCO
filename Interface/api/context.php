<?php
/**
 * API Context Endpoint
 * Fournit à l'agent IA un résumé de l'état actuel du site pour qu'il ait du contexte.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Vérifier la clé API X-API-Key
verifyApiKey();

$db = Database::getInstance();

try {
    // 1. Récupérer la configuration générale
    $configResult = $db->fetchAll("SELECT config_key, config_value, config_type FROM site_config");
    $config = [];
    foreach ($configResult as $row) {
        $value = $row['config_value'];
        if ($row['config_type'] === 'json') {
            $value = json_decode($value, true);
        } elseif ($row['config_type'] === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($row['config_type'] === 'number') {
            $value = (float)$value;
        }
        $config[$row['config_key']] = $value;
    }

    // 2. Résumé des blogs (articles récents)
    $blogs = $db->fetchAll("SELECT id, titre, categorie, statut, created_at FROM blogs ORDER BY created_at DESC LIMIT 10");

    // 3. Résumé des formations
    $formations = $db->fetchAll("SELECT id, titre, categorie, niveau, prix, statut FROM formations LIMIT 10");

    // 4. Résumé des ressources
    $ressources = $db->fetchAll("SELECT id, titre, categorie, format, statut FROM ressources LIMIT 10");

    // 5. Statistiques rapides
    $stats = [
        'total_blogs' => $db->fetchOne("SELECT COUNT(*) as c FROM blogs")['c'],
        'total_formations' => $db->fetchOne("SELECT COUNT(*) as c FROM formations")['c'],
        'total_ressources' => $db->fetchOne("SELECT COUNT(*) as c FROM ressources")['c'],
        'total_faq' => $db->fetchOne("SELECT COUNT(*) as c FROM faq")['c'],
    ];

    $context = [
        'site_config' => $config,
        'recent_blogs' => $blogs,
        'recent_formations' => $formations,
        'recent_ressources' => $ressources,
        'stats' => $stats,
        'current_time' => date('Y-m-d H:i:s'),
        'environment' => 'production'
    ];

    sendJsonResponse('success', $context, 'Contexte récupéré avec succès');

} catch (Exception $e) {
    sendJsonResponse('error', null, 'Erreur lors de la récupération du contexte : ' . $e->getMessage(), 500);
}
