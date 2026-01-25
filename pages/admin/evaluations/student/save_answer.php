<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 1. Authentification
requireLogin();
$user = getCurrentUser();

// 2. Validation de l'entrée
$sessionId = (int) ($_POST['session_id'] ?? 0);
$questionId = (int) ($_POST['question_id'] ?? 0);
$answer = $_POST['answer'] ?? null;

if (!$sessionId || !$questionId) {
    die("Données manquantes.");
}

try {
    $db = Database::getInstance();

    // 3. Récupérer la session
    $session = $db->fetchOne(
        "SELECT * FROM exam_sessions WHERE id = ? AND user_id = ? AND statut = 'en_cours'",
        [$sessionId, $user['id']]
    );

    if (!$session) {
        die("Session invalide.");
    }

    // 4. Sauvegarder la réponse
    $currentAnswers = json_decode($session['reponses'] ?? '{}', true);

    // On ne sauvegarde que si une réponse est fournie (même vide si c'est un timeout)
    // Si c'est un timeout, $answer peut être null ou vide, on enregistre quand même pour marquer le passage
    $currentAnswers[$questionId] = $answer;

    // 5. Avancer à la question suivante
    $nextIndex = $session['current_question_index'] + 1;

    // 6. Mise à jour Atomique
    $db->update(
        "UPDATE exam_sessions SET 
         reponses = ?, 
         current_question_index = ?,
         current_question_start_time = NULL 
         WHERE id = ?",
        [json_encode($currentAnswers), $nextIndex, $sessionId]
    );

    // 7. Redirection vers la suite de l'examen
    header("Location: exam.php?session_id=" . $sessionId);
    exit;

} catch (Exception $e) {
    die("Erreur système : " . $e->getMessage());
}
?>