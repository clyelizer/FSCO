<?php
$pageTitle = 'Tableau de bord professeur';
require_once '../includes/admin_header.php';

// Statistiques détaillées pour le professeur
$stats = [
    'total_questions' => DBHelper::countQuestionsByUser($user['id']),
    'published_questions' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_questions WHERE created_by = ? AND published = 1",
        [$user['id']]
    )['total'],
    'draft_questions' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_questions WHERE created_by = ? AND published = 0",
        [$user['id']]
    )['total'],
    'total_exams' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_examens WHERE created_by = ?",
        [$user['id']]
    )['total'],
    'active_exams' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_examens WHERE created_by = ? AND statut = 'published'",
        [$user['id']]
    )['total'],
    'total_sessions' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_sessions es
         JOIN exam_examens e ON es.examen_id = e.id
         WHERE e.created_by = ?",
        [$user['id']]
    )['total'],
    'completed_sessions' => Database::getInstance()->fetchOne(
        "SELECT COUNT(*) as total FROM exam_sessions es
         JOIN exam_examens e ON es.examen_id = e.id
         WHERE e.created_by = ? AND es.statut = 'termine'",
        [$user['id']]
    )['total']
];

// Questions récentes
$recentQuestions = Database::getInstance()->fetchAll(
    "SELECT q.*, c.nom as categorie_nom
     FROM exam_questions q
     LEFT JOIN exam_categories c ON q.categorie_id = c.id
     WHERE q.created_by = ?
     ORDER BY q.created_at DESC LIMIT 5",
    [$user['id']]
);

// Tests récents
$recentExams = Database::getInstance()->fetchAll(
    "SELECT * FROM exam_examens
     WHERE created_by = ?
     ORDER BY created_at DESC LIMIT 5",
    [$user['id']]
);
?>

<div class="dashboard">
    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon formations">
                <i class="fas fa-question"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_questions']; ?></h3>
                <p>Questions totales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ressources">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['published_questions']; ?></h3>
                <p>Questions publiées</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blogs">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['draft_questions']; ?></h3>
                <p>Brouillons</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_exams']; ?></h3>
                <p>Tests créés</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon formations">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['active_exams']; ?></h3>
                <p>Tests actifs</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ressources">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_sessions']; ?></h3>
                <p>Sessions totales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blogs">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['completed_sessions']; ?></h3>
                <p>Sessions terminées</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?php
                    $completion_rate = $stats['total_sessions'] > 0
                        ? round(($stats['completed_sessions'] / $stats['total_sessions']) * 100, 1)
                        : 0;
                    echo $completion_rate . '%';
                    ?>
                </h3>
                <p>Taux de complétion</p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <h2>Actions Rapides</h2>
        <div class="actions-grid">
            <a href="add_question.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Nouvelle Question</h3>
                <p>Créer une question QCM, Vrai/Faux ou Ouverte</p>
            </a>
            <a href="create_test.php" class="action-card">
                <i class="fas fa-file-signature"></i>
                <h3>Nouveau Test</h3>
                <p>Assembler des questions dans un test</p>
            </a>
            <a href="tests.php" class="action-card">
                <i class="fas fa-list"></i>
                <h3>Gérer les Tests</h3>
                <p>Voir et gérer tous vos tests</p>
            </a>
            <a href="reports.php" class="action-card">
                <i class="fas fa-chart-line"></i>
                <h3>Rapports</h3>
                <p>Analyser les résultats des étudiants</p>
            </a>
        </div>
    </div>

    <div class="content-lists">
        <!-- Questions récentes -->
        <div class="content-section">
            <h3>Questions récentes</h3>
            <?php if (empty($recentQuestions)): ?>
                <div class="empty-state">
                    <i class="fas fa-question-circle"></i>
                    <p>Aucune question créée pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="content-list">
                    <?php foreach ($recentQuestions as $question): ?>
                        <div class="content-item">
                            <div class="content-info">
                                <h4><?php echo htmlspecialchars(substr($question['texte'], 0, 50)) . '...'; ?></h4>
                                <p>
                                    Type: <?php echo getQuestionTypeLabel($question['type']); ?> |
                                    Catégorie: <?php echo htmlspecialchars($question['categorie_nom'] ?? 'Aucune'); ?>
                                </p>
                                <span class="content-date">
                                    <?php echo $question['published'] ? '<span class="badge badge-success">Publiée</span>' : '<span class="badge badge-warning">Brouillon</span>'; ?>
                                    - <?php echo formatDate($question['created_at']); ?>
                                </span>
                            </div>
                            <div class="content-actions">
                                <a href="edit_question.php?id=<?php echo $question['id']; ?>" class="btn-sm">Modifier</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="questions.php" class="btn-sm" style="background: #64748b;">Voir toutes les questions</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tests récents -->
        <div class="content-section">
            <h3>Tests récents</h3>
            <?php if (empty($recentExams)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p>Aucun test créé pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="content-list">
                    <?php foreach ($recentExams as $exam): ?>
                        <div class="content-item">
                            <div class="content-info">
                                <h4><?php echo htmlspecialchars($exam['titre']); ?></h4>
                                <p>Durée: <?php echo $exam['duree_minutes']; ?> min</p>
                                <span class="content-date">
                                    <span class="badge badge-<?= $exam['statut'] == 'published' ? 'success' : 'warning' ?>">
                                        <?php echo getExamStatusLabel($exam['statut']); ?>
                                    </span>
                                    - <?php echo formatDate($exam['created_at']); ?>
                                </span>
                            </div>
                            <div class="content-actions">
                                <a href="edit_exam.php?id=<?php echo $exam['id']; ?>" class="btn-sm">Modifier</a>
                                <a href="exam_sessions.php?exam_id=<?php echo $exam['id']; ?>" class="btn-sm"
                                    style="background: #64748b;">Sessions</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="tests.php" class="btn-sm" style="background: #64748b;">Voir tous les tests</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Graphiques et analyses (si activé) -->
    <?php if ($stats['total_sessions'] > 0): ?>
        <div class="content-section" style="margin-top: 2rem;">
            <h3>Analyse des performances</h3>
            <div class="performance-summary">
                <div class="performance-item">
                    <span class="performance-label"
                        style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Questions les plus répondues
                        :</span>
                    <?php
                    $popularQuestions = Database::getInstance()->fetchAll(
                        "SELECT q.texte, COUNT(es.id) as total_reponses
                         FROM exam_questions q
                         JOIN exam_examen_questions eq ON q.id = eq.question_id
                         JOIN exam_sessions es ON eq.examen_id = es.examen_id
                         WHERE q.created_by = ?
                         GROUP BY q.id
                         ORDER BY total_reponses DESC
                         LIMIT 3",
                        [$user['id']]
                    );

                    if (!empty($popularQuestions)) {
                        echo '<ul style="list-style: none; padding: 0;">';
                        foreach ($popularQuestions as $pq) {
                            echo '<li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars(substr($pq['texte'], 0, 50)) . '... <span style="color: #64748b;">(' . $pq['total_reponses'] . ' réponses)</span></li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<span style="color: #64748b;">Aucune donnée disponible</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/admin_footer.php'; ?>