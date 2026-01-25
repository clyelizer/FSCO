<?php
require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Smart Redirect Logic
if (isLoggedIn()) {
    if (isProf()) {
        // Professors go to their administration dashboard
        header('Location: admin/dashboard.php');
        exit;
    } elseif (isStudent()) {
        // Students go to the Formations page where tests are listed
        header('Location: ../../Formations/formations.php');
        exit;
    }
}

// Guests or unauthenticated users go to login
header('Location: ../../auth/login.php');
exit;
?>