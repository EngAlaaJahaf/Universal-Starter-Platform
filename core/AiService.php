<?php

/**
 * Universal AI Service Engine
 *
 * Centralized, multi-provider AI orchestrator supporting:
 * - Google Gemini, OpenAI, Groq, DeepSeek, Omniroute, Custom Local/Ollama, OpenCode, Free Web
 * - Multi-turn Chat & Single Prompts
 * - Structured JSON Generation
 * - Automatic Multi-Provider Failover Chain (Auto-fallback on error / rate limit / downtime)
 * - Live Health Checks and Latency Metrics
 * - RAG and Embedding / Context Ingestion
 */
class AiService
{
    private static $config = null;

    /**
     * Load AI Configuration
     */
    public static function getConfig()
    {
        if (self::$config === null) {
            $file = dirname(__DIR__) . '/config/ai_providers.php';
            if (is_file($file)) {
                self::$config = require $file;
            } else {
                self::$config = [
                    'default_provider' => 'omniroute',
                    'fallback_enabled' => true,
                    'failover_order'   => ['omniroute', 'gemini', 'groq', 'deepseek', 'openai', 'custom_api', 'opencode', 'mymemory'],
                    'providers'        => []
                ];
            }
        }
        return self::$config;
    }

    /**
     * Get the active default AI Provider from Settings or Config
     */
    public static function getActiveProvider()
    {
        if (class_exists('Settings')) {
            $p = Settings::get('ai_provider');
            if (!empty($p)) {
                return $p;
            }
        }
        $cfg = self::getConfig();
        return $cfg['default_provider'] ?? 'omniroute';
    }

    /**
     * Check if automatic fallback is enabled
     */
    public static function isFallbackEnabled()
    {
        if (class_exists('Settings')) {
            $fb = Settings::get('ai_fallback_enabled', '1');
            return !empty($fb) && $fb != '0';
        }
        return true;
    }

    /**
     * Send Chat / Prompt Request with Automatic Multi-Provider Failover
     *
     * @param array|string $promptOrMessages Single string prompt OR array of messages [['role' => 'user', 'content' => '...']]
     * @param array $options Options:
     *   - 'provider': specific provider (defaults to active provider)
     *   - 'model': specific model override
     *   - 'system_prompt': custom system prompt
     *   - 'temperature': float (0.0 to 1.0)
     *   - 'max_tokens': int
     *   - 'json_mode': bool (force JSON return)
     *   - 'fallback': bool (enable/disable auto failover, default true)
     *   - 'context': string (optional context data to append)
     *
     * @return array ['success' => bool, 'content' => string, 'provider' => string, 'model' => string, 'duration_ms' => int, 'error' => string|null]
     */
    public static function chat($promptOrMessages, array $options = [])
    {
        $cfg = self::getConfig();
        $primaryProvider = $options['provider'] ?? self::getActiveProvider();
        $fallbackOn = $options['fallback'] ?? self::isFallbackEnabled();

        // Normalize messages structure
        $messages = [];
        $systemPrompt = $options['system_prompt'] ?? (class_exists('Settings') ? Settings::get('ai_system_prompt', '') : '');

        if (!empty($systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        if (is_string($promptOrMessages)) {
            $userContent = $promptOrMessages;
            if (!empty($options['context'])) {
                $userContent = "Context Information:\n" . $options['context'] . "\n\nUser Query: " . $userContent;
            }
            $messages[] = ['role' => 'user', 'content' => $userContent];
        } elseif (is_array($promptOrMessages)) {
            foreach ($promptOrMessages as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = $msg;
                }
            }
        }

        // Build execution chain: primary provider first, then failover list
        $chain = [$primaryProvider];
        if ($fallbackOn) {
            $failoverOrder = $cfg['failover_order'] ?? ['omniroute', 'gemini', 'groq', 'deepseek', 'openai', 'custom_api', 'opencode'];
            foreach ($failoverOrder as $prov) {
                if (!in_array($prov, $chain, true) && $prov !== 'mymemory') {
                    $chain[] = $prov;
                }
            }
        }

        $attempts = [];
        $startTime = microtime(true);

        foreach ($chain as $provider) {
            $res = self::callProvider($provider, $messages, $options);
            if ($res['success']) {
                $res['duration_ms'] = (int) round((microtime(true) - $startTime) * 1000);
                $res['attempts'] = $attempts;
                return $res;
            }
            $attempts[$provider] = $res['error'] ?? 'Provider failed';
        }

        return [
            'success'     => false,
            'content'     => '',
            'provider'    => $primaryProvider,
            'model'       => '',
            'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
            'error'       => 'All AI providers in failover chain failed: ' . implode(' | ', array_map(function ($k, $v) {
                return "{$k}: {$v}";
            }, array_keys($attempts), $attempts)),
            'attempts'    => $attempts
        ];
    }

    /**
     * Convenient shorthand for simple single-prompt text generation
     */
    public static function prompt($prompt, array $options = [])
    {
        $res = self::chat($prompt, $options);
        return $res['success'] ? $res['content'] : false;
    }

    /**
     * Generate Structured JSON response
     */
    public static function generateJson($prompt, array $options = [])
    {
        $options['json_mode'] = true;
        $res = self::chat($prompt, $options);
        if (!$res['success']) {
            return false;
        }

        $content = trim($res['content']);
        // Strip markdown ```json wrappers if present
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        $json = json_decode($content, true);
        return is_array($json) ? $json : false;
    }

    /**
     * Call specific provider implementation
     */
    private static function callProvider($provider, array $messages, array $options)
    {
        switch ($provider) {
            case 'omniroute':
                return self::callOmniroute($messages, $options);
            case 'gemini':
                return self::callGemini($messages, $options);
            case 'groq':
                return self::callGroq($messages, $options);
            case 'deepseek':
                return self::callDeepSeek($messages, $options);
            case 'openai':
                return self::callOpenAi($messages, $options);
            case 'custom_api':
                return self::callCustomApi($messages, $options);
            case 'opencode':
                return self::callOpencode($messages, $options);
            default:
                return ['success' => false, 'error' => "Unknown AI Provider: {$provider}"];
        }
    }

    /**
     * Google Gemini Implementation
     */
    private static function callGemini(array $messages, array $options)
    {
        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('gemini_api_key', '') : '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Google Gemini API Key is missing.'];
        }

        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('gemini_model', 'gemini-3.7-flash') : 'gemini-3.7-flash');
        $temp = isset($options['temperature']) ? (float)$options['temperature'] : 0.3;

        // Convert messages to Gemini format
        $geminiContents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = ['parts' => [['text' => $msg['content']]]];
            } else {
                $geminiContents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        if (empty($geminiContents)) {
            return ['success' => false, 'error' => 'No messages provided for Gemini.'];
        }

        $payload = [
            'contents'         => $geminiContents,
            'generationConfig' => [
                'temperature' => $temp,
            ]
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = $systemInstruction;
        }

        if (!empty($options['json_mode'])) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $modelsToTry = [$model, 'gemini-3.6-flash', 'gemini-3.5-flash', 'gemini-2.5-pro'];
        $lastError = '';

        foreach ($modelsToTry as $currentModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key=" . $apiKey;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $data = json_decode($res, true);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($text)) {
                    return [
                        'success'  => true,
                        'content'  => $text,
                        'provider' => 'gemini',
                        'model'    => $currentModel
                    ];
                }
            }

            $lastError = "Gemini ({$currentModel}) HTTP {$httpCode}: " . ($res ?: $err);
        }

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * Groq Cloud Implementation
     */
    private static function callGroq(array $messages, array $options)
    {
        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('groq_api_key', '') : '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'Groq API Key is missing.'];
        }

        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('groq_model', 'llama-3.3-70b-versatile') : 'llama-3.3-70b-versatile');
        $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

        return self::callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, $messages, $options, 'groq');
    }

    /**
     * DeepSeek Implementation
     */
    private static function callDeepSeek(array $messages, array $options)
    {
        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('deepseek_api_key', '') : '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'DeepSeek API Key is missing.'];
        }

        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('deepseek_model', 'deepseek-chat') : 'deepseek-chat');
        $endpoint = 'https://api.deepseek.com/v1/chat/completions';

        return self::callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, $messages, $options, 'deepseek');
    }

    /**
     * OpenAI (ChatGPT) Implementation
     */
    private static function callOpenAi(array $messages, array $options)
    {
        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('openai_api_key', '') : '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OpenAI API Key is missing.'];
        }

        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('openai_model', 'gpt-4o-mini') : 'gpt-4o-mini');
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        return self::callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, $messages, $options, 'openai');
    }

    /**
     * Omniroute Gateway Implementation
     */
    private static function callOmniroute(array $messages, array $options)
    {
        $endpoint = $options['endpoint'] ?? (class_exists('Settings') ? Settings::get('omniroute_endpoint', 'http://127.0.0.1:8080/v1') : 'http://127.0.0.1:8080/v1');
        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('omniroute_api_key', '') : '');
        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('omniroute_model', 'antigravity/gemini-3.7-flash-high') : 'antigravity/gemini-3.7-flash-high');

        $endpoint = rtrim($endpoint, '/');
        if (!str_ends_with($endpoint, '/chat/completions')) {
            $endpoint .= '/chat/completions';
        }

        return self::callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, $messages, $options, 'omniroute');
    }

    /**
     * Custom OpenAI-Compatible / Local Endpoint (Ollama / OpenRouter / LocalAI)
     */
    private static function callCustomApi(array $messages, array $options)
    {
        $endpoint = $options['endpoint'] ?? (class_exists('Settings') ? Settings::get('custom_api_endpoint', '') : '');
        if (empty($endpoint)) {
            return ['success' => false, 'error' => 'Custom API endpoint is not configured.'];
        }

        $apiKey = $options['api_key'] ?? (class_exists('Settings') ? Settings::get('custom_api_key', '') : '');
        $model = $options['model'] ?? (class_exists('Settings') ? Settings::get('custom_api_model', 'custom') : 'custom');

        $endpoint = rtrim($endpoint, '/');
        if (!str_ends_with($endpoint, '/chat/completions')) {
            $endpoint .= '/chat/completions';
        }

        return self::callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, $messages, $options, 'custom_api');
    }

    /**
     * OpenCode Zen Gateway
     */
    private static function callOpencode(array $messages, array $options)
    {
        // Shorthand tunnel into opencode gateway
        $endpoint = 'http://127.0.0.1:8080/v1/chat/completions';
        return self::callOpenAiCompatibleEndpoint($endpoint, '', 'opencode/gemini-3.7-flash', $messages, $options, 'opencode');
    }

    /**
     * Universal OpenAI-Compatible HTTP Client
     */
    private static function callOpenAiCompatibleEndpoint($endpoint, $apiKey, $model, array $messages, array $options, $providerName)
    {
        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => isset($options['temperature']) ? (float)$options['temperature'] : 0.3,
        ];

        if (!empty($options['json_mode'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        if (!empty($options['max_tokens'])) {
            $payload['max_tokens'] = (int)$options['max_tokens'];
        }

        $headers = ['Content-Type: application/json'];
        if (!empty($apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            if (!empty($content)) {
                return [
                    'success'  => true,
                    'content'  => $content,
                    'provider' => $providerName,
                    'model'    => $model
                ];
            }
        }

        $errMsg = "{$providerName} ({$model}) HTTP {$httpCode}: " . ($res ?: $err);
        return ['success' => false, 'error' => $errMsg];
    }

    /**
     * Live Test Specific AI Provider with sample prompt and measure response time
     */
    public static function testProvider($provider, array $overrides = [])
    {
        $samplePrompt = "Hello AI! Please reply with exactly: 'OK 200 PROCEED' and nothing else.";
        $startTime = microtime(true);

        $res = self::chat($samplePrompt, array_merge(['provider' => $provider, 'fallback' => false], $overrides));
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($res['success']) {
            return [
                'ok'          => true,
                'provider'    => $provider,
                'model'       => $res['model'],
                'duration_ms' => $durationMs,
                'response'    => trim($res['content']),
                'message'     => "الاتصال سليم وناجح. زمن الاستجابة: {$durationMs}ms"
            ];
        }

        return [
            'ok'          => false,
            'provider'    => $provider,
            'duration_ms' => $durationMs,
            'error'       => $res['error'],
            'message'     => "فشل الاتصال بالمزود: " . $res['error']
        ];
    }
}
