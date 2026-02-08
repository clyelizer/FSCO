<?php
/**
 * Evaluations Management API (SQL Version)
 * Permet de gérer les tests et évaluations via MySQL
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/admin/evaluations
 * Liste les tests, questions ou catégories
 */
function handleGet() {
    $user = requireAuth();
    $db = Database::getInstance();
    
    $type = $_GET['type'] ?? 'tests';
    
    switch ($type) {
        case 'tests':
            return getTests($db);
        case 'questions':
            return getQuestions($db);
        case 'categories':
            return getCategories($db);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: tests, questions, categories', 400);
    }
}

/**
 * POST /api/admin/evaluations
 * Crée un test, une question ou une catégorie
 */
function handlePost() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'test';
    
    switch ($type) {
        case 'test':
            return createTest($db, $data, $user);
        case 'question':
            return createQuestion($db, $data, $user);
        case 'category':
            return createCategory($db, $data, $user);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: test, question, category', 400);
    }
}

/**
 * PUT /api/admin/evaluations
 * Met à jour un test, une question ou une catégorie
 */
function handlePut() {
    $user = requirePermission('write');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'test';
    
    switch ($type) {
        case 'test':
            return updateTest($db, $data);
        case 'question':
            return updateQuestion($db, $data);
        case 'category':
            return updateCategory($db, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: test, question, category', 400);
    }
}

/**
 * DELETE /api/admin/evaluations
 * Supprime un test ou une question
 */
function handleDelete() {
    $user = requirePermission('delete');
    $db = Database::getInstance();
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'test';
    
    switch ($type) {
        case 'test':
            return deleteTest($db, $data);
        case 'question':
            return deleteQuestion($db, $data);
        case 'category':
            return deleteCategory($db, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: test, question, category', 400);
    }
}

// ============ TESTS (exam_examens) ============

function getTests($db) {
    if (isset($_GET['id'])) {
        $test = $db->fetchOne("SELECT * FROM exam_examens WHERE id = ?", [$_GET['id']]);
        if (!$test) sendResponse('error', null, 'Test non trouvé', 404);
        
        // Ajouter les questions
        $test['questions'] = $db->fetchAll(
            "SELECT q.* FROM exam_questions q 
             JOIN exam_examen_questions eq ON q.id = eq.question_id 
             WHERE eq.examen_id = ? ORDER BY eq.ordre", 
            [$test['id']]
        );
        sendResponse('success', $test);
    }
    
    $tests = $db->fetchAll("SELECT * FROM exam_examens ORDER BY created_at DESC");
    sendResponse('success', $tests);
}

function createTest($db, $data, $user) {
    validateRequired($data, ['titre', 'duree_minutes']);
    
    $id = $db->insert(
        "INSERT INTO exam_examens (titre, description, duree_minutes, note_passage, statut, created_by, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        [
            sanitizeInput($data['titre']),
            $data['description'] ?? '',
            intval($data['duree_minutes']),
            intval($data['note_passage'] ?? 50),
            $data['statut'] ?? 'draft',
            $user['user_id'] ?? 0
        ]
    );
    
    logApiAction('create_test', ['id' => $id, 'titre' => $data['titre']]);
    sendResponse('success', ['id' => $id], 'Test créé avec succès');
}

function updateTest($db, $data) {
    validateRequired($data, ['id']);
    $db->update(
        "UPDATE exam_examens SET titre = ?, description = ?, duree_minutes = ?, note_passage = ?, statut = ? WHERE id = ?",
        [
            sanitizeInput($data['titre']),
            $data['description'] ?? '',
            intval($data['duree_minutes']),
            intval($data['note_passage'] ?? 50),
            $data['statut'] ?? 'draft',
            $data['id']
        ]
    );
    logApiAction('update_test', ['id' => $data['id']]);
    sendResponse('success', null, 'Test mis à jour');
}

function deleteTest($db, $data) {
    validateRequired($data, ['id']);
    $db->delete("DELETE FROM exam_examens WHERE id = ?", [$data['id']]);
    logApiAction('delete_test', ['id' => $data['id']]);
    sendResponse('success', null, 'Test supprimé');
}

// ============ QUESTIONS (exam_questions) ============

function getQuestions($db) {
    if (isset($_GET['id'])) {
        $q = $db->fetchOne("SELECT * FROM exam_questions WHERE id = ?", [$_GET['id']]);
        if (!$q) sendResponse('error', null, 'Question non trouvée', 404);
        sendResponse('success', $q);
    }
    
    $sql = "SELECT * FROM exam_questions";
    $params = [];
    if (isset($_GET['categorie_id'])) {
        $sql .= " WHERE categorie_id = ?";
        $params[] = $_GET['categorie_id'];
    }
    
    $questions = $db->fetchAll($sql . " ORDER BY created_at DESC", $params);
    sendResponse('success', $questions);
}

function createQuestion($db, $data, $user) {
    validateRequired($data, ['enonce', 'type_question', 'reponse_correcte']);
    
    $id = $db->insert(
        "INSERT INTO exam_questions (titre, enonce, type_question, options_json, reponse_correcte, explication, categorie_id, created_by) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [
            sanitizeInput($data['titre'] ?? ''),
            $data['enonce'],
            $data['type_question'],
            is_array($data['options']) ? json_encode($data['options']) : ($data['options_json'] ?? null),
            $data['reponse_correcte'],
            $data['explication'] ?? '',
            intval($data['categorie_id'] ?? 0),
            $user['user_id'] ?? 0
        ]
    );
    
    logApiAction('create_question', ['id' => $id]);
    sendResponse('success', ['id' => $id], 'Question créée');
}

function updateQuestion($db, $data) {
    validateRequired($data, ['id']);
    $db->update(
        "UPDATE exam_questions SET titre = ?, enonce = ?, type_question = ?, options_json = ?, reponse_correcte = ?, explication = ?, categorie_id = ? WHERE id = ?",
        [
            sanitizeInput($data['titre'] ?? ''),
            $data['enonce'],
            $data['type_question'],
            is_array($data['options']) ? json_encode($data['options']) : ($data['options_json'] ?? null),
            $data['reponse_correcte'],
            $data['explication'] ?? '',
            intval($data['categorie_id'] ?? 0),
            $data['id']
        ]
    );
    logApiAction('update_question', ['id' => $data['id']]);
    sendResponse('success', null, 'Question mise à jour');
}

function deleteQuestion($db, $data) {
    validateRequired($data, ['id']);
    $db->delete("DELETE FROM exam_questions WHERE id = ?", [$data['id']]);
    logApiAction('delete_question', ['id' => $data['id']]);
    sendResponse('success', null, 'Question supprimée');
}

// ============ CATEGORIES (exam_categories) ============

function getCategories($db) {
    $categories = $db->fetchAll("SELECT * FROM exam_categories ORDER BY nom ASC");
    sendResponse('success', $categories);
}

function createCategory($db, $data, $user) {
    validateRequired($data, ['nom']);
    $id = $db->insert(
        "INSERT INTO exam_categories (nom, description, couleur, created_by) VALUES (?, ?, ?, ?)",
        [$data['nom'], $data['description'] ?? '', $data['couleur'] ?? '#007bff', $user['user_id'] ?? 0]
    );
    sendResponse('success', ['id' => $id], 'Catégorie créée');
}

function updateCategory($db, $data) {
    validateRequired($data, ['id', 'nom']);
    $db->update(
        "UPDATE exam_categories SET nom = ?, description = ?, couleur = ? WHERE id = ?",
        [$data['nom'], $data['description'] ?? '', $data['couleur'] ?? '#007bff', $data['id']]
    );
    sendResponse('success', null, 'Catégorie mise à jour');
}

function deleteCategory($db, $data) {
    validateRequired($data, ['id']);
    $db->delete("DELETE FROM exam_categories WHERE id = ?", [$data['id']]);
    sendResponse('success', null, 'Catégorie supprimée');
}
