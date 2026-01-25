<?php
$pageTitle = 'Ajouter une Question';
require_once '../includes/admin_header.php';
require_once '../includes/file_upload.php';

$message = '';
$messageType = 'info';

// Si on vient d'un test spécifique
$testId = (int) ($_GET['test_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $texte = trim($_POST['texte'] ?? '');
        $type = $_POST['type'] ?? 'qcm';
        $points = (float) ($_POST['points'] ?? 1); // Allow decimals
        $duree_secondes = (int) ($_POST['duree_secondes'] ?? 60); // En secondes

        // Valeurs par défaut pour les champs supprimés de l'UI mais requis par la DB (si non nullables)
        // On suppose que la DB a des valeurs par défaut ou on met des valeurs génériques
        $categorieId = null; // Ou une catégorie "Générale" par défaut si nécessaire
        $niveauDifficulte = 'moyen'; // Optionnel - peut rester par défaut

        if (empty($texte)) {
            $message = 'Le texte de la question est requis';
            $messageType = 'error';
        } else {
            try {
                // Gestion des fichiers
                $imagePath = null;
                $audioPath = null;

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploader = new FileUploader(['jpg', 'jpeg', 'png', 'gif']);
                    $imagePath = $uploader->upload($_FILES['image'], 'questions/images');
                }

                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $uploader = new FileUploader(['mp3', 'wav']);
                    $audioPath = $uploader->upload($_FILES['audio'], 'questions/audio');
                }

                // Préparation des réponses pour les colonnes de exam_questions
                $optionA = null;
                $optionB = null;
                $optionC = null;
                $optionD = null;
                $reponseQCM = null;
                $reponseVraiFaux = null;
                $reponseOuverte = null;

                if ($type === 'qcm') {
                    $reponses = $_POST['reponses'] ?? [];
                    $correctes = $_POST['correctes'] ?? [];

                    $optionA = $reponses[0] ?? null;
                    $optionB = $reponses[1] ?? null;
                    $optionC = $reponses[2] ?? null;
                    $optionD = $reponses[3] ?? null;

                    // Déterminer la bonne réponse
                    if (isset($correctes[0]))
                        $reponseQCM = 'A';
                    elseif (isset($correctes[1]))
                        $reponseQCM = 'B';
                    elseif (isset($correctes[2]))
                        $reponseQCM = 'C';
                    elseif (isset($correctes[3]))
                        $reponseQCM = 'D';

                } elseif ($type === 'vrai_faux') {
                    $reponseVraiFaux = $_POST['vrai_faux_correct'] ?? null;
                } elseif ($type === 'ouverte') {
                    $reponseOuverte = trim($_POST['reponse_ouverte'] ?? '');
                }

                // Insertion de la question avec les réponses dans la même table
                $questionId = Database::getInstance()->insert(
                    "INSERT INTO exam_questions (
                        texte, type, points, duree_secondes, 
                        option_a, option_b, option_c, option_d, 
                        reponse_qcm, reponse_vrai_faux, reponse_ouverte,
                        image_path, audio_path, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $texte,
                        $type,
                        $points,
                        $duree_secondes,
                        $optionA,
                        $optionB,
                        $optionC,
                        $optionD,
                        $reponseQCM,
                        $reponseVraiFaux,
                        $reponseOuverte,
                        $imagePath,
                        $audioPath,
                        $user['id']
                    ]
                );

                // Si lié à un test, on l'ajoute directement
                if ($testId) {
                    Database::getInstance()->insert(
                        "INSERT INTO exam_examen_questions (examen_id, question_id) VALUES (?, ?)",
                        [$testId, $questionId]
                    );
                    header("Location: manage_test_questions.php?id=$testId&message=Question créée et ajoutée&type=success");
                    exit;
                }

                $message = 'Question créée avec succès';
                $messageType = 'success';

                // Reset form data on success if not redirecting
                $_POST = [];

            } catch (Exception $e) {
                $message = 'Erreur lors de la création de la question : ' . $e->getMessage();
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
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Nouvelle Question</h2>
        <p style="color: #64748b;">Ajoutez une question à votre banque</p>
    </div>
    <div class="page-actions">
        <?php if ($testId): ?>
            <a href="manage_test_questions.php?id=<?php echo $testId; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au Test
            </a>
        <?php else: ?>
            <a href="questions.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la banque
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="content-card"
    style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 2rem; max-width: 800px; margin: 0 auto;">
    <form method="POST" action="" enctype="multipart/form-data" id="question-form">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <?php if ($testId): ?>
            <input type="hidden" name="test_id" value="<?php echo $testId; ?>">
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="texte" class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Énoncé de la question
                *</label>
            <textarea id="texte" name="texte" required rows="3" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; resize: vertical;"><?php echo htmlspecialchars($_POST['texte'] ?? ''); ?></textarea>
        </div>

        <div class="form-grid"
            style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="type" class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Type de
                    question</label>
                <select id="type" name="type" class="form-select"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                    onchange="toggleResponseFields()">
                    <option value="qcm" <?php echo ($_POST['type'] ?? '') === 'qcm' ? 'selected' : ''; ?>>QCM (Choix
                        Multiples)</option>
                    <option value="vrai_faux" <?php echo ($_POST['type'] ?? '') === 'vrai_faux' ? 'selected' : ''; ?>>Vrai
                        / Faux</option>
                    <option value="ouverte" <?php echo ($_POST['type'] ?? '') === 'ouverte' ? 'selected' : ''; ?>>Question
                        Ouverte</option>
                </select>
            </div>
            <div class="form-group">
                <label for="points" class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Points</label>
                <input type="number" id="points" name="points" min="0.25" step="0.25"
                    value="<?php echo $_POST['points'] ?? 1; ?>" class="form-input"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div class="form-group">
                <label for="duree_secondes" class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Durée
                    (secondes)</label>
                <input type="number" id="duree_secondes" name="duree_secondes" min="4" max="600"
                    value="<?php echo $_POST['duree_secondes'] ?? 60; ?>" class="form-input"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <small style="color: #64748b; font-size: 0.75rem;">Temps pour répondre</small>
            </div>
        </div>

        <!-- Uploads -->
        <div class="form-group"
            style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Médias
                (Optionnel)</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1;">
                    <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Image
                        (JPG, PNG)</label>
                    <input type="file" name="image" accept="image/*" class="form-input" style="font-size: 0.9rem;">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Audio
                        (MP3, WAV)</label>
                    <input type="file" name="audio" accept="audio/*" class="form-input" style="font-size: 0.9rem;">
                </div>
            </div>
        </div>

        <!-- Sections Réponses Dynamiques -->
        <div id="reponses-qcm" class="response-section" style="margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Réponses QCM</label>
            <div id="qcm-container">
                <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <input type="checkbox" name="correctes[0]" title="Cocher si correcte">
                    <input type="text" name="reponses[0]" placeholder="Réponse 1" class="form-input"
                        style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                </div>
                <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <input type="checkbox" name="correctes[1]" title="Cocher si correcte">
                    <input type="text" name="reponses[1]" placeholder="Réponse 2" class="form-input"
                        style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                </div>
                <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <input type="checkbox" name="correctes[2]" title="Cocher si correcte">
                    <input type="text" name="reponses[2]" placeholder="Réponse 3" class="form-input"
                        style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                </div>
                <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                    <input type="checkbox" name="correctes[3]" title="Cocher si correcte">
                    <input type="text" name="reponses[3]" placeholder="Réponse 4" class="form-input"
                        style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                </div>
            </div>
            <button type="button" onclick="addQcmOption()" class="btn-xs btn-secondary" style="margin-top: 0.5rem;">+
                Ajouter une option</button>
        </div>

        <div id="reponses-vrai-faux" class="response-section" style="display: none; margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">La réponse correcte est
                :</label>
            <div style="display: flex; gap: 2rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="vrai_faux_correct" value="vrai"> <span
                        style="font-weight: 500; color: #166534;">Vrai</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="vrai_faux_correct" value="faux"> <span
                        style="font-weight: 500; color: #991b1b;">Faux</span>
                </label>
            </div>
        </div>

        <div id="reponses-ouverte" class="response-section" style="display: none; margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Réponse type /
                Mots-clés (pour correction IA)</label>
            <textarea name="reponse_ouverte" rows="3" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                placeholder="Entrez ici la réponse attendue ou les points clés..."></textarea>
        </div>

        <div class="form-actions"
            style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <?php if ($testId): ?>
                <a href="manage_test_questions.php?id=<?php echo $testId; ?>" class="btn btn-secondary">Annuler</a>
            <?php else: ?>
                <a href="questions.php" class="btn btn-secondary">Annuler</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Enregistrer la question</button>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>

<script>
    function toggleResponseFields() {
        const type = document.getElementById('type').value;
        document.querySelectorAll('.response-section').forEach(el => el.style.display = 'none');

        if (type === 'qcm') {
            document.getElementById('reponses-qcm').style.display = 'block';
        } else if (type === 'vrai_faux') {
            document.getElementById('reponses-vrai-faux').style.display = 'block';
        } else if (type === 'ouverte') {
            document.getElementById('reponses-ouverte').style.display = 'block';
        }
    }

    function addQcmOption() {
        const container = document.getElementById('qcm-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'qcm-option';
        div.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;';
        div.innerHTML = `
        <input type="checkbox" name="correctes[${index}]" title="Cocher si correcte">
        <input type="text" name="reponses[${index}]" placeholder="Réponse ${index + 1}" class="form-input" style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
    `;
        container.appendChild(div);
    }

    // Init
    toggleResponseFields();
</script>