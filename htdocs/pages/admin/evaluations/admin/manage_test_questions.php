<?php
$pageTitle = 'Gérer les Questions du Test';
require_once '../includes/admin_header.php';

$testId = (int) ($_GET['id'] ?? 0);
$isNew = isset($_GET['new']);

if (!$testId) {
    header('Location: tests.php');
    exit;
}

// Récupérer les infos du test
$test = Database::getInstance()->fetchOne(
    "SELECT * FROM exam_examens WHERE id = ? AND created_by = ?",
    [$testId, $user['id']]
);

if (!$test) {
    header('Location: tests.php?message=Test introuvable&type=error');
    exit;
}

$message = '';
$messageType = 'info';

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_question') {
            $questionId = (int) $_POST['question_id'];
            try {
                // Vérifier si déjà liée
                $exists = Database::getInstance()->fetchOne(
                    "SELECT COUNT(*) as total FROM exam_examen_questions WHERE examen_id = ? AND question_id = ?",
                    [$testId, $questionId]
                )['total'];

                if (!$exists) {
                    Database::getInstance()->insert(
                        "INSERT INTO exam_examen_questions (examen_id, question_id) VALUES (?, ?)",
                        [$testId, $questionId]
                    );

                    // Recalculer la durée totale du test
                    updateTestDuration($testId);

                    $message = 'Question ajoutée au test.';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Erreur lors de l\'ajout de la question.';
                $messageType = 'error';
            }
        } elseif ($action === 'remove_question') {
            $questionId = (int) $_POST['question_id'];
            try {
                Database::getInstance()->delete(
                    "DELETE FROM exam_examen_questions WHERE examen_id = ? AND question_id = ?",
                    [$testId, $questionId]
                );

                // Recalculer la durée totale du test
                updateTestDuration($testId);

                $message = 'Question retirée du test.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Erreur lors du retrait de la question.';
                $messageType = 'error';
            }
        }
    }
}

/**
 * Recalcule et met à jour la durée totale d'un test basé sur la somme des durées estimées de ses questions
 */
function updateTestDuration($testId)
{
    try {
        $stats = Database::getInstance()->fetchOne(
            "SELECT SUM(q.duree_secondes) as total_seconds 
             FROM exam_examen_questions eq
             JOIN exam_questions q ON eq.question_id = q.id
             WHERE eq.examen_id = ?",
            [$testId]
        );

        $totalSeconds = (int) ($stats['total_seconds'] ?? 0);
        $totalMinutes = $totalSeconds > 0 ? ceil($totalSeconds / 60) : null;

        Database::getInstance()->update(
            "UPDATE exam_examens SET duree_minutes = ? WHERE id = ?",
            [$totalMinutes, $testId]
        );
    } catch (Exception $e) {
        // Log error silently
    }
}

// Récupérer les questions du test
$testQuestions = Database::getInstance()->fetchAll(
    "SELECT q.* FROM exam_questions q 
     JOIN exam_examen_questions eq ON q.id = eq.question_id 
     WHERE eq.examen_id = ? 
     ORDER BY q.created_at DESC",
    [$testId]
);

// Récupérer les questions disponibles (non liées)
$availableQuestions = Database::getInstance()->fetchAll(
    "SELECT * FROM exam_questions 
     WHERE created_by = ? 
     AND id NOT IN (SELECT question_id FROM exam_examen_questions WHERE examen_id = ?)
     ORDER BY created_at DESC",
    [$user['id'], $testId]
);
?>

<?php echo showMessage($message, $messageType); ?>

<?php if ($isNew): ?>
    <div class="alert alert-success"
        style="margin-bottom: 2rem; padding: 1rem; border-radius: 8px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> Le test "<strong><?php echo htmlspecialchars($test['titre']); ?></strong>" a été
        créé avec succès. Ajoutez maintenant des questions.
    </div>
<?php endif; ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Questions du Test :
            <?php echo htmlspecialchars($test['titre']); ?>
        </h2>
        <p style="color: #64748b;">
            <?php echo count($testQuestions); ?> question(s) ajoutée(s)
            <?php if ($test['duree_minutes']): ?>
                • Durée totale : <strong><?php echo $test['duree_minutes']; ?> min</strong> (calculée automatiquement)
            <?php else: ?>
                • <em>Durée non définie (ajoutez des questions)</em>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-actions" style="display: flex; gap: 1rem;">
        <a href="tests.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux tests
        </a>
        <?php if ($test['statut'] !== 'published'): ?>
            <form method="POST" action="tests.php">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="test_id" value="<?php echo $testId; ?>">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Publier le Test
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Colonne Gauche : Questions du Test -->
    <div class="content-card"
        style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 1.5rem;">
        <h3
            style="font-size: 1.1rem; font-weight: 600; color: #1e293b; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            Questions incluses
        </h3>

        <?php if (empty($testQuestions)): ?>
            <div class="empty-state" style="text-align: center; padding: 2rem; color: #64748b;">
                <p>Aucune question dans ce test pour le moment.</p>
                <p style="font-size: 0.9rem;">Sélectionnez des questions dans la colonne de droite ou créez-en de nouvelles.
                </p>
            </div>
        <?php else: ?>
            <div class="questions-list" style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($testQuestions as $q): ?>
                    <div class="question-item"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div>
                            <div style="font-weight: 600; color: #334155; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars(substr($q['texte'], 0, 80)) . (strlen($q['texte']) > 80 ? '...' : ''); ?>
                            </div>
                            <div style="font-size: 0.85rem; color: #64748b;">
                                <span class="badge badge-info"><?php echo ucfirst($q['type']); ?></span>
                                • <?php echo $q['points']; ?> pts
                            </div>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="action" value="remove_question">
                            <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                            <button type="submit" class="btn-xs btn-danger" title="Retirer du test">
                                <i class="fas fa-minus"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Colonne Droite : Banque de Questions -->
    <div class="content-card"
        style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 1.5rem;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 600; color: #1e293b; margin: 0;">
                Banque de Questions
            </h3>
            <a href="add_question.php?test_id=<?php echo $testId; ?>" class="btn-xs btn-primary">
                <i class="fas fa-plus"></i> Créer
            </a>
        </div>

        <?php if (empty($availableQuestions)): ?>
            <div class="empty-state" style="text-align: center; padding: 2rem; color: #64748b;">
                <p>Toutes vos questions sont déjà dans ce test ou vous n'en avez pas encore créé.</p>
            </div>
        <?php else: ?>
            <div class="questions-list"
                style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 600px; overflow-y: auto;">
                <?php foreach ($availableQuestions as $q): ?>
                    <div class="question-item"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; hover:bg-gray-50;">
                        <div style="flex: 1; margin-right: 0.5rem;">
                            <div style="font-size: 0.9rem; color: #334155; margin-bottom: 0.2rem;">
                                <?php echo htmlspecialchars(substr($q['texte'], 0, 50)) . (strlen($q['texte']) > 50 ? '...' : ''); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                <?php echo ucfirst($q['type']); ?> • <?php echo $q['points']; ?> pts
                            </div>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <input type="hidden" name="action" value="add_question">
                            <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                            <button type="submit" class="btn-xs btn-secondary" title="Ajouter au test">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>