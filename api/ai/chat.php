<?php
/**
 * AI Central Chat API - Gemini Edition
 * Point d'entrée principal pour les interactions avec l'Agent IA (Web/WA).
 */
require_once __DIR__ . '/../includes/api_common.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    sendResponse('error', null, 'Méthode non autorisée. Utilisez POST.', 405);
}

// 1. Authentification requise (Utilisateur ou Agent)
$user = requireAuth();
$db = Database::getInstance();

// 2. Récupérer les données
$data = getJsonInput();
validateRequired($data, ['message']);

$message = $data['message'];
$source = $data['source'] ?? ($user['type'] === 'api_key' ? 'api' : 'web');
$agentCodeName = $data['agent_code_name'] ?? 'fsco_master_agent';

// 3. Configuration Gemini
$geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '';
if (empty($geminiApiKey)) {
    sendResponse('error', null, 'Configuration Gemini manquante (GEMINI_API_KEY).', 500);
}

try {
    // 4. Identifier l'Agent
    $agent = $db->fetchOne("SELECT id, config_json FROM ai_agents WHERE code_name = ?", [$agentCodeName]);
    if (!$agent) {
        sendResponse('error', null, "Agent '$agentCodeName' non trouvé.", 404);
    }

    // 5. Récupérer le Contexte du Site (Dynamique)
    $blogs = $db->fetchAll("SELECT id, title, category FROM blogs ORDER BY created_at DESC LIMIT 5");
    $formations = $db->fetchAll("SELECT id, title, price FROM formations ORDER BY created_at DESC LIMIT 5");
    
    $context = [
        'site_url' => 'https://fsco.gt.tc',
        'recent_blogs' => $blogs,
        'recent_formations' => $formations,
        'user_role' => $user['type'] === 'jwt' ? $user['role'] : 'agent'
    ];

    // 6. Historique de conversation (récupérer les 10 dernières interactions)
    $historyRecords = $db->fetchAll(
        "SELECT request_text, response_text FROM ai_interactions 
         WHERE agent_id = ? AND (user_id = ? OR source = ?) 
         ORDER BY created_at DESC LIMIT 10",
        [
            $agent['id'],
            ($user['type'] === 'jwt') ? $user['user_id'] : null,
            $source
        ]
    );
    
    $historyPrompt = "";
    foreach (array_reverse($historyRecords) as $record) {
        $historyPrompt .= "User: " . $record['request_text'] . "\nAI: " . $record['response_text'] . "\n";
    }

    // 7. Préparer le Prompt Système
    $systemPrompt = "Tu es l'Agent IA de FSCO (Formation, Sécurité, Consulting, Organisation).
    Tu aides l'administrateur à gérer le site.
    
    CONTEXTE DU SITE:
    " . json_encode($context, JSON_PRETTY_PRINT) . "
    
    CONSIGNES:
    - Réponds en français de manière professionnelle et concise.
    - Si l'utilisateur demande une modification (créer un blog, changer un prix, etc.), propose le changement au format JSON dans un bloc ```json ... ```.
    - Pour créer une demande, utilise ce format: {\"action\": \"create_blog\", \"data\": {...}}.
    - Demande TOUJOURS confirmation avant d'agir.
    
    HISTORIQUE RÉCENT:
    $historyPrompt";

    // 8. Appel à l'API Gemini (REST)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $geminiApiKey;
    
    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $systemPrompt . "\nUser: " . $message]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 2048
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour Infinity Free si nécessaire

    $rawResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Erreur API Gemini ($httpCode) : " . $rawResponse);
    }

    $jsonResponse = json_decode($rawResponse, true);
    $aiTextResponse = $jsonResponse['candidates'][0]['content']['parts'][0]['text'] ?? "Désolé, je n'ai pas pu générer de réponse.";

    // 9. Analyser si la réponse contient une action JSON
    $actionCreated = false;
    $requestId = null;
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $aiTextResponse, $matches)) {
        $actionData = json_decode($matches[1], true);
        if ($actionData && isset($actionData['action'])) {
            // Créer automatiquement une entrée dans ai_requests (status pending)
            // Note: generateRequestId() doit être accessible ou recréé ici
            $year = date('Y');
            $count = $db->fetchOne("SELECT COUNT(*) as total FROM ai_requests WHERE id LIKE 'REQ-$year-%'")['total'];
            $requestId = "REQ-$year-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            
            $db->insert(
                "INSERT INTO ai_requests (id, type, status, created_by, payload_json, created_at) 
                 VALUES (?, ?, 'pending_confirmation', ?, ?, NOW())",
                [
                    $requestId,
                    $actionData['action'],
                    ($user['type'] === 'jwt') ? $user['email'] : ($data['phone'] ?? 'agent_web'),
                    json_encode($actionData['data'] ?? [])
                ]
            );
            $actionCreated = true;
            $aiTextResponse .= "\n\n✅ Une demande de modification a été créée (ID: $requestId). Veuillez la confirmer pour l'appliquer.";
        }
    }

    // 10. Enregistrer l'interaction
    $interactionId = $db->insert(
        "INSERT INTO ai_interactions (agent_id, user_id, source, request_text, response_text, metadata_json, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        [
            $agent['id'],
            ($user['type'] === 'jwt') ? $user['user_id'] : null,
            $source,
            $message,
            $aiTextResponse,
            json_encode([
                'model' => 'gemini-2.0-flash',
                'request_id' => $requestId,
                'action_created' => $actionCreated
            ])
        ]
    );

    // 11. Log d'audit
    logApiAction('ai_chat_interaction', [
        'interaction_id' => $interactionId,
        'agent' => $agentCodeName,
        'source' => $source,
        'request_id' => $requestId
    ]);

    // 12. Répondre
    sendResponse('success', [
        'interaction_id' => $interactionId,
        'response' => $aiTextResponse,
        'request_id' => $requestId,
        'action_created' => $actionCreated
    ], 'Réponse de l\'IA générée via Gemini');

} catch (Exception $e) {
    sendResponse('error', null, "Erreur lors de l'interaction IA : " . $e->getMessage(), 500);
}
