<?php
/**
 * Fonctions utilitaires communes
 */

// Fonctions de sécurité
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateToken($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

// Fonctions de formatage
function formatDate($date, $format = 'd/m/Y H:i')
{
    if (empty($date))
        return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

function formatDateRelative($date)
{
    if (empty($date))
        return '';

    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return 'À l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Il y a {$minutes} minute" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Il y a {$hours} heure" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "Il y a {$days} jour" . ($days > 1 ? 's' : '');
    } else {
        return formatDate($date, 'd/m/Y');
    }
}

function formatTime($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

/**
 * Calcule le score d'un examen
 * @param int $sessionId
 * @return array
 */
function calculateExamScore($sessionId)
{
    $session = Database::getInstance()->fetchOne(
        "SELECT * FROM exam_sessions WHERE id = ?",
        [$sessionId]
    );

    if (!$session) {
        return ['score' => 0, 'total' => 0, 'percentage' => 0];
    }

    $reponses = json_decode($session['reponses'] ?? '{}', true);
    $questionsOrder = json_decode($session['questions_order'] ?? '[]', true);

    $score = 0;
    $totalPoints = 0;
    $details = [];

    if (!empty($questionsOrder)) {
        $placeholders = str_repeat('?,', count($questionsOrder) - 1) . '?';
        $questions = Database::getInstance()->fetchAll(
            "SELECT q.*, eq.points_question
             FROM exam_questions q
             JOIN exam_examen_questions eq ON q.id = eq.question_id
             WHERE q.id IN ($placeholders) AND eq.examen_id = ?",
            array_merge($questionsOrder, [$session['examen_id']])
        );

        foreach ($questions as $question) {
            $points = $question['points_question'] ?: $question['points'];
            $totalPoints += $points;
            $questionId = $question['id'];
            $userAnswer = $reponses[$questionId] ?? null;
            $isCorrect = false;
            $pointsAwarded = 0;

            if ($question['type'] === 'qcm') {
                if ($userAnswer === $question['reponse_qcm']) {
                    $isCorrect = true;
                    $pointsAwarded = $points;
                }
            } elseif ($question['type'] === 'vrai_faux') {
                if ($userAnswer === $question['reponse_vrai_faux']) {
                    $isCorrect = true;
                    $pointsAwarded = $points;
                }
            } elseif ($question['type'] === 'ouverte') {
                // Pour l'instant, 0 points en attendant la correction manuelle ou IA
                $pointsAwarded = 0;
                // TODO: Marquer comme "à corriger"
            }

            $score += $pointsAwarded;
            $details[$questionId] = [
                'user_answer' => $userAnswer,
                'correct' => $isCorrect,
                'points' => $pointsAwarded,
                'max_points' => $points
            ];
        }
    }

    $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

    return [
        'score' => $score,
        'total' => $totalPoints,
        'percentage' => $percentage,
        'details' => $details
    ];
}
function formatFileSize($bytes)
{
    if ($bytes == 0)
        return '0 B';

    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));

    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

// Fonctions pour les questions
function getQuestionTypeLabel($type)
{
    return QUESTION_TYPES[$type] ?? $type;
}

function getDifficultyLabel($difficulty)
{
    return DIFFICULTY_LEVELS[$difficulty] ?? $difficulty;
}

function getExamStatusLabel($status)
{
    return EXAM_STATUSES[$status] ?? $status;
}

function getSessionStatusLabel($status)
{
    return SESSION_STATUSES[$status] ?? $status;
}

// Fonctions de calcul
function calculateScore($totalQuestions, $correctAnswers)
{
    if ($totalQuestions == 0)
        return 0;
    return round(($correctAnswers / $totalQuestions) * 100, 2);
}

function formatScore($score)
{
    return number_format($score, 2) . '%';
}

// Fonctions d'upload
function createUploadDirectories()
{
    $dirs = [IMAGE_PATH, AUDIO_PATH];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function generateUniqueFilename($originalName, $prefix = '')
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $timestamp = time();
    $random = bin2hex(random_bytes(4));

    return $prefix . $timestamp . '_' . $random . '.' . $extension;
}

function validateFileUpload($file, $allowedTypes, $maxSize = MAX_FILE_SIZE)
{
    $errors = [];

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erreur lors de l\'upload du fichier';
        return $errors;
    }

    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        $errors[] = 'Le fichier est trop volumineux (max ' . formatFileSize($maxSize) . ')';
    }

    // Vérifier le type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        $errors[] = 'Type de fichier non autorisé. Types acceptés : ' . implode(', ', $allowedTypes);
    }

    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4'
    ];

    if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
        $errors[] = 'Type de fichier invalide';
    }

    return $errors;
}

function moveUploadedFile($file, $destination)
{
    $dir = dirname($destination);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        chmod($destination, 0644);
        return true;
    }

    return false;
}

// Fonctions de pagination
function calculatePagination($totalItems, $itemsPerPage, $currentPage)
{
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
}

function renderPagination($pagination, $baseUrl = '')
{
    if ($pagination['total_pages'] <= 1)
        return '';

    $html = '<div class="pagination">';

    if ($pagination['has_previous']) {
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['previous_page'] . '" class="page-link">&laquo; Précédent</a>';
    }

    // Pages autour de la page actuelle
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1" class="page-link">1</a>';
        if ($start > 2) {
            $html .= '<span class="page-dots">...</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $class = ($i == $pagination['current_page']) ? 'page-link active' : 'page-link';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="' . $class . '">' . $i . '</a>';
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<span class="page-dots">...</span>';
        }
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '" class="page-link">' . $pagination['total_pages'] . '</a>';
    }

    if ($pagination['has_next']) {
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['next_page'] . '" class="page-link">Suivant &raquo;</a>';
    }

    $html .= '</div>';

    return $html;
}

// Fonctions d'interface utilisateur
function showMessage($message = null, $type = 'info')
{
    $message = $message ?? $_GET['message'] ?? '';
    $type = $_GET['type'] ?? $type;

    if (empty($message))
        return '';

    $types = [
        'success' => 'alert-success',
        'error' => 'alert-error',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];

    $class = $types[$type] ?? 'alert-info';

    return '<div class="alert ' . $class . '">' . htmlspecialchars($message) . '</div>';
}

function renderStars($rating, $maxStars = 5)
{
    $html = '<div class="stars">';
    for ($i = 1; $i <= $maxStars; $i++) {
        $class = ($i <= $rating) ? 'star filled' : 'star';
        $html .= '<span class="' . $class . '">★</span>';
    }
    $html .= '</div>';
    return $html;
}

// Fonctions de débogage
function debug($data, $die = false)
{
    if (!DEBUG_MODE)
        return;

    echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ccc; margin: 10px;">';
    var_dump($data);
    echo '</pre>';

    if ($die)
        die;
}

function logError($message, $context = [])
{
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logMessage .= ' - Context: ' . json_encode($context);
    }

    error_log($logMessage . PHP_EOL, 3, ROOT_PATH . 'logs/error.log');
}

// Fonctions de redirection
function redirect($url, $message = '', $type = 'info')
{
    if ($message) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        $url .= $separator . 'message=' . urlencode($message) . '&type=' . urlencode($type);
    }

    header('Location: ' . $url);
    exit;
}

function redirectBack($message = '', $type = 'info')
{
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    redirect($referer, $message, $type);
}

// Fonctions de validation de formulaire
function validateRequired($value, $fieldName)
{
    if (empty(trim($value))) {
        return "Le champ {$fieldName} est requis";
    }
    return true;
}

function validateNumeric($value, $fieldName, $min = null, $max = null)
{
    if (!is_numeric($value)) {
        return "Le champ {$fieldName} doit être un nombre";
    }

    $num = (float) $value;

    if ($min !== null && $num < $min) {
        return "Le champ {$fieldName} doit être supérieur ou égal à {$min}";
    }

    if ($max !== null && $num > $max) {
        return "Le champ {$fieldName} doit être inférieur ou égal à {$max}";
    }

    return true;
}

function validateLength($value, $fieldName, $min = null, $max = null)
{
    $length = strlen(trim($value));

    if ($min !== null && $length < $min) {
        return "Le champ {$fieldName} doit contenir au moins {$min} caractères";
    }

    if ($max !== null && $length > $max) {
        return "Le champ {$fieldName} doit contenir au plus {$max} caractères";
    }

    return true;
}
?>