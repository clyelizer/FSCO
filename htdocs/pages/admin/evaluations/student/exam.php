<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 1. Authentification
requireLogin();
$user = getCurrentUser();

// 2. Validation de la session
$sessionId = (int) ($_GET['session_id'] ?? 0);
if (!$sessionId) {
    die("ID de session manquant.");
}

try {
    $db = Database::getInstance();

    // 3. Récupérer la session
    $session = $db->fetchOne(
        "SELECT es.*, e.titre 
         FROM exam_sessions es
         JOIN exam_examens e ON es.examen_id = e.id
         WHERE es.id = ? AND es.user_id = ? AND es.statut = 'en_cours'",
        [$sessionId, $user['id']]
    );

    if (!$session) {
        // Peut-être terminée ?
        $finishedSession = $db->fetchOne("SELECT id FROM exam_sessions WHERE id = ? AND statut = 'termine'", [$sessionId]);
        if ($finishedSession) {
            header("Location: results.php?session_id=$sessionId");
            exit;
        }
        die("Session invalide.");
    }

    // 4. Déterminer la question actuelle
    $questionsOrder = json_decode($session['questions_order'], true);
    $currentIndex = (int) $session['current_question_index'];
    $totalQuestions = count($questionsOrder);

    // Si on a dépassé la dernière question, c'est fini
    if ($currentIndex >= $totalQuestions) {
        header("Location: submit_exam.php?session_id=$sessionId&auto_submit=1");
        exit;
    }

    $currentQuestionId = $questionsOrder[$currentIndex];

    // 5. Récupérer les détails de la question
    $question = $db->fetchOne(
        "SELECT * FROM exam_questions WHERE id = ?",
        [$currentQuestionId]
    );

    // 6. Gestion du Timer par Question
    $duration = $question['duree_secondes'] ?: 60; // Défaut 60s

    if ($session['current_question_start_time'] === null) {
        // Premier chargement de cette question : on démarre le chrono
        $db->update(
            "UPDATE exam_sessions SET current_question_start_time = NOW() WHERE id = ?",
            [$sessionId]
        );
        $startTime = time();
    } else {
        $startTime = strtotime($session['current_question_start_time']);
    }

    $elapsed = time() - $startTime;
    $remaining = max(0, $duration - $elapsed);

    if ($remaining <= 0) {
        // Temps écoulé -> Auto-skip (envoi vide)
        // On redirige vers save_answer avec une réponse vide pour passer à la suivante
        // Note: Idéalement on ferait un POST automatique via JS, mais ici c'est la sécurité PHP
        // On va laisser le JS faire le submit, mais si l'utilisateur rafraichit, on force le passage
        // Pour l'instant, on affiche 0 et le JS soumettra immédiatement.
        $remaining = 0;
    }

} catch (Exception $e) {
    die("Erreur système : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($session['titre']) ?> - Question <?= $currentIndex + 1 ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            color: #e2e8f0;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #1e293b;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }

        .progress-fill {
            height: 100%;
            background: #60a5fa;
            width:
                <?= ($currentIndex / $totalQuestions) * 100 ?>
                %;
            transition: width 0.3s ease;
        }

        .question-container {
            max-width: 800px;
            margin: 2rem auto;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border: 1px solid #475569;
        }

        .timer-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 5px solid #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 2rem;
            color: #e2e8f0;
        }

        .timer-warning {
            border-color: #ef4444;
            color: #ef4444;
        }

        .option-label {
            display: block;
            padding: 1rem;
            border: 2px solid #475569;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            background: #1e293b;
        }

        .option-label:hover {
            border-color: #60a5fa;
            background: #334155;
        }

        .option-label input {
            margin-right: 1rem;
        }

        .option-label input:checked+span {
            font-weight: bold;
            color: #60a5fa;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="question-container">
            <!-- Progress -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #64748b;">
                <span>Question <?= $currentIndex + 1 ?> sur <?= $totalQuestions ?></span>
                <span><?= round(($currentIndex / $totalQuestions) * 100) ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= round(($currentIndex / $totalQuestions) * 100) ?>%;"></div>
            </div>

            <!-- Timer -->
            <div id="timer" class="timer-circle">
                <?= $remaining ?>
            </div>

            <!-- Question -->
            <h2 style="margin-bottom: 1.5rem;"><?= nl2br(htmlspecialchars($question['texte'])) ?></h2>

            <?php if ($question['image_path']): ?>
                <div style="text-align: center; margin-bottom: 2rem;">
                    <img src="../<?= htmlspecialchars($question['image_path']) ?>"
                        style="max-width: 100%; max-height: 400px; border-radius: 8px;">
                </div>
            <?php endif; ?>

            <?php if ($question['audio_path']): ?>
                <div style="text-align: center; margin-bottom: 2rem;">
                    <audio controls src="../<?= htmlspecialchars($question['audio_path']) ?>" style="width: 100%;"></audio>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form id="question-form" action="save_answer.php" method="POST">
                <input type="hidden" name="session_id" value="<?= $sessionId ?>">
                <input type="hidden" name="question_id" value="<?= $currentQuestionId ?>">

                <div class="options-list">
                    <?php if ($question['type'] === 'qcm'): ?>
                        <?php
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                        foreach ($options as $key => $val):
                            if ($val): ?>
                                <label class="option-label">
                                    <input type="radio" name="answer" value="<?= $key ?>">
                                    <span><?= htmlspecialchars($val) ?></span>
                                </label>
                            <?php endif;
                        endforeach; ?>

                    <?php elseif ($question['type'] === 'vrai_faux'): ?>
                        <label class="option-label">
                            <input type="radio" name="answer" value="vrai">
                            <span>Vrai</span>
                        </label>
                        <label class="option-label">
                            <input type="radio" name="answer" value="faux">
                            <span>Faux</span>
                        </label>

                    <?php elseif ($question['type'] === 'ouverte'): ?>
                        <textarea name="answer" rows="6" class="form-control"
                            style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px;"
                            placeholder="Votre réponse..."></textarea>
                    <?php endif; ?>
                </div>

                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Valider et Suivant →</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let remaining = <?= $remaining ?>;
        const timerEl = document.getElementById('timer');
        const form = document.getElementById('question-form');

        // Timer Loop
        const interval = setInterval(() => {
            remaining--;
            timerEl.textContent = remaining;

            if (remaining <= 10) {
                timerEl.classList.add('timer-warning');
            }

            if (remaining <= 0) {
                clearInterval(interval);
                // Auto submit
                form.submit();
            }
        }, 1000);

        // Empêcher le retour arrière
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>

</html>