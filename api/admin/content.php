<?php
/**
 * Content Management API
 * Permet de gérer les témoignages et FAQ via API
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];
$testimonialsPath = __DIR__ . '/../../htdocs/pages/admin/data/testimonials.json';
$faqPath = __DIR__ . '/../../htdocs/pages/admin/data/faq.json';
$faqBlogsPath = __DIR__ . '/../../htdocs/pages/admin/data/faq_blogs.json';
$faqFormationsPath = __DIR__ . '/../../htdocs/pages/admin/data/faq_formations.json';
$faqRessourcesPath = __DIR__ . '/../../htdocs/pages/admin/data/faq_ressources.json';

switch ($method) {
    case 'GET':
        handleGet($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath);
        break;
    case 'POST':
        handlePost($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath);
        break;
    case 'PUT':
        handlePut($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath);
        break;
    case 'DELETE':
        handleDelete($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath);
        break;
    default:
        sendResponse('error', null, 'Méthode non autorisée', 405);
}

/**
 * GET /api/admin/content
 * Récupère les témoignages ou FAQ
 */
function handleGet($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath) {
    $user = requireAuth();
    
    $type = $_GET['type'] ?? 'testimonials';
    
    switch ($type) {
        case 'testimonials':
            return getTestimonials($testimonialsPath);
        case 'faq':
            return getFAQ($faqPath);
        case 'faq_blogs':
            return getFAQ($faqBlogsPath);
        case 'faq_formations':
            return getFAQ($faqFormationsPath);
        case 'faq_ressources':
            return getFAQ($faqRessourcesPath);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: testimonials, faq, faq_blogs, faq_formations, faq_ressources', 400);
    }
}

/**
 * POST /api/admin/content
 * Crée un témoignage ou une FAQ
 */
function handlePost($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath) {
    $user = requirePermission('write');
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'testimonials';
    
    switch ($type) {
        case 'testimonial':
            return createTestimonial($testimonialsPath, $data);
        case 'faq':
            return createFAQ($faqPath, $data);
        case 'faq_blogs':
            return createFAQ($faqBlogsPath, $data);
        case 'faq_formations':
            return createFAQ($faqFormationsPath, $data);
        case 'faq_ressources':
            return createFAQ($faqRessourcesPath, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: testimonial, faq, faq_blogs, faq_formations, faq_ressources', 400);
    }
}

/**
 * PUT /api/admin/content
 * Met à jour un témoignage ou une FAQ
 */
function handlePut($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath) {
    $user = requirePermission('write');
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'testimonials';
    
    switch ($type) {
        case 'testimonial':
            return updateTestimonial($testimonialsPath, $data);
        case 'faq':
            return updateFAQ($faqPath, $data);
        case 'faq_blogs':
            return updateFAQ($faqBlogsPath, $data);
        case 'faq_formations':
            return updateFAQ($faqFormationsPath, $data);
        case 'faq_ressources':
            return updateFAQ($faqRessourcesPath, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: testimonial, faq, faq_blogs, faq_formations, faq_ressources', 400);
    }
}

/**
 * DELETE /api/admin/content
 * Supprime un témoignage ou une FAQ
 */
function handleDelete($testimonialsPath, $faqPath, $faqBlogsPath, $faqFormationsPath, $faqRessourcesPath) {
    $user = requirePermission('delete');
    
    $data = getJsonInput();
    $type = $data['type'] ?? 'testimonials';
    
    switch ($type) {
        case 'testimonial':
            return deleteTestimonial($testimonialsPath, $data);
        case 'faq':
            return deleteFAQ($faqPath, $data);
        case 'faq_blogs':
            return deleteFAQ($faqBlogsPath, $data);
        case 'faq_formations':
            return deleteFAQ($faqFormationsPath, $data);
        case 'faq_ressources':
            return deleteFAQ($faqRessourcesPath, $data);
        default:
            sendResponse('error', null, 'Type invalide. Utilisez: testimonial, faq, faq_blogs, faq_formations, faq_ressources', 400);
    }
}

// ============ TESTIMONIALS ============

function getTestimonials($testimonialsPath) {
    $testimonials = readJsonFile($testimonialsPath);
    
    // Filtrer par ID si spécifié
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $testimonial = null;
        foreach ($testimonials as $t) {
            if ($t['id'] == $id) {
                $testimonial = $t;
                break;
            }
        }
        
        if (!$testimonial) {
            sendResponse('error', null, 'Témoignage non trouvé', 404);
        }
        
        sendResponse('success', $testimonial, 'Témoignage récupéré avec succès');
    }
    
    // Filtrer par statut
    if (isset($_GET['status'])) {
        $status = sanitizeInput($_GET['status']);
        $testimonials = array_filter($testimonials, function($t) use ($status) {
            return isset($t['status']) && $t['status'] === $status;
        });
    }
    
    // Trier par date
    usort($testimonials, function($a, $b) {
        return ($b['created_at'] ?? 0) - ($a['created_at'] ?? 0);
    });
    
    sendResponse('success', $testimonials, 'Témoignages récupérés avec succès');
}

function createTestimonial($testimonialsPath, $data) {
    validateRequired($data, ['name', 'content', 'rating']);
    
    $testimonials = readJsonFile($testimonialsPath);
    
    $id = time();
    while (isset($testimonials[$id])) {
        $id++;
    }
    
    $testimonial = [
        'id' => $id,
        'name' => sanitizeInput($data['name']),
        'role' => sanitizeInput($data['role'] ?? ''),
        'company' => sanitizeInput($data['company'] ?? ''),
        'content' => $data['content'],
        'rating' => intval($data['rating']),
        'avatar' => sanitizeInput($data['avatar'] ?? ''),
        'status' => sanitizeInput($data['status'] ?? 'pending'), // pending, approved, rejected
        'created_at' => time()
    ];
    
    $testimonials[] = $testimonial;
    
    if (file_put_contents($testimonialsPath, json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('create_testimonial', ['id' => $id, 'name' => $testimonial['name']]);
        sendResponse('success', $testimonial, 'Témoignage créé avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

function updateTestimonial($testimonialsPath, $data) {
    validateRequired($data, ['id']);
    
    $testimonials = readJsonFile($testimonialsPath);
    $id = $data['id'];
    $found = false;
    
    foreach ($testimonials as &$testimonial) {
        if ($testimonial['id'] == $id) {
            $found = true;
            
            if (isset($data['name'])) $testimonial['name'] = sanitizeInput($data['name']);
            if (isset($data['role'])) $testimonial['role'] = sanitizeInput($data['role']);
            if (isset($data['company'])) $testimonial['company'] = sanitizeInput($data['company']);
            if (isset($data['content'])) $testimonial['content'] = $data['content'];
            if (isset($data['rating'])) $testimonial['rating'] = intval($data['rating']);
            if (isset($data['avatar'])) $testimonial['avatar'] = sanitizeInput($data['avatar']);
            if (isset($data['status'])) $testimonial['status'] = sanitizeInput($data['status']);
            
            break;
        }
    }
    
    if (!$found) {
        sendResponse('error', null, 'Témoignage non trouvé', 404);
    }
    
    if (file_put_contents($testimonialsPath, json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('update_testimonial', ['id' => $id]);
        sendResponse('success', $testimonial, 'Témoignage mis à jour avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

function deleteTestimonial($testimonialsPath, $data) {
    validateRequired($data, ['id']);
    
    $testimonials = readJsonFile($testimonialsPath);
    $id = $data['id'];
    $found = false;
    
    foreach ($testimonials as $key => $testimonial) {
        if ($testimonial['id'] == $id) {
            $found = true;
            unset($testimonials[$key]);
            break;
        }
    }
    
    if (!$found) {
        sendResponse('error', null, 'Témoignage non trouvé', 404);
    }
    
    $testimonials = array_values($testimonials);
    
    if (file_put_contents($testimonialsPath, json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('delete_testimonial', ['id' => $id]);
        sendResponse('success', null, 'Témoignage supprimé avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la suppression', 500);
    }
}

// ============ FAQ ============

function getFAQ($faqPath) {
    $faqs = readJsonFile($faqPath);
    
    // Filtrer par ID si spécifié
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $faq = null;
        foreach ($faqs as $f) {
            if ($f['id'] == $id) {
                $faq = $f;
                break;
            }
        }
        
        if (!$faq) {
            sendResponse('error', null, 'FAQ non trouvée', 404);
        }
        
        sendResponse('success', $faq, 'FAQ récupérée avec succès');
    }
    
    // Filtrer par catégorie
    if (isset($_GET['category'])) {
        $category = sanitizeInput($_GET['category']);
        $faqs = array_filter($faqs, function($f) use ($category) {
            return isset($f['category']) && $f['category'] === $category;
        });
    }
    
    // Trier par ordre
    usort($faqs, function($a, $b) {
        return ($a['order'] ?? 0) - ($b['order'] ?? 0);
    });
    
    sendResponse('success', $faqs, 'FAQ récupérées avec succès');
}

function createFAQ($faqPath, $data) {
    validateRequired($data, ['question', 'answer']);
    
    $faqs = readJsonFile($faqPath);
    
    $id = time();
    while (isset($faqs[$id])) {
        $id++;
    }
    
    $faq = [
        'id' => $id,
        'question' => sanitizeInput($data['question']),
        'answer' => $data['answer'],
        'category' => sanitizeInput($data['category'] ?? 'General'),
        'order' => intval($data['order'] ?? 0),
        'created_at' => time()
    ];
    
    $faqs[] = $faq;
    
    if (file_put_contents($faqPath, json_encode($faqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('create_faq', ['id' => $id, 'question' => $faq['question']]);
        sendResponse('success', $faq, 'FAQ créée avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

function updateFAQ($faqPath, $data) {
    validateRequired($data, ['id']);
    
    $faqs = readJsonFile($faqPath);
    $id = $data['id'];
    $found = false;
    
    foreach ($faqs as &$faq) {
        if ($faq['id'] == $id) {
            $found = true;
            
            if (isset($data['question'])) $faq['question'] = sanitizeInput($data['question']);
            if (isset($data['answer'])) $faq['answer'] = $data['answer'];
            if (isset($data['category'])) $faq['category'] = sanitizeInput($data['category']);
            if (isset($data['order'])) $faq['order'] = intval($data['order']);
            
            break;
        }
    }
    
    if (!$found) {
        sendResponse('error', null, 'FAQ non trouvée', 404);
    }
    
    if (file_put_contents($faqPath, json_encode($faqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('update_faq', ['id' => $id]);
        sendResponse('success', $faq, 'FAQ mise à jour avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la sauvegarde', 500);
    }
}

function deleteFAQ($faqPath, $data) {
    validateRequired($data, ['id']);
    
    $faqs = readJsonFile($faqPath);
    $id = $data['id'];
    $found = false;
    
    foreach ($faqs as $key => $faq) {
        if ($faq['id'] == $id) {
            $found = true;
            unset($faqs[$key]);
            break;
        }
    }
    
    if (!$found) {
        sendResponse('error', null, 'FAQ non trouvée', 404);
    }
    
    $faqs = array_values($faqs);
    
    if (file_put_contents($faqPath, json_encode($faqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
        logApiAction('delete_faq', ['id' => $id]);
        sendResponse('success', null, 'FAQ supprimée avec succès');
    } else {
        sendResponse('error', null, 'Erreur lors de la suppression', 500);
    }
}
