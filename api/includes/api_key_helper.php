<?php
/**
 * API Key Authentication Helper (SQL Version)
 * Permet l'authentification via clés API pour les agents IA et intégrations externes.
 * Utilise la table MySQL `api_keys`.
 */

require_once __DIR__ . '/../../pages/admin/evaluations/includes/database.php';

class APIKey {
    /**
     * Génère une nouvelle clé API sécurisée et l'enregistre en base
     */
    public static function generate($name, $permissions = ['read', 'write'], $expiresIn = null) {
        $db = Database::getInstance();
        
        // Générer une clé unique et sécurisée
        $key = 'fsco_' . bin2hex(random_bytes(32));
        
        $expiresAt = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;
        
        $db->insert(
            "INSERT INTO api_keys (api_key, name, permissions, created_at, expires_at, is_active, usage_count) 
             VALUES (?, ?, ?, NOW(), ?, 1, 0)",
            [
                $key,
                $name,
                json_encode($permissions),
                $expiresAt
            ]
        );
        
        return [
            'key' => $key,
            'name' => $name,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'is_active' => true
        ];
    }

    /**
     * Valide une clé API
     */
    public static function validate($key) {
        $db = Database::getInstance();
        
        $apiKey = $db->fetchOne(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
            [$key]
        );
        
        if (!$apiKey) {
            return false;
        }
        
        // Vérifier l'expiration
        if ($apiKey['expires_at'] && strtotime($apiKey['expires_at']) < time()) {
            // Optionnel: marquer comme inactive si expirée
            self::revoke($key);
            return false;
        }
        
        // Mettre à jour la dernière utilisation et le compteur
        $db->update(
            "UPDATE api_keys SET last_used = NOW(), usage_count = usage_count + 1 WHERE id = ?",
            [$apiKey['id']]
        );
        
        // Décoder les permissions
        $apiKey['permissions'] = json_decode($apiKey['permissions'], true) ?? [];
        
        return $apiKey;
    }

    /**
     * Révoque une clé API
     */
    public static function revoke($key) {
        $db = Database::getInstance();
        return $db->update("UPDATE api_keys SET is_active = 0 WHERE api_key = ?", [$key]) > 0;
    }

    /**
     * Supprime une clé API
     */
    public static function delete($key) {
        $db = Database::getInstance();
        return $db->delete("DELETE FROM api_keys WHERE api_key = ?", [$key]) > 0;
    }

    /**
     * Liste toutes les clés API (avec masquage partiel si nécessaire par l'appelant)
     */
    public static function listAll() {
        $db = Database::getInstance();
        $keys = $db->fetchAll("SELECT * FROM api_keys ORDER BY created_at DESC");
        
        foreach ($keys as &$key) {
            $key['key'] = $key['api_key']; // Alias pour compatibilité
            $key['permissions'] = json_decode($key['permissions'], true) ?? [];
            $key['created_at'] = strtotime($key['created_at']);
            $key['last_used'] = $key['last_used'] ? strtotime($key['last_used']) : null;
            $key['expires_at'] = $key['expires_at'] ? strtotime($key['expires_at']) : null;
        }
        
        return $keys;
    }

    /**
     * Récupère les détails d'une clé API
     */
    public static function getDetails($key) {
        $db = Database::getInstance();
        $apiKey = $db->fetchOne("SELECT * FROM api_keys WHERE api_key = ?", [$key]);
        
        if ($apiKey) {
            $apiKey['permissions'] = json_decode($apiKey['permissions'], true) ?? [];
        }
        
        return $apiKey;
    }

    /**
     * Met à jour les permissions d'une clé API
     */
    public static function updatePermissions($key, $permissions) {
        $db = Database::getInstance();
        return $db->update(
            "UPDATE api_keys SET permissions = ? WHERE api_key = ?",
            [json_encode($permissions), $key]
        ) > 0;
    }

    /**
     * Vérifie si une clé API a une permission spécifique
     */
    public static function hasPermission($key, $permission) {
        $apiKey = self::validate($key);
        
        if (!$apiKey) {
            return false;
        }
        
        // permissions est déjà un tableau grâce à validate()
        return in_array($permission, $apiKey['permissions']) || in_array('admin', $apiKey['permissions']);
    }

    /**
     * Extrait la clé API depuis l'en-tête Authorization
     */
    public static function extractFromHeader() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        // Format: Bearer fsco_xxxxx ou API-Key fsco_xxxxx
        if (preg_match('/(?:Bearer|API-Key)\s+(fsco_\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // Vérifier aussi dans X-API-Key
        $apiKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? '';
        if (strpos($apiKey, 'fsco_') === 0) {
            return $apiKey;
        }
        
        return null;
    }

    /**
     * Nettoie les clés expirées (supprime physiquement)
     */
    public static function cleanExpired() {
        $db = Database::getInstance();
        return $db->delete("DELETE FROM api_keys WHERE expires_at < NOW()");
    }
}
