<?php
/**
 * Classe utilitaire pour la gestion sécurisée des uploads de fichiers
 */

class FileUpload
{
    private $allowedTypes = [];
    private $maxSize = MAX_FILE_SIZE;
    private $uploadDir = '';
    private $errors = [];

    public function __construct($uploadDir = '', $allowedTypes = [], $maxSize = null)
    {
        $this->uploadDir = $uploadDir ?: UPLOAD_PATH;
        $this->allowedTypes = $allowedTypes ?: array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_AUDIO_TYPES);
        $this->maxSize = $maxSize ?: MAX_FILE_SIZE;

        // Créer le répertoire s'il n'existe pas
        createUploadDirectories();
    }

    /**
     * Upload un fichier unique
     */
    public function uploadFile($fileInput, $prefix = '')
    {
        $this->errors = [];

        // Vérifier si un fichier a été uploadé
        if (!isset($fileInput) || $fileInput['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // Pas d'erreur, pas de fichier
        }

        // Valider le fichier
        $validationErrors = $this->validateFile($fileInput);
        if (!empty($validationErrors)) {
            $this->errors = $validationErrors;
            return false;
        }

        // Générer un nom de fichier sécurisé
        $filename = $this->generateSecureFilename($fileInput['name'], $prefix);

        // Déterminer le sous-répertoire selon le type
        $subDir = $this->getSubDirectory($fileInput['name']);
        $targetDir = $this->uploadDir . $subDir . '/';
        $targetPath = $targetDir . $filename;

        // Créer le sous-répertoire s'il n'existe pas
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Déplacer le fichier
        if (move_uploaded_file($fileInput['tmp_name'], $targetPath)) {
            // Définir les permissions
            chmod($targetPath, 0644);

            // Retourner le chemin relatif
            return $subDir . '/' . $filename;
        } else {
            $this->errors[] = 'Erreur lors du déplacement du fichier';
            return false;
        }
    }

    /**
     * Upload multiple fichiers
     */
    public function uploadMultipleFiles($fileInputs, $prefix = '')
    {
        $uploadedFiles = [];
        $this->errors = [];

        foreach ($fileInputs as $key => $fileInput) {
            if ($fileInput['error'] !== UPLOAD_ERR_NO_FILE) {
                $result = $this->uploadFile($fileInput, $prefix . '_' . $key);
                if ($result === false) {
                    // En cas d'erreur, supprimer les fichiers déjà uploadés
                    foreach ($uploadedFiles as $uploadedFile) {
                        $fullPath = ROOT_PATH . $uploadedFile;
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    return false;
                } elseif ($result !== null) {
                    $uploadedFiles[] = $result;
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Supprimer un fichier
     */
    public function deleteFile($filePath)
    {
        $fullPath = ROOT_PATH . $filePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Valider un fichier
     */
    public function validateFile($file)
    {
        $errors = [];

        // Vérifier les erreurs d'upload PHP
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Le fichier est trop volumineux';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Le fichier n\'a été que partiellement uploadé';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Aucun fichier n\'a été uploadé';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errors[] = 'Dossier temporaire manquant';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errors[] = 'Impossible d\'écrire le fichier sur le disque';
                break;
            case UPLOAD_ERR_EXTENSION:
                $errors[] = 'Une extension PHP a arrêté l\'upload du fichier';
                break;
            default:
                $errors[] = 'Erreur inconnue lors de l\'upload';
                break;
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Vérifier la taille
        if ($file['size'] > $this->maxSize) {
            $errors[] = 'Le fichier est trop volumineux (max ' . formatFileSize($this->maxSize) . ')';
        }

        // Vérifier le type de fichier
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $errors[] = 'Type de fichier non autorisé. Types acceptés : ' . implode(', ', $this->allowedTypes);
        }

        // Vérifier le type MIME
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $expectedMimes = $this->getExpectedMimeTypes($extension);
            if (!in_array($mimeType, $expectedMimes)) {
                $errors[] = 'Type de fichier invalide (MIME type non reconnu)';
            }
        }

        // Vérifications de sécurité supplémentaires
        if ($this->isFileSuspicious($file['tmp_name'])) {
            $errors[] = 'Le fichier semble suspect et a été rejeté pour des raisons de sécurité';
        }

        return $errors;
    }

    /**
     * Générer un nom de fichier sécurisé
     */
    private function generateSecureFilename($originalName, $prefix = '')
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8)); // 16 caractères aléatoires

        $filename = '';
        if ($prefix) {
            $filename .= $prefix . '_';
        }
        $filename .= $timestamp . '_' . $random . '.' . $extension;

        return $filename;
    }

    /**
     * Déterminer le sous-répertoire selon le type de fichier
     */
    private function getSubDirectory($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
            return 'images';
        } elseif (in_array($extension, ALLOWED_AUDIO_TYPES)) {
            return 'audio';
        } else {
            return 'other';
        }
    }

    /**
     * Obtenir les types MIME attendus pour une extension
     */
    private function getExpectedMimeTypes($extension)
    {
        $mimeMap = [
            // Images
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],

            // Audio
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'wav' => ['audio/wav', 'audio/wave', 'audio/x-wav'],
            'ogg' => ['audio/ogg', 'application/ogg'],
            'm4a' => ['audio/mp4', 'audio/x-m4a'],
        ];

        return $mimeMap[$extension] ?? [];
    }

    /**
     * Vérifier si un fichier semble suspect
     */
    private function isFileSuspicious($filePath)
    {
        // Vérifier la taille du fichier (fichiers vides ou trop petits)
        if (filesize($filePath) < 10) {
            return true;
        }

        // Vérifier les signatures de fichiers (magic bytes)
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $bytes = fread($handle, 20);
            fclose($handle);

            // Signatures communes pour les types autorisés
            $signatures = [
                'image/jpeg' => "\xFF\xD8\xFF",
                'image/png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
                'image/gif' => "\x47\x49\x46\x38",
                'image/webp' => "\x52\x49\x46\x46",
                'audio/mpeg' => "\xFF\xFB", // MP3
                'audio/wav' => "\x52\x49\x46\x46", // WAV
                'audio/ogg' => "\x4F\x67\x67\x53", // OGG
            ];

            $isValidSignature = false;
            foreach ($signatures as $type => $signature) {
                if (strpos($bytes, $signature) === 0) {
                    $isValidSignature = true;
                    break;
                }
            }

            if (!$isValidSignature) {
                return true;
            }
        }

        // Vérifier le contenu pour les scripts potentiels
        $content = file_get_contents($filePath);
        $suspiciousPatterns = [
            '<?php',
            '<script',
            '<iframe',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror=',
            'eval(',
            'exec(',
            'system(',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir les erreurs
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Vérifier si des erreurs existent
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Nettoyer les anciens fichiers (maintenance)
     */
    public static function cleanupOldFiles($daysOld = 30)
    {
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        $filesCleaned = 0;

        // Nettoyer les répertoires d'upload
        $dirs = [IMAGE_PATH, AUDIO_PATH];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffTime) {
                        // Vérifier si le fichier n'est pas référencé en base
                        $relativePath = str_replace(ROOT_PATH, '', $file);
                        $isReferenced = Database::getInstance()->fetchOne(
                            "SELECT COUNT(*) as total FROM exam_questions WHERE image_path = ? OR audio_path = ?",
                            [$relativePath, $relativePath]
                        )['total'] > 0;

                        if (!$isReferenced) {
                            unlink($file);
                            $filesCleaned++;
                        }
                    }
                }
            }
        }

        return $filesCleaned;
    }

    /**
     * Obtenir les statistiques d'upload
     */
    public static function getUploadStats()
    {
        $stats = [
            'images' => [
                'count' => 0,
                'total_size' => 0,
            ],
            'audio' => [
                'count' => 0,
                'total_size' => 0,
            ],
        ];

        // Images
        if (is_dir(IMAGE_PATH)) {
            $imageFiles = glob(IMAGE_PATH . '*');
            $stats['images']['count'] = count($imageFiles);
            $stats['images']['total_size'] = array_sum(array_map('filesize', $imageFiles));
        }

        // Audio
        if (is_dir(AUDIO_PATH)) {
            $audioFiles = glob(AUDIO_PATH . '*');
            $stats['audio']['count'] = count($audioFiles);
            $stats['audio']['total_size'] = array_sum(array_map('filesize', $audioFiles));
        }

        return $stats;
    }
}

// Fonctions utilitaires pour faciliter l'utilisation
function handleFileUpload($fileInput, $prefix = '')
{
    $uploader = new FileUpload();
    return $uploader->uploadFile($fileInput, $prefix);
}

function handleMultipleFileUpload($fileInputs, $prefix = '')
{
    $uploader = new FileUpload();
    return $uploader->uploadMultipleFiles($fileInputs, $prefix);
}

function deleteUploadedFile($filePath)
{
    $uploader = new FileUpload();
    return $uploader->deleteFile($filePath);
}

function validateUploadedFile($file, $allowedTypes = null, $maxSize = null)
{
    $uploader = new FileUpload('', $allowedTypes, $maxSize);
    return $uploader->validateFile($file);
}
?>