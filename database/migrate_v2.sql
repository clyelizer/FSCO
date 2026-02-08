-- FSCO Database Extension Strategy
-- Auteur: Agent IA (Architecte DB Senior)
-- Date: 2026-01-31

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- 1. Gestion des Clés API (Migration du JSON vers SQL)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(128) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` text NOT NULL, -- Stockage JSON (ex: ["read", "write"])
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. Gestion de l'Agent IA
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code_name` varchar(50) NOT NULL,
  `status` enum('active','inactive','testing') DEFAULT 'active',
  `config_json` text DEFAULT NULL, -- Modèle, prompt système, etc.
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_name` (`code_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL, -- Peut être lié à un utilisateur ou être NULL (agent autonome)
  `source` enum('web','api','whatsapp','mobile') DEFAULT 'api',
  `request_text` text NOT NULL,
  `response_text` text DEFAULT NULL,
  `metadata_json` text DEFAULT NULL, -- Tokens, latence, refs documents
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ai_agent` FOREIGN KEY (`agent_id`) REFERENCES `ai_agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3. Système de Logs Unifié (Audit)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `api_key_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL, -- ex: 'CREATE_BLOG', 'UPDATE_SETTING'
  `entity_type` varchar(50) NOT NULL, -- ex: 'blog', 'formation', 'config'
  `entity_id` varchar(100) DEFAULT NULL,
  `old_values` text DEFAULT NULL, -- JSON
  `new_values` text DEFAULT NULL, -- JSON
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `action` (`action`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_apikey` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4. Intégration WhatsApp
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wa_id` varchar(20) NOT NULL, -- Numéro de téléphone au format international
  `user_id` int(11) DEFAULT NULL, -- Lien optionnel vers un compte utilisateur
  `status` enum('open','closed','archived') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_message_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wa_id` (`wa_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_wa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `direction` enum('in','out') NOT NULL, -- in = reçu, out = envoyé
  `message_type` enum('text','image','document','location','interactive') DEFAULT 'text',
  `body` text DEFAULT NULL,
  `media_url` varchar(500) DEFAULT NULL,
  `wa_message_id` varchar(150) DEFAULT NULL, -- ID interne WhatsApp/Meta
  `status` enum('sent','delivered','read','failed') DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  KEY `wa_message_id` (`wa_message_id`),
  CONSTRAINT `fk_msg_chat` FOREIGN KEY (`chat_id`) REFERENCES `whatsapp_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
