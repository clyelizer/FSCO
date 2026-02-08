<?php
session_start();
require_once '../../config.php';
require_once '../../includes/survey_mailer.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour effectuer cette action.']);
    exit;
}

$pdo = getDBConnection();

try {
    if ($action === 'add_to_library') {
        $resource_id = $_POST['resource_id'] ?? '';
        $type = $_POST['type'] ?? 'ressource';

        if (!$resource_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID ressource manquant.']);
            exit;
        }

        // Check if already exists
        $stmt = $pdo->prepare("SELECT id FROM user_library WHERE user_id = ? AND resource_id = ? AND type = ?");
        $stmt->execute([$user_id, $resource_id, $type]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update status to 'en_cours' to ensure it appears in the list
            $stmt = $pdo->prepare("UPDATE user_library SET status = 'en_cours', created_at = NOW() WHERE id = ?");
            if ($stmt->execute([$existing['id']])) {
                echo json_encode(['status' => 'success', 'message' => 'Ajouté à votre bibliothèque !']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour.']);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_library (user_id, resource_id, type, status, is_favorite) VALUES (?, ?, ?, 'en_cours', 0)");
            if ($stmt->execute([$user_id, $resource_id, $type])) {
                echo json_encode(['status' => 'success', 'message' => 'Ajouté à votre bibliothèque !']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout.']);
            }
        }

    } elseif ($action === 'send_comment') {
        $resource_title = $_POST['resource_title'] ?? 'Ressource inconnue';
        $comment = $_POST['comment'] ?? '';
        $user_name = $_SESSION['user_name'] ?? 'Utilisateur';

        // Get user email from DB to include in message
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_email = $stmt->fetchColumn();

        if (!$comment) {
            echo json_encode(['status' => 'error', 'message' => 'Le commentaire ne peut pas être vide.']);
            exit;
        }

        $subject = "Nouveau commentaire sur : $resource_title";
        $message = "
        <html>
        <body>
            <h3>Nouveau commentaire d'un utilisateur</h3>
            <p><strong>Utilisateur :</strong> $user_name ($user_email)</p>
            <p><strong>Ressource :</strong> $resource_title</p>
            <hr>
            <p><strong>Commentaire :</strong></p>
            <p>" . nl2br(htmlspecialchars($comment)) . "</p>
        </body>
        </html>
        ";

        // Send to admin
        if (sendEmail('clyelise1@gmail.com', 'Admin FSCo', $subject, $message)) {
            echo json_encode(['status' => 'success', 'message' => 'Commentaire envoyé à l\'administrateur.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du commentaire.']);
        }

    } elseif ($action === 'remove_from_library') {
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID manquant.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM user_library WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['status' => 'success', 'message' => 'Supprimé de la bibliothèque.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression.']);
        }

    } elseif ($action === 'mark_as_reading') {
        $resource_id = $_POST['resource_id'] ?? '';
        $type = $_POST['type'] ?? 'ressource';

        if (!$resource_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID ressource manquant.']);
            exit;
        }

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM user_library WHERE user_id = ? AND resource_id = ? AND type = ?");
        $stmt->execute([$user_id, $resource_id, $type]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update status to 'en_cours', keep is_favorite as is
            $stmt = $pdo->prepare("UPDATE user_library SET status = 'en_cours', created_at = NOW() WHERE id = ?");
            if ($stmt->execute([$existing['id']])) {
                echo json_encode(['status' => 'success', 'message' => 'Ajouté à "En cours de lecture".']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour.']);
            }
        } else {
            // Insert as en_cours
            $stmt = $pdo->prepare("INSERT INTO user_library (user_id, resource_id, type, status, is_favorite) VALUES (?, ?, ?, 'en_cours', 0)");
            if ($stmt->execute([$user_id, $resource_id, $type])) {
                echo json_encode(['status' => 'success', 'message' => 'Ajouté à "En cours de lecture".']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout.']);
            }
        }

    } elseif ($action === 'mark_as_favorite') {
        $id = $_POST['id'] ?? ''; // This is the user_library ID

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID manquant.']);
            exit;
        }

        // Update is_favorite=1, leave status alone
        $stmt = $pdo->prepare("UPDATE user_library SET is_favorite = 1 WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['status' => 'success', 'message' => 'Ajouté aux favoris !']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour.']);
        }

    } elseif ($action === 'unmark_favorite') {
        $id = $_POST['id'] ?? ''; // This is the user_library ID

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID manquant.']);
            exit;
        }

        // Update is_favorite=0, leave status alone
        $stmt = $pdo->prepare("UPDATE user_library SET is_favorite = 0 WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['status' => 'success', 'message' => 'Retiré des favoris.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action inconnue.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
