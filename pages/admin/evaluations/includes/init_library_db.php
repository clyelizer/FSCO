<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/database.php';

try {
    $pdo = Database::getInstance();

    $sql = "CREATE TABLE IF NOT EXISTS user_library (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        resource_id VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'ressource',
        status ENUM('favoris', 'en_cours', 'termine') DEFAULT 'favoris',
        is_favorite TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_resource (user_id, resource_id, type),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->getConnection()->exec($sql);
    echo "Table 'user_library' created or already exists successfully.\n";

} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>