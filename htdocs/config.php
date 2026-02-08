<?php
/**
 * Database Configuration File (config.php)
 */

function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception('.env file not found. Please create it with your database credentials.');
    }

    $handle = fopen($path, 'r');
    if (!$handle) {
        throw new Exception('Cannot open .env file for reading.');
    }

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);

        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);

                // Remove surrounding quotes if present
                $value = trim($value, '"\'');

                // Set environment variable
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    fclose($handle);
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Database configuration constants
// CORRECTION: Utiliser $_ENV et $_SERVER au lieu de getenv()
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'fsco');
define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');

/**
 * Get PDO database connection
 */
function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_NAME
            );

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // Test the connection
            $pdo->query('SELECT 1');

        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage() . '. Please check your .env configuration.');
        }
    }

    return $pdo;
}

/**
 * Test database connection
 */
function testDBConnection()
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query('SELECT VERSION() as version');
        $result = $stmt->fetch();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>