<?php
/**
 * Système d'authentification et gestion des sessions (Intégration FSCo)
 */

class Auth
{
    private static $instance = null;
    private $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
        // La session est déjà démarrée par FSCo ou config.php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Vérifier si l'utilisateur est connecté (FSCo session)
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // Récupérer l'utilisateur actuel
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        // On utilise les données de session FSCo si disponibles, sinon on fetch
        if (isset($_SESSION['user_name']) && isset($_SESSION['user_role'])) {
            return [
                'id' => $_SESSION['user_id'],
                'nom' => $_SESSION['user_name'],
                'role' => $_SESSION['user_role'],
                'email' => $_SESSION['user_email'] ?? '', // FSCo stocke peut-être l'email en session ?
                'statut' => 'active' // Supposé actif si connecté
            ];
        }

        // Fallback: récupérer depuis la DB
        return DBHelper::getUserById($_SESSION['user_id']);
    }

    // Vérifier le rôle de l'utilisateur
    public function hasRole($role)
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    // Vérifier si l'utilisateur est professeur
    public function isProf()
    {
        return $this->hasRole('prof') || $this->hasRole('admin'); // Admin a aussi accès prof
    }

    // Vérifier si l'utilisateur est étudiant
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    // Vérifier si l'utilisateur est admin
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    // Générer un token CSRF
    public function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
        }
        return $_SESSION['csrf_token'];
    }

    // Vérifier un token CSRF
    public function verifyCSRFToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // Vérifier l'accès à une ressource
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            // Rediriger vers le login FSCo
            header('Location: /pages/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    // Vérifier l'accès professeur
    public function requireProf()
    {
        $this->requireLogin();
        if (!$this->isProf()) {
            header('Location: ' . APP_URL . '/index.php?error=access_denied');
            exit;
        }
    }

    // Vérifier l'accès étudiant
    public function requireStudent()
    {
        $this->requireLogin();
        if (!$this->isStudent()) {
            header('Location: ' . APP_URL . '/index.php?error=access_denied');
            exit;
        }
    }
}

// Fonctions globales pour faciliter l'utilisation
function auth()
{
    return Auth::getInstance();
}

function isLoggedIn()
{
    return auth()->isLoggedIn();
}

function getCurrentUser()
{
    return auth()->getCurrentUser();
}

function isProf()
{
    return auth()->isProf();
}

function isStudent()
{
    return auth()->isStudent();
}

function isAdmin()
{
    return auth()->isAdmin();
}

function requireLogin()
{
    auth()->requireLogin();
}

function requireProf()
{
    auth()->requireProf();
}

function requireStudent()
{
    auth()->requireStudent();
}

function csrf_token()
{
    return auth()->generateCSRFToken();
}

function verify_csrf($token)
{
    return auth()->verifyCSRFToken($token);
}
?>