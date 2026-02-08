<?php
/**
 * Migration SQL v3 - Demandes et IA
 * Exécute le schéma SQL et migre les données JSON existantes.
 */
require_once __DIR__ . '/../Interface/config.php';

echo "Démarrage de la migration v3...\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 1. Exécuter le SQL de création de table
    $sql = file_get_contents(__DIR__ . '/migrate_v3.sql');
    $pdo->exec($sql);
    echo "✅ Table ai_requests créée ou déjà existante.\n";

    // 2. Migrer requests.json si présent
    if (file_exists(REQUESTS_FILE)) {
        $requests = json_decode(file_get_contents(REQUESTS_FILE), true);
        if ($requests) {
            $count = 0;
            foreach ($requests as $req) {
                // Vérifier si déjà importé
                $exists = $db->fetchOne("SELECT id FROM ai_requests WHERE id = ?", [$req['id']]);
                if (!$exists) {
                    $db->insert(
                        "INSERT INTO ai_requests (id, type, status, created_by, payload_json, applied_result, created_at, applied_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $req['id'],
                            $req['type'],
                            $req['status'] ?? 'pending_confirmation',
                            $req['created_by'] ?? 'legacy',
                            json_encode($req['data'] ?? []),
                            isset($req['applied_result']) ? json_encode($req['applied_result']) : null,
                            $req['created_at'] ?? date('Y-m-d H:i:s'),
                            $req['applied_at'] ?? null
                        ]
                    );
                    $count++;
                }
            }
            echo "✅ $count demandes migrées depuis JSON.\n";
            
            // Renommer le fichier pour éviter les réimports
            // rename(REQUESTS_FILE, REQUESTS_FILE . '.bak');
        }
    }

    echo "\nMigration terminée avec succès !\n";

} catch (Exception $e) {
    echo "❌ Erreur de migration : " . $e->getMessage() . "\n";
}
