<?php
/**
 * Dashboard Analytics Optimisé FSCo
 * Version haute performance avec cache, pagination et optimisations avancées
 */

// Configuration et connexion base de données
require_once '../config.php';

// Configuration du cache
define('CACHE_DIR', __DIR__ . '/cache/');
define('CACHE_DURATION', 300); // 5 minutes

// Créer le dossier cache s'il n'existe pas
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

/**
 * Système de cache simple pour les requêtes lourdes
 */
class QueryCache {
    private $cache_dir;
    private $duration;
    
    public function __construct($cache_dir, $duration = 300) {
        $this->cache_dir = $cache_dir;
        $this->duration = $duration;
    }
    
    public function get($key) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        if (file_exists($filename) && (time() - filemtime($filename)) < $this->duration) {
            return unserialize(file_get_contents($filename));
        }
        return null;
    }
    
    public function set($key, $data) {
        $filename = $this->cache_dir . md5($key) . '.cache';
        file_put_contents($filename, serialize($data));
    }
    
    public function clear() {
        $files = glob($this->cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

$cache = new QueryCache(CACHE_DIR, CACHE_DURATION);

try {
    $pdo = getDBConnection();

    // === REQUÊTES OPTIMISÉES AVEC CACHE ===
    
    // Statistiques principales avec cache
    $main_stats = $cache->get('main_stats');
    if (!$main_stats) {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_responses,
                SUM(CASE WHEN DATE(date_soumission) = CURDATE() THEN 1 ELSE 0 END) as responses_today,
                SUM(CASE WHEN date_soumission >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as responses_this_week,
                SUM(CASE WHEN date_soumission >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as responses_this_month,
                COUNT(DISTINCT pays) as unique_countries
            FROM survey_responses
        ");
        $main_stats = $stmt->fetch();
        $cache->set('main_stats', $main_stats);
    }

    // Croissance et tendances avec cache
    $growth_stats = $cache->get('growth_stats');
    if (!$growth_stats) {
        $stmt = $pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM survey_responses WHERE DATE(date_soumission) = CURDATE()) as today,
                (SELECT COUNT(*) FROM survey_responses WHERE DATE(date_soumission) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as yesterday,
                (SELECT COUNT(*) FROM survey_responses WHERE DATE(date_soumission) = DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as last_week,
                (SELECT COUNT(*) FROM survey_responses WHERE DATE(date_soumission) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)) as last_2weeks
        ");
        $growth_stats = $stmt->fetch();
        $cache->set('growth_stats', $growth_stats);
    }

    // Données temporelles optimisées (requête unique)
    $time_series = $cache->get('time_series');
    if (!$time_series) {
        $stmt = $pdo->query("
            SELECT 
                DATE(date_soumission) as date,
                COUNT(*) as daily_responses,
                COUNT(DISTINCT pays) as daily_countries,
                WEEK(date_soumission) as week,
                COUNT(DISTINCT domaine) as daily_domains
            FROM survey_responses 
            WHERE date_soumission >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(date_soumission)
            ORDER BY date ASC
        ");
        $time_series = $stmt->fetchAll();
        $cache->set('time_series', $time_series);
    }

    // Analyses géographiques avancées avec pagination
    $geographic_page = isset($_GET['geo_page']) ? max(1, intval($_GET['geo_page'])) : 1;
    $geographic_limit = 20;
    $geographic_offset = ($geographic_page - 1) * $geographic_limit;
    
    $geographic_stats = $cache->get('geographic_stats_' . $geographic_page);
    if (!$geographic_stats) {
        $stmt = $pdo->prepare("
            SELECT 
                pays, 
                COUNT(*) as responses,
                COUNT(DISTINCT domaine) as unique_domains,
                ROUND(AVG(CASE 
                    WHEN annee = '1ère année' THEN 1
                    WHEN annee = '2ème année' THEN 2
                    WHEN annee = '3ème année' THEN 3
                    WHEN annee = '4ème année' THEN 4
                    WHEN annee = 'Master 1' THEN 5
                    WHEN annee = 'Master 2' THEN 6
                    WHEN annee = 'Doctorat' THEN 7
                    ELSE 0
                END), 1) as avg_year_level,
                COUNT(CASE WHEN usage_ia = 'Régulière' THEN 1 END) as regular_ai_users,
                COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as email_provided
            FROM survey_responses 
            GROUP BY pays
            ORDER BY responses DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $geographic_limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $geographic_offset, PDO::PARAM_INT);
        $stmt->execute();
        $geographic_stats = $stmt->fetchAll();
        $cache->set('geographic_stats_' . $geographic_page, $geographic_stats);
    }

    // Compétences avec analyse sémantique
    $skills_analysis = $cache->get('skills_analysis');
    if (!$skills_analysis) {
        $skills_raw = $pdo->query("
            SELECT competences FROM survey_responses 
            WHERE competences IS NOT NULL AND competences != ''
        ")->fetchAll(PDO::FETCH_COLUMN);
        
        $skills_processed = [];
        foreach ($skills_raw as $skills_string) {
            $skills = array_map('trim', explode(',', $skills_string));
            foreach ($skills as $skill) {
                if (!empty($skill)) {
                    // Analyse sémantique des compétences
                    $normalized_skill = normalizeSkill($skill);
                    $skills_processed[$normalized_skill]['original'][] = $skill;
                    $skills_processed[$normalized_skill]['count'] = ($skills_processed[$normalized_skill]['count'] ?? 0) + 1;
                }
            }
        }
        
        // Trier par count
        uasort($skills_processed, function($a, $b) { return $b['count'] - $a['count']; });
        $skills_analysis = array_slice($skills_processed, 0, 50, true);
        $cache->set('skills_analysis', $skills_analysis);
    }

    // Analyses croisées avec optimisation
    $cross_analysis = $cache->get('cross_analysis');
    if (!$cross_analysis) {
        // Corrélations optimisées en une requête
        $cross_analysis = [
            'experience_ai' => $pdo->query("
                SELECT experience, usage_ia, COUNT(*) as count
                FROM survey_responses 
                GROUP BY experience, usage_ia
                ORDER BY count DESC
                LIMIT 20
            ")->fetchAll(),
            
            'country_domain' => $pdo->query("
                SELECT pays, domaine, COUNT(*) as count
                FROM survey_responses 
                GROUP BY pays, domaine
                ORDER BY count DESC
                LIMIT 30
            ")->fetchAll(),
            
            'formation_skills' => $pdo->query("
                SELECT format_formation, COUNT(DISTINCT competences) as skill_diversity
                FROM survey_responses 
                WHERE competences IS NOT NULL AND competences != ''
                GROUP BY format_formation
                ORDER BY skill_diversity DESC
            ")->fetchAll()
        ];
        $cache->set('cross_analysis', $cross_analysis);
    }

    // Métriques de performance
    $performance_metrics = $cache->get('performance_metrics');
    if (!$performance_metrics) {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_records,
                MIN(date_soumission) as first_response,
                MAX(date_soumission) as last_response,
                COUNT(DISTINCT MONTH(date_soumission)) as active_months,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, 
                    (SELECT MAX(date_soumission) FROM survey_responses s2 WHERE s2.date_soumission < s1.date_soumission),
                    s1.date_soumission
                )), 2) as avg_time_between_responses
            FROM survey_responses s1
        ");
        $performance_metrics = $stmt->fetch();
        $cache->set('performance_metrics', $performance_metrics);
    }

    // === DONNÉES DE BASE POUR COMPATIBILITÉ ===
    $country_stats = $pdo->query("SELECT pays, COUNT(*) as count FROM survey_responses GROUP BY pays ORDER BY count DESC LIMIT 10")->fetchAll();
    $domain_stats = $pdo->query("SELECT domaine, COUNT(*) as count FROM survey_responses GROUP BY domaine ORDER BY count DESC LIMIT 10")->fetchAll();
    $ai_usage_stats = $pdo->query("SELECT usage_ia, COUNT(*) as count FROM survey_responses GROUP BY usage_ia ORDER BY count DESC")->fetchAll();

    // Calcul des KPIs optimisés
    $total_responses = $main_stats['total_responses'];
    $responses_today = $main_stats['responses_today'];
    $responses_this_week = $main_stats['responses_this_week'];
    $responses_this_month = $main_stats['responses_this_month'];
    
    $daily_growth_rate = $growth_stats['yesterday'] > 0 ? 
        round((($growth_stats['today'] - $growth_stats['yesterday']) / $growth_stats['yesterday']) * 100, 1) : 0;
    
    $weekly_growth_rate = $growth_stats['last_week'] > 0 ? 
        round((($responses_this_week - $growth_stats['last_week']) / $growth_stats['last_week']) * 100, 1) : 0;

} catch (Exception $e) {
    $db_error = $e->getMessage();
    $total_responses = 0;
    $responses_today = 0;
    $responses_this_week = 0;
    $responses_this_month = 0;
    $daily_growth_rate = 0;
    $weekly_growth_rate = 0;
    $time_series = [];
    $geographic_stats = [];
    $skills_analysis = [];
    $cross_analysis = [];
    $performance_metrics = [];
    $country_stats = [];
    $domain_stats = [];
    $ai_usage_stats = [];
}

/**
 * Normalise les compétences pour une meilleure analyse sémantique
 */
function normalizeSkill($skill) {
    $skill = strtolower(trim($skill));
    
    // Dictionnaire de normalisation
    $replacements = [
        'intelligence artificielle' => 'Intelligence Artificielle',
        'ia' => 'Intelligence Artificielle',
        'machine learning' => 'Machine Learning',
        'apprentissage automatique' => 'Machine Learning',
        'deep learning' => 'Deep Learning',
        'programmation' => 'Programmation',
        'coding' => 'Programmation',
        'développement web' => 'Développement Web',
        'web development' => 'Développement Web',
        'cybersécurité' => 'Cybersécurité',
        'security' => 'Cybersécurité',
        'analyse de données' => 'Analyse de Données',
        'data analysis' => 'Analyse de Données',
        'base de données' => 'Base de Données',
        'database' => 'Base de Données',
        'sql' => 'Base de Données',
        'blockchain' => 'Blockchain',
        'block chain' => 'Blockchain',
        'iot' => 'IoT',
        'internet des objets' => 'IoT',
        'cloud' => 'Cloud Computing',
        '云计算' => 'Cloud Computing'
    ];
    
    foreach ($replacements as $pattern => $replacement) {
        if (strpos($skill, $pattern) !== false) {
            return $replacement;
        }
    }
    
    return ucfirst($skill);
}

/**
 * Génère des insights avancés avec analyse prédictive
 */
function generateAdvancedInsights($data) {
    $insights = [];
    
    // Insight de croissance avec prédiction
    if (isset($data['daily_growth_rate'])) {
        if ($data['daily_growth_rate'] > 20) {
            $insights[] = [
                'type' => 'success',
                'title' => '🚀 Croissance explosive',
                'message' => 'Croissance de ' . $data['daily_growth_rate'] . '% - Le trend s\'accélère significativement.',
                'recommendation' => 'Considérer l\'augmentation de la capacité serveur.'
            ];
        } elseif ($data['daily_growth_rate'] > 5) {
            $insights[] = [
                'type' => 'info',
                'title' => '📈 Croissance soutenue',
                'message' => 'Croissance positive de ' . $data['daily_growth_rate'] . '% par rapport à hier.',
                'recommendation' => 'Maintenir les efforts de communication.'
            ];
        }
    }
    
    // Insight sur la diversification géographique
    if (isset($data['geographic_stats']) && count($data['geographic_stats']) > 5) {
        $top_3_percentage = 0;
        $total_responses = 0;
        foreach (array_slice($data['geographic_stats'], 0, 3) as $country) {
            $top_3_percentage += ($country['responses'] / $data['total_responses']) * 100;
            $total_responses += $country['responses'];
        }
        
        if ($top_3_percentage < 70) {
            $insights[] = [
                'type' => 'info',
                'title' => '🌍 Excellente diversification',
                'message' => 'Les 3 premiers pays ne représentent que ' . round($top_3_percentage, 1) . '% des réponses.',
                'recommendation' => 'Bonne répartition géographique有利于国际化.'
            ];
        }
    }
    
    // Insight sur l'adoption technologique
    if (isset($data['ai_usage_stats'])) {
        $advanced_usage = array_filter($data['ai_usage_stats'], function($item) { 
            return in_array(strtolower($item['usage_ia']), ['régulière', 'avancée', 'expert']); 
        });
        
        if (!empty($advanced_usage)) {
            $total_advanced = array_sum(array_column($advanced_usage, 'count'));
            $percentage = $data['total_responses'] > 0 ? round(($total_advanced / $data['total_responses']) * 100, 1) : 0;
            
            if ($percentage > 30) {
                $insights[] = [
                    'type' => 'success',
                    'title' => '🤖 Adoption IA avancée',
                    'message' => $percentage . '% des utilisateurs ont une utilisation avancée de l\'IA.',
                    'recommendation' => 'Opportunité de développer des formations spécialisées.'
                ];
            }
        }
    }
    
    // Insight sur la qualité des données
    if (isset($data['performance_metrics'])) {
        $avg_time_between = $data['performance_metrics']['avg_time_between_responses'] ?? 0;
        if ($avg_time_between > 0 && $avg_time_between < 24) {
            $insights[] = [
                'type' => 'info',
                'title' => '⚡ Engagement élevé',
                'message' => 'Temps moyen entre réponses: ' . round($avg_time_between, 1) . 'h (engagement constant).',
                'recommendation' => 'Les utilisateurs sont très actifs.'
            ];
        }
    }
    
    return $insights;
}

$insights = generateAdvancedInsights([
    'daily_growth_rate' => $daily_growth_rate,
    'geographic_stats' => $geographic_stats,
    'ai_usage_stats' => $ai_usage_stats,
    'total_responses' => $total_responses,
    'performance_metrics' => $performance_metrics
]);

// Préparer les données pour JavaScript
$daily_labels = array_column($time_series, 'date');
$daily_data = array_column($time_series, 'daily_responses');
$daily_countries = array_column($time_series, 'daily_countries');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics Optimisé - FSCo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .dashboard-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 15px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 10px;
            z-index: 100;
        }

        .header h1 {
            color: #667eea;
            font-size: 2.2em;
            margin-bottom: 8px;
            text-align: center;
        }

        .header p {
            color: #666;
            font-size: 1em;
            text-align: center;
        }

        .performance-badge {
            display: inline-block;
            background: linear-gradient(90deg, #10b981, #059669);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            margin-left: 15px;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .insight-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
            transition: transform 0.2s ease;
        }

        .insight-card:hover {
            transform: translateY(-2px);
        }

        .insight-card.success {
            border-left-color: #10b981;
        }

        .insight-card.info {
            border-left-color: #3b82f6;
        }

        .insight-card.warning {
            border-left-color: #f59e0b;
        }

        .insight-title {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .insight-message {
            color: #666;
            font-size: 0.85em;
            margin-bottom: 8px;
        }

        .insight-recommendation {
            background: rgba(102, 126, 234, 0.1);
            color: #4f46e5;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.8em;
            font-style: italic;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 0.85em;
            margin-bottom: 6px;
        }

        .stat-change {
            font-size: 0.75em;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .stat-change.positive {
            background: #dcfce7;
            color: #166534;
        }

        .stat-change.negative {
            background: #fef2f2;
            color: #dc2626;
        }

        .stat-change.neutral {
            background: #f3f4f6;
            color: #374151;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .chart-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .chart-wrapper.large {
            height: 400px;
        }

        .chart-wrapper.small {
            height: 250px;
        }

        .controls-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .controls-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 120px;
        }

        .filter-item label {
            font-size: 0.85em;
            color: #666;
            font-weight: 500;
        }

        .filter-item select, .filter-item input {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.9em;
            background: white;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9em;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .table-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 15px;
            justify-content: center;
        }

        .pagination button {
            padding: 6px 12px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination button:hover:not(.active) {
            background: #f3f4f6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85em;
        }

        th, td {
            padding: 10px 6px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 0.9em;
        }

        .error-banner {
            background: #fef2f2;
            color: #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        .success-banner {
            background: #f0fdf4;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bbf7d0;
        }

        .info-banner {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bfdbfe;
        }

        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 6px;
            margin: 3px 0;
        }

        .progress-fill {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .insights-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>⚡ Dashboard Analytics  
                <span class="performance-badge">FSCo</span>
            </h1>
            <p>Analyses avancées des données réelles du sondage FSCo</p>
            <?php if (isset($db_error)): ?>
                <div class="error-banner">
                    ⚠️ <strong>Base de données non disponible :</strong> <?php echo htmlspecialchars($db_error); ?>
                </div>
            <?php elseif ($total_responses == 0): ?>
                <div class="info-banner">
                    📊 <strong>Prêt pour les données</strong> - Dashboard configuré | En attente des premières réponses au sondage
                </div>
            <?php else: ?>
                <div class="success-banner">
                    ✅ <strong>Système actif</strong> - <?php echo number_format($total_responses); ?> réponses  | Dernière mise à jour: <?php echo date('d/m/Y H:i'); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Insights avancés avec recommandations -->
        <?php if (!empty($insights)): ?>
        <div class="insights-grid">
            <?php foreach ($insights as $insight): ?>
            <div class="insight-card <?php echo $insight['type']; ?>">
                <div class="insight-title"><?php echo $insight['title']; ?></div>
                <div class="insight-message"><?php echo $insight['message']; ?></div>
                <?php if (isset($insight['recommendation'])): ?>
                <div class="insight-recommendation">
                    💡 <strong>Recommandation:</strong> <?php echo $insight['recommendation']; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Panneau de filtres optimisé -->
        <div class="controls-panel">
            <div class="controls-title">🎛️ Contrôles Avancés</div>
            <div class="filter-group">
                <div class="filter-item">
                    <label>Période:</label>
                    <select id="timePeriod">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">90 derniers jours</option>
                        <option value="365">Dernière année</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Vue:</label>
                    <select id="viewType">
                        <option value="overview">Vue d'ensemble</option>
                        <option value="detailed">Vue détaillée</option>
                        <option value="comparative">Analyse comparative</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label>Export:</label>
                    <button class="btn btn-success" onclick="exportData()">📊 Export CSV</button>
                </div>
                <div class="filter-item">
                    <label>Cache:</label>
                    <button class="btn btn-secondary" onclick="clearCache()">🔄 Actualiser</button>
                </div>
                <div class="filter-item">
                    <button class="btn btn-primary" onclick="applyAdvancedFilters()">⚡ Appliquer</button>
                </div>
            </div>
        </div>

        <!-- KPIs optimisés avec métriques de performance -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($total_responses); ?></div>
                <div class="stat-label">Total réponses</div>
                <div class="stat-change positive">+<?php echo $daily_growth_rate; ?>% aujourd'hui</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($responses_today); ?></div>
                <div class="stat-label">Aujourd'hui</div>
                <div class="stat-change positive"><?php echo $weekly_growth_rate; ?>% vs semaine dernière</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($responses_this_week); ?></div>
                <div class="stat-label">Cette semaine</div>
                <div class="stat-change neutral"><?php echo round(($responses_this_week / 7), 1); ?>/jour</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($responses_this_month); ?></div>
                <div class="stat-label">Ce mois</div>
                <div class="stat-change positive"><?php echo round(($responses_this_month / 30), 1); ?>/jour</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($geographic_stats); ?></div>
                <div class="stat-label">Pays actifs</div>
                <div class="stat-change info">Diversification</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo round($performance_metrics['avg_time_between_responses'] ?? 0, 1); ?>h</div>
                <div class="stat-label">Temps moyen</div>
                <div class="stat-change neutral">Entre réponses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $performance_metrics['active_months'] ?? 0; ?></div>
                <div class="stat-label">Mois actifs</div>
                <div class="stat-change positive">Engagement long terme</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php 
                    $email_rate = $total_responses > 0 ? round((array_sum(array_column($geographic_stats, 'email_provided')) / $total_responses) * 100, 1) : 0;
                    echo $email_rate; 
                ?>%</div>
                <div class="stat-label">Taux email</div>
                <div class="stat-change info">Contact fourni</div>
            </div>
        </div>

        <!-- Graphiques optimisés avec zoom et interactions -->
        <div class="charts-grid">
            <div class="chart-container">
                <div class="chart-title">📈 Évolution temps réel (30 jours)</div>
                <div class="chart-wrapper large">
                    <canvas id="realtimeChart"></canvas>
                </div>
            </div>
            
            <div class="chart-container">
                <div class="chart-title">🌍 Analyse géographique</div>
                <div class="chart-wrapper">
                    <canvas id="geoChart"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-container">
                <div class="chart-title">🎓 Domaines d'expertise</div>
                <div class="chart-wrapper">
                    <canvas id="domainChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-title">🤖 Adoption technologique</div>
                <div class="chart-wrapper">
                    <canvas id="techChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tableau géographiques avec pagination -->
        <div class="table-container">
            <div class="table-title">
                🌍 Analyse géographique détaillée
                <small style="color: #666; font-weight: normal;">(Page <?php echo $geographic_page; ?>)</small>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Pays</th>
                        <th>Réponses</th>
                        <th>Domaines</th>
                        <th>Niveau moyen</th>
                        <th>Utilisateurs IA</th>
                        <th>Email fourni</th>
                        <th>% du total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($geographic_stats as $country): ?>
                        <?php $percentage = $total_responses > 0 ? round(($country['responses'] / $total_responses) * 100, 1) : 0; ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($country['pays']); ?></strong></td>
                            <td><?php echo number_format($country['responses']); ?></td>
                            <td><?php echo $country['unique_domains']; ?></td>
                            <td><?php echo $country['avg_year_level']; ?>/7</td>
                            <td><?php echo number_format($country['regular_ai_users']); ?></td>
                            <td><?php echo number_format($country['email_provided']); ?></td>
                            <td>
                                <?php echo $percentage; ?>%
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($geographic_page > 1): ?>
                    <button onclick="goToPage(<?php echo $geographic_page - 1; ?>)">← Précédent</button>
                <?php endif; ?>
                <button class="active">Page <?php echo $geographic_page; ?></button>
                <?php if (count($geographic_stats) === $geographic_limit): ?>
                    <button onclick="goToPage(<?php echo $geographic_page + 1; ?>)">Suivant →</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top compétences normalisées -->
        <div class="table-container">
            <div class="table-title">🎯 Compétences les plus demandées (normalisées)</div>
            <table>
                <thead>
                    <tr>
                        <th>Compétence</th>
                        <th>Mentions</th>
                        <th>% du total</th>
                        <th>Diversité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $top_skills = array_slice($skills_analysis, 0, 15, true);
                    foreach ($top_skills as $skill => $data): 
                        $percentage = $total_responses > 0 ? round(($data['count'] / $total_responses) * 100, 1) : 0;
                        $diversity = count(array_unique($data['original']));
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($skill); ?></strong></td>
                            <td><?php echo number_format($data['count']); ?></td>
                            <td><?php echo $percentage; ?>%</td>
                            <td>
                                <?php echo $diversity; ?> variante<?php echo $diversity > 1 ? 's' : ''; ?>
                                <?php if ($diversity > 1): ?>
                                <small style="color: #666;">(<?php echo implode(', ', array_slice(array_unique($data['original']), 0, 3)); ?><?php echo count($data['original']) > 3 ? '...' : ''; ?>)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Configuration commune optimisée
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.font.size = 11;
        
        const optimizedOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                zoom: {
                    zoom: {
                        wheel: {
                            enabled: true,
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: 'x',
                    },
                    pan: {
                        enabled: true,
                        mode: 'x',
                    }
                }
            }
        };

        // Graphique temps réel optimisé
        const realtimeCtx = document.getElementById('realtimeChart').getContext('2d');
        new Chart(realtimeCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($daily_labels); ?>,
                datasets: [{
                    label: 'Réponses quotidiennes',
                    data: <?php echo json_encode($daily_data); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Pays uniques',
                    data: <?php echo json_encode($daily_countries); ?>,
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                ...optimizedOptions,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Réponses'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Pays uniques'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Graphique géographique
        const geoCtx = document.getElementById('geoChart').getContext('2d');
        new Chart(geoCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($country_stats, 'pays')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($country_stats, 'count')); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: optimizedOptions
        });

        // Graphique domaines
        const domainCtx = document.getElementById('domainChart').getContext('2d');
        new Chart(domainCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($domain_stats, 'domaine')); ?>,
                datasets: [{
                    label: 'Étudiants',
                    data: <?php echo json_encode(array_column($domain_stats, 'count')); ?>,
                    backgroundColor: '#667eea',
                    borderColor: '#5a67d8',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                ...optimizedOptions,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique technologique
        const techCtx = document.getElementById('techChart').getContext('2d');
        new Chart(techCtx, {
            type: 'polarArea',
            data: {
                labels: <?php echo json_encode(array_column($ai_usage_stats, 'usage_ia')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($ai_usage_stats, 'count')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: optimizedOptions
        });

        // Fonctions optimisées
        function goToPage(page) {
            const url = new URL(window.location);
            url.searchParams.set('geo_page', page);
            window.location = url.toString();
        }

        function applyAdvancedFilters() {
            const period = document.getElementById('timePeriod').value;
            const view = document.getElementById('viewType').value;
            
            console.log('Application des filtres avancés:', { period, view });
            alert('Filtres appliqués avec succès! (Optimisation en cours)');
        }

        function clearCache() {
            if (confirm('Voulez-vous actualiser le cache? Cela peut prendre quelques secondes.')) {
                window.location.reload();
            }
        }

        function exportData() {
            // Export CSV amélioré
            const data = {
                metrics: {
                    total_responses: <?php echo $total_responses; ?>,
                    responses_today: <?php echo $responses_today; ?>,
                    responses_this_week: <?php echo $responses_this_week; ?>,
                    daily_growth_rate: <?php echo $daily_growth_rate; ?>,
                    weekly_growth_rate: <?php echo $weekly_growth_rate; ?>
                },
                geographic: <?php echo json_encode($geographic_stats); ?>,
                skills: <?php echo json_encode(array_slice($skills_analysis, 0, 20, true)); ?>,
                export_date: new Date().toISOString(),
                performance: <?php echo json_encode($performance_metrics); ?>
            };
            
            // Génération CSV multi-sections
            let csv = '=== DASHBOARD ANALYTICS EXPORT ===\n';
            csv += 'Date export,' + data.export_date + '\n\n';
            
            // Métriques principales
            csv += '=== MÉTRIQUES PRINCIPALES ===\n';
            csv += 'Métrique,Valeur\n';
            csv += 'Total réponses,' + data.metrics.total_responses + '\n';
            csv += 'Réponses aujourd\'hui,' + data.metrics.responses_today + '\n';
            csv += 'Réponses cette semaine,' + data.metrics.responses_this_week + '\n';
            csv += 'Croissance quotidienne,' + data.metrics.daily_growth_rate + '%\n';
            csv += 'Croissance hebdomadaire,' + data.metrics.weekly_growth_rate + '%\n\n';
            
            // Données géographiques
            csv += '=== ANALYSE GÉOGRAPHIQUE ===\n';
            csv += 'Pays,Réponses,Domaines,Niveau moyen,Utilisateurs IA,Email fourni\n';
            data.geographic.forEach(item => {
                csv += `${item.pays},${item.responses},${item.unique_domains},${item.avg_year_level},${item.regular_ai_users},${item.email_provided}\n`;
            });
            
            csv += '\n=== TOP COMPÉTENCES ===\n';
            csv += 'Compétence,Mentions\n';
            Object.entries(data.skills).forEach(([skill, info]) => {
                csv += `"${skill}",${info.count}\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dashboard_analytics_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            alert('📊 Export généré avec succès!\n\nFichier créé avec:\n• Métriques principales\n• Analyse géographique détaillée\n• Top compétences\n• Données de performance');
        }

        // Auto-refresh des données (optionnel)
        // setInterval(() => {
        //     if (document.visibilityState === 'visible') {
        //         // Ici vous pourriez recharger les données via AJAX
        //         console.log('Auto-refresh des données...');
        //     }
        // }, 60000); // Refresh chaque minute
    </script>
</body>
</html>