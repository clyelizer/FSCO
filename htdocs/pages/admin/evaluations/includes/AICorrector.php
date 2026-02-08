<?php
/**
 * AICorrector - Correction assistée par IA Multi-Provider
 * 
 * Stratégie de Fallback (Cascade):
 * 1. Groq (Llama 3.3 70B) - Ultra-rapide (~300ms)
 * 2. Cerebras (Llama 3.3 70B) - Rapide (~500ms)
 * 3. Mistral (Mistral Small) - Fiable (~700ms)
 * 4. Gemini (2.5 Flash) - Secours (~1.9s)
 */

class AICorrector
{
    private $providers = [];
    private $logFile;

    public function __construct()
    {
        $this->logFile = __DIR__ . '/../../../../logs/ai_correction.log';
        $this->initProviders();
    }

    private function initProviders()
    {
        // 1. Groq (Fastest)
        if (!empty($_ENV['GROQ_API_KEY'])) {
            $this->providers[] = [
                'name' => 'Groq (Llama 3.3)',
                'type' => 'openai_compatible',
                'url' => 'https://api.groq.com/openai/v1/chat/completions',
                'model' => 'llama-3.3-70b-versatile',
                'key' => $_ENV['GROQ_API_KEY']
            ];
        }

        // 2. Cerebras
        if (!empty($_ENV['CEREBRAS_API_KEY'])) {
            $this->providers[] = [
                'name' => 'Cerebras (Llama 3.3)',
                'type' => 'openai_compatible',
                'url' => 'https://api.cerebras.ai/v1/chat/completions',
                'model' => 'llama-3.3-70b',
                'key' => $_ENV['CEREBRAS_API_KEY']
            ];
        }

        // 3. Mistral
        if (!empty($_ENV['MISTRAL_API_KEY'])) {
            $this->providers[] = [
                'name' => 'Mistral (Small)',
                'type' => 'openai_compatible',
                'url' => 'https://api.mistral.ai/v1/chat/completions',
                'model' => 'mistral-small-latest',
                'key' => $_ENV['MISTRAL_API_KEY']
            ];
        }

        // 4. Gemini (Fallback)
        if (!empty($_ENV['GEMINI_API_KEY'])) {
            $this->providers[] = [
                'name' => 'Google Gemini (2.5 Flash)',
                'type' => 'gemini',
                'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
                'key' => $_ENV['GEMINI_API_KEY']
            ];
        }
    }

    public function correctAnswer($questionText, $expectedAnswer, $studentAnswer, $maxPoints)
    {
        // Réponse vide = 0 points direct
        if (empty(trim($studentAnswer))) {
            return [
                'score' => 0,
                'justification' => 'Aucune réponse fournie.',
                'success' => true,
                'provider' => 'System'
            ];
        }

        $prompt = $this->buildPrompt($questionText, $expectedAnswer, $studentAnswer, $maxPoints);
        $errors = [];

        // Itération sur les providers (Fallback Strategy)
        foreach ($this->providers as $provider) {
            try {
                $start = microtime(true);
                $this->log("Tentative avec {$provider['name']}...");

                if ($provider['type'] === 'gemini') {
                    $response = $this->callGemini($provider, $prompt);
                    $result = $this->parseGeminiResponse($response, $maxPoints);
                } else {
                    $response = $this->callOpenAICompatible($provider, $prompt);
                    $result = $this->parseOpenAIResponse($response, $maxPoints);
                }

                $duration = round((microtime(true) - $start) * 1000);
                $this->log("✅ Succès {$provider['name']} en {$duration}ms");

                return [
                    'score' => $result['score'],
                    'justification' => $result['justification'],
                    'success' => true,
                    'provider' => $provider['name']
                ];

            } catch (Exception $e) {
                $duration = round((microtime(true) - $start) * 1000);
                $errorMsg = "❌ Échec {$provider['name']} ({$duration}ms): " . $e->getMessage();
                $this->log($errorMsg);
                $errors[] = $errorMsg;
                // On continue vers le prochain provider
            }
        }

        // Si tous les providers ont échoué
        return [
            'score' => 0,
            'justification' => 'Erreur technique (Tous les services IA indisponibles). Correction manuelle requise.',
            'success' => false,
            'errors' => $errors
        ];
    }

    private function buildPrompt($q, $e, $s, $m)
    {
        $q = addslashes($q);
        $e = addslashes($e);
        $s = addslashes($s);

        return "Tu es un correcteur d'examen strict.
        
        CONTEXTE:
        - Question: $q
        - Réponse attendue: $e
        - Réponse étudiant: $s
        - Barème: $m points
        
        TACHE:
        1. Compare la réponse étudiant à la réponse attendue.
        2. Attribue une note de 0 à $m (décimales autorisées).
        3. Justifie brièvement.
        
        FORMAT DE SORTIE (JSON STRICT):
        {
            \"score\": 0.0,
            \"justification\": \"...\"
        }";
    }

    private function callOpenAICompatible($provider, $prompt)
    {
        $data = [
            'model' => $provider['model'],
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un correcteur automatique. Réponds UNIQUEMENT en JSON valide.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.1,
            'max_tokens' => 300
        ];

        return $this->makeRequest($provider['url'], $data, [
            'Authorization: Bearer ' . $provider['key']
        ]);
    }

    private function callGemini($provider, $prompt)
    {
        $url = $provider['url'] . '?key=' . $provider['key'];
        $data = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 300,
                'response_mime_type' => 'application/json'
            ]
        ];

        return $this->makeRequest($url, $data);
    }

    private function makeRequest($url, $data, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout court pour passer vite au suivant

        $headers = array_merge(['Content-Type: application/json'], $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            $msg = $err['error']['message'] ?? substr($response, 0, 100);
            throw new Exception("HTTP $httpCode: $msg");
        }

        return $response;
    }

    private function parseOpenAIResponse($response, $maxPoints)
    {
        $decoded = json_decode($response, true);
        if (!isset($decoded['choices'][0]['message']['content'])) {
            throw new Exception('Structure OpenAI invalide');
        }
        return $this->extractScoreAndJustification($decoded['choices'][0]['message']['content'], $maxPoints);
    }

    private function parseGeminiResponse($response, $maxPoints)
    {
        $decoded = json_decode($response, true);
        if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Structure Gemini invalide');
        }
        return $this->extractScoreAndJustification($decoded['candidates'][0]['content']['parts'][0]['text'], $maxPoints);
    }

    private function extractScoreAndJustification($jsonString, $maxPoints)
    {
        // Nettoyage JSON
        $jsonString = preg_replace('/^```json\s*|\s*```$/i', '', trim($jsonString));
        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON invalide: ' . json_last_error_msg());
        }

        if (!isset($data['score']) || !isset($data['justification'])) {
            throw new Exception('Champs score/justification manquants');
        }

        $score = floatval($data['score']);
        // Bornage du score
        $score = max(0, min($maxPoints, $score));

        return [
            'score' => $score,
            'justification' => $data['justification']
        ];
    }

    private function log($msg)
    {
        if (!file_exists(dirname($this->logFile))) {
            @mkdir(dirname($this->logFile), 0777, true);
        }
        file_put_contents($this->logFile, "[" . date('Y-m-d H:i:s') . "] $msg" . PHP_EOL, FILE_APPEND);
    }
}
?>