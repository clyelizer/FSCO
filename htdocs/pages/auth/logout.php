<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également le cookie de session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalement, détruire la session.
session_destroy();

// Rediriger
$redirect = $_GET['redirect'] ?? '';
$url = ($redirect === 'login')
    ? 'login.php?message=Vous avez été déconnecté pour changer de compte'
    : '../../index.php?message=Vous avez été déconnecté avec succès';

if (!headers_sent()) {
    header("Location: $url");
} else {
    // Fallback si les headers sont déjà envoyés
    echo "<script>window.location.href='$url';</script>";
    echo "<meta http-equiv='refresh' content='0;url=$url'>";
}
exit;
?>