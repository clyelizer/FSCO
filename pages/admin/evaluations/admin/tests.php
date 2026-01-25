<?php
$pageTitle = 'Gestion des Tests';
require_once '../includes/admin_header.php';

// Gestion des actions (suppression, publication)
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        $testId = (int) ($_POST['test_id'] ?? 0);

        if ($testId) {
            if ($action === 'delete') {
                try {
                    // Vérifier s'il y a des sessions (résultats) associés
                    $sessionCount = Database::getInstance()->fetchOne(
                        "SELECT COUNT(*) as total FROM exam_sessions WHERE examen_id = ?",
                        [$testId]
                    )['total'];

                    if ($sessionCount > 0) {
                        $message = 'Impossible de supprimer ce test car des élèves l\'ont déjà passé.';
                        $messageType = 'error';
                    } else {
                        Database::getInstance()->delete(
                            "DELETE FROM exam_examens WHERE id = ? AND created_by = ?",
                            [$testId, $user['id']]
                        );
                        $message = 'Test supprimé avec succès.';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Erreur lors de la suppression du test.';
                    $messageType = 'error';
                }
            } elseif ($action === 'toggle_status') {
                try {
                    $currentStatus = Database::getInstance()->fetchOne(
                        "SELECT statut FROM exam_examens WHERE id = ? AND created_by = ?",
                        [$testId, $user['id']]
                    )['statut'];

                    $newStatus = ($currentStatus === 'published') ? 'draft' : 'published';

                    Database::getInstance()->update(
                        "UPDATE exam_examens SET statut = ? WHERE id = ? AND created_by = ?",
                        [$newStatus, $testId, $user['id']]
                    );

                    $message = 'Statut du test mis à jour.';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Erreur lors de la mise à jour du statut.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Récupérer les tests
$tests = Database::getInstance()->fetchAll(
    "SELECT e.*, 
            (SELECT COUNT(*) FROM exam_examen_questions eq WHERE eq.examen_id = e.id) as question_count,
            (SELECT COUNT(*) FROM exam_sessions es WHERE es.examen_id = e.id) as session_count
     FROM exam_examens e
     WHERE e.created_by = ?
     ORDER BY e.created_at DESC",
    [$user['id']]
);
?>

<?php echo showMessage($message, $messageType); ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Mes Tests</h2>
        <p style="color: #64748b;">Gérez vos tests et suivez les résultats</p>
    </div>
    <div class="page-actions">
        <a href="create_test.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Créer un Test
        </a>
    </div>
</div>

<?php if (empty($tests)): ?>
    <div class="empty-state"
        style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Aucun test créé</h3>
        <p style="color: #64748b; margin-bottom: 2rem;">Commencez par créer votre premier test pour évaluer vos élèves.</p>
        <a href="create_test.php" class="btn btn-primary">Créer mon premier test</a>
    </div>
<?php else: ?>
    <div class="content-card"
        style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: #475569;">Nom du Test</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Statut</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Questions</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Durée</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Passations</th>
                        <th style="padding: 1rem; text-align: right; font-weight: 600; color: #475569;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($test['titre']); ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #64748b;"><?php echo formatDate($test['created_at']); ?>
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <?php if ($test['statut'] === 'published'): ?>
                                    <span class="badge badge-success">Publié</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Brouillon</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <span class="badge badge-info"><?php echo $test['question_count']; ?></span>
                            </td>
                            <td style="padding: 1rem; text-align: center; color: #64748b;">
                                <?php echo $test['duree_minutes']; ?> min
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <?php echo $test['session_count']; ?>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                    <a href="manage_test_questions.php?id=<?php echo $test['id']; ?>"
                                        class="btn-xs btn-secondary" title="Gérer les questions">
                                        <i class="fas fa-list"></i> Questions
                                    </a>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                        <button type="submit"
                                            class="btn-xs <?php echo $test['statut'] === 'published' ? 'btn-warning' : 'btn-success'; ?>"
                                            title="<?php echo $test['statut'] === 'published' ? 'Dépublier' : 'Publier'; ?>">
                                            <i
                                                class="fas <?php echo $test['statut'] === 'published' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="" style="display: inline;"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce test ?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                        <button type="submit" class="btn-xs btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/admin_footer.php'; ?>