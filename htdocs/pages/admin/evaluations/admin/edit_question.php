<?php
$pageTitle = 'Modifier une Question';
require_once '../includes/admin_header.php';
require_once '../includes/file_upload.php';

$questionId = (int) ($_GET['id'] ?? 0);
$message = '';
$messageType = 'info';

// Récupérer la question
$question = Database::getInstance()->fetchOne(
    "SELECT * FROM exam_questions WHERE id = ? AND created_by = ?",
    [$questionId, $user['id']]
);

if (!$question) {
    header('Location: questions.php?message=Question introuvable&type=error');
    exit;
}

// Récupérer les réponses existantes
// Préparer les réponses pour l'affichage
$reponses = [];
if ($question['type'] === 'qcm') {
    if ($question['option_a'])
        $reponses[] = ['texte' => $question['option_a'], 'est_correcte' => $question['reponse_qcm'] === 'A'];
    if ($question['option_b'])
        $reponses[] = ['texte' => $question['option_b'], 'est_correcte' => $question['reponse_qcm'] === 'B'];
    if ($question['option_c'])
        $reponses[] = ['texte' => $question['option_c'], 'est_correcte' => $question['reponse_qcm'] === 'C'];
    if ($question['option_d'])
        $reponses[] = ['texte' => $question['option_d'], 'est_correcte' => $question['reponse_qcm'] === 'D'];
} elseif ($question['type'] === 'vrai_faux') {
    $reponses[] = ['texte' => 'Vrai', 'est_correcte' => $question['reponse_vrai_faux'] === 'vrai'];
    $reponses[] = ['texte' => 'Faux', 'est_correcte' => $question['reponse_vrai_faux'] === 'faux'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $texte = trim($_POST['texte'] ?? '');
        $type = $_POST['type'] ?? '';
        $points = (float) ($_POST['points'] ?? 1);
        $duree_secondes = (int) ($_POST['duree_secondes'] ?? 60);

        if (empty($texte)) {
            $message = 'Le texte de la question est requis';
            $messageType = 'error';
        } else {
            try {
                // Gestion des fichiers
                $imagePath = $question['image_path'];
                $audioPath = $question['audio_path'];

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    // Supprimer l'ancienne image
                    if ($imagePath && file_exists(ROOT_PATH . $imagePath)) {
                        unlink(ROOT_PATH . $imagePath);
                    }
                    $uploader = new FileUpload('', ['jpg', 'jpeg', 'png', 'gif']);
                    $imagePath = $uploader->uploadFile($_FILES['image'], 'q_img');
                }

                if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    // Supprimer l'ancien audio
                    if ($audioPath && file_exists(ROOT_PATH . $audioPath)) {
                        unlink(ROOT_PATH . $audioPath);
                    }
                    $uploader = new FileUpload('', ['mp3', 'wav']);
                    $audioPath = $uploader->uploadFile($_FILES['audio'], 'q_audio');
                }

                // Préparer les données de mise à jour
                $updateData = [
                    'texte' => $texte,
                    'type' => $type,
                    'points' => $points,
                    'duree_secondes' => $duree_secondes,
                    'image_path' => $imagePath,
                    'audio_path' => $audioPath,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Gestion des réponses selon le type
                if ($type === 'qcm') {
                    $reponses = $_POST['reponses'] ?? [];
                    $correctes = $_POST['correctes'] ?? [];

                    $updateData['option_a'] = $reponses[0] ?? null;
                    $updateData['option_b'] = $reponses[1] ?? null;
                    $updateData['option_c'] = $reponses[2] ?? null;
                    $updateData['option_d'] = $reponses[3] ?? null;

                    // Déterminer la bonne réponse (A, B, C ou D)
                    $correctAnswer = '';
                    if (isset($correctes[0]))
                        $correctAnswer = 'A';
                    elseif (isset($correctes[1]))
                        $correctAnswer = 'B';
                    elseif (isset($correctes[2]))
                        $correctAnswer = 'C';
                    elseif (isset($correctes[3]))
                        $correctAnswer = 'D';

                    $updateData['reponse_qcm'] = $correctAnswer;

                } elseif ($type === 'vrai_faux') {
                    $correcte = $_POST['vrai_faux_correct'] ?? '';
                    $updateData['reponse_vrai_faux'] = $correcte;

                } elseif ($type === 'ouverte') {
                    $reponseType = trim($_POST['reponse_ouverte'] ?? '');
                    $updateData['reponse_ouverte'] = $reponseType;
                }

                // Construire la requête SQL dynamiquement
                $setClause = [];
                $params = [];
                foreach ($updateData as $key => $value) {
                    $setClause[] = "$key = ?";
                    $params[] = $value;
                }

                // Ajouter ID et User ID pour le WHERE
                $params[] = $questionId;
                $params[] = $user['id'];

                // Exécuter la mise à jour
                Database::getInstance()->update(
                    "UPDATE exam_questions SET " . implode(', ', $setClause) . " WHERE id = ? AND created_by = ?",
                    $params
                );

                $message = 'Question modifiée avec succès';
                $messageType = 'success';

                // Recharger la question
                $question = Database::getInstance()->fetchOne(
                    "SELECT * FROM exam_questions WHERE id = ?",
                    [$questionId]
                );

                // Recharger les réponses pour l'affichage
                $reponses = [];
                if ($question['type'] === 'qcm') {
                    if ($question['option_a'])
                        $reponses[] = ['texte' => $question['option_a'], 'est_correcte' => $question['reponse_qcm'] === 'A'];
                    if ($question['option_b'])
                        $reponses[] = ['texte' => $question['option_b'], 'est_correcte' => $question['reponse_qcm'] === 'B'];
                    if ($question['option_c'])
                        $reponses[] = ['texte' => $question['option_c'], 'est_correcte' => $question['reponse_qcm'] === 'C'];
                    if ($question['option_d'])
                        $reponses[] = ['texte' => $question['option_d'], 'est_correcte' => $question['reponse_qcm'] === 'D'];
                } elseif ($question['type'] === 'vrai_faux') {
                    $reponses[] = ['texte' => 'Vrai', 'est_correcte' => $question['reponse_vrai_faux'] === 'vrai'];
                    $reponses[] = ['texte' => 'Faux', 'est_correcte' => $question['reponse_vrai_faux'] === 'faux'];
                }

            } catch (Exception $e) {
                $message = 'Erreur lors de la modification : ' . $e->getMessage();
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
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Modifier la Question
        </h2>
        <p style="color: #64748b;">Modifiez les détails de votre question</p>
    </div>
    <div class="page-actions">
        <a href="questions.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la banque
        </a>
    </div>
</div>

<div class="content-card"
    style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 2rem; max-width: 800px; margin: 0 auto;">
    <form method="POST" action="" enctype="multipart/form-data" id="question-form">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="texte" class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Énoncé de la question
                *</label>
            <textarea id="texte" name="texte" required rows="3" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; resize: vertical;"><?php echo htmlspecialchars($question['texte']); ?></textarea>
        </div>

        <div class="form-grid"
            style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="type" class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Type de
                    question</label>
                <select id="type" name="type" class="form-select"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                    onchange="toggleResponseFields()">
                    <option value="qcm" <?php echo $question['type'] === 'qcm' ? 'selected' : ''; ?>>QCM (Choix Multiples)
                    </option>
                    <option value="vrai_faux" <?php echo $question['type'] === 'vrai_faux' ? 'selected' : ''; ?>>Vrai /
                        Faux</option>
                    <option value="ouverte" <?php echo $question['type'] === 'ouverte' ? 'selected' : ''; ?>>Question
                        Ouverte</option>
                </select>
            </div>
            <div class="form-group">
                <label for="points" class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Points</label>
                <input type="number" id="points" name="points" min="0.25" step="0.25"
                    value="<?php echo $question['points']; ?>" class="form-input"
                    style="width: 100%; padding: 0.625rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label"
                    style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Durée (secondes)
                    *</label>
                <input type="number" name="duree_secondes" required min="4" max="600"
                    value="<?php echo $question['duree_secondes'] ?? 60; ?>" class="form-input"
                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <small style="color: #64748b;">Temps imparti pour répondre à cette question</small>
            </div>
        </div>

        <!-- Fichiers existants -->
        <?php if ($question['image_path'] || $question['audio_path']): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Médias
                    actuels</label>
                <?php if ($question['image_path']): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-image" style="color: #3b82f6;"></i>
                        <span style="font-size: 0.9rem; color: #64748b;">Image actuelle</span>
                        <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question image"
                            style="max-width: 100px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                <?php if ($question['audio_path']): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-volume-up" style="color: #3b82f6;"></i>
                        <span style="font-size: 0.9rem; color: #64748b;">Audio actuel</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Uploads -->
        <div class="form-group"
            style="margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Modifier les médias
                (Optionnel)</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1;">
                    <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Nouvelle
                        image</label>
                    <input type="file" name="image" accept="image/*" class="form-input" style="font-size: 0.9rem;">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.85rem; color: #64748b; display: block; margin-bottom: 0.25rem;">Nouvel
                        audio</label>
                    <input type="file" name="audio" accept="audio/*" class="form-input" style="font-size: 0.9rem;">
                </div>
            </div>
        </div>

        <!-- Sections Réponses Dynamiques -->
        <div id="reponses-qcm" class="response-section" style="margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Réponses QCM</label>
            <div id="qcm-container">
                <?php if ($question['type'] === 'qcm' && count($reponses) > 0): ?>
                    <?php foreach ($reponses as $index => $rep): ?>
                        <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                            <input type="checkbox" name="correctes[<?php echo $index; ?>]" <?php echo $rep['est_correcte'] ? 'checked' : ''; ?> title="Cocher si correcte">
                            <input type="text" name="reponses[<?php echo $index; ?>]"
                                value="<?php echo htmlspecialchars($rep['texte']); ?>"
                                placeholder="Réponse <?php echo $index + 1; ?>" class="form-input"
                                style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="qcm-option" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                        <input type="checkbox" name="correctes[0]" title="Cocher si correcte">
                        <input type="text" name="reponses[0]" placeholder="Réponse 1" class="form-input"
                            style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" onclick="addQcmOption()" class="btn-xs btn-secondary" style="margin-top: 0.5rem;">+
                Ajouter une option</button>
        </div>

        <div id="reponses-vrai-faux" class="response-section" style="display: none; margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">La réponse correcte est
                :</label>
            <div style="display: flex; gap: 2rem;">
                <?php
                $vraiFauxCorrect = '';
                if ($question['type'] === 'vrai_faux' && count($reponses) > 0) {
                    foreach ($reponses as $rep) {
                        if ($rep['est_correcte'] && $rep['texte'] === 'Vrai') {
                            $vraiFauxCorrect = 'vrai';
                        } elseif ($rep['est_correcte'] && $rep['texte'] === 'Faux') {
                            $vraiFauxCorrect = 'faux';
                        }
                    }
                }
                ?>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="vrai_faux_correct" value="vrai" <?php echo $vraiFauxCorrect === 'vrai' ? 'checked' : ''; ?>> <span style="font-weight: 500; color: #166534;">Vrai</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="vrai_faux_correct" value="faux" <?php echo $vraiFauxCorrect === 'faux' ? 'checked' : ''; ?>> <span style="font-weight: 500; color: #991b1b;">Faux</span>
                </label>
            </div>
        </div>

        <div id="reponses-ouverte" class="response-section" style="display: none; margin-bottom: 1.5rem;">
            <label class="form-label"
                style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #475569;">Réponse type /
                Mots-clés (pour correction IA)</label>
            <textarea name="reponse_ouverte" rows="3" class="form-textarea"
                style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                placeholder="Entrez ici la réponse attendue ou les points clés..."><?php echo htmlspecialchars($question['reponse_ouverte'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions"
            style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
            <a href="questions.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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