-- FSCO Database Extension - Request Management
-- Migration de requests.json vers SQL
-- Date: 2026-01-31

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `ai_requests` (
  `id` varchar(20) NOT NULL, -- Format REQ-YYYY-NNN
  `type` varchar(50) NOT NULL, -- ex: 'create_blog', 'update_settings'
  `status` enum('pending_confirmation','approved','applied','rejected') DEFAULT 'pending_confirmation',
  `created_by` varchar(100) NOT NULL, -- WhatsApp ID ou User Email
  `payload_json` text NOT NULL, -- Données de la requête
  `applied_result` text DEFAULT NULL, -- Log de l'application
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `applied_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
