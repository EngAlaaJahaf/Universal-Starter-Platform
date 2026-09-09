<?php

class CategoryClassifier
{
    private static $rulesFile = null;

    /**
     * Default Fallback Rules
     */
    private static $defaultRules = [
        'artificial-intelligence' => [
            'name_ar' => 'ذكاء اصطناعي',
            'high_priority' => [
                'artificial intelligence', 'machine learning', 'deep learning', 'generative ai',
                'llm', 'slm', 'large language model', 'chatgpt', 'openai', 'claude', 'gemini',
                'deepseek', 'anthropic', 'mistral', 'groq', 'llama', 'copilot', 'midjourney',
                'stable diffusion', 'sora', 'runway', 'hugging face', 'huggingface', 'perplexity',
                'openrouter', 'sam altman', 'transformer', 'neural network', 'computer vision',
                'nlp', 'genai', 'prompt engineering', 'fine-tuning', 'rlhf', 'rag', 'vector database',
                'embedding', 'ai agent', 'autonomous agent', 'reasoning model', 'o1-preview',
                'o3-mini', 'synthetic data', 'ai lab', 'ai benchmark', 'ai alliance',
                'ذكاء اصطناعي', 'تعلم الآلة', 'تعلم عميق', 'ذكاء توليدي', 'نماذج لغوية', 'شات جي بي تي',
                'أوبن إيه آي', 'أوبن أي', 'كلود', 'جيميني', 'ديب سيك', 'أنثروبيك', 'لاما', 'روبوت محادثة',
                'شبكات عصبية', 'توليد الصور', 'توليد الفيديو', 'وكيل ذكي', 'وكلاء الذكاء', 'استدلال ذكي',
                'معالجة اللغات الطبيعية', 'رؤية حاسوبية', 'استرجاع معزز', 'تضمين المتجهات'
            ],
            'medium_priority' => [
                'model weights', 'inference', 'context window', 'tokens', 'hallucination',
                'multimodal', 'diffusion model', 'agi', 'asi', 'ai safety', 'alignment',
                'توليدي', 'هلوسة الذكاء', 'نافذة السياق', 'الذكاء العام', 'سلامة الذكاء'
            ]
        ],

        'cybersecurity' => [
            'name_ar' => 'أمن سيبراني',
            'high_priority' => [
                'cybersecurity', 'cyber security', 'cyberattack', 'ransomware', 'malware',
                'spyware', 'trojan', 'botnet', 'ddos', 'phishing', 'spear phishing', 'smishing',
                'zero-day', 'zero day', '0-day', 'cve-', 'exploit', 'vulnerability', 'backdoor',
                'data breach', 'data leak', 'credential stuffing', 'infostealer', 'c2 server',
                'lockbit', 'blackcat', 'darkside', 'threat actor', 'hacker', 'hackers',
                'penetration testing', 'red team', 'blue team', 'soc team', 'siem', 'edr',
                'xdr', 'firewall', 'zero trust', 'passkeys', 'identity theft', 'extortion',
                'أمن سيبراني', 'أمن المعلومات', 'قرصنة', 'مخترق', 'مخترقين', 'هكر', 'هكرز',
                'اختراق', 'ثغرة', 'ثغرة أمنية', 'ثغرات', 'برمجية خبيثة', 'برمجيات خبيثة', 'برامج الفدية',
                'فدية', 'تجسس', 'تسريب بيانات', 'تسريبات', 'هجوم سيبراني', 'هجمات سيبرانية',
                'حرمان من الخدمة', 'تصيد احتيالي', 'تصيد', 'حصان طروادة', 'هندسة اجتماعية',
                'جدار حماية', 'انعدام الثقة', 'تشفير طرفي', 'تسلل إلكتروني', 'حقن قواعد البيانات'
            ],
            'medium_priority' => [
                'security flaw', 'patch tuesday', 'bug bounty', 'cve', 'infosec', 'encryption',
                'decryption', '2fa', 'mfa', 'authenticator', 'adware', 'keylogger',
                'تحديث أمني', 'رقعة أمنية', 'تشفير', 'فك تشفير', 'مصادقة ثنائية', 'برامج إعلانية'
            ]
        ],

        'hardware-devices' => [
            'name_ar' => 'أجهزة وعتاد',
            'high_priority' => [
                'iphone', 'ipad', 'macbook', 'mac studio', 'imac', 'apple watch', 'vision pro',
                'airpods', 'galaxy s24', 'galaxy s25', 'galaxy s26', 'galaxy z fold', 'galaxy z flip',
                'pixel 9', 'pixel 10', 'pixel 11', 'pixel watch', 'snapdragon', 'exynos', 'geforce',
                'rtx 40', 'rtx 50', 'rtx 5070', 'rtx 5080', 'rtx 5090', 'blackwell', 'b200',
                'h100', 'h200', 'ryzen', 'radeon', 'arrow lake', 'lunar lake', 'core ultra',
                'tsmc', 'semiconductor', 'chipset', 'soc', 'nanometer', '3nm', '2nm', 'ddr5',
                'nvme ssd', 'motherboard', 'oled display', 'microled', 'amoled', 'smartwatch',
                'smart glasses', 'vr headset', 'ar glasses', 'foldable phone', 'foldables',
                'drone', 'drones', 'raspberry pi', 'gboard',
                'هاتف', 'هواتف', 'جوال', 'جوالات', 'آيفون', 'سامسونج جالاكسي', 'بكسل', 'ساعة ذكية',
                'سماعات لاسلكية', 'معالج', 'معالجات', 'كرت شاشة', 'بطاقة رسوميات', 'رقاقة', 'رقائق',
                'أشباه الموصلات', 'عتاد', 'عتاد صلب', 'شاشة أوليد', 'لوحة أم', 'شحن سريع',
                'هواتف قابلة للطي', 'نظارات واقع معزز', 'خوذة واقع افتراضي', 'حاسوب محمول', 'كاميرا احترافية',
                'طائرة مسيرة', 'طائرات مسيرة', 'درون'
            ],
            'medium_priority' => [
                'hardware', 'laptop', 'desktop pc', 'battery life', 'charging speed', 'camera sensor',
                'megapixels', 'refresh rate', '120hz', '144hz', 'gpu', 'cpu', 'ram', 'ssd',
                'chassis', 'earbuds', 'wearable', 'tablet',
                'حاسوب', 'لابتوب', 'بطارية', 'مستشعر', 'ميجابكسل', 'معدل تحديث', 'أجهزة ذكية', 'لوحي'
            ]
        ],

        'software-development' => [
            'name_ar' => 'برمجة وتطوير',
            'high_priority' => [
                'github', 'gitlab', 'bitbucket', 'pull request', 'repository', 'open source',
                'open-source', 'javascript', 'typescript', 'python', 'golang', 'rust lang',
                'c++', 'c#', 'kotlin', 'swift lang', 'php', 'ruby', 'sql', 'postgresql',
                'mysql', 'mongodb', 'redis', 'graphql', 'rest api', 'grpc', 'websocket',
                'react.js', 'react js', 'next.js', 'nextjs', 'vue.js', 'vuejs', 'nuxt',
                'angular', 'svelte', 'node.js', 'nodejs', 'deno', 'bun.sh', 'tailwind css',
                'webassembly', 'wasm', 'docker', 'kubernetes', 'k8s', 'terraform', 'ci/cd',
                'github actions', 'devops', 'microservices', 'serverless', 'sdk', 'npm package',
                'pypi', 'composer', 'cargo', 'framework', 'compiler', 'debugger', 'refactoring',
                'bare metal', 'cloud infrastructure', 'cloud native', 'api endpoint',
                'برمجة', 'تطوير برمجيات', 'مطور', 'مطورين', 'مبرمج', 'مبرمجين', 'كود', 'شيفرة',
                'مستودع كود', 'مفتوح المصدر', 'إطار عمل', 'مكتبة برمجية', 'واجهة برمجة التطبيقات',
                'قاعدة بيانات', 'قواعد بيانات', 'حاويات دوكر', 'كوبرنيتس', 'بناء وتجميع',
                'تصحيح أخطاء', 'هندسة البرمجيات', 'تطوير الويب', 'تطوير التطبيقات', 'خوارزميات',
                'بنية سحابية', 'خادم سحابي'
            ],
            'medium_priority' => [
                'developer', 'coding', 'backend', 'frontend', 'fullstack', 'library', 'runtime',
                'npm', 'git', 'commit', 'branch', 'syntax', 'stack trace',
                'واجهة أمامية', 'واجهة خلفية', 'مكتبة', 'أمر برمجي', 'أداء الكود'
            ]
        ],

        'general-tech' => [
            'name_ar' => 'تقنية عامة',
            'high_priority' => [
                'big tech', 'antitrust', 'ftc', 'doj', 'eu regulation', 'dma', 'dsa',
                'venture capital', 'startup funding', 'ipo', 'spacex', 'starlink',
                'elon musk', 'satya nadella', 'sundar pichai', 'tim cook', 'mark zuckerberg',
                'telecom', '5g network', '6g', 'broadband', 'fintech', 'stripe', 'crypto', 'bitcoin',
                'blockchain', 'digital economy', 'tech policy',
                'تقنية عامة', 'شركات تقنية', 'استحواذ', 'اندماج', 'اكتتاب', 'استثمار جريء',
                'شركات ناشئة', 'احتكار تقني', 'تنظيم رقمي', 'الاتحاد الأوروبي للتقنية',
                'سبيس إكس', 'ستارلينك', 'شبكات الاتصال', 'الجيل الخامس', 'اقتصاد رقمي',
                'عملات مشفرة', 'بلوك تشين', 'إيلون ماسك', 'تيم كوك', 'ساتيا ناديلا'
            ],
            'medium_priority' => [
                'technology', 'tech industry', 'silicon valley', 'layoffs', 'hiring',
                'market cap', 'quarterly earnings', 'revenue', 'cloud service', 'datacenter',
                'سوق التقنية', 'وادي السيليكون', 'أرباح فصلية', 'مراكز بيانات', 'خدمات سحابية'
            ]
        ]
    ];

    private static $sourceRulesFile = null;

    private static $defaultSourceRules = [
        'bleepingcomputer' => ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'the hacker news'  => ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'securityweek'     => ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'dark reading'     => ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'krebs on security'=> ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'threatpost'       => ['mode' => 'strict', 'target_slug' => 'cybersecurity', 'weight' => 10.0],
        'openai blog'      => ['mode' => 'strict', 'target_slug' => 'artificial-intelligence', 'weight' => 10.0],
        'hugging face'     => ['mode' => 'strict', 'target_slug' => 'artificial-intelligence', 'weight' => 10.0],
        'marktechpost'     => ['mode' => 'strict', 'target_slug' => 'artificial-intelligence', 'weight' => 10.0],
        'ai news'          => ['mode' => 'strict', 'target_slug' => 'artificial-intelligence', 'weight' => 10.0],
        'freecodecamp'     => ['mode' => 'strict', 'target_slug' => 'software-development', 'weight' => 10.0],
        'dev.to'           => ['mode' => 'smart',  'target_slug' => 'software-development', 'weight' => 6.0],
        'github blog'      => ['mode' => 'smart',  'target_slug' => 'software-development', 'weight' => 6.0],
        'infoq'            => ['mode' => 'smart',  'target_slug' => 'software-development', 'weight' => 6.0],
        'smashing magazine'=> ['mode' => 'smart',  'target_slug' => 'software-development', 'weight' => 6.0],
        'tom\'s hardware'  => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 6.0],
        'tom\'s guide'     => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 5.0],
        'anandtech'        => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 6.0],
        'gsm arena'        => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 6.0],
        'gsmarena'         => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 6.0],
        'wccftech'         => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 4.0],
        '9to5mac'          => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 4.0],
        'sammobile'        => ['mode' => 'smart',  'target_slug' => 'hardware-devices', 'weight' => 4.0],
        'the verge'        => ['mode' => 'smart',  'target_slug' => 'general-tech', 'weight' => 0.0],
        'techcrunch'       => ['mode' => 'smart',  'target_slug' => 'general-tech', 'weight' => 0.0],
        'ars technica'     => ['mode' => 'smart',  'target_slug' => 'general-tech', 'weight' => 0.0],
        'engadget'         => ['mode' => 'smart',  'target_slug' => 'general-tech', 'weight' => 0.0],
        'البوابة العربية للأخبار التقنية' => ['mode' => 'smart', 'target_slug' => 'general-tech', 'weight' => 0.0],
        'عالم التقنية'     => ['mode' => 'smart',  'target_slug' => 'general-tech', 'weight' => 0.0]
    ];

    private static function getStoragePath(): string
    {
        if (self::$rulesFile === null) {
            self::$rulesFile = dirname(__DIR__) . '/config/classifier_rules.json';
        }
        return self::$rulesFile;
    }

    private static function getSourceRulesPath(): string
    {
        if (self::$sourceRulesFile === null) {
            self::$sourceRulesFile = dirname(__DIR__) . '/config/source_classifier_rules.json';
        }
        return self::$sourceRulesFile;
    }

    /**
     * Get active rules (from JSON file if exists, or default)
     */
    public static function getRules(): array
    {
        $file = self::getStoragePath();
        if (file_exists($file)) {
            $json = @json_decode(file_get_contents($file), true);
            if (is_array($json) && !empty($json)) {
                return $json;
            }
        }
        // Initialize file if not exists
        @file_put_contents($file, json_encode(self::$defaultRules, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return self::$defaultRules;
    }

    /**
     * Save rules to JSON
     */
    public static function saveRules(array $rules): bool
    {
        $file = self::getStoragePath();
        return (bool) @file_put_contents($file, json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Get Source Classification Rules
     */
    public static function getSourceRules(): array
    {
        $file = self::getSourceRulesPath();
        if (file_exists($file)) {
            $json = @json_decode(file_get_contents($file), true);
            if (is_array($json)) {
                return $json;
            }
        }
        @file_put_contents($file, json_encode(self::$defaultSourceRules, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return self::$defaultSourceRules;
    }

    /**
     * Save Source Classification Rules
     */
    public static function saveSourceRules(array $rules): bool
    {
        $file = self::getSourceRulesPath();
        return (bool) @file_put_contents($file, json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Update rule for a single source
     */
    public static function setSourceRule(string $sourceName, string $mode, string $targetSlug, float $weight = 5.0): bool
    {
        $rules = self::getSourceRules();
        $key = mb_strtolower(trim($sourceName));
        $rules[$key] = [
            'mode'        => in_array($mode, ['strict', 'smart', 'neutral'], true) ? $mode : 'smart',
            'target_slug' => $targetSlug,
            'weight'      => max(0.0, min(15.0, $weight))
        ];
        return self::saveSourceRules($rules);
    }

    /**
     * Check if a keyword conflicts with other categories
     */
    public static function checkKeywordConflicts(string $keyword, string $targetCategorySlug = ''): array
    {
        $rules = self::getRules();
        $keywordLower = mb_strtolower(trim($keyword));
        $conflicts = [];

        foreach ($rules as $slug => $data) {
            if ($slug === $targetCategorySlug) continue;

            $high = array_map('mb_strtolower', $data['high_priority'] ?? []);
            $med  = array_map('mb_strtolower', $data['medium_priority'] ?? []);

            if (in_array($keywordLower, $high, true) || in_array($keywordLower, $med, true)) {
                $priority = in_array($keywordLower, $high, true) ? 'عالية (High)' : 'متوسطة (Medium)';
                $conflicts[] = [
                    'category_slug' => $slug,
                    'category_name' => $data['name_ar'] ?? $slug,
                    'priority'      => $priority
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Scan the entire dictionary and find all cross-category duplicate keywords
     */
    public static function getAllConflicts(): array
    {
        $rules = self::getRules();
        $seen = [];
        $conflicts = [];

        foreach ($rules as $slug => $data) {
            $catName = $data['name_ar'] ?? $slug;
            $allKw = array_merge(
                array_map('mb_strtolower', $data['high_priority'] ?? []),
                array_map('mb_strtolower', $data['medium_priority'] ?? [])
            );

            foreach ($allKw as $kw) {
                $kw = trim($kw);
                if (empty($kw)) continue;

                if (isset($seen[$kw])) {
                    $conflicts[$kw][] = [
                        'slug' => $slug,
                        'name' => $catName
                    ];
                    // Also make sure original category is listed
                    $first = $seen[$kw];
                    if (!in_array($first, array_column($conflicts[$kw], 'slug'), true)) {
                        array_unshift($conflicts[$kw], [
                            'slug' => $first['slug'],
                            'name' => $first['name']
                        ]);
                    }
                } else {
                    $seen[$kw] = [
                        'slug' => $slug,
                        'name' => $catName
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Add a new keyword with conflict detection
     */
    public static function addKeyword(string $categorySlug, string $keyword, string $priority = 'high'): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return ['ok' => false, 'error' => 'المصطلح لا يمكن أن يكون فارغاً.'];
        }

        $conflicts = self::checkKeywordConflicts($keyword, $categorySlug);
        $rules = self::getRules();

        if (!isset($rules[$categorySlug])) {
            return ['ok' => false, 'error' => 'التصنيف المطلوب غير موجود.'];
        }

        $bucket = ($priority === 'medium') ? 'medium_priority' : 'high_priority';
        if (!isset($rules[$categorySlug][$bucket])) {
            $rules[$categorySlug][$bucket] = [];
        }

        // Avoid adding duplicate in same category
        $currentLower = array_map('mb_strtolower', $rules[$categorySlug][$bucket]);
        if (in_array(mb_strtolower($keyword), $currentLower, true)) {
            return ['ok' => false, 'error' => 'هذا المصطلح مضاف بالفعل في هذا التصنيف.'];
        }

        array_unshift($rules[$categorySlug][$bucket], $keyword);
        self::saveRules($rules);

        return [
            'ok'        => true,
            'keyword'   => $keyword,
            'priority'  => $priority,
            'conflicts' => $conflicts
        ];
    }

    /**
     * Delete a keyword from a category
     */
    public static function removeKeyword(string $categorySlug, string $keyword): bool
    {
        $rules = self::getRules();
        if (!isset($rules[$categorySlug])) return false;

        $keywordLower = mb_strtolower(trim($keyword));
        $modified = false;

        foreach (['high_priority', 'medium_priority'] as $bucket) {
            if (isset($rules[$categorySlug][$bucket])) {
                $rules[$categorySlug][$bucket] = array_values(array_filter($rules[$categorySlug][$bucket], function($k) use ($keywordLower, &$modified) {
                    if (mb_strtolower(trim($k)) === $keywordLower) {
                        $modified = true;
                        return false;
                    }
                    return true;
                }));
            }
        }

        if ($modified) {
            self::saveRules($rules);
        }
        return $modified;
    }

    /**
     * Smart Source-Aware Weighted Classification Engine
     */
    public static function classify(
        string $titleEn, 
        string $contentEn, 
        string $titleAr = '', 
        int $sourceCatId = 0, 
        array $catSlugMap = [],
        string $sourceName = '',
        string $articleUrl = ''
    ): int {
        $rules = self::getRules();
        $sourceRules = self::getSourceRules();

        $titleCombined = mb_strtolower($titleEn . ' ' . $titleAr);
        $contentSample = mb_strtolower(mb_substr($contentEn, 0, 2000));
        $sourceNameClean = mb_strtolower(trim($sourceName));

        // ─── 1. Check for Strict Source Override ────────────────────
        if (!empty($sourceNameClean) && isset($sourceRules[$sourceNameClean])) {
            $sRule = $sourceRules[$sourceNameClean];
            if (($sRule['mode'] ?? '') === 'strict' && !empty($sRule['target_slug'])) {
                $targetSlug = $sRule['target_slug'];
                if (isset($catSlugMap[$targetSlug])) {
                    return (int) $catSlugMap[$targetSlug];
                }
            }
        }

        // Initialize Category Scores
        $scores = [];
        foreach (array_keys($rules) as $slug) {
            $scores[$slug] = 0.0;
        }

        // ─── 2. Source Prior Weighting (Smart Mode) ────────────────
        if (!empty($sourceNameClean) && isset($sourceRules[$sourceNameClean])) {
            $sRule = $sourceRules[$sourceNameClean];
            $tSlug = $sRule['target_slug'] ?? '';
            $sWeight = (float) ($sRule['weight'] ?? 0.0);
            if (($sRule['mode'] ?? '') === 'smart' && isset($scores[$tSlug])) {
                $scores[$tSlug] += $sWeight;
            }
        } elseif ($sourceCatId > 0 && !empty($catSlugMap)) {
            $reverseMap = array_flip($catSlugMap);
            $presetSlug = $reverseMap[$sourceCatId] ?? '';
            if (isset($scores[$presetSlug])) {
                $scores[$presetSlug] += 3.0;
            }
        }

        // ─── 3. Article URL Category / Tag Path Signals ─────────────
        if (!empty($articleUrl)) {
            $urlLower = mb_strtolower($articleUrl);
            // AI URL signals
            if (preg_match('#/(artificial-intelligence|ai|machine-learning|generative-ai|chatgpt|openai)/#i', $urlLower)) {
                if (isset($scores['artificial-intelligence'])) $scores['artificial-intelligence'] += 5.0;
            }
            // Security URL signals
            if (preg_match('#/(cybersecurity|security|privacy|malware|vulnerabilities|hacks)/#i', $urlLower)) {
                if (isset($scores['cybersecurity'])) $scores['cybersecurity'] += 5.0;
            }
            // Hardware URL signals
            if (preg_match('#/(hardware|gadgets|phones|mobile|laptops|reviews|devices|smartphones)/#i', $urlLower)) {
                if (isset($scores['hardware-devices'])) $scores['hardware-devices'] += 5.0;
            }
            // Software Dev URL signals
            if (preg_match('#/(developer|programming|coding|software-development|devops|open-source)/#i', $urlLower)) {
                if (isset($scores['software-development'])) $scores['software-development'] += 5.0;
            }
        }

        // ─── 4. Specific High-Confidence Regex Pattern Matchers ─────
        // Go / Golang releases
        if (preg_match('/\bgo\s+\d+(\.\d+)*\b/i', $titleCombined) || preg_match('/\bgolang\b/i', $titleCombined)) {
            if (isset($scores['software-development'])) $scores['software-development'] += 10.0;
        }
        // CVE Vulnerabilities
        if (preg_match('/\bcve-\d{4}-\d+\b/i', $titleCombined)) {
            if (isset($scores['cybersecurity'])) $scores['cybersecurity'] += 10.0;
        }
        // Specific GPUs / CPUs
        if (preg_match('/\b(rtx\s*50\d0|ryzen\s*\d{4}|intel\s*core\s*ultra|snapdragon\s*8\s*elite)\b/i', $titleCombined)) {
            if (isset($scores['hardware-devices'])) $scores['hardware-devices'] += 10.0;
        }
        // Specific AI Models
        if (preg_match('/\b(claude\s*3\.\d|gpt-4o|deepseek-r1|gemini\s*2\.\d|sora|o3-mini)\b/i', $titleCombined)) {
            if (isset($scores['artificial-intelligence'])) $scores['artificial-intelligence'] += 10.0;
        }

        // ─── 5. Calculate Title & Content Keyword Scores ────────────
        foreach ($rules as $slug => $data) {
            // High-priority matches in TITLE (Score: 6.0 each)
            foreach (($data['high_priority'] ?? []) as $kw) {
                if (self::containsKeyword($titleCombined, $kw)) {
                    $scores[$slug] += 6.0;
                }
                // High-priority matches in CONTENT (Score: 2.0 each)
                if (self::containsKeyword($contentSample, $kw)) {
                    $scores[$slug] += 2.0;
                }
            }

            // Medium-priority matches in TITLE (Score: 3.0 each)
            foreach (($data['medium_priority'] ?? []) as $kw) {
                if (self::containsKeyword($titleCombined, $kw)) {
                    $scores[$slug] += 3.0;
                }
                // Medium-priority matches in CONTENT (Score: 1.0 each)
                if (self::containsKeyword($contentSample, $kw)) {
                    $scores[$slug] += 1.0;
                }
            }
        }

        // ─── 6. Find the Category with the Highest Score ────────────
        arsort($scores);
        $bestSlug = key($scores);
        $topScore = current($scores);

        // Fallback if score is too low or neutral
        if ($topScore < 2.5) {
            if ($sourceCatId > 0 && in_array($sourceCatId, $catSlugMap, true)) {
                return $sourceCatId;
            }
            $bestSlug = 'general-tech';
        }

        return $catSlugMap[$bestSlug] ?? ($catSlugMap['general-tech'] ?? 9);
    }

    private static function containsKeyword(string $text, string $kw): bool
    {
        $kw = mb_strtolower(trim($kw));
        if ($kw === '') return false;

        if (mb_strlen($kw) <= 3) {
            return (bool) preg_match('/(?:^|[^a-z0-9\x{0600}-\x{06FF}])' . preg_quote($kw, '/') . '(?:$|[^a-z0-9\x{0600}-\x{06FF}])/ui', $text);
        }

        return (mb_strpos($text, $kw) !== false);
    }
}
