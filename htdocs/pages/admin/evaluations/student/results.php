<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 1. Authentification
requireLogin();
$user = getCurrentUser();

// 2. Validation de l'entrée
$sessionId = (int) ($_GET['session_id'] ?? 0);
if (!$sessionId) {
    die("ID de session manquant.");
}

try {
    $db = Database::getInstance();

    // 3. Récupérer la session terminée avec le score de passage
    $session = $db->fetchOne(
        "SELECT es.*, e.titre, e.description, e.afficher_resultats, e.afficher_corrections, e.note_passage 
         FROM exam_sessions es
         JOIN exam_examens e ON es.examen_id = e.id
         WHERE es.id = ? AND es.user_id = ? AND es.statut = 'termine'",
        [$sessionId, $user['id']]
    );

    if (!$session) {
        die("Résultats introuvables ou accès refusé.");
    }

    // 4. Récupérer les détails
    $questionsOrder = json_decode($session['questions_order'], true);
    $userAnswers = json_decode($session['reponses'] ?? '{}', true);
    $aiCorrections = json_decode($session['ai_corrections'] ?? '{}', true);

    $placeholders = str_repeat('?,', count($questionsOrder) - 1) . '?';
    $questions = $db->fetchAll(
        "SELECT * FROM exam_questions WHERE id IN ($placeholders) 
         ORDER BY FIELD(id, " . implode(',', $questionsOrder) . ")",
        $questionsOrder
    );

    // Calculs statistiques
    $correctCount = 0;
    $totalPoints = 0;

    foreach ($questions as $q) {
        $totalPoints += $q['points'];
        $userAns = $userAnswers[$q['id']] ?? null;
        $isCorrect = false;

        if ($q['type'] === 'qcm' && $userAns === $q['reponse_qcm'])
            $isCorrect = true;
        if ($q['type'] === 'vrai_faux' && $userAns === $q['reponse_vrai_faux'])
            $isCorrect = true;

        if ($isCorrect)
            $correctCount++;
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
    <title>Résultats - <?= htmlspecialchars($session['titre']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }

        .container {
            background: transparent;
        }

        .score-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
            border: 1px solid #334155;
        }

        .score-card h1 {
            color: #f1f5f9;
            margin-bottom: 0.5rem;
        }

        .score-card p {
            color: #94a3b8;
        }

        .score-big {
            font-size: 4rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .result-item {
            background: #1e293b;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            border-left: 5px solid #475569;
            color: #e2e8f0;
        }

        .result-item.correct {
            border-left-color: #10b981;
            background-color: #064e3b;
        }

        .result-item.incorrect {
            border-left-color: #ef4444;
            background-color: #7f1d1d;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-correct {
            background: #d4edda;
            color: #155724;
        }

        .badge-incorrect {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 800px; margin-top: 2rem;">
        <div class="header-actions" style="margin-bottom: 1rem;">
            <a href="../../../Formations/formations.php" class="btn btn-secondary"
                style="background: #334155; color: #e2e8f0; border: none;">Quitter le test</a>
        </div>

        <div class="score-card">
            <h1><?= htmlspecialchars($session['titre']) ?></h1>
            <p>Examen terminé le <?= date('d/m/Y à H:i', strtotime($session['date_fin'])) ?></p>

            <div class="score-big"
                style="color: <?= $session['pourcentage'] >= ($session['note_passage'] ?? 50) ? '#10b981' : '#ef4444' ?>;">
                <?= number_format($session['pourcentage'], 1) ?>%
            </div>

            <?php if (isset($session['note_passage'])): ?>
                <p style="color: #64748b; font-size: 0.95rem; margin-top: -0.5rem;">
                    Score de passage: <?= $session['note_passage'] ?>%
                    <?php if ($session['pourcentage'] >= $session['note_passage']): ?>
                        <span style="color: #10b981; font-weight: 600;">✓ Validé</span>
                    <?php else: ?>
                        <span style="color: #ef4444; font-weight: 600;">✗ Non validé</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <div
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; text-align: center;">
                <div style="background: #1e293b; padding: 1rem; border-radius: 8px; border: 2px solid #475569;">
                    <div style="font-size: 2rem; font-weight: bold; color: #60a5fa;">
                        <?= number_format(($correctCount / count($questions)) * 100, 0) ?>%
                    </div>
                    <div style="color: #94a3b8; font-weight: 600; margin-top: 0.5rem;">
                        Questions réussies
                    </div>
                    <div style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">
                        <?= $correctCount ?> / <?= count($questions) ?> questions
                    </div>
                </div>

                <div style="background: #1e293b; padding: 1rem; border-radius: 8px; border: 2px solid #475569;">
                    <div style="font-size: 2rem; font-weight: bold; color: #fb923c;">
                        <?= number_format($session['note_finale'], 1) ?>
                    </div>
                    <div style="color: #94a3b8; font-weight: 600; margin-top: 0.5rem;">
                        Points obtenus
                    </div>
                    <div style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">
                        sur <?= number_format($totalPoints, 1) ?> points
                    </div>
                </div>
            </div>

            <p style="margin-top: 1.5rem; color: #64748b; font-size: 0.95rem;">
                💡 <em>Chaque question peut valoir un nombre de points différent</em>
            </p>
        </div>

        <?php if ($session['afficher_corrections']): ?>
            <h2 style="color: #f1f5f9; margin-top: 2rem;">Détail des réponses</h2>

            <?php foreach ($questions as $index => $q):
                $userAns = $userAnswers[$q['id']] ?? null;
                $isCorrect = false;
                $correctAnswerText = '';

                if ($q['type'] === 'qcm') {
                    $isCorrect = ($userAns === $q['reponse_qcm']);
                    // Trouver le texte de la réponse correcte
                    if ($q['reponse_qcm']) {
                        $optKey = 'option_' . strtolower($q['reponse_qcm']);
                        $correctAnswerText = $q[$optKey] ?? $q['reponse_qcm'];
                    } else {
                        $correctAnswerText = 'Non définie';
                    }
                } elseif ($q['type'] === 'vrai_faux') {
                    $isCorrect = ($userAns === $q['reponse_vrai_faux']);
                    $correctAnswerText = ucfirst($q['reponse_vrai_faux']?? '');
                } else {
                    // Question ouverte - Check AI correction
                    $aiCorrection = $aiCorrections[$q['id']] ?? null;
                    if ($aiCorrection) {
                        $aiScore = $aiCorrection['score'];
                        $aiMaxPoints = $aiCorrection['max_points'];
                        $aiPercentage = ($aiMaxPoints > 0) ? ($aiScore / $aiMaxPoints) * 100 : 0;
                        $isCorrect = ($aiPercentage >= 50); // Consider 50%+ as "correct"
                        $correctAnswerText = "Corrigé par IA";
                    } else {
                        $correctAnswerText = "Correction manuelle requise";
                    }
                }

                $class = $isCorrect ? 'correct' : 'incorrect';
                ?>
                <div class="result-item <?= $class ?>">
                    <div style="display: flex; justify-content: space-between;">
                        <strong>Question <?= $index + 1 ?></strong>
                        <span class="badge <?= $isCorrect ? 'badge-correct' : 'badge-incorrect' ?>">
                            <?= $isCorrect ? 'Correct' : 'Incorrect' ?>
                        </span>
                    </div>
                    <p><?= htmlspecialchars($q['texte']) ?></p>

                    <div style="margin-top: 1rem; font-size: 0.9rem;">
                        <p><strong>Votre réponse :</strong>
                            <?php
                            if ($q['type'] === 'qcm') {
                                if ($userAns) {
                                    $userOptKey = 'option_' . strtolower($userAns);
                                    echo htmlspecialchars($q[$userOptKey] ?? $userAns);
                                } else {
                                    echo 'Aucune réponse';
                                }
                            } else {
                                echo htmlspecialchars($userAns ?? 'Aucune réponse');
                            }
                            ?>
                        </p>

                        <?php if ($q['type'] === 'ouverte' && isset($aiCorrections[$q['id']])): ?>
                            <!-- AI Correction Feedback -->
                            <?php $aiCorrection = $aiCorrections[$q['id']]; ?>
                            <div
                                style="margin-top: 0.5rem; padding: 0.75rem; background: #d1fae5; border-radius: 6px; border-left: 3px solid #10b981; color: #064e3b;">
                                <p style="color: #065f46;"><strong>Évaluation question ouverte :</strong>
                                    <?= number_format($aiCorrection['score'], 1) ?> /
                                    <?= $aiCorrection['max_points'] ?> points
                                </p>
                                <p style="margin-top: 0.5rem; color: #065f46;">
                                    <em><?= htmlspecialchars($aiCorrection['justification']) ?></em></p>
                                <?php if (isset($aiCorrection['needs_manual_review']) && $aiCorrection['needs_manual_review']): ?>
                                    <p style="color: #e74c3c; margin-top: 0.5rem;"><strong>⚠️ Nécessite une révision manuelle</strong>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php elseif (!$isCorrect): ?>
                            <p style="color: #27ae60;"><strong>Réponse correcte :</strong>
                                <?= htmlspecialchars($correctAnswerText) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                Les corrections détaillées ne sont pas disponibles pour cet examen.
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 2rem 0;">
            <a href="../../../../../index.php" class="btn btn-primary">Retour à l'accueil</a>
        </div>

        <!-- Feedback Section -->
        <div
            style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 16px; padding: 2.5rem; margin-top: 3rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5); border: 1px solid #475569;">
            <div style="text-align: center; color: white; margin-bottom: 2rem;">
                <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem; font-weight: 700;">💬 Votre Avis Compte !</h2>
                <p style="font-size: 1.1rem; opacity: 0.95; margin-bottom: 0;">
                    Que pensez-vous du test et de vos résultats ?<br>
                    Voulez-vous en parler avec l'équipe technique ?
                </p>
            </div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 800px; margin: 0 auto;">
                <!-- WhatsApp Button -->
                <a href="https://wa.me/212698771627?text=Bonjour%2C%20je%20viens%20de%20terminer%20le%20test%20<?= urlencode($session['titre']) ?>%20et%20j'aimerais%20discuter%20de%20mes%20résultats."
                    target="_blank"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; background: #25D366; color: white; padding: 1.25rem 1.5rem; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1.05rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);"
                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(37, 211, 102, 0.4)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(37, 211, 102, 0.3)';">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    <span>Discuter sur WhatsApp</span>
                </a>

                <!-- Email Button (Toggle) -->
                <button onclick="toggleEmailForm()"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; background: white; color: #667eea; padding: 1.25rem 1.5rem; border: 2px solid rgba(255,255,255,0.3); border-radius: 12px; font-weight: 600; font-size: 1.05rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);"
                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(255, 255, 255, 0.3)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 255, 255, 0.2)';">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <span>Envoyer un Email</span>
                </button>
            </div>

            <!-- Email Form (Hidden by default) -->
            <div id="emailFormContainer"
                style="display: none; margin-top: 2rem; background: white; border-radius: 12px; padding: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #667eea; margin-bottom: 1.5rem; font-size: 1.3rem;">📧 Envoyer un message</h3>
                <form id="feedbackForm" onsubmit="sendFeedback(event)">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem;">Votre
                            Email</label>
                        <input type="email" name="email" required placeholder="votre.email@exemple.com"
                            style="width: 100%; padding: 0.875rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                            onfocus="this.style.borderColor='#667eea';" onblur="this.style.borderColor='#e5e7eb';">
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 0.5rem;">Votre
                            Message</label>
                        <textarea name="message" rows="5" required
                            placeholder="Parlez-nous de votre expérience avec ce test..."
                            style="width: 100%; padding: 0.875rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; resize: vertical; transition: border-color 0.3s; font-family: inherit;"
                            onfocus="this.style.borderColor='#667eea';"
                            onblur="this.style.borderColor='#e5e7eb';"></textarea>
                    </div>
                    <input type="hidden" name="test_title" value="<?= htmlspecialchars($session['titre']) ?>">
                    <input type="hidden" name="test_score" value="<?= number_format($session['pourcentage'], 1) ?>%">

                    <button type="submit"
                        style="width: 100%; background:linear-gradient(135deg, rgb(137 150 208) 0%, rgb(152 108 198) 100%); color: white; padding: 1rem; border: none; border-radius: 8px; font-weight: 600; font-size: 1.05rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(102, 126, 234, 0.4)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.3)';">
                        Envoyer le message ✉️
                    </button>
                </form>
                <div id="feedbackMessage"
                    style="margin-top: 1rem; padding: 0.75rem; border-radius: 8px; display: none;"></div>
            </div>
        </div>

        <script>
            function toggleEmailForm() {
                const form = document.getElementById('emailFormContainer');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            }

            function sendFeedback(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const messageDiv = document.getElementById('feedbackMessage');

                fetch('../../../includes/form_mail_handler.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        messageDiv.style.display = 'block';
                        if (data.success) {
                            messageDiv.style.background = '#dcfce7';
                            messageDiv.style.color = '#166534';
                            messageDiv.innerHTML = '✅ ' + data.message;
                            form.reset();
                            setTimeout(() => {
                                messageDiv.style.display = 'none';
                            }, 5000);
                        } else {
                            messageDiv.style.background = '#fee2e2';
                            messageDiv.style.color = '#dc2626';
                            messageDiv.innerHTML = '❌ ' + data.message;
                        }
                    })
                    .catch(error => {
                        messageDiv.style.display = 'block';
                        messageDiv.style.background = '#fee2e2';
                        messageDiv.style.color = '#dc2626';
                        messageDiv.innerHTML = '❌ Erreur lors de l\'envoi. Veuillez réessayer.';
                    });
            }
        </script>
    </div>
</body>

</html>