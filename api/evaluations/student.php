<?php
/**
 * Student Evaluations API
 * Permet aux étudiants de passer des tests et voir leurs résultats via API
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];
$testsPath = __DIR__ . '/../../htdocs/pages/admin/evaluations/database/tests.json';
$questionsPath = __DIR__ . '/../../htdocs/pages/admin/evaluations/database/questions.json';
$resultsPath = __DIR__ . '/../../htdocs/pages/admin/evaluations/database/results.json';

switch ($method) {
    case 'GET':
        handleGet($testsPath, $questionsPath, $resultsPath);
        break;
    case 'POST':
        handlePost($testsPath, $questionsPath, $resultsPath);
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/evaluations/student
 * Récupère les tests disponibles ou les résultats de l'étudiant
 */
function handleGet($testsPath, $questionsPath, $resultsPath) {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $type = $_GET['type'] ?? 'available_tests';
    
    switch ($type) {
        case 'available_tests':
            return getAvailableTests($testsPath, $resultsPath, $user);
        case 'test_questions':
            return getTestQuestions($testsPath, $questionsPath, $user);
        case 'results':
            return getStudentResults($resultsPath, $user);
        case 'result_detail':
            return getResultDetail($resultsPath, $questionsPath, $user);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: available_tests, test_questions, results, result_detail', 400);
    }
}

/**
 * POST /api/evaluations/student
 * Soumet une réponse ou démarre un test
 */
function handlePost($testsPath, $questionsPath, $resultsPath) {
    $user = requireAuth();
    
    if ($user['type'] === 'api_key') {
        sendResponse('error', null, 'Cette fonctionnalité nécessite une authentification utilisateur', 403);
    }
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'submit_answer';
    
    switch ($type) {
        case 'start_test':
            return startTest($testsPath, $resultsPath, $user, $data);
        case 'submit_answer':
            return submitAnswer($resultsPath, $user, $data);
        case 'submit_test':
            return submitTest($testsPath, $questionsPath, $resultsPath, $user, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: start_test, submit_answer, submit_test', 400);
    }
}

// ============ AVAILABLE TESTS ============

function getAvailableTests($testsPath, $resultsPath, $user) {
    $tests = readJsonFile($testsPath);
    $results = readJsonFile($resultsPath);
    
    // Filtrer les tests publiés
    $availableTests = array_filter($tests, function($test) {
        return isset($test['status']) && $test['status'] === 'published';
    });
    
    // Ajouter les informations de progression pour chaque test
    foreach ($availableTests as &$test) {
        $testResults = array_filter($results, function($r) use ($user, $test) {
            return $r['user_id'] == $user['user_id'] && $r['test_id'] == $test['id'];
        });
        
        $test['attempts'] = count($testResults);
        $test['best_score'] = !empty($testResults) ? max(array_column($testResults, 'score')) : 0;
        $test['passed'] = !empty($testResults) && max(array_column($testResults, 'score')) >= $test['passing_score'];
        $test['can_retake'] = $test['attempts'] < $test['max_attempts'];
        
        // Masquer les réponses correctes
        unset($test['questions']);
    }
    
    sendResponse('success', array_values($availableTests), 'Tests disponibles récupérés avec succès');
}

// ============ TEST QUESTIONS ============

function getTestQuestions($testsPath, $questionsPath, $user) {
    $testId = $_GET['test_id'] ?? null;
    
    if (!$testId) {
        sendResponse('error', null, 'test_id requis', 400);
    }
    
    $tests = readJsonFile($testsPath);
    $questions = readJsonFile($questionsPath);
    
    // Récupérer le test
    $test = null;
    foreach ($tests as $t) {
        if ($t['id'] == $testId) {
            $test = $t;
            break;
        }
    }
    
    if (!$test) {
        sendResponse('error', null, 'Test non trouvé', 404);
    }
    
    if ($test['status'] !== 'published') {
        sendResponse('error', null, 'Ce test n\'est pas disponible', 403);
    }
    
    // Récupérer les questions du test
    $testQuestions = array_filter($questions, function($q) use ($testId) {
        return isset($q['test_id']) && $q['test_id'] == $testId;
    });
    
    // Mélanger les questions si demandé
    if ($test['randomize_questions']) {
        shuffle($testQuestions);
    }
    
    // Trier par ordre
    usort($testQuestions, function($a, $b) {
        return ($a['order'] ?? 0) - ($b['order'] ?? 0);
    });
    
    // Masquer les réponses correctes
    foreach ($testQuestions as &$question) {
        unset($question['correct_answer']);
    }
    
    sendResponse('success', [
        'test' => [
            'id' => $test['id'],
            'title' => $test['title'],
            'description' => $test['description'],
            'duration' => $test['duration'],
            'passing_score' => $test['passing_score'],
            'instructions' => $test['instructions']
        ],
        'questions' => array_values($testQuestions)
    ], 'Questions du test récupérées avec succès');
}

// ============ STUDENT RESULTS ============

function getStudentResults($resultsPath, $user) {
    $results = readJsonFile($resultsPath);
    
    // Filtrer par utilisateur
    $studentResults = array_filter($results, function($r) use ($user) {
        return isset($r['user_id']) && $r['user_id'] == $user['user_id'];
    });
    
    // Trier par date (plus récent en premier)
    usort($studentResults, function($a, $b) {
        return ($b['completed_at'] ?? 0) - ($a['completed_at'] ?? 0);
    });
    
    sendResponse('success', array_values($studentResults), 'Résultats récupérés avec succès');
}

function getResultDetail($resultsPath, $questionsPath, $user) {
    $resultId = $_GET['result_id'] ?? null;
    
    if (!$resultId) {
        sendResponse('error', null, 'result_id requis', 400);
    }
    
    $results = readJsonFile($resultsPath);
    $questions = readJsonFile($questionsPath);
    
    // Récupérer le résultat
    $result = null;
    foreach ($results as $r) {
        if ($r['id'] == $resultId && $r['user_id'] == $user['user_id']) {
            $result = $r;
            break;
        }
    }
    
    if (!$result) {
        sendResponse('error', null, 'Résultat non trouvé', 404);
    }
    
    // Récupérer les questions avec les réponses
    $testQuestions = array_filter($questions, function($q) use ($result) {
        return isset($q['test_id']) && $q['test_id'] == $result['test_id'];
    });
    
    // Construire la réponse détaillée
    $detailedResult = [
        'result' => $result,
        'answers' => []
    ];
    
    foreach ($testQuestions as $question) {
        $userAnswer = null;
        foreach ($result['answers'] as $answer) {
            if ($answer['question_id'] == $question['id']) {
                $userAnswer = $answer;
                break;
            }
        }
        
        $detailedResult['answers'][] = [
            'question' => [
                'id' => $question['id'],
                'question_text' => $question['question_text'],
                'question_type' => $question['question_type'],
                'options' => $question['options'],
                'points' => $question['points']
            ],
            'user_answer' => $userAnswer['answer'] ?? null,
            'is_correct' => $userAnswer['is_correct'] ?? false,
            'correct_answer' => $result['show_results_immediately'] ? $question['correct_answer'] : null,
            'explanation' => $result['show_results_immediately'] ? $question['explanation'] : null
        ];
    }
    
    sendResponse('success', $detailedResult, 'Détail du résultat récupéré avec succès');
}

// ============ START TEST ============

function startTest($testsPath, $resultsPath, $user, $data) {
    validateRequired($data, ['test_id']);
    
    $tests = readJsonFile($testsPath);
    $results = readJsonFile($resultsPath);
    
    // Récupérer le test
    $test = null;
    foreach ($tests as $t) {
        if ($t['id'] == $data['test_id']) {
            $test = $t;
            break;
        }
    }
    
    if (!$test) {
        sendResponse('error', null, 'Test non trouvé', 404);
    }
    
    if ($test['status'] !== 'published') {
        sendResponse('error', null, 'Ce test n\'est pas disponible', 403);
    }
    
    // Vérifier le nombre de tentatives
    $testResults = array_filter($results, function($r) use ($user, $test) {
        return $r['user_id'] == $user['user_id'] && $r['test_id'] == $test['id'] && isset($r['completed_at']);
    });
    
    if (count($testResults) >= $test['max_attempts']) {
        sendResponse('error', null, 'Nombre maximum de tentatives atteint', 403);
    }
    
    // Créer une nouvelle tentative
    $result = [
        'id' => time(),
        'user_id' => $user['user_id'],
        'test_id' => $test['id'],
        'test_title' => $test['title'],
        'started_at' => time(),
        'completed_at' => null,
        'answers' => [],
        'score' => 0,
        'total_points' => 0,
        'passed' => false,
        'status' => 'in_progress'
    ];
    
    $results[] = $result;
    
    if (file_put_contents($resultsPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('start_test', [
            'user_id' => $user['user_id'],
            'test_id' => $test['id'],
            'result_id' => $result['id']
        ]);
        sendResponse('success', [
            'result_id' => $result['id'],
            'test' => [
                'id' => $test['id'],
                'title' => $test['title'],
                'duration' => $test['duration'],
                'passing_score' => $test['passing_score']
            ]
        ], 'Test démarré avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la création de la tentative', 500);
    }
}

// ============ SUBMIT ANSWER ============

function submitAnswer($resultsPath, $user, $data) {
    validateRequired($data, ['result_id', 'question_id', 'answer']);
    
    $results = readJsonFile($resultsPath);
    
    // Récupérer le résultat
    $result = null;
    $resultKey = null;
    foreach ($results as $key => $r) {
        if ($r['id'] == $data['result_id'] && $r['user_id'] == $user['user_id']) {
            $result = &$results[$key];
            $resultKey = $key;
            break;
        }
    }
    
    if (!$result) {
        sendResponse('error', null, 'Résultat non trouvé', 404);
    }
    
    if (isset($result['completed_at'])) {
        sendResponse('error', null, 'Ce test est déjà terminé', 400);
    }
    
    // Vérifier si la question a déjà été répondue
    $existingAnswer = null;
    foreach ($result['answers'] as &$answer) {
        if ($answer['question_id'] == $data['question_id']) {
            $existingAnswer = &$answer;
            break;
        }
    }
    
    if ($existingAnswer) {
        $existingAnswer['answer'] = $data['answer'];
        $existingAnswer['answered_at'] = time();
    } else {
        $result['answers'][] = [
            'question_id' => intval($data['question_id']),
            'answer' => $data['answer'],
            'answered_at' => time()
        ];
    }
    
    if (file_put_contents($resultsPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        sendResponse('success', null, 'Réponse enregistrée');
    } else {
        sendResponse('error', null, 'Erreur lors de l\'enregistrement', 500);
    }
}

// ============ SUBMIT TEST ============

function submitTest($testsPath, $questionsPath, $resultsPath, $user, $data) {
    validateRequired($data, ['result_id']);
    
    $tests = readJsonFile($testsPath);
    $questions = readJsonFile($questionsPath);
    $results = readJsonFile($resultsPath);
    
    // Récupérer le résultat
    $result = null;
    foreach ($results as &$r) {
        if ($r['id'] == $data['result_id'] && $r['user_id'] == $user['user_id']) {
            $result = &$r;
            break;
        }
    }
    
    if (!$result) {
        sendResponse('error', null, 'Résultat non trouvé', 404);
    }
    
    if (isset($result['completed_at'])) {
        sendResponse('error', null, 'Ce test est déjà terminé', 400);
    }
    
    // Récupérer le test
    $test = null;
    foreach ($tests as $t) {
        if ($t['id'] == $result['test_id']) {
            $test = $t;
            break;
        }
    }
    
    if (!$test) {
        sendResponse('error', null, 'Test non trouvé', 404);
    }
    
    // Récupérer les questions du test
    $testQuestions = array_filter($questions, function($q) use ($result) {
        return isset($q['test_id']) && $q['test_id'] == $result['test_id'];
    });
    
    // Calculer le score
    $totalPoints = 0;
    $earnedPoints = 0;
    
    foreach ($testQuestions as $question) {
        $totalPoints += $question['points'];
        
        $userAnswer = null;
        foreach ($result['answers'] as $answer) {
            if ($answer['question_id'] == $question['id']) {
                $userAnswer = $answer;
                break;
            }
        }
        
        $isCorrect = false;
        if ($userAnswer) {
            if ($question['question_type'] === 'multiple_choice') {
                $isCorrect = $userAnswer['answer'] == $question['correct_answer'];
            } elseif ($question['question_type'] === 'true_false') {
                $isCorrect = $userAnswer['answer'] == $question['correct_answer'];
            } elseif ($question['question_type'] === 'short_answer') {
                $isCorrect = strtolower(trim($userAnswer['answer'])) === strtolower(trim($question['correct_answer']));
            }
        }
        
        if ($isCorrect) {
            $earnedPoints += $question['points'];
        }
        
        // Mettre à jour la réponse avec le résultat
        foreach ($result['answers'] as &$answer) {
            if ($answer['question_id'] == $question['id']) {
                $answer['is_correct'] = $isCorrect;
                break;
            }
        }
    }
    
    $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    
    $result['score'] = $score;
    $result['total_points'] = $totalPoints;
    $result['earned_points'] = $earnedPoints;
    $result['passed'] = $score >= $test['passing_score'];
    $result['completed_at'] = time();
    $result['status'] = 'completed';
    
    if (file_put_contents($resultsPath, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('submit_test', [
            'user_id' => $user['user_id'],
            'test_id' => $test['id'],
            'result_id' => $result['id'],
            'score' => $score,
            'passed' => $result['passed']
        ]);
        sendResponse('success', [
            'result_id' => $result['id'],
            'score' => $score,
            'passed' => $result['passed'],
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints
        ], 'Test soumis avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la soumission', 500);
    }
}
