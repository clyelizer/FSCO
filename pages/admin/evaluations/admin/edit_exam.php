<?php
$pageTitle = 'Modifier le Test';
require_once '../includes/admin_header.php';

$examId = (int) ($_GET['id'] ?? 0);
$message = '';
$messageType = 'info';

// Récupérer l'examen
$exam = Database::getInstance()->fetchOne(
    "SELECT * FROM exam_examens WHERE id = ? AND created_by = ?",
    [$examId, $user['id']]
);

if (!$exam) {
    header('Location: tests.php?message=Test introuvable&type=error');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $duree = (int) $_POST['duree'];
        $score_passage = (int) $_POST['score_passage'];
        $is_public = isset($_POST['is_public']) ? 1 : 0;

        if (empty($titre)) {
            $message = 'Le titre est requis';
            $messageType = 'error';
        } else {
            try {
                Database::getInstance()->update(
                    "UPDATE exam_examens SET 
                     titre = ?, description = ?, duree_minutes = ?, 
                     note_passage = ?, is_public = ?, updated_at = NOW() 
                     WHERE id = ?",
                    [$titre, $description, $duree, $score_passage, $is_public, $examId]
                );
                $message = 'Test modifié avec succès';
                $messageType = 'success';

                // Recharger les données
                $exam = Database::getInstance()->fetchOne(
                    "SELECT * FROM exam_examens WHERE id = ?",
                    [$examId]
                );
            } catch (Exception $e) {
                $message = 'Erreur : ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>

<?php echo showMessage($message, $messageType); ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Modifier le Test</h2>
        <p style="color: #64748b;">Mettez à jour les informations générales du test</p>
    </div>
    <div class="page-actions">
        <a href="tests.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="content-card"
    style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; max-width: 800px; margin: 0 auto;">
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Titre du test *</label>
            <input type="text" name="titre" required value="<?php echo htmlspecialchars($exam['titre']); ?>"
                class="form-input"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Description</label>
            <textarea name="description" rows="3" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;"><?php echo htmlspecialchars($exam['description']); ?></textarea>
        </div>

        <div class="form-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Durée estimée
                    (minutes)</label>
                <input type="number" name="duree" min="1" value="<?php echo $exam['duree_minutes']; ?>"
                    class="form-input"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <small style="color: #64748b;">Indicatif pour l'étudiant</small>
            </div>
            <div class="form-group">
                <label class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Score de passage
                    (%)</label>
                <input type="number" name="score_passage" min="0" max="100" value="<?php echo $exam['note_passage']; ?>"
                    class="form-input"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                <input type="checkbox" name="is_public" value="1" <?php echo $exam['is_public'] ? 'checked' : ''; ?>
                    style="width: 1.2rem; height: 1.2rem;">
                <span style="font-weight: 500; color: #475569;">Rendre ce test public (visible sur la page
                    Formations)</span>
            </label>
        </div>

        <div class="form-actions"
            style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="tests.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>