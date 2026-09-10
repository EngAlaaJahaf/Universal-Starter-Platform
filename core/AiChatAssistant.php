<?php

class AiChatAssistant
{
    /**
     * Comfortable per-visitor free budget (configurable in admin settings).
     */
    public static function freeLimit()
    {
        $limit = (int) Settings::get('ai_assistant_free_limit', '3');
        return $limit >= 0 ? $limit : 3;
    }

    public static function enabled()
    {
        $active = Settings::get('ai_assistant_enabled', '1');
        return !empty($active);
    }

    /** Today's date in the site timezone (where a "day" resets). */
    public static function todaySiteDate()
    {
        return (new DateTime('now', new DateTimeZone(site_timezone())))->format('Y-m-d');
    }

    /**
     * Whether the daily-login-quota migration columns exist on users.
     * Fail-open (no quota) until the admin runs migrate_ai_daily_quota.sql.
     */
    public static function quotaColumnsReady($db)
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }
        try {
            $found = $db->fetchAll(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
                 AND COLUMN_NAME IN ('ai_quota_date','ai_quota_used')"
            );
            $cols = array_column($found, 'COLUMN_NAME');
            $ready = in_array('ai_quota_date', $cols, true) && in_array('ai_quota_used', $cols, true);
        } catch (Throwable $e) {
            $ready = false;
        }
        return $ready;
    }

    /** Used count today for a user (resets when the stored day changes). */
    public static function quotaUsedToday($db, $userId)
    {
        $today = self::todaySiteDate();
        if (!self::quotaColumnsReady($db)) {
            return ['date' => $today, 'used' => 0];
        }
        $user = $db->fetch('SELECT ai_quota_date, ai_quota_used FROM users WHERE id = ? LIMIT 1', [(int) $userId]);
        $used = 0;
        if ($user && $user['ai_quota_date'] === $today) {
            $used = (int) $user['ai_quota_used'];
        }
        return ['date' => $today, 'used' => $used];
    }

    /** Increment today's counter after a successful answer. */
    public static function bumpQuota($db, $userId)
    {
        if (!self::quotaColumnsReady($db)) {
            return;
        }
        $today = self::todaySiteDate();
        $db->query(
            "INSERT INTO users (id, ai_quota_date, ai_quota_used) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE
               ai_quota_date = VALUES(ai_quota_date),
               ai_quota_used = IF(ai_quota_date = VALUES(ai_quota_date), ai_quota_used + 1, 1)",
            [(int) $userId, $today]
        );
    }

    /**
     * Retrieve the published-article context relevant to the question
     * (lightweight RAG over the site's own content).
     */
    public static function retrieveContext($db, $question, $limit = 4, $pageSlug = '')
    {
        $limit = max(1, min(6, (int) $limit));
        $rows = [];

        // 1) Force-include the article currently open on the page (if any).
        if ($pageSlug !== '') {
            $page = $db->fetch(
                "SELECT id, slug, title, title_ar, excerpt, excerpt_ar, content, content_ar
                 FROM articles
                 WHERE slug = ? AND status = 'published' AND published_at IS NOT NULL
                 LIMIT 1",
                [$pageSlug]
            );
            if ($page) {
                $rows[] = $page;
            }
        }

        // 2) Lightweight keyword RAG over the site's own published content.
        $terms = preg_split('/[\s،,؟?.:!\n]+/u', strip_tags($question));
        $terms = array_values(array_filter(array_map(function ($t) {
            return trim($t);
        }, $terms), function ($t) {
            return mb_strlen($t) >= 3;
        }));
        $terms = array_slice(array_unique($terms), 0, 6);

        $already = $rows ? array_map(function ($r) {
            return (int) $r['id'];
        }, $rows) : [];

        if (!empty($terms)) {
            $where = [];
            $params = [];
            $fields = ['title', 'title_ar', 'excerpt', 'excerpt_ar', 'content', 'content_ar'];
            foreach ($terms as $t) {
                foreach ($fields as $f) {
                    $where[] = "`{$f}` LIKE ?";
                    $params[] = '%' . $t . '%';
                }
            }
            if ($already) {
                $placeholders = implode(',', array_fill(0, count($already), '?'));
                $where[] = 'id NOT IN (' . $placeholders . ')';
                $params = array_merge($params, $already);
            }

            $sql = "SELECT id, slug, title, title_ar, excerpt, excerpt_ar, content, content_ar
                    FROM articles
                    WHERE status = 'published' AND published_at IS NOT NULL
                      AND (" . implode(' OR ', $where) . ")
                    ORDER BY is_featured DESC, is_editors_pick DESC, views_count DESC, published_at DESC
                    LIMIT " . (int) $limit . ";
                    ";
            $rows = array_merge($rows, $db->fetchAll($sql, $params));
        }

        $rows = array_slice($rows, 0, $limit);

        $textParts = [];
        $sources = [];
        foreach ($rows as $row) {
            $title = !empty($row['title_ar']) ? $row['title_ar'] : $row['title'];
            $body = strip_tags((string) (!empty($row['content_ar']) ? $row['content_ar'] : $row['content']));
            $body = preg_replace('/\s+/u', ' ', $body);
            $snippet = mb_substr($body, 0, 1400, 'UTF-8');
            $textParts[] = "- «{$title}» (/article/{$row['slug']})\n" . $snippet;
            $sources[] = [
                'title' => $title,
                'url'   => app_url('article/' . $row['slug'])
            ];
        }

        return [
            'articles' => $rows,
            'text'     => implode("\n\n", $textParts),
            'sources'  => $sources
        ];
    }

    public static function ask($question, array $history = [], array $options = [])
    {
        $db = new Database();
        $keys = [
            'ai_provider', 'openai_api_key', 'openai_model', 'gemini_api_key', 'gemini_model',
            'omniroute_endpoint', 'omniroute_api_key', 'omniroute_model',
            'groq_api_key', 'groq_model', 'deepseek_api_key', 'deepseek_model',
            'custom_api_endpoint', 'custom_api_key', 'custom_api_model',
            'ai_fallback_enabled', 'ai_temperature',
            'ai_assistant_provider', 'ai_assistant_model', 'ai_assistant_temperature',
            'ai_assistant_tone', 'ai_assistant_context_articles', 'ai_assistant_include_page',
            'ai_assistant_fallback_enabled'
        ];
        $inClause = "'" . implode("','", $keys) . "'";
        $settingsRow = $db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ({$inClause})");
        $cfg = array_column($settingsRow, 'value', 'key');

        $activeProvider = ($cfg['ai_assistant_provider'] ?? 'default') !== 'default'
            ? $cfg['ai_assistant_provider']
            : ($cfg['ai_provider'] ?? 'omniroute');

        if (($cfg['ai_assistant_temperature'] ?? '') !== '' && is_numeric($cfg['ai_assistant_temperature'])) {
            $cfg['ai_temperature'] = (string) (float) $cfg['ai_assistant_temperature'];
        }

        $overrideModel = trim((string) ($cfg['ai_assistant_model'] ?? ''));
        if ($overrideModel !== '') {
            $cfg[$activeProvider . '_model'] = $overrideModel;
        }

        $limit = (int) ($cfg['ai_assistant_context_articles'] ?? 4);
        $pageSlug = (($cfg['ai_assistant_include_page'] ?? '1') === '1')
            ? trim((string) ($options['page_slug'] ?? ''))
            : '';

        $context = self::retrieveContext($db, $question, $limit, $pageSlug);

        $messages = [
            ['role' => 'system', 'content' => self::systemPrompt((string) ($cfg['ai_assistant_tone'] ?? 'balanced'))]
        ];
        if (!empty($context['text'])) {
            $messages[] = ['role' => 'system', 'content' => 'محتوى عصب التقنية المتاح للإجابة (إن وجد مطابقاً):' . "\n" . mb_substr($context['text'], 0, 7000, 'UTF-8')];
        }
        foreach (array_slice($history, -8) as $turn) {
            $role = $turn['role'] ?? '';
            $content = trim((string) ($turn['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => mb_substr($content, 0, 800, 'UTF-8')];
        }
        $messages[] = ['role' => 'user', 'content' => mb_substr($question, 0, 500, 'UTF-8')];

        $fallbackOn = ($cfg['ai_assistant_fallback_enabled'] ?? ($cfg['ai_fallback_enabled'] ?? '1')) == '1';

        $res = self::callProvider($activeProvider, $messages, $cfg);
        if ($res['success']) {
            $res['sources'] = $context['sources'];
            return $res;
        }

        $attempts = [$activeProvider => $res['error'] ?? 'فشل الاتصال بالمزود الافتراضي.'];

        if ($fallbackOn) {
            $order = ['omniroute', 'gemini', 'openai', 'groq', 'deepseek', 'custom_api'];
            foreach ($order as $provider) {
                if ($provider === $activeProvider || !self::providerReady($provider, $cfg)) {
                    continue;
                }
                $res = self::callProvider($provider, $messages, $cfg);
                if ($res['success']) {
                    $res['sources'] = $context['sources'];
                    $res['attempts'] = $attempts;
                    return $res;
                }
                $attempts[$provider] = $res['error'] ?? 'فشل الاتصال بالمزود الاحتياطي.';
            }
        }

        return [
            'success' => false,
            'error'   => 'تعذر الحصول على إجابة من مزودي الذكاء الاصطناعي حالياً. حاول مجدداً بعد قليل.',
            'attempts'=> $attempts
        ];
    }

    private static function systemPrompt($tone = 'balanced')
    {
        $toneLine = [
            'formal'     => 'كن رسمياً وصحفياً: لغة خبرية دقيقة، جُمل موجزة، بلا مزاح أو مصطلحات عامية.',
            'friendly'   => 'كن ودوداً وخفيف الظل: نبرة ودّية قريبة من القارئ، مع بقاء المحتوى مفيداً ودقيقاً.',
            'educational'=> 'كن معلّماً مبسّطاً: اشرح المصطلحات التقنية بلغة سهلة يناسبها الزائر غير المختص، مع أمثلة عملية قصيرة.',
            'balanced'   => 'كن متوازناً: واضحاً ومرتّباً وسهل العبارة، ودقيقاً تقنياً في آن واحد.',
        ];
        return 'أنت «مرشد عصب التقنية» (AsabTech AI Advisor)، المساعد الذكي لمنصة عصب التقنية العربية المختصة بأخبار التقنية والذكاء الاصطناعي والهواتف والعتاد والبرمجيات والأمن السيبراني.'
            . "\n" . ($toneLine[$tone] ?? $toneLine['balanced'])
            . "\n" . 'أجب دائماً باللغة العربية الفصحى، بأسلوب واضح ومختصر ومرتب، مع تنسيق بسيط بالفقرات.'
            . "\n" . 'اعتمد أولاً على «محتوى عصب التقنية المتاح للإجابة» المرفق ضمن الرسالة إن كان مطابقاً لسؤال الزائر.'
            . "\n" . 'إذا لم تجد في المحتوى المرفق ما يغطي السؤال، أجِب من معرفتك العامة ووضّح ذلك بوضوح، ولا تختلق معلومات ولا روابط.'
            . "\n" . 'إذا كان سؤالك يتعلق بمقالات المنصة، اجعل كل إحالة داخل نص إجابتك فقط بصيغة: [عنوان المقال](/article/اسم-الرابط).'
            . "\n" . 'لا تضع أبداً روابط مطلقة (تبدأ بـ https://) لمقالات الموقع، ولا تُرفق أي قائمة ولا سطر «المصادر:» ولا «المراجع:» في نهاية إجابتك — المنصة تعرض المصادر بنفسها بجانب إجابتك.'
            . "\n" . 'لا تضع روابط خارجية إلا عند ذكر اسم شركة أو خدمة معروفة، وبالصيغة: [الاسم](الرابط).'
            . "\n" . 'كن مهذباً، وناسب الزوار غير المختصين مع بقاء الدقة التقنية.'
            . "\n" . 'المحاذير: لا تقدم نصائح مالية أو استثمارية قطعية، ولا تعطِ آراء طبية، ولا تذكر أي تعارض مع محتوى المنصة.';
    }

    private static function providerReady($provider, array $cfg)
    {
        switch ($provider) {
            case 'omniroute':
                return !empty($cfg['omniroute_endpoint']);
            case 'gemini':
                return !empty($cfg['gemini_api_key']);
            case 'openai':
                return !empty($cfg['openai_api_key']);
            case 'groq':
                return !empty($cfg['groq_api_key']);
            case 'deepseek':
                return !empty($cfg['deepseek_api_key']);
            case 'custom_api':
                return !empty($cfg['custom_api_endpoint']);
        }
        return false;
    }

    private static function callProvider($provider, array $messages, array $cfg)
    {
        switch ($provider) {
            case 'omniroute':
                $endpoint = rtrim($cfg['omniroute_endpoint'] ?? 'http://127.0.0.1:8080/v1', '/');
                $model = !empty($cfg['omniroute_model']) ? $cfg['omniroute_model'] : 'antigravity/gemini-3.7-flash-high';
                return self::callOpenAiCompat($endpoint, $cfg['omniroute_api_key'] ?? '', $model, $messages, $cfg);
            case 'openai':
                return self::callOpenAiCompat('https://api.openai.com/v1', $cfg['openai_api_key'] ?? '', $cfg['openai_model'] ?? 'gpt-4o-mini', $messages, $cfg);
            case 'groq':
                return self::callOpenAiCompat('https://api.groq.com/openai/v1', $cfg['groq_api_key'] ?? '', $cfg['groq_model'] ?? 'llama-3.3-70b-versatile', $messages, $cfg);
            case 'deepseek':
                return self::callOpenAiCompat('https://api.deepseek.com/v1', $cfg['deepseek_api_key'] ?? '', $cfg['deepseek_model'] ?? 'deepseek-chat', $messages, $cfg);
            case 'custom_api':
                return self::callOpenAiCompat($cfg['custom_api_endpoint'] ?? '', $cfg['custom_api_key'] ?? '', $cfg['custom_api_model'] ?? 'deepseek-chat', $messages, $cfg);
            case 'gemini':
                return self::callGemini($cfg['gemini_api_key'] ?? '', $cfg['gemini_model'] ?? 'gemini-3.7-flash', $messages, $cfg);
        }
        return ['success' => false, 'error' => 'المزود غير مدعوم للمحادثة.'];
    }

    private static function callOpenAiCompat($endpoint, $apiKey, $model, array $messages, array $cfg)
    {
        $endpoint = rtrim(trim($endpoint), '/');
        if (!str_ends_with($endpoint, '/chat/completions')) {
            $endpoint .= '/chat/completions';
        }

        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float) $cfg['ai_temperature'] : 0.4;

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temp
        ];

        $headers = ['Content-Type: application/json'];
        if (!empty($apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $answer = $data['choices'][0]['message']['content'] ?? '';
            $answer = trim($answer);
            if ($answer !== '') {
                return ['success' => true, 'answer' => $answer, 'provider' => self::providerLabel($model)];
            }
            return ['success' => false, 'error' => 'استجابة فارغة من المزود.'];
        }

        $errorSummary = "فشل الاتصال بالمزود ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "API Error [{$httpCode}]: " . mb_substr($errData['error']['message'], 0, 200, 'UTF-8');
            }
        }
        return ['success' => false, 'error' => $errorSummary];
    }

    private static function callGemini($apiKey, $model, array $messages, array $cfg)
    {
        if ($apiKey === '') {
            return ['success' => false, 'error' => 'مفتاح Google Gemini غير مسجل.'];
        }

        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float) $cfg['ai_temperature'] : 0.4;

        $systemParts = [];
        $chatParts = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemParts[] = $msg['content'];
            } else {
                if (!empty($systemParts) && $msg['role'] === 'user' && count($chatParts) === 0) {
                    $chatParts[] = ['role' => 'user', 'parts' => [['text' => implode("\n", $systemParts) . "\n\n" . $msg['content']]]];
                    $systemParts = [];
                } else {
                    $chatParts[] = ['role' => $msg['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $msg['content']]]];
                }
            }
        }
        if (!empty($systemParts)) {
            $chatParts[] = ['role' => 'user', 'parts' => [['text' => implode("\n", $systemParts)]]];
        }

        foreach ($chatParts as $i => $cpart) {
            if ($cpart['role'] === 'model' && $i === 0) {
                $chatParts[$i]['parts'][0]['text'] = 'حسناً، أسألني عن أخبار التقنية.';
            }
        }

        $payload = [
            'contents' => $chatParts,
            'generationConfig' => [
                'temperature' => $temp
            ]
        ];

        $candidateModels = [$model, 'gemini-3.7-flash', 'gemini-2.5-flash'];
        $lastError = 'فشل الاتصال بخوادم Google Gemini.';
        foreach ($candidateModels as $candidate) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $candidate . ':generateContent?key=' . urlencode($apiKey);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $data = json_decode($res, true);
                $answer = trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '');
                if ($answer !== '') {
                    return ['success' => true, 'answer' => $answer, 'provider' => self::providerLabel($candidate)];
                }
            }

            if ($httpCode !== 429 && $httpCode !== 503) {
                break;
            }
            $lastError = $res ? (json_decode($res, true)['error']['message'] ?? '') : $curlErr;
            $lastError = "Gemini Error [{$httpCode}]: " . mb_substr($lastError, 0, 200, 'UTF-8');
        }

        return ['success' => false, 'error' => $lastError];
    }

    private static function providerLabel($model)
    {
        if (str_contains($model, 'gemini')) return 'gemini';
        if (str_contains($model, 'gpt') || str_contains($model, 'o1') || str_contains($model, 'o3')) return 'openai';
        if (str_contains($model, 'llama') || str_contains($model, 'mixtral')) return 'groq';
        if (str_contains($model, 'deepseek')) return 'deepseek';
        return $model;
    }
}