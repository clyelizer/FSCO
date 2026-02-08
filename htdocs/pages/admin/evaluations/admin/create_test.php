<?php
// Traitement du POST doit être AVANT l'inclusion du header pour permettre la redirection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Charger les dépendances minimum nécessaires
    require_once '../includes/admin_header.php';

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $notePassage = (int) ($_POST['note_passage'] ?? 50);
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (empty($titre)) {
            $message = 'Le titre du test est requis';
            $messageType = 'error';
        } else {
            try {
                // La durée sera calculée automatiquement en fonction des questions ajoutées
                $testId = Database::getInstance()->insert(
                    "INSERT INTO exam_examens (titre, description, duree_minutes, note_passage, is_public, created_by, statut) 
                     VALUES (?, ?, NULL, ?, ?, ?, 'draft')",
                    [$titre, $description, $notePassage, $isPublic, $user['id']]
                );

                // TODO: Add logging when DBHelper is implemented
                // DBHelper::logActivity($user['id'], 'test_created', "Test créé : $titre");

                // Redirection vers la gestion des questions
                header("Location: manage_test_questions.php?id=$testId&new=1");
                exit;
            } catch (Exception $e) {
                $message = 'Erreur lors de la création du test: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
} else {
    // Si GET, charger le header normalement
    $pageTitle = 'Créer un Test';
    require_once '../includes/admin_header.php';
    $message = '';
    $messageType = 'info';
}
?>

<?php echo showMessage($message, $messageType); ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Nouveau Test</h2>
        <p style="color: #64748b;">Étape 1 : Définissez les informations générales du test</p>
    </div>
    <div class="page-actions">
        <a href="tests.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux tests
        </a>
    </div>
</div>

<div class="content-card"
    style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 2rem; max-width: 800px; margin: 0 auto;">
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="titre" class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Titre du Test *</label>
            <input type="text" id="titre" name="titre" required class="form-input"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;"
                placeholder="Ex: Test Mathématiques - Chapitre 1">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="description" class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Description</label>
            <textarea id="description" name="description" rows="4" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; resize: vertical;"
                placeholder="Instructions pour les élèves..."></textarea>
        </div>


        <div class="form-group">
            <label for="note_passage" class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Score de passage
                (%)</label>
            <input type="number" id="note_passage" name="note_passage" min="0" max="100" value="50" class="form-input"
                style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            <small style="color: #64748b; font-size: 0.85rem;">La durée totale du test sera calculée automatiquement en
                fonction des questions ajoutées.</small>
        </div>


        <div class="form-group"
            style="margin-bottom: 2rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                <input type="checkbox" name="is_public" value="1"
                    style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                <div>
                    <span style="font-weight: 600; color: #1e293b; display: block;">Test Public</span>
                    <span style="font-size: 0.85rem; color: #64748b;">Si coché, ce test sera visible par tous les
                        étudiants sur la page des formations.</span>
                </div>
            </label>
        </div>

        <div class="form-actions"
            style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="tests.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                Suivant : Ajouter des questions <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>