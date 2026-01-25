<?php
session_start();
require_once __DIR__ . '/../../config.php';
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/survey_mailer.php';

$action = $_POST['action'] ?? '';
$pdo = getDBConnection();

try {
    if ($action === 'check_email') {
        $email = $_POST['email'] ?? '';
        $stmt = $pdo->prepare("SELECT id, nom FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode(['status' => 'exists', 'nom' => $user['nom']]);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
    } elseif ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['motdepasse'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_plan'] = $user['plan'] ?? 'free'; // Default to free if not set
            echo json_encode(['status' => 'success', 'redirect' => '../../index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Mot de passe incorrect.']);
        }
    } elseif ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $plan = $_POST['plan'] ?? 'free'; // Get plan from request

        // Check if email exists again to be safe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Cet email est déjà utilisé.']);
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Insert with plan
        $stmt = $pdo->prepare("INSERT INTO users (nom, email, motdepasse, role, statut, plan) VALUES (?, ?, ?, 'student', 'active', ?)");

        if ($stmt->execute([$nom, $email, $hashed_password, $plan])) {
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $nom;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_plan'] = $plan;
            echo json_encode(['status' => 'success', 'redirect' => '../../index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'inscription.']);
        }
    } elseif ($action === 'reset_request') {
        $email = $_POST['email'] ?? '';
        $stmt = $pdo->prepare("SELECT id, nom FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

            $message = "
            <html>
            <body>
                <h2>Réinitialisation de mot de passe</h2>
                <p>Bonjour {$user['nom']},</p>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Cliquez sur le lien suivant pour le réinitialiser : <a href='$resetLink'>$resetLink</a></p>
                <p>Ce lien expire dans 1 heure.</p>
            </body>
            </html>
            ";

            if (sendEmail($email, $user['nom'], 'Réinitialisation de mot de passe - FSCo', $message)) {
                echo json_encode(['status' => 'success', 'message' => 'Email envoyé.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi de l\'email.']);
            }
        } else {
            // Don't reveal if email exists or not for security, but for UX we might want to say "If email exists..."
            // However, the user asked for a flow where we check email first, so we already know if it exists or not in the UI.
            // But this is a separate action.
            echo json_encode(['status' => 'success', 'message' => 'Si cet email existe, un lien a été envoyé.']);
        }
    } elseif ($action === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET motdepasse = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            echo json_encode(['status' => 'success', 'message' => 'Mot de passe modifié avec succès.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lien invalide ou expiré.']);
        }
    } elseif ($action === 'logout') {
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

        echo json_encode(['status' => 'success', 'redirect' => '../../index.php?message=Vous avez été déconnecté avec succès']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
