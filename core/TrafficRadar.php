<?php

class TrafficRadar
{
    private static $startTime = null;

    public static function startTimer()
    {
        self::$startTime = microtime(true);
    }

    /**
     * Inspect and record every hit, crawler, bot, and visitor
     */
    public static function recordHit($statusCode = null)
    {
        try {
            if (PHP_SAPI === 'cli') return;

            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($uri, PHP_URL_PATH) ?? '';

            // Skip static assets (CSS, JS, images) from cluttering logs
            if (preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|woff|woff2|ttf|map)$/i', $path)) {
                return;
            }

            // Skip the admin panel: the radar measures public visitors & crawlers.
            if (strpos('/' . ltrim($path, '/'), '/admin') === 0) {
                return;
            }

            if ($statusCode === null) {
                $code = http_response_code();
                $statusCode = ($code === false || $code <= 0) ? 200 : (int) $code;
            }
            $statusCode = (int) $statusCode;

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Prefer the real client IP when the host sits behind a proxy/CDN.
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'] as $h) {
                if (!empty($_SERVER[$h])) {
                    $candidate = trim(explode(',', (string) $_SERVER[$h])[0]);
                    if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                        $ipAddress = $candidate;
                        break;
                    }
                }
            }

            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $referer = $_SERVER['HTTP_REFERER'] ?? null;
            $sessionId = session_id() ?: (Session::get('session_token') ?: null);

            $classification = self::classifyVisitor($userAgent);

            $responseTimeMs = 0;
            if (self::$startTime !== null) {
                $responseTimeMs = (int) round((microtime(true) - self::$startTime) * 1000);
            }

            $db = new Database();
            $db->query("
                INSERT INTO bot_traffic_logs 
                (visitor_type, bot_name, ip_address, request_uri, http_method, status_code, user_agent, referer, session_id, response_time_ms, created_at)
                VALUES 
                (:v_type, :b_name, :ip, :uri, :method, :status, :ua, :ref, :sess, :rtime, CURRENT_TIMESTAMP)
            ", [
                ':v_type' => $classification['type'],
                ':b_name' => $classification['name'],
                ':ip'     => $ipAddress,
                ':uri'    => substr($uri, 0, 500),
                ':method' => substr($method, 0, 10),
                ':status' => (int) $statusCode,
                ':ua'     => substr($userAgent, 0, 1000),
                ':ref'    => $referer ? substr($referer, 0, 500) : null,
                ':sess'   => $sessionId,
                ':rtime'  => $responseTimeMs,
            ]);
        } catch (Throwable $e) {
            error_log("TrafficRadar Error: " . $e->getMessage());
        }
    }

    /**
     * Categorize Visitor / Spider / AI Bot / Human
     */
    public static function classifyVisitor($ua)
    {
        $ua = strtolower((string) $ua);

        if (empty($ua)) {
            return ['type' => 'malicious_bot', 'name' => 'Unknown / Empty User-Agent'];
        }

        // 1. Search Engine Spiders
        if (str_contains($ua, 'googlebot-news')) return ['type' => 'search_engine', 'name' => 'Google News Spider 🗞️'];
        if (str_contains($ua, 'googlebot-image')) return ['type' => 'search_engine', 'name' => 'Google Image Spider 🖼️'];
        if (str_contains($ua, 'googlebot')) return ['type' => 'search_engine', 'name' => 'Google Search Spider 🔍'];
        if (str_contains($ua, 'bingbot') || str_contains($ua, 'msnbot')) return ['type' => 'search_engine', 'name' => 'Microsoft Bingbot 🔍'];
        if (str_contains($ua, 'yandexbot') || str_contains($ua, 'yandex')) return ['type' => 'search_engine', 'name' => 'Yandex Spider 🇷🇺'];
        if (str_contains($ua, 'baiduspider')) return ['type' => 'search_engine', 'name' => 'Baidu Spider 🇨🇳'];
        if (str_contains($ua, 'duckduckbot')) return ['type' => 'search_engine', 'name' => 'DuckDuckGo Bot 🦆'];
        if (str_contains($ua, 'applebot')) return ['type' => 'search_engine', 'name' => 'Apple Siri & Search Bot 🍎'];

        // 2. AI Crawlers & LLM Ingestion Bots
        if (str_contains($ua, 'gptbot') || str_contains($ua, 'chatgpt-user') || str_contains($ua, 'openai')) return ['type' => 'ai_crawler', 'name' => 'OpenAI GPTBot 🤖'];
        if (str_contains($ua, 'claudebot') || str_contains($ua, 'anthropic')) return ['type' => 'ai_crawler', 'name' => 'Anthropic ClaudeBot 🧠'];
        if (str_contains($ua, 'perplexitybot')) return ['type' => 'ai_crawler', 'name' => 'Perplexity AI 🔮'];
        if (str_contains($ua, 'bytespider')) return ['type' => 'ai_crawler', 'name' => 'ByteDance TikTok Spider 🎵'];
        if (str_contains($ua, 'meta-externalagent') || str_contains($ua, 'facebookexternalhit')) return ['type' => 'ai_crawler', 'name' => 'Meta / Facebook Bot 📘'];
        if (str_contains($ua, 'cohere-ai')) return ['type' => 'ai_crawler', 'name' => 'Cohere AI Spider'];

        // 3. SEO Analyzers & Crawlers
        if (str_contains($ua, 'ahrefsbot')) return ['type' => 'seo_crawler', 'name' => 'Ahrefs SEO Crawler 📊'];
        if (str_contains($ua, 'semrushbot')) return ['type' => 'seo_crawler', 'name' => 'Semrush SEO Bot 📈'];
        if (str_contains($ua, 'dotbot') || str_contains($ua, 'rogerbot')) return ['type' => 'seo_crawler', 'name' => 'Moz DotBot 🌐'];
        if (str_contains($ua, 'screaming frog')) return ['type' => 'seo_crawler', 'name' => 'Screaming Frog SEO'];

        // 4. Security & Vulnerability Scanners
        if (str_contains($ua, 'sqlmap') || str_contains($ua, 'nikto') || str_contains($ua, 'nmap') || str_contains($ua, 'masscan') || str_contains($ua, 'wpscan')) {
            return ['type' => 'security_scanner', 'name' => 'Security Vulnerability Scanner ⚠️'];
        }

        // 5. Automated Scripts & Scrapers
        if (str_contains($ua, 'curl') || str_contains($ua, 'python') || str_contains($ua, 'wget') || str_contains($ua, 'guzzle') || str_contains($ua, 'postman')) {
            return ['type' => 'automated_scraper', 'name' => 'Automated Script / cURL 💻'];
        }

        // 6. Legitimate Human Visitors (Browsers)
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return ['type' => 'human_visitor', 'name' => 'Mobile Browser 📱'];
        }
        if (str_contains($ua, 'chrome')) return ['type' => 'human_visitor', 'name' => 'Google Chrome (Desktop) 💻'];
        if (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) return ['type' => 'human_visitor', 'name' => 'Apple Safari 🧭'];
        if (str_contains($ua, 'firefox')) return ['type' => 'human_visitor', 'name' => 'Mozilla Firefox 🦊'];
        if (str_contains($ua, 'edg')) return ['type' => 'human_visitor', 'name' => 'Microsoft Edge 🌊'];

        return ['type' => 'human_visitor', 'name' => 'Web Browser 🌐'];
    }
}
