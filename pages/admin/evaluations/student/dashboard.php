<?php
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireStudent();

$user = getCurrentUser();

// Récupérer les examens disponibles
$availableExams = Database::getInstance()->fetchAll(
    "SELECT e.*, u.nom as createur_nom,
            COUNT(eq.question_id) as nombre_questions,
            CASE
                WHEN es.id IS NOT NULL THEN 'en_cours'
                WHEN es2.id IS NOT NULL AND es2.statut = 'termine' THEN 'termine'
                ELSE 'disponible'
            END as statut_participation
     FROM exam_examens e
     JOIN users u ON e.created_by = u.id
     LEFT JOIN exam_sessions es ON e.id = es.examen_id AND es.user_id = ? AND es.statut = 'en_cours'
     LEFT JOIN exam_sessions es2 ON e.id = es2.examen_id AND es2.user_id = ? AND es2.statut = 'termine'
     WHERE e.statut = 'published'
     AND (e.date_debut IS NULL OR e.date_debut <= NOW())
     AND (e.date_fin IS NULL OR e.date_fin >= NOW())
     GROUP BY e.id
     ORDER BY e.created_at DESC",
    [$user['id'], $user['id']]
);

// Récupérer les examens terminés avec résultats
$completedExams = Database::getInstance()->fetchAll(
    "SELECT e.titre, e.duree_minutes, es.note_finale, es.pourcentage, es.date_debut, es.date_fin, es.duree_reelle,
            u.nom as createur_nom
     FROM exam_sessions es
     JOIN exam_examens e ON es.examen_id = e.id
     JOIN users u ON e.created_by = u.id
     WHERE es.user_id = ? AND es.statut = 'termine'
     ORDER BY es.date_fin DESC
     LIMIT 10",
    [$user['id']]
);

// Statistiques de l'étudiant
$stats = [
    'total_exams' => count($availableExams),
    'completed_exams' => count($completedExams),
    'in_progress_exams' => count(array_filter($availableExams, function ($exam) {
        return $exam['statut_participation'] === 'en_cours';
    })),
    'avg_score' => 0,
    'total_time' => 0
];

if (!empty($completedExams)) {
    $scores = array_column($completedExams, 'pourcentage');
    $stats['avg_score'] = round(array_sum($scores) / count($scores), 1);

    $times = array_column($completedExams, 'duree_reelle');
    $stats['total_time'] = array_sum($times);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Examens - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <h1><?php echo APP_NAME; ?> - Mes Examens</h1>
                <nav class="main-nav">
                    <a href="dashboard.php">Mes Examens</a>
                    <a href="results.php">Mes Résultats</a>
                    <a href="profile.php">Mon Profil</a>
                    <a href="../../../index.php">Retour au site</a>
                    <a href="../../../auth/logout.php">Déconnexion</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h2>Bonjour, <?php echo htmlspecialchars($user['nom']); ?> !</h2>
                    <p>Espace étudiant - Gérez vos examens et consultez vos résultats</p>
                </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_exams']; ?></div>
                        <div class="stat-label">Examens disponibles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['completed_exams']; ?></div>
                        <div class="stat-label">Examens terminés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['in_progress_exams']; ?></div>
                        <div class="stat-label">En cours</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['avg_score']; ?>%</div>
                        <div class="stat-label">Note moyenne</div>
                    </div>
                </div>

                <!-- Examens disponibles -->
                <div class="dashboard-section">
                    <h3>Examens disponibles</h3>
                    <?php if (empty($availableExams)): ?>
                        <div class="empty-state">
                            <p>Aucun examen n'est actuellement disponible.</p>
                            <p>Revenez plus tard ou contactez votre professeur.</p>
                        </div>
                    <?php else: ?>
                        <div class="exams-grid">
                            <?php foreach ($availableExams as $exam): ?>
                                <div class="exam-card <?php echo $exam['statut_participation']; ?>">
                                    <div class="exam-header">
                                        <h4><?php echo htmlspecialchars($exam['titre']); ?></h4>
                                        <span class="exam-status status-<?php echo $exam['statut_participation']; ?>">
                                            <?php
                                            switch ($exam['statut_participation']) {
                                                case 'en_cours':
                                                    echo 'En cours';
                                                    break;
                                                case 'termine':
                                                    echo 'Terminé';
                                                    break;
                                                default:
                                                    echo 'Disponible';
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </div>

                                    <div class="exam-content">
                                        <?php if ($exam['description']): ?>
                                            <p><?php echo htmlspecialchars(substr($exam['description'], 0, 100)); ?>
                                                <?php if (strlen($exam['description']) > 100): ?>...<?php endif; ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="exam-info">
                                            <span>📚 <?php echo $exam['nombre_questions']; ?> question(s)</span>
                                            <span>⏱️ <?php echo $exam['duree_minutes']; ?> minutes</span>
                                            <span>👨‍🏫 <?php echo htmlspecialchars($exam['createur_nom']); ?></span>
                                        </div>

                                        <?php if ($exam['date_debut'] || $exam['date_fin']): ?>
                                            <div class="exam-dates">
                                                <?php if ($exam['date_debut']): ?>
                                                    <span>Début: <?php echo formatDate($exam['date_debut'], 'd/m/Y H:i'); ?></span>
                                                <?php endif; ?>
                                                <?php if ($exam['date_fin']): ?>
                                                    <span>Fin: <?php echo formatDate($exam['date_fin'], 'd/m/Y H:i'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="exam-actions">
                                        <?php if ($exam['statut_participation'] === 'en_cours'): ?>
                                            <a href="exam.php?session_id=<?php
                                            // Récupérer l'ID de session en cours
                                            $session = Database::getInstance()->fetchOne(
                                                "SELECT id FROM exam_sessions WHERE examen_id = ? AND user_id = ? AND statut = 'en_cours'",
                                                [$exam['id'], $user['id']]
                                            );
                                            echo $session ? $session['id'] : '';
                                            ?>" class="btn btn-primary">
                                                Continuer l'examen
                                            </a>
                                        <?php elseif ($exam['statut_participation'] === 'termine'): ?>
                                            <a href="results.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-secondary">
                                                Voir les résultats
                                            </a>
                                        <?php else: ?>
                                            <a href="start_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-primary"
                                                onclick="return confirm('Êtes-vous sûr de vouloir commencer cet examen ? Une fois commencé, le timer démarrera.')">
                                                Commencer l'examen
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Examens récents terminés -->
                <?php if (!empty($completedExams)): ?>
                    <div class="dashboard-section">
                        <h3>Derniers résultats</h3>
                        <div class="results-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Examen</th>
                                        <th>Note</th>
                                        <th>Pourcentage</th>
                                        <th>Durée</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($completedExams, 0, 5) as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['titre']); ?></td>
                                            <td><?php echo $result['note_finale'] ? number_format($result['note_finale'], 2) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <span class="score-badge score-<?php
                                                $score = $result['pourcentage'];
                                                if ($score >= 80)
                                                    echo 'excellent';
                                                elseif ($score >= 60)
                                                    echo 'good';
                                                elseif ($score >= 40)
                                                    echo 'average';
                                                else
                                                    echo 'poor';
                                                ?>">
                                                    <?php echo $result['pourcentage']; ?>%
                                                </span>
                                            </td>
                                            <td><?php echo $result['duree_reelle'] ? formatTime($result['duree_reelle']) : 'N/A'; ?>
                                            </td>
                                            <td><?php echo formatDate($result['date_fin'], 'd/m/Y'); ?></td>
                                            <td>
                                                <a href="results.php?exam_id=<?php
                                                $examId = Database::getInstance()->fetchOne(
                                                    "SELECT id FROM exam_examens WHERE titre = ? AND created_by = (SELECT id FROM users WHERE nom = ?)",
                                                    [$result['titre'], $result['createur_nom']]
                                                )['id'];
                                                echo $examId;
                                                ?>" class="btn btn-small">Détails</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="section-footer">
                            <a href="results.php">Voir tous les résultats →</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>

</html>

<style>
    .exams-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .exam-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border-left: 4px solid #007bff;
        transition: transform 0.3s;
    }

    .exam-card:hover {
        transform: translateY(-2px);
    }

    .exam-card.en_cours {
        border-left-color: #f39c12;
    }

    .exam-card.termine {
        border-left-color: #27ae60;
    }

    .exam-header {
        padding: 1rem;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .exam-header h4 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .exam-status {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-disponible {
        background: #d4edda;
        color: #155724;
    }

    .status-en_cours {
        background: #fff3cd;
        color: #856404;
    }

    .status-termine {
        background: #d1ecf1;
        color: #0c5460;
    }

    .exam-content {
        padding: 1rem;
    }

    .exam-info {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.9rem;
        color: #7f8c8d;
        margin: 0.5rem 0;
    }

    .exam-dates {
        font-size: 0.8rem;
        color: #7f8c8d;
        margin-top: 0.5rem;
    }

    .exam-actions {
        padding: 1rem;
        background: #f8f9fa;
        text-align: center;
    }

    .results-table {
        margin-bottom: 1rem;
    }

    .score-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .score-excellent {
        background: #d4edda;
        color: #155724;
    }

    .score-good {
        background: #d1ecf1;
        color: #0c5460;
    }

    .score-average {
        background: #fff3cd;
        color: #856404;
    }

    .score-poor {
        background: #f8d7da;
        color: #721c24;
    }
</style>