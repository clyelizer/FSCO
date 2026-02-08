<?php
/**
 * Classe de connexion et gestion de la base de données
 */

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
            }

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the error
            error_log("Database connection error: " . $e->getMessage());

            // Throw a generic exception to avoid exposing sensitive info if not in debug mode
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            } else {
                throw new Exception(defined('ERROR_MESSAGES') && isset(ERROR_MESSAGES['db_connection']) ? ERROR_MESSAGES['db_connection'] : "Erreur de connexion à la base de données");
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Méthode pour exécuter des requêtes préparées
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception("Erreur SQL : " . $e->getMessage());
            } else {
                throw new Exception("Erreur de base de données");
            }
        }
    }

    // Récupérer une seule ligne
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Récupérer plusieurs lignes
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Insérer et récupérer l'ID
    public function insert($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    // Mettre à jour
    public function update($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Supprimer
    public function delete($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Démarrer une transaction
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    // Valider une transaction
    public function commit()
    {
        return $this->pdo->commit();
    }

    // Annuler une transaction
    public function rollback()
    {
        return $this->pdo->rollback();
    }
}

// Fonction globale pour accéder à la base de données
function db()
{
    return Database::getInstance()->getConnection();
}

// Fonctions utilitaires pour les opérations courantes
class DBHelper
{
    // Vérifier si un email existe
    public static function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::getInstance()->fetchOne($sql, $params);
        return $result !== false;
    }

    // Récupérer un utilisateur par email
    public static function getUserByEmail($email)
    {
        return Database::getInstance()->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    // Récupérer un utilisateur par ID
    public static function getUserById($id)
    {
        return Database::getInstance()->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }

    // Mettre à jour la dernière connexion
    public static function updateLastLogin($userId)
    {
        Database::getInstance()->update(
            "UPDATE users SET derniere_connexion = NOW() WHERE id = ?",
            [$userId]
        );
    }

    // Compter les questions d'un professeur
    public static function countQuestionsByUser($userId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total FROM exam_questions WHERE created_by = ?",
            [$userId]
        );
        return $result['total'];
    }

    // Récupérer les questions publiées
    public static function getPublishedQuestions($limit = null, $offset = 0)
    {
        $sql = "SELECT q.*, c.nom as categorie_nom, u.nom as createur_nom
                FROM exam_questions q
                LEFT JOIN exam_categories c ON q.categorie_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE q.published = 1
                ORDER BY q.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::getInstance()->fetchAll($sql, [$limit, $offset]);
        }

        return Database::getInstance()->fetchAll($sql);
    }

    // Récupérer les catégories
    public static function getCategories()
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM exam_categories ORDER BY ordre ASC, nom ASC"
        );
    }

    // Logger une activité
    public static function logActivity($userId, $action, $description = '', $ipAddress = null, $userAgent = null)
    {
        if (!$ipAddress) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        if (!$userAgent) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }

        Database::getInstance()->insert(
            "INSERT INTO exam_logs_activite (user_id, action, description, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)",
            [$userId, $action, $description, $ipAddress, $userAgent]
        );
    }

    // Récupérer les examens publiés et disponibles
    public static function getPublishedExams()
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM exam_examens 
                WHERE statut = 'published' 
                AND date_debut <= ? 
                AND (date_fin IS NULL OR date_fin >= ?)
                ORDER BY created_at DESC";

        return Database::getInstance()->fetchAll($sql, [$now, $now]);
    }
}
?>