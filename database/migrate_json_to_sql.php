<?php
/**
 * ============================================================
 * FSCO Migration Script - JSON to SQL
 * ============================================================
 * Author: Database Architect Senior
 * Date: 2026-02-07
 * 
 * USAGE: php migrate_json_to_sql.php
 * 
 * This script:
 * 1. Reads all JSON data files
 * 2. Validates and transforms data
 * 3. Inserts into SQL tables with transaction safety
 * 4. Generates detailed logs
 * ============================================================
 */

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Africa/Casablanca');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../pages/admin/evaluations/includes/database.php';

// Paths to JSON files
define('DATA_PATH', __DIR__ . '/../pages/admin/data/');
define('LOG_FILE', __DIR__ . '/migration_' . date('Y-m-d_His') . '.log');

// ============================================================
// LOGGING FUNCTIONS
// ============================================================

function logMessage($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] [$type] $message\n";
    echo $logLine;
    file_put_contents(LOG_FILE, $logLine, FILE_APPEND);
}

function logError($message) {
    logMessage($message, 'ERROR');
}

function logSuccess($message) {
    logMessage($message, 'SUCCESS');
}

function logWarning($message) {
    logMessage($message, 'WARNING');
}

// ============================================================
// JSON READING FUNCTIONS
// ============================================================

function readJsonFile($filename) {
    $path = DATA_PATH . $filename;
    if (!file_exists($path)) {
        logWarning("File not found: $path");
        return [];
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("JSON parse error in $filename: " . json_last_error_msg());
        return false;
    }
    
    logMessage("Read $filename: " . count($data) . " entries");
    return $data;
}

// ============================================================
// DATA TRANSFORMATION FUNCTIONS
// ============================================================

function parseDateTime($dateStr) {
    if (empty($dateStr)) return date('Y-m-d H:i:s');
    
    // Try different formats
    $formats = ['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $dateStr);
        if ($dt !== false) {
            return $dt->format('Y-m-d H:i:s');
        }
    }
    
    // Fallback: try strtotime
    $ts = strtotime($dateStr);
    if ($ts !== false) {
        return date('Y-m-d H:i:s', $ts);
    }
    
    logWarning("Could not parse date: $dateStr, using current time");
    return date('Y-m-d H:i:s');
}

function normalizeStatus($status) {
    $status = strtolower(trim($status ?? ''));
    $map = [
        'publié' => 'publié',
        'publie' => 'publié',
        'published' => 'publié',
        'brouillon' => 'brouillon',
        'draft' => 'brouillon',
        'archivé' => 'archivé',
        'archived' => 'archivé',
    ];
    return $map[$status] ?? 'brouillon';
}

function normalizeNiveau($niveau) {
    $niveau = strtolower(trim($niveau ?? ''));
    $map = [
        'débutant' => 'Débutant',
        'debutant' => 'Débutant',
        'beginner' => 'Débutant',
        'intermédiaire' => 'Intermédiaire',
        'intermediaire' => 'Intermédiaire',
        'intermediate' => 'Intermédiaire',
        'avancé' => 'Avancé',
        'avance' => 'Avancé',
        'advanced' => 'Avancé',
        'expert' => 'Expert',
    ];
    return $map[$niveau] ?? 'Débutant';
}

function cleanHtmlEntities($text) {
    if (empty($text)) return $text;
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $text;
}

// ============================================================
// MIGRATION FUNCTIONS
// ============================================================

function migrateInstructors($db) {
    logMessage("=== Migrating INSTRUCTORS ===");
    
    $data = readJsonFile('instructors.json');
    if ($data === false) return false;
    if (empty($data)) {
        logWarning("No instructors to migrate");
        return true;
    }
    
    $count = 0;
    foreach ($data as $item) {
        // Parse students count (remove '+' sign)
        $studentsCount = intval(str_replace('+', '', $item['students'] ?? '0'));
        
        $sql = "INSERT INTO instructors (nom, specialite, bio, avatar, rating, students_count, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
        
        try {
            $db->insert($sql, [
                $item['name'] ?? 'Unknown',
                $item['specialty'] ?? '',
                $item['bio'] ?? null,
                $item['avatar'] ?? null,
                floatval($item['rating'] ?? 0),
                $studentsCount
            ]);
            $count++;
        } catch (Exception $e) {
            logError("Failed to insert instructor '{$item['name']}': " . $e->getMessage());
            return false;
        }
    }
    
    logSuccess("Migrated $count instructors");
    return true;
}

function migrateBlogs($db) {
    logMessage("=== Migrating BLOGS ===");
    
    $data = readJsonFile('blogs.json');
    if ($data === false) return false;
    if (empty($data)) {
        logWarning("No blogs to migrate");
        return true;
    }
    
    $count = 0;
    $skipped = 0;
    
    foreach ($data as $item) {
        // Skip test/garbage entries
        $titre = $item['titre'] ?? $item['title'] ?? '';
        if (strlen($titre) < 3) {
            logWarning("Skipping blog with short/empty title: '$titre'");
            $skipped++;
            continue;
        }
        
        $contenu = cleanHtmlEntities($item['contenu'] ?? $item['content'] ?? '');
        $extrait = cleanHtmlEntities($item['extrait'] ?? $item['excerpt'] ?? '');
        $categorie = $item['categorie'] ?? $item['category'] ?? 'Général';
        $tags = $item['tags'] ?? [];
        
        $sql = "INSERT INTO blogs (titre, extrait, contenu, auteur, categorie, tags, image, statut, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $db->insert($sql, [
                cleanHtmlEntities($titre),
                $extrait,
                $contenu,
                $item['auteur'] ?? $item['author'] ?? 'Admin',
                $categorie,
                json_encode($tags, JSON_UNESCAPED_UNICODE),
                $item['image'] ?? null,
                normalizeStatus($item['statut'] ?? $item['status'] ?? 'brouillon'),
                parseDateTime($item['created_at'] ?? null),
                parseDateTime($item['updated_at'] ?? null)
            ]);
            $count++;
        } catch (Exception $e) {
            logError("Failed to insert blog '$titre': " . $e->getMessage());
            return false;
        }
    }
    
    logSuccess("Migrated $count blogs" . ($skipped > 0 ? " (skipped $skipped invalid entries)" : ""));
    return true;
}

function migrateRessources($db) {
    logMessage("=== Migrating RESSOURCES ===");
    
    $data = readJsonFile('ressources.json');
    if ($data === false) return false;
    if (empty($data)) {
        logWarning("No ressources to migrate");
        return true;
    }
    
    $count = 0;
    
    foreach ($data as $item) {
        $format = strtoupper($item['format'] ?? 'PDF');
        // Normalize format to match ENUM
        $formatMap = ['PDF' => 'PDF', 'VIDEO' => 'Video', 'AUDIO' => 'Audio', 'DOC' => 'Document', 'DOCX' => 'Document', 'ZIP' => 'Archive', 'RAR' => 'Archive'];
        $format = $formatMap[$format] ?? 'Autre';
        
        $sql = "INSERT INTO ressources (titre, description, format, taille, categorie, niveau, image, fichier, url_externe, statut, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $db->insert($sql, [
                cleanHtmlEntities($item['titre'] ?? ''),
                cleanHtmlEntities($item['description'] ?? ''),
                $format,
                !empty($item['taille']) ? $item['taille'] : null,
                $item['categorie'] ?? 'Général',
                normalizeNiveau($item['niveau'] ?? 'Débutant'),
                $item['image'] ?? null,
                $item['fichier'] ?? null,
                $item['url_externe'] ?? null,
                normalizeStatus($item['statut'] ?? 'brouillon'),
                parseDateTime($item['created_at'] ?? null),
                parseDateTime($item['updated_at'] ?? null)
            ]);
            $count++;
        } catch (Exception $e) {
            logError("Failed to insert ressource '{$item['titre']}': " . $e->getMessage());
            return false;
        }
    }
    
    logSuccess("Migrated $count ressources");
    return true;
}

function migrateFaq($db) {
    logMessage("=== Migrating FAQ ===");
    
    $data = readJsonFile('faq.json');
    if ($data === false) return false;
    if (empty($data)) {
        logWarning("No FAQ to migrate");
        return true;
    }
    
    $count = 0;
    $ordre = 1;
    
    foreach ($data as $item) {
        $sql = "INSERT INTO faq (question, reponse, categorie, ordre, is_active, created_at) 
                VALUES (?, ?, 'general', ?, 1, NOW())";
        
        try {
            $db->insert($sql, [
                $item['question'] ?? '',
                $item['answer'] ?? $item['reponse'] ?? '',
                $ordre++
            ]);
            $count++;
        } catch (Exception $e) {
            logError("Failed to insert FAQ: " . $e->getMessage());
            return false;
        }
    }
    
    logSuccess("Migrated $count FAQ entries");
    return true;
}

function migrateTestimonials($db) {
    logMessage("=== Migrating TESTIMONIALS ===");
    
    $data = readJsonFile('testimonials.json');
    if ($data === false) return false;
    if (empty($data)) {
        logWarning("No testimonials to migrate");
        return true;
    }
    
    $count = 0;
    
    foreach ($data as $item) {
        $sql = "INSERT INTO testimonials (nom, role, avatar, rating, texte, contexte, is_featured, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 0, 1, NOW())";
        
        try {
            $db->insert($sql, [
                $item['name'] ?? '',
                $item['role'] ?? null,
                $item['avatar'] ?? null,
                floatval($item['rating'] ?? 5),
                $item['text'] ?? '',
                $item['context'] ?? null
            ]);
            $count++;
        } catch (Exception $e) {
            logError("Failed to insert testimonial: " . $e->getMessage());
            return false;
        }
    }
    
    logSuccess("Migrated $count testimonials");
    return true;
}

// ============================================================
// MAIN EXECUTION
// ============================================================

logMessage("============================================================");
logMessage("FSCO MIGRATION SCRIPT - JSON TO SQL");
logMessage("============================================================");
logMessage("Started at: " . date('Y-m-d H:i:s'));
logMessage("Log file: " . LOG_FILE);
logMessage("");

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Start transaction
    logMessage("Starting transaction...");
    $pdo->beginTransaction();
    
    // Execute migrations in order (respecting FK dependencies)
    $success = true;
    
    if ($success) $success = migrateInstructors($db);
    if ($success) $success = migrateBlogs($db);
    if ($success) $success = migrateRessources($db);
    if ($success) $success = migrateFaq($db);
    if ($success) $success = migrateTestimonials($db);
    
    if ($success) {
        $pdo->commit();
        logMessage("");
        logSuccess("============================================================");
        logSuccess("MIGRATION COMPLETED SUCCESSFULLY");
        logSuccess("============================================================");
    } else {
        $pdo->rollBack();
        logMessage("");
        logError("============================================================");
        logError("MIGRATION FAILED - ROLLED BACK");
        logError("============================================================");
        exit(1);
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError("Critical error: " . $e->getMessage());
    logError("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

// Summary
logMessage("");
logMessage("=== MIGRATION SUMMARY ===");
try {
    $db = Database::getInstance();
    $tables = ['instructors', 'blogs', 'ressources', 'faq', 'testimonials', 'site_config'];
    foreach ($tables as $table) {
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
        logMessage("  $table: " . $result['count'] . " rows");
    }
} catch (Exception $e) {
    logWarning("Could not generate summary: " . $e->getMessage());
}

logMessage("");
logMessage("Migration log saved to: " . LOG_FILE);
logMessage("Done.");
