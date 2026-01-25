<?php
/**
 * Fonctions de sécurité supplémentaires
 */

// Générer un token CSRF unique pour les formulaires
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifier un token CSRF
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Régénérer le token CSRF après utilisation
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Nettoyer et valider les entrées utilisateur
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return trim(strip_tags($data));
}

// Valider une adresse email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Générer un mot de passe sécurisé
function generate_secure_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $password;
}

// Hacher un mot de passe
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Vérifier un mot de passe
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Générer un token de réinitialisation de mot de passe
function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

// Encoder pour l'URL de manière sécurisée
function secure_url_encode($string) {
    return urlencode($string);
}

// Décoder depuis l'URL
function secure_url_decode($string) {
    return urldecode($string);
}

// Échapper les caractères spéciaux pour JavaScript
function escape_js($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

// Générer un identifiant unique sécurisé
function generate_secure_id($prefix = '') {
    $timestamp = microtime(true) * 10000;
    $random = bin2hex(random_bytes(8));
    return $prefix . $timestamp . '_' . $random;
}

// Vérifier si l'utilisateur a accès à une ressource
function check_resource_access($resource_owner_id, $current_user_id, $required_role = null) {
    // L'utilisateur peut accéder à ses propres ressources
    if ($resource_owner_id == $current_user_id) {
        return true;
    }

    // Vérifier le rôle si requis
    if ($required_role && (!isLoggedIn() || getCurrentUser()['role'] !== $required_role)) {
        return false;
    }

    return false;
}

// Journaliser les tentatives de sécurité
function log_security_event($event, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => isLoggedIn() ? getCurrentUser()['id'] : null,
        'details' => $details
    ];

    $log_file = ROOT_PATH . 'logs/security.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_line = json_encode($log_entry) . PHP_EOL;
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

// Détecter les tentatives de brute force
class BruteForceProtection {
    private static $attempts = [];
    private static $lockouts = [];

    public static function check_attempt($identifier, $max_attempts = MAX_LOGIN_ATTEMPTS, $lockout_time = LOGIN_LOCKOUT_TIME) {
        $now = time();

        // Nettoyer les anciennes tentatives
        self::cleanup_old_attempts($identifier, $lockout_time);

        // Vérifier si l'identifiant est verrouillé
        if (isset(self::$lockouts[$identifier]) && self::$lockouts[$identifier] > $now) {
            return [
                'allowed' => false,
                'remaining_time' => self::$lockouts[$identifier] - $now,
                'message' => 'Trop de tentatives. Réessayez dans ' . ceil((self::$lockouts[$identifier] - $now) / 60) . ' minutes.'
            ];
        }

        // Compter les tentatives récentes
        $recent_attempts = isset(self::$attempts[$identifier]) ? count(self::$attempts[$identifier]) : 0;

        if ($recent_attempts >= $max_attempts) {
            self::$lockouts[$identifier] = $now + $lockout_time;
            log_security_event('brute_force_lockout', ['identifier' => $identifier]);
            return [
                'allowed' => false,
                'remaining_time' => $lockout_time,
                'message' => 'Trop de tentatives. Compte temporairement verrouillé.'
            ];
        }

        return ['allowed' => true];
    }

    public static function record_attempt($identifier) {
        $now = time();

        if (!isset(self::$attempts[$identifier])) {
            self::$attempts[$identifier] = [];
        }

        self::$attempts[$identifier][] = $now;
    }

    public static function clear_attempts($identifier) {
        unset(self::$attempts[$identifier]);
        unset(self::$lockouts[$identifier]);
    }

    private static function cleanup_old_attempts($identifier, $window_time) {
        $now = time();
        $cutoff = $now - $window_time;

        if (isset(self::$attempts[$identifier])) {
            self::$attempts[$identifier] = array_filter(self::$attempts[$identifier], function($time) use ($cutoff) {
                return $time > $cutoff;
            });
        }

        if (isset(self::$lockouts[$identifier]) && self::$lockouts[$identifier] < $now) {
            unset(self::$lockouts[$identifier]);
        }
    }
}

// Classe pour la validation des données
class DataValidator {
    private $errors = [];

    public function validate_required($value, $field_name) {
        if (empty(trim($value))) {
            $this->errors[] = "Le champ {$field_name} est requis";
            return false;
        }
        return true;
    }

    public function validate_email($email, $field_name = 'email') {
        if (!is_valid_email($email)) {
            $this->errors[] = "Le format de l'{$field_name} est invalide";
            return false;
        }
        return true;
    }

    public function validate_length($value, $field_name, $min = null, $max = null) {
        $length = strlen(trim($value));

        if ($min !== null && $length < $min) {
            $this->errors[] = "Le champ {$field_name} doit contenir au moins {$min} caractères";
            return false;
        }

        if ($max !== null && $length > $max) {
            $this->errors[] = "Le champ {$field_name} doit contenir au plus {$max} caractères";
            return false;
        }

        return true;
    }

    public function validate_numeric($value, $field_name, $min = null, $max = null) {
        if (!is_numeric($value)) {
            $this->errors[] = "Le champ {$field_name} doit être un nombre";
            return false;
        }

        $num = (float) $value;

        if ($min !== null && $num < $min) {
            $this->errors[] = "Le champ {$field_name} doit être supérieur ou égal à {$min}";
            return false;
        }

        if ($max !== null && $num > $max) {
            $this->errors[] = "Le champ {$field_name} doit être inférieur ou égal à {$max}";
            return false;
        }

        return true;
    }

    public function validate_password_strength($password, $field_name = 'mot de passe') {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $this->errors[] = "Le {$field_name} doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères";
            return false;
        }

        // Vérifier la complexité
        $has_lower = preg_match('/[a-z]/', $password);
        $has_upper = preg_match('/[A-Z]/', $password);
        $has_digit = preg_match('/[0-9]/', $password);
        $has_special = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password);

        if (!$has_lower || !$has_upper || !$has_digit) {
            $this->errors[] = "Le {$field_name} doit contenir au moins une minuscule, une majuscule et un chiffre";
            return false;
        }

        return true;
    }

    public function validate_unique_email($email, $exclude_id = null) {
        $existing = DBHelper::emailExists($email, $exclude_id);
        if ($existing) {
            $this->errors[] = "Cette adresse email est déjà utilisée";
            return false;
        }
        return true;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function has_errors() {
        return !empty($this->errors);
    }

    public function get_error_string() {
        return implode('<br>', $this->errors);
    }

    public function reset() {
        $this->errors = [];
    }
}

// Fonctions de rate limiting
class RateLimiter {
    private static $attempts = [];

    public static function check_rate_limit($identifier, $max_attempts = 10, $time_window = 60) {
        $now = time();

        if (!isset(self::$attempts[$identifier])) {
            self::$attempts[$identifier] = [];
        }

        // Nettoyer les anciennes tentatives
        self::$attempts[$identifier] = array_filter(self::$attempts[$identifier], function($time) use ($now, $time_window) {
            return ($now - $time) < $time_window;
        });

        // Vérifier la limite
        if (count(self::$attempts[$identifier]) >= $max_attempts) {
            return false;
        }

        // Enregistrer la tentative
        self::$attempts[$identifier][] = $now;
        return true;
    }

    public static function clear_rate_limit($identifier) {
        unset(self::$attempts[$identifier]);
    }
}

// Fonction pour vérifier les permissions d'accès aux fichiers
function check_file_access($file_path, $user_id = null) {
    // Vérifier que le fichier existe
    $full_path = ROOT_PATH . $file_path;
    if (!file_exists($full_path)) {
        return false;
    }

    // Pour les fichiers d'examen, vérifier que l'utilisateur y a accès
    if (strpos($file_path, 'uploads/') === 0) {
        // Cette fonction pourrait être étendue pour vérifier les permissions spécifiques
        // Pour l'instant, on autorise l'accès si le fichier existe
        return true;
    }

    return false;
}

// Nettoyer les données de session suspectes
function sanitize_session_data() {
    $suspicious_keys = ['admin', 'root', 'system', 'config', 'password', 'token'];

    foreach ($_SESSION as $key => $value) {
        foreach ($suspicious_keys as $suspicious) {
            if (stripos($key, $suspicious) !== false) {
                log_security_event('suspicious_session_key', ['key' => $key]);
                unset($_SESSION[$key]);
                break;
            }
        }
    }
}

// Initialiser les mesures de sécurité
function init_security() {
    // Régénérer périodiquement l'ID de session
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // Nettoyer les données de session
    sanitize_session_data();

    // Définir les headers de sécurité
    if (!headers_sent()) {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self'");
    }
}
?>