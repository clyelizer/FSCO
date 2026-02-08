<?php
/**
 * Data Migration Script: JSON to SQL
 * Migre les clés API du fichier JSON vers la nouvelle table MySQL.
 */

// Charger les dépendances (Ajustez les chemins si nécessaire)
require_once __DIR__ . '/../htdocs/config.php';
require_once __DIR__ . '/../pages/admin/evaluations/includes/database.php';
require_once __DIR__ . '/../api/includes/api_key_helper.php';

header('Content-Type: text/plain');
echo "Starting Migration...\n";

try {
    $db = Database::getInstance();
    
    // 1. Migration des Clés API
    $jsonPath = __DIR__ . '/../api_keys.json';
    if (file_exists($jsonPath)) {
        echo "Found api_keys.json. Migrating...\n";
        $keys = json_decode(file_get_contents($jsonPath), true) ?? [];
        
        $count = 0;
        foreach ($keys as $keyData) {
            // Vérifier si la clé existe déjà en base
            $exists = $db->fetchOne("SELECT id FROM api_keys WHERE api_key = ?", [$keyData['key']]);
            
            if (!$exists) {
                $db->insert(
                    "INSERT INTO api_keys (api_key, name, permissions, created_at, last_used, expires_at, is_active, usage_count) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $keyData['key'],
                        $keyData['name'],
                        json_encode($keyData['permissions']),
                        date('Y-m-d H:i:s', $keyData['created_at']),
                        $keyData['last_used'] ? date('Y-m-d H:i:s', $keyData['last_used']) : null,
                        $keyData['expires_at'] ? date('Y-m-d H:i:s', $keyData['expires_at']) : null,
                        $keyData['is_active'] ? 1 : 0,
                        $keyData['usage_count'] ?? 0
                    ]
                );
                $count++;
            }
        }
        echo "Successfully migrated $count API keys.\n";
    } else {
        echo "api_keys.json not found, skipping API keys migration.\n";
    }

    // 2. Initialisation de l'Agent IA par défaut (facultatif)
    $agentExists = $db->fetchOne("SELECT id FROM ai_agents WHERE code_name = ?", ['fsco_master_agent']);
    if (!$agentExists) {
        $db->insert(
            "INSERT INTO ai_agents (name, code_name, status, config_json) VALUES (?, ?, ?, ?)",
            [
                'FSCO Master Agent',
                'fsco_master_agent',
                'active',
                json_encode([
                    'model' => 'glm-4',
                    'role' => 'Principal Administrator',
                    'can_edit_settings' => true
                ])
            ]
        );
        echo "Default AI Agent created.\n";
    }

    echo "Migration Completed Successfully!\n";

} catch (Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
}
