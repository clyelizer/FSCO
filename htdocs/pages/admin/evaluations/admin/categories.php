<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireProf();

$user = getCurrentUser();
$message = '';
$messageType = 'info';

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de sécurité invalide';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $nom = trim($_POST['nom'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $couleur = $_POST['couleur'] ?? '#007bff';

                if (empty($nom)) {
                    $message = 'Le nom de la catégorie est requis';
                    $messageType = 'error';
                } else {
                    try {
                        Database::getInstance()->insert(
                            "INSERT INTO exam_categories (nom, description, couleur, created_by) VALUES (?, ?, ?, ?)",
                            [$nom, $description, $couleur, $user['id']]
                        );

                        DBHelper::logActivity($user['id'], 'category_created', "Catégorie créée : $nom");

                        $message = 'Catégorie créée avec succès';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'Erreur lors de la création de la catégorie';
                        $messageType = 'error';
                    }
                }
                break;

            case 'edit':
                $categorieId = (int) ($_POST['categorie_id'] ?? 0);
                $nom = trim($_POST['nom'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $couleur = $_POST['couleur'] ?? '#007bff';

                if (empty($nom)) {
                    $message = 'Le nom de la catégorie est requis';
                    $messageType = 'error';
                } elseif ($categorieId) {
                    try {
                        Database::getInstance()->update(
                            "UPDATE exam_categories SET nom = ?, description = ?, couleur = ?, updated_at = NOW() WHERE id = ? AND created_by = ?",
                            [$nom, $description, $couleur, $categorieId, $user['id']]
                        );

                        DBHelper::logActivity($user['id'], 'category_updated', "Catégorie modifiée : $nom");

                        $message = 'Catégorie modifiée avec succès';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'Erreur lors de la modification de la catégorie';
                        $messageType = 'error';
                    }
                }
                break;

            case 'delete':
                $categorieId = (int) ($_POST['categorie_id'] ?? 0);

                if ($categorieId) {
                    // Vérifier si la catégorie contient des questions
                    $questionCount = Database::getInstance()->fetchOne(
                        "SELECT COUNT(*) as total FROM exam_questions WHERE categorie_id = ? AND created_by = ?",
                        [$categorieId, $user['id']]
                    )['total'];

                    if ($questionCount > 0) {
                        $message = 'Impossible de supprimer cette catégorie car elle contient des questions';
                        $messageType = 'error';
                    } else {
                        try {
                            Database::getInstance()->delete(
                                "DELETE FROM exam_categories WHERE id = ? AND created_by = ?",
                                [$categorieId, $user['id']]
                            );

                            DBHelper::logActivity($user['id'], 'category_deleted', "Catégorie supprimée : $categorieId");

                            $message = 'Catégorie supprimée avec succès';
                            $messageType = 'success';
                        } catch (Exception $e) {
                            $message = 'Erreur lors de la suppression de la catégorie';
                            $messageType = 'error';
                        }
                    }
                }
                break;

            case 'reorder':
                $orderData = $_POST['category_order'] ?? [];
                foreach ($orderData as $id => $ordre) {
                    Database::getInstance()->update(
                        "UPDATE exam_categories SET ordre = ? WHERE id = ? AND created_by = ?",
                        [$ordre, $id, $user['id']]
                    );
                }
                $message = 'Ordre des catégories mis à jour';
                $messageType = 'success';
                break;
        }
    }
}

// Récupérer les catégories
$categories = Database::getInstance()->fetchAll(
    "SELECT c.*, COUNT(q.id) as questions_count
     FROM exam_categories c
     LEFT JOIN exam_questions q ON c.id = q.categorie_id AND q.created_by = ?
     WHERE c.created_by = ?
     GROUP BY c.id
     ORDER BY c.ordre ASC, c.nom ASC",
    [$user['id'], $user['id']]
);

// Statistiques
$stats = [
    'total_categories' => count($categories),
    'total_questions' => array_sum(array_column($categories, 'questions_count')),
    'categories_with_questions' => count(array_filter($categories, function ($cat) {
        return $cat['questions_count'] > 0;
    }))
];
?>
$pageTitle = 'Gestion des Catégories';
require_once '../includes/admin_header.php';
?>

<?php echo showMessage($message, $messageType); ?>

<div class="page-header"
    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">Gestion des Catégories
        </h2>
        <p style="color: #64748b;"><?php echo $stats['total_categories']; ?> catégorie(s) -
            <?php echo $stats['total_questions']; ?>
            question(s) au total</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="showAddCategoryModal()">
            <i class="fas fa-plus"></i> Nouvelle Catégorie
        </button>
    </div>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon formations">
            <i class="fas fa-tags"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_categories']; ?></h3>
            <p>Catégories totales</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon ressources">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['categories_with_questions']; ?></h3>
            <p>Catégories utilisées</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blogs">
            <i class="fas fa-question"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_questions']; ?></h3>
            <p>Questions catégorisées</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon total">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-content">
            <h3>
                <?php
                $avgQuestions = $stats['total_categories'] > 0
                    ? round($stats['total_questions'] / $stats['total_categories'], 1)
                    : 0;
                echo $avgQuestions;
                ?>
            </h3>
            <p>Questions/catégorie (moyenne)</p>
        </div>
    </div>
</div>

<!-- Liste des catégories -->
<?php if (empty($categories)): ?>
    <div class="empty-state">
        <i class="fas fa-tags"></i>
        <p>Aucune catégorie créée pour le moment.</p>
        <button class="btn btn-primary" onclick="showAddCategoryModal()" style="margin-top: 1rem;">Créer votre première
            catégorie</button>
    </div>
<?php else: ?>
    <div class="categories-grid"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php foreach ($categories as $categorie): ?>
            <div class="category-card"
                style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border-left: 4px solid <?php echo htmlspecialchars($categorie['couleur']); ?>; overflow: hidden; transition: transform 0.2s;">
                <div class="category-header"
                    style="padding: 1rem; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #1e293b;">
                        <?php echo htmlspecialchars($categorie['nom']); ?></h3>
                    <div class="category-actions" style="display: flex; gap: 0.5rem;">
                        <button class="btn-xs btn-secondary"
                            onclick="editCategory(<?php echo $categorie['id']; ?>, '<?php echo addslashes($categorie['nom']); ?>', '<?php echo addslashes($categorie['description']); ?>', '<?php echo $categorie['couleur']; ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($categorie['questions_count'] == 0): ?>
                            <form method="POST" action="" style="display: inline;"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="categorie_id" value="<?php echo $categorie['id']; ?>">
                                <button type="submit" class="btn-xs btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="category-content" style="padding: 1.5rem;">
                    <?php if ($categorie['description']): ?>
                        <p style="color: #64748b; margin-bottom: 1rem; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($categorie['description']); ?></p>
                    <?php else: ?>
                        <p style="color: #94a3b8; margin-bottom: 1rem; font-size: 0.95rem; font-style: italic;">Aucune description
                        </p>
                    <?php endif; ?>

                    <div class="category-stats"
                        style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                        <span><i class="fas fa-question-circle"></i> <?php echo $categorie['questions_count']; ?>
                            question(s)</span>
                        <span><?php echo formatDate($categorie['created_at']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal pour ajouter/modifier une catégorie -->
<div id="categoryModal" class="modal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000;">
    <div class="modal-content"
        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div class="modal-header"
            style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Nouvelle
                Catégorie</h3>
            <button class="modal-close" onclick="closeCategoryModal()"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="categoryForm" method="POST" action="" style="padding: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="categorie_id" id="categorieId" value="">

            <div class="form-group">
                <label for="nom" class="form-label">Nom de la catégorie *</label>
                <input type="text" id="nom" name="nom" required maxlength="100" class="form-input">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" maxlength="500" class="form-textarea"></textarea>
            </div>

            <div class="form-group">
                <label for="couleur" class="form-label">Couleur</label>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <input type="color" id="couleur" name="couleur" value="#007bff"
                        style="height: 40px; width: 60px; padding: 0; border: none; border-radius: 4px; cursor: pointer;">
                    <span style="font-size: 0.9rem; color: #64748b;">Choisissez une couleur pour identifier cette
                        catégorie</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>

<script>
    function showAddCategoryModal() {
        document.getElementById('modalTitle').textContent = 'Nouvelle Catégorie';
        document.getElementById('formAction').value = 'add';
        document.getElementById('categorieId').value = '';
        document.getElementById('nom').value = '';
        document.getElementById('description').value = '';
        document.getElementById('couleur').value = '#007bff';

        document.getElementById('categoryModal').style.display = 'block';
    }

    function editCategory(id, nom, description, couleur) {
        document.getElementById('modalTitle').textContent = 'Modifier la Catégorie';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('categorieId').value = id;
        document.getElementById('nom').value = nom;
        document.getElementById('description').value = description;
        document.getElementById('couleur').value = couleur;

        document.getElementById('categoryModal').style.display = 'block';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
    }

    // Fermer le modal en cliquant en dehors
    document.getElementById('categoryModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeCategoryModal();
        }
    });
</script>