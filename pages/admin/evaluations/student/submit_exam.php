<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 1. Authentification
requireLogin();
$user = getCurrentUser();

// 2. Validation de l'entrée
$sessionId = (int) ($_POST['session_id'] ?? $_GET['session_id'] ?? 0);
$autoSubmit = isset($_GET['auto_submit']) || isset($_POST['auto_submit']);

if (!$sessionId) {
    die("ID de session manquant.");
}

try {
    $db = Database::getInstance();

    // 3. Récupérer la session
    $session = $db->fetchOne(
        "SELECT * FROM exam_sessions WHERE id = ? AND user_id = ? AND statut = 'en_cours'",
        [$sessionId, $user['id']]
    );

    if (!$session) {
        die("Session invalide ou déjà terminée.");
    }

    // 4. Sauvegarder les réponses
    $answers = $_POST['answers'] ?? [];

    // Si auto-submit, on garde les réponses existantes si le POST est vide
    if (empty($answers) && $autoSubmit) {
        $answers = json_decode($session['reponses'] ?? '{}', true);
    }

    // 5. Calcul du Score
    $questionsOrder = json_decode($session['questions_order'], true);
    $placeholders = str_repeat('?,', count($questionsOrder) - 1) . '?';

    $questions = $db->fetchAll(
        "SELECT * FROM exam_questions WHERE id IN ($placeholders)",
        $questionsOrder
    );

    $score = 0;
    $totalPoints = 0;
    $aiCorrections = []; // Store AI feedback

    foreach ($questions as $q) {
        $totalPoints += $q['points'];
        $userAnswer = $answers[$q['id']] ?? null;

        if ($q['type'] === 'qcm') {
            // La réponse correcte est stockée dans 'reponse_qcm' (A, B, C, D)
            if ($userAnswer === $q['reponse_qcm']) {
                $score += $q['points'];
            }
        } elseif ($q['type'] === 'vrai_faux') {
            // La réponse correcte est stockée dans 'reponse_vrai_faux' (vrai, faux)
            if ($userAnswer === $q['reponse_vrai_faux']) {
                $score += $q['points'];
            }
        } elseif ($q['type'] === 'ouverte') {
            // AI Correction for open questions
            try {
                require_once '../includes/AICorrector.php';
                $corrector = new AICorrector();

                $result = $corrector->correctAnswer(
                    $q['texte'],
                    $q['reponse_ouverte'] ?: 'Pas de réponse type fournie',
                    $userAnswer ?: '',
                    $q['points']
                );

                if ($result['success']) {
                    $score += $result['score'];
                    $aiCorrections[$q['id']] = [
                        'score' => $result['score'],
                        'max_points' => $q['points'],
                        'justification' => $result['justification'],
                        'provider' => $result['provider'] ?? 'AI'
                    ];
                } else {
                    // AI failed, mark for manual correction
                    $aiCorrections[$q['id']] = [
                        'score' => 0,
                        'max_points' => $q['points'],
                        'justification' => $result['justification'],
                        'needs_manual_review' => true
                    ];
                }
            } catch (Exception $e) {
                // Fallback if AICorrector fails to initialize
                $aiCorrections[$q['id']] = [
                    'score' => 0,
                    'max_points' => $q['points'],
                    'justification' => 'Correction IA non disponible: ' . $e->getMessage(),
                    'needs_manual_review' => true
                ];
            }
        }
    }

    $percentage = ($totalPoints > 0) ? ($score / $totalPoints) * 100 : 0;
    $dureeReelle = time() - strtotime($session['date_debut']);

    // 6. Mettre à jour la session
    $db->update(
        "UPDATE exam_sessions SET 
         statut = 'termine', 
         date_fin = NOW(), 
         reponses = ?, 
         note_finale = ?, 
         pourcentage = ?, 
         duree_reelle = ?,
         ai_corrections = ?
         WHERE id = ?",
        [json_encode($answers), $score, $percentage, $dureeReelle, json_encode($aiCorrections), $sessionId]
    );

    // 7. Redirection vers les résultats
    header("Location: results.php?session_id=" . $sessionId . "&just_completed=1");
    exit;

} catch (Exception $e) {
    die("Erreur lors de la soumission : " . $e->getMessage());
}
?>