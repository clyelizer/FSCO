<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 1. Authentification Universelle
requireLogin();
$user = getCurrentUser();

// 2. Validation de l'entrée
$examId = (int) ($_GET['exam_id'] ?? 0);
if (!$examId) {
    die("ID de test manquant.");
}

try {
    $db = Database::getInstance();

    // 3. Vérifier l'existence et le statut du test
    $exam = $db->fetchOne(
        "SELECT * FROM exam_examens WHERE id = ? AND statut = 'published'",
        [$examId]
    );

    if (!$exam) {
        die("Ce test n'existe pas ou n'est pas disponible.");
    }

    // 4. Vérifier si une session est déjà en cours
    $existingSession = $db->fetchOne(
        "SELECT id FROM exam_sessions WHERE examen_id = ? AND user_id = ? AND statut = 'en_cours'",
        [$examId, $user['id']]
    );

    if ($existingSession) {
        // Reprendre la session existante
        header("Location: exam.php?session_id=" . $existingSession['id']);
        exit;
    }

    // 5. Créer une nouvelle session
    // Récupérer les questions
    $questions = $db->fetchAll(
        "SELECT question_id FROM exam_examen_questions WHERE examen_id = ? ORDER BY ordre ASC",
        [$examId]
    );

    if (empty($questions)) {
        die("Ce test ne contient aucune question.");
    }

    // Préparer l'ordre des questions
    $questionIds = array_column($questions, 'question_id');
    // Optionnel : Mélanger les questions si l'option est activée (à implémenter plus tard si besoin)
    // shuffle($questionIds);

    // Générer un token de session unique
    $tokenSession = bin2hex(random_bytes(16));

    // Insérer la session
    $sessionId = $db->insert(
        "INSERT INTO exam_sessions (examen_id, user_id, date_debut, statut, questions_order, reponses, token_session) 
         VALUES (?, ?, NOW(), 'en_cours', ?, '{}', ?)",
        [$examId, $user['id'], json_encode($questionIds), $tokenSession]
    );

    // 6. Affichage de la page de préparation (Compte à rebours)
    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Préparation Examen - <?= htmlspecialchars($exam['titre']) ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background: #0f172a;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }

            .prep-card {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                padding: 3rem;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
                text-align: center;
                max-width: 500px;
                width: 90%;
                border: 1px solid #475569;
                color: #e2e8f0;
            }

            .countdown-circle {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                border: 4px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3.5rem;
                font-weight: 700;
                color: #2563eb;
                margin: 2rem auto;
                position: relative;
            }

            .countdown-circle::after {
                content: '';
                position: absolute;
                top: -4px;
                left: -4px;
                right: -4px;
                bottom: -4px;
                border-radius: 50%;
                border: 4px solid transparent;
                border-top-color: #2563eb;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            h1 {
                color: #f1f5f9;
                margin-bottom: 0.5rem;
            }

            p {
                color: #94a3b8;
                line-height: 1.6;
            }

            .instructions {
                background: #334155;
                padding: 1.5rem;
                border-radius: 8px;
                margin: 2rem 0;
                border-left: 4px solid #60a5fa;
                text-align: left;
                font-size: 0.9rem;
            }

            .instructions h3 {
                color: #60a5fa;
                margin-top: 0;
            }

            .instructions li {
                color: #cbd5e1;
                margin-bottom: 0.5rem;
            }
        </style>
    </head>

    <body>
        <div class="prep-card">
            <h1>Préparez-vous !</h1>
            <p>L'examen <strong><?= htmlspecialchars($exam['titre']) ?></strong> va commencer.</p>

            <div class="countdown-circle" id="timer">5</div>

            <div class="instructions">
                <strong>⚠️ Consignes importantes :</strong>
                <ul style="margin-top: 0.5rem; padding-left: 1.2rem; margin-bottom: 0;">
                    <li>Ne quittez pas la page et ne rafraîchissez pas.</li>
                    <li>Une fois le temps écoulé, la question suivante s'affiche.</li>
                    <li>Bonne chance ! 🍀</li>
                </ul>
            </div>
        </div>

        <script>
            let timeLeft = 5;
            const timerElement = document.getElementById('timer');
            const redirectUrl = "exam.php?session_id=<?= $sessionId ?>";

            const countdown = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        </script>
    </body>

    </html>
    <?php
    exit;

} catch (Exception $e) {
    die("Erreur système : " . $e->getMessage());
}
?>