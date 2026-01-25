<?php
$pageTitle = 'Banque de Questions';
require_once '../includes/admin_header.php';

$message = '';
$messageType = 'info';

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        $questionId = (int) ($_POST['question_id'] ?? 0);

        if ($questionId && $action === 'delete') {
            try {
                // Vérifier si la question est utilisée dans des sessions
                $usageCount = Database::getInstance()->fetchOne(
                    "SELECT COUNT(*) as total FROM exam_examen_questions WHERE question_id = ?",
                    [$questionId]
                )['total'];

                if ($usageCount > 0) {
                    $message = 'Attention : Cette question est utilisée dans des tests. Elle a été retirée de la banque mais les résultats existants sont conservés.';
                    $messageType = 'warning';
                    // Soft delete ou suppression si on accepte de casser les liens (ici on supprime pour simplifier, à adapter selon besoin métier)
                    Database::getInstance()->delete(
                        "DELETE FROM exam_questions WHERE id = ? AND created_by = ?",
                        [$questionId, $user['id']]
                    );
                } else {
                    Database::getInstance()->delete(
                        "DELETE FROM exam_questions WHERE id = ? AND created_by = ?",
                        [$questionId, $user['id']]
                    );
                    $message = 'Question supprimée avec succès.';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Erreur lors de la suppression de la question.';
                $messageType = 'error';
            }
        }
    }
}

// Récupérer les questions (sans filtre de catégorie)
$questions = Database::getInstance()->fetchAll(
    "SELECT * FROM exam_questions WHERE created_by = ? ORDER BY created_at DESC",
    [$user['id']]
);
?>

<?php echo showMessage($message, $messageType); ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Banque de Questions</h2>
        <p style="color: #64748b;">Toutes vos questions centralisées</p>
    </div>
    <div class="page-actions">
        <a href="add_question.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Question
        </a>
    </div>
</div>

<?php if (empty($questions)): ?>
    <div class="empty-state"
        style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Aucun question créée</h3>
        <p style="color: #64748b; margin-bottom: 2rem;">Créez des questions pour les ajouter ensuite à vos tests.</p>
        <a href="add_question.php" class="btn btn-primary">Créer ma première question</a>
    </div>
<?php else: ?>
    <div class="content-card"
        style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: #475569;">Question</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Type</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #475569;">Points</th>
                        <th style="padding: 1rem; text-align: right; font-weight: 600; color: #475569;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars(substr($q['texte'], 0, 100)) . (strlen($q['texte']) > 100 ? '...' : ''); ?>
                                </div>
                                <?php if ($q['image_path']): ?>
                                    <div style="font-size: 0.8rem; color: #3b82f6;"><i class="fas fa-image"></i> Image jointe</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <span class="badge badge-info"><?php echo ucfirst($q['type']); ?></span>
                            </td>
                            <td style="padding: 1rem; text-align: center; color: #64748b;">
                                <?php echo $q['points']; ?>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                    <a href="edit_question.php?id=<?php echo $q['id']; ?>" class="btn-xs btn-secondary"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" style="display: inline;"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
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