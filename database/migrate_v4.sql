-- ============================================================
-- FSCO Database Migration V4 - Full SQL Architecture
-- Author: Database Architect Senior
-- Date: 2026-02-07
-- Description: Migration complète JSON -> SQL
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- ============================================================
-- TABLE: instructors
-- Description: Formateurs et instructeurs des formations
-- ============================================================
DROP TABLE IF EXISTS `instructors`;
CREATE TABLE `instructors` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(150) NOT NULL,
    `specialite` VARCHAR(200) NOT NULL,
    `bio` TEXT NULL,
    `avatar` VARCHAR(500) NULL,
    `rating` DECIMAL(2,1) UNSIGNED DEFAULT 0.0 CHECK (`rating` >= 0 AND `rating` <= 5),
    `students_count` INT UNSIGNED DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_specialite` (`specialite`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: blogs
-- Description: Articles de blog
-- ============================================================
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(300) NOT NULL,
    `extrait` VARCHAR(500) NULL,
    `contenu` LONGTEXT NOT NULL,
    `auteur` VARCHAR(100) NOT NULL DEFAULT 'Admin',
    `categorie` VARCHAR(100) NOT NULL,
    `tags` JSON NULL COMMENT 'Array de tags: ["tag1", "tag2"]',
    `image` VARCHAR(500) NULL,
    `statut` ENUM('brouillon', 'publié', 'archivé') NOT NULL DEFAULT 'brouillon',
    `views_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_categorie` (`categorie`),
    INDEX `idx_statut` (`statut`),
    INDEX `idx_created_at` (`created_at` DESC),
    FULLTEXT INDEX `idx_fulltext_search` (`titre`, `contenu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: formations
-- Description: Formations et cours proposés
-- ============================================================
DROP TABLE IF EXISTS `formations`;
CREATE TABLE `formations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(300) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `description_courte` VARCHAR(500) NULL,
    `categorie` VARCHAR(100) NOT NULL,
    `niveau` ENUM('Débutant', 'Intermédiaire', 'Avancé', 'Expert') NOT NULL DEFAULT 'Débutant',
    `duree` VARCHAR(100) NOT NULL COMMENT 'Ex: "20 heures", "3 mois"',
    `prix` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
    `devise` CHAR(3) NOT NULL DEFAULT 'MAD',
    `instructeur_id` INT UNSIGNED NULL,
    `image` VARCHAR(500) NULL,
    `prerequis` JSON NULL COMMENT 'Array de prérequis',
    `objectifs` JSON NULL COMMENT 'Array des objectifs pédagogiques',
    `curriculum` JSON NULL COMMENT 'Structure du programme',
    `statut` ENUM('brouillon', 'publié', 'archivé') NOT NULL DEFAULT 'brouillon',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `enrolled_count` INT UNSIGNED DEFAULT 0,
    `rating` DECIMAL(2,1) UNSIGNED DEFAULT 0.0 CHECK (`rating` >= 0 AND `rating` <= 5),
    `reviews_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_categorie` (`categorie`),
    INDEX `idx_niveau` (`niveau`),
    INDEX `idx_statut` (`statut`),
    INDEX `idx_instructeur` (`instructeur_id`),
    INDEX `idx_prix` (`prix`),
    INDEX `idx_is_featured` (`is_featured`),
    CONSTRAINT `fk_formations_instructeur` FOREIGN KEY (`instructeur_id`) 
        REFERENCES `instructors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ressources
-- Description: Ressources téléchargeables (PDF, vidéos, etc.)
-- ============================================================
DROP TABLE IF EXISTS `ressources`;
CREATE TABLE `ressources` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(300) NOT NULL,
    `description` TEXT NULL,
    `format` ENUM('PDF', 'Video', 'Audio', 'Document', 'Archive', 'Autre') NOT NULL DEFAULT 'PDF',
    `taille` VARCHAR(50) NULL COMMENT 'Taille du fichier, ex: "2.5 MB"',
    `categorie` VARCHAR(100) NOT NULL,
    `niveau` ENUM('Débutant', 'Intermédiaire', 'Avancé', 'Expert') NOT NULL DEFAULT 'Débutant',
    `image` VARCHAR(500) NULL,
    `fichier` VARCHAR(500) NULL COMMENT 'Chemin vers le fichier local',
    `url_externe` VARCHAR(500) NULL COMMENT 'URL externe si applicable',
    `statut` ENUM('brouillon', 'publié', 'archivé') NOT NULL DEFAULT 'brouillon',
    `downloads_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_categorie` (`categorie`),
    INDEX `idx_format` (`format`),
    INDEX `idx_niveau` (`niveau`),
    INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: faq
-- Description: Questions fréquemment posées
-- ============================================================
DROP TABLE IF EXISTS `faq`;
CREATE TABLE `faq` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` VARCHAR(500) NOT NULL,
    `reponse` TEXT NOT NULL,
    `categorie` VARCHAR(100) NULL DEFAULT 'general',
    `ordre` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordre d affichage',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_categorie` (`categorie`),
    INDEX `idx_ordre` (`ordre`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: testimonials
-- Description: Témoignages et avis clients
-- ============================================================
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(150) NOT NULL,
    `role` VARCHAR(200) NULL COMMENT 'Poste et entreprise',
    `avatar` VARCHAR(500) NULL,
    `rating` DECIMAL(2,1) UNSIGNED NOT NULL DEFAULT 5.0 CHECK (`rating` >= 0 AND `rating` <= 5),
    `texte` TEXT NOT NULL,
    `contexte` VARCHAR(200) NULL COMMENT 'Formation ou service concerné',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_is_featured` (`is_featured`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: site_config
-- Description: Configuration du site (key/value)
-- ============================================================
DROP TABLE IF EXISTS `site_config`;
CREATE TABLE `site_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `config_key` VARCHAR(100) NOT NULL,
    `config_value` LONGTEXT NULL,
    `config_type` ENUM('string', 'json', 'boolean', 'number', 'html') NOT NULL DEFAULT 'string',
    `category` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(300) NULL COMMENT 'Description pour l admin',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_config_key` (`config_key`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: api_keys
-- Description: Clés d'accès pour les agents IA
-- ============================================================
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `api_key` VARCHAR(100) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `permissions` JSON NOT NULL COMMENT 'Array: ["read", "write", "admin"]',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `usage_count` INT UNSIGNED DEFAULT 0,
    `last_used` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_api_key` (`api_key`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ai_requests
-- Description: Demandes de modification du site générées par l'IA
-- ============================================================
DROP TABLE IF EXISTS `ai_requests`;
CREATE TABLE `ai_requests` (
    `id` VARCHAR(50) NOT NULL,
    `type` VARCHAR(100) NOT NULL COMMENT 'Ex: create_blog, update_settings',
    `status` ENUM('pending_confirmation', 'approved', 'rejected', 'applied', 'failed') NOT NULL DEFAULT 'pending_confirmation',
    `created_by` VARCHAR(100) NOT NULL COMMENT 'Ex: num wa ou nom agent',
    `payload_json` LONGTEXT NOT NULL COMMENT 'Données de la modification',
    `applied_at` TIMESTAMP NULL,
    `applied_result` JSON NULL COMMENT 'Log du résultat de l''application',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ai_interactions
-- Description: Historique des conversations avec l'IA
-- ============================================================
DROP TABLE IF EXISTS `ai_interactions`;
CREATE TABLE `ai_interactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(100) NOT NULL,
    `role` ENUM('user', 'assistant', 'system') NOT NULL,
    `content` LONGTEXT NOT NULL,
    `tokens_count` INT UNSIGNED DEFAULT 0,
    `meta_data` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_session` (`session_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- Description: Journal d'audit détaillé des actions
-- ============================================================
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `api_key_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NULL,
    `entity_id` VARCHAR(100) NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_api_key` (`api_key_id`),
    INDEX `idx_action` (`action`),
    CONSTRAINT `fk_audit_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: whatsapp_chats
-- Description: Sessions de discussion WhatsApp
-- ============================================================
DROP TABLE IF EXISTS `whatsapp_chats`;
CREATE TABLE `whatsapp_chats` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `wa_id` VARCHAR(50) NOT NULL COMMENT 'Format: num_tel@s.whatsapp.net',
    `status` ENUM('open', 'closed', 'paused') NOT NULL DEFAULT 'open',
    `last_message_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wa_id` (`wa_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: whatsapp_messages
-- Description: Messages individuels WhatsApp
-- ============================================================
DROP TABLE IF EXISTS `whatsapp_messages`;
CREATE TABLE `whatsapp_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `chat_id` INT UNSIGNED NOT NULL,
    `direction` ENUM('in', 'out') NOT NULL,
    `message_type` VARCHAR(20) NOT NULL DEFAULT 'text',
    `body` LONGTEXT NULL,
    `media_url` VARCHAR(500) NULL,
    `wa_message_id` VARCHAR(100) NULL,
    `status` ENUM('pending', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_chat` (`chat_id`),
    INDEX `idx_wa_message_id` (`wa_message_id`),
    CONSTRAINT `fk_wa_msg_chat` FOREIGN KEY (`chat_id`) REFERENCES `whatsapp_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insert default site_config values (from site_config.json)
-- ============================================================
INSERT INTO `site_config` (`config_key`, `config_value`, `config_type`, `category`, `description`) VALUES
-- General
('site_title', 'FSCo - Formation Suivi Conseil', 'string', 'general', 'Titre du site'),
('contact_email', 'clyelise1@gmail.com', 'string', 'general', 'Email de contact'),
('contact_phone', '+212 698771629', 'string', 'general', 'Téléphone de contact'),
('contact_address', 'Casablanca, Maroc', 'string', 'general', 'Adresse physique'),

-- Hero Section
('hero_title', 'FSCo - Formation Suivi Conseil', 'string', 'hero', 'Titre du hero'),
('hero_subtitle', '<p><strong>Votre partenaire pour la sécurisation numérique, l''optimisation avec l''IA Et la formation en ligne</strong></p>', 'html', 'hero', 'Sous-titre du hero'),
('hero_cta_primary', 'Découvrir nos services', 'string', 'hero', 'Texte du bouton principal'),
('hero_cta_secondary', 'Nous contacter', 'string', 'hero', 'Texte du bouton secondaire'),
('hero_background_image', 'images/hero-bg.jpg', 'string', 'hero', 'Image de fond'),

-- Theme
('theme_primary_color', '#2563eb', 'string', 'theme', 'Couleur principale'),
('theme_secondary_color', '#1e293b', 'string', 'theme', 'Couleur secondaire'),
('theme_font_family', 'Inter', 'string', 'theme', 'Police de caractères'),
('theme_font_size_base', '16px', 'string', 'theme', 'Taille de police de base'),

-- SEO
('meta_title', 'FSCo - Formation Suivi Conseil', 'string', 'seo', 'Meta title'),
('meta_description', 'Votre partenaire expert en formation, suivi et conseil numérique.', 'string', 'seo', 'Meta description'),
('og_image', 'images/hero-bg.jpg', 'string', 'seo', 'Image OpenGraph'),

-- Sondage
('sondage_enabled', 'false', 'boolean', 'sondage', 'Sondage activé'),
('sondage_title', '🗳️ Sondage en Cours', 'string', 'sondage', 'Titre du sondage'),
('sondage_subtitle', 'Faites Évoluer Nos Services', 'string', 'sondage', 'Sous-titre'),
('sondage_description', '<p>Aidez-nous à adapter nos objectifs pour mieux répondre aux besoins réels des particuliers et entreprises.</p>', 'html', 'sondage', 'Description'),

-- Pages config
('page_formations_title', 'Nos Formations', 'string', 'pages', 'Titre page formations'),
('page_formations_intro', 'Découvrez nos formations conçues pour vous faire progresser dans votre carrière.', 'string', 'pages', 'Intro page formations'),
('page_ressources_title', 'Nos differentes Ressources', 'string', 'pages', 'Titre page ressources'),
('page_ressources_intro', 'Accédez à notre bibliothèque de documents, guides et outils.', 'string', 'pages', 'Intro page ressources'),
('page_blogs_title', 'Blog & Actualités', 'string', 'pages', 'Titre page blog'),
('page_blogs_intro', 'Restez informé des dernières tendances et actualités.', 'string', 'pages', 'Intro page blog'),

-- Services (stored as JSON array)
('services', '[{"title":"Sécurisation Numérique","description":"Protection de vos systèmes informatiques","image":"images/security.jpg"},{"title":"Outils PUISSANTS et automatiques","description":"Outils automatiques de dernières générations","image":"images/ai.jpg"},{"title":"Vulgarisation des Technologies","description":"Technologies accessibles à tous","image":"images/education.jpg"},{"title":"Formations en Ligne","description":"Apprentissage 100% en ligne","image":"images/education.jpg"},{"title":"Accompagnement","description":"Suivis personnalisés adaptés à vos besoins","image":"images/consulting.jpg"}]', 'json', 'services', 'Liste des services');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION QUERIES (à exécuter après migration)
-- ============================================================
-- SELECT 'Tables créées:' as info;
-- SHOW TABLES;
-- SELECT 'Config site:' as info;
-- SELECT config_key, LEFT(config_value, 50) as value_preview, category FROM site_config;
