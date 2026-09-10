<?php

class AiTranslator
{
    /**
     * Specialized Arabic Tech Glossary for precise non-literal translation
     */
    public static $techGlossary = [
        'Zero-day vulnerability'      => 'ثغرة يوم الصفر الأمنية (Zero-Day)',
        'zero-day'                   => 'ثغرة يوم الصفر (Zero-Day)',
        'Data breach'                => 'تسريب واختراق للبيانات',
        'data breach'                => 'تسريب بيانات',
        'Ransomware attack'          => 'هجوم ببرمجيات الفدية الخبيثة (Ransomware)',
        'ransomware'                 => 'برمجيات الفدية',
        'Large Language Models'      => 'النماذج اللغوية الكبيرة (LLMs)',
        'large language model'       => 'نموذج لغوي كبير (LLM)',
        'Generative AI'              => 'الذكاء الاصطناعي التوليدي (Generative AI)',
        'generative AI'              => 'الذكاء الاصطناعي التوليدي',
        'Artificial Intelligence'    => 'الذكاء الاصطناعي (AI)',
        'Machine Learning'           => 'تعلم الآلة (Machine Learning)',
        'Deep Learning'              => 'التعلم العميق (Deep Learning)',
        'Prompt Engineering'         => 'هندسة الأوامر والتوجيهات (Prompt Engineering)',
        'Fine-tuning'                => 'الضبط والتخصيص الدقيق (Fine-tuning)',
        'Autonomous Agents'          => 'الوكلاء الأذكياء المستقلون (AI Agents)',
        'Autonomous systems'         => 'الأنظمة المؤتمتة ذاتية التشغيل',
        'Quantum Computing'          => 'الحوسبة الكمومية (Quantum Computing)',
        'Quantum supremacy'          => 'التفوق الكمومي الحوسبي',
        'Semiconductor chips'        => 'رقائق وأشباه الموصلات',
        'Semiconductors'             => 'أشباه الموصلات',
        'Venture Capital'            => 'رأس المال الجريء والاستثماري (VC)',
        'Funding round'              => 'جولة تمويل استثمارية',
        'Series A funding'           => 'جولة تمويل أولى (Series A)',
        'Series B funding'           => 'جولة تمويل ثانية (Series B)',
        'Initial Public Offering'    => 'الطرح العام الأولي (IPO)',
        'Cloud Computing'            => 'الحوسبة السحابية (Cloud Computing)',
        'Edge Computing'             => 'الحوسبة الطرفية الموزعة (Edge Computing)',
        'Supply chain attack'        => 'هجوم استهداف سلاسل التوريد البرمجية',
        'Cybersecurity'              => 'الأمن السيبراني والدفاع الرقمي',
        'Malware'                    => 'البرمجيات الخبيثة والضارة',
        'Phishing campaign'          => 'حملة تصيد احتيالي إلكتروني (Phishing)',
        'Open source repository'     => 'مستودع برمجي مفتوح المصدر',
        'Open source'                => 'مفتوح المصدر (Open Source)',
        'Silicon photonics'          => 'تقنية الضوئيات السيليكونية فائقة السرعة',
        'GPU cluster'                => 'مجموعات معالجات الرسوميات والحوسبة المكثفة (GPU Cluster)',
        'Flagship smartphone'        => 'هاتف رائد متطور',
        'Operating system'           => 'نظام التشغيل (OS)',
        'Kernel vulnerability'       => 'ثغرة في نواة النظام البرمجية (Kernel)',
        'Threat actor'               => 'جهة التهديد السيبراني',
        'State-sponsored hackers'    => 'قراصنة مدعومون من دول',
        'Bug bounty program'         => 'برنامج مكافآت اكتشاف الثغرات الأمنية',
        'Critical patch'             => 'تحديث وتصحيح أمني طارئ',
        'Breakthrough technology'    => 'قفزة تكنولوجية غير مسبوقة',
        'Continuous Integration'     => 'التكامل المستمر (CI)',
        'Continuous Delivery'        => 'التسليم المستمر (CD)',
        'CI/CD'                      => 'خطوط النشر والتكامل المستمر (CI/CD)',
        'App Router'                 => 'نظام التوجيه App Router',
        'API Routes'                 => 'مسارات واجهات برمجة التطبيقات (API Routes)',
        'API endpoint'               => 'نقطة نهاية واجهة البرمجة (API Endpoint)',
        'API endpoints'              => 'نقاط نهاية واجهة البرمجة (API Endpoints)',
        'API'                        => 'واجهة برمجة التطبيقات (API)',
        'Microservices'              => 'البنية المعمارية للخدمات المصغرة (Microservices)',
        'Serverless'                 => 'الحوسبة بدون خوادم (Serverless)',
        'Pull Request'               => 'طلب سحب وتعديل برمجي (Pull Request)',
        'Memory Leak'                => 'تسريب في الذاكرة (Memory Leak)',
        'Garbage Collection'         => 'جامع الذاكرة المهملة (Garbage Collector)',
        'Automatic Reference Counting' => 'العد التلقائي للمراجع (ARC)',
        'ARC'                        => 'نظام إدارة الذاكرة (ARC)',
        'DevOps'                     => 'عمليات التطوير والتشغيل (DevOps)',
        'Backend'                    => 'الواجهة الخلفية والخوادم (Backend)',
        'Frontend'                   => 'واجهة المستخدم الأمامية (Frontend)',
        'Fullstack'                  => 'التطوير الشامل المتكامل (Full-Stack)',
        'Data Center'                => 'مركز البيانات (Data Center)',
        'Load Balancer'              => 'موزع الأحمال البرمجية (Load Balancer)',
    ];

    /**
     * Get Models List with Live Health Status (🟢 Online / 🔴 Offline) for UI Dropdowns
     */
    public static function getProviderModelsWithHealth($provider, $overrides = [])
    {
        $db = new Database();
        $keys = [
            'ai_provider', 'openai_api_key', 'openai_model', 'gemini_api_key', 'gemini_model',
            'omniroute_endpoint', 'omniroute_api_key', 'omniroute_model',
            'groq_api_key', 'groq_model', 'deepseek_api_key', 'deepseek_model',
            'custom_api_endpoint', 'custom_api_key', 'custom_api_model',
            'opencode_fallback_enabled', 'opencode_model'
        ];
        $inClause = "'" . implode("','", $keys) . "'";
        $settingsRow = $db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ({$inClause})");
        $settings = array_column($settingsRow, 'value', 'key');
        if (!empty($overrides)) {
            $settings = array_merge($settings, $overrides);
        }

        $models = [];
        $selectedModel = $settings[$provider . '_model'] ?? '';

        switch ($provider) {
            case 'omniroute':
                $endpoint = rtrim($settings['omniroute_endpoint'] ?? 'http://127.0.0.1:8080/v1', '/');
                $apiKey = $settings['omniroute_api_key'] ?? '';
                $selectedModel = $settings['omniroute_model'] ?? 'antigravity/gemini-3.7-flash-high';

                $liveModels = [];
                $ch = curl_init($endpoint . '/models');
                $headers = ['Content-Type: application/json'];
                if (!empty($apiKey)) $headers[] = 'Authorization: Bearer ' . $apiKey;
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);
                $res = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code === 200 && $res) {
                    $json = json_decode($res, true);
                    if (!empty($json['data']) && is_array($json['data'])) {
                        $priorityIds = [
                            'antigravity/gemini-3.7-flash-high',
                            'opencode/gemini-3.7-flash',
                            'auto/gemini',
                            'auto/fast',
                            'auto/best-fast',
                            'dva/gemini-3-7-flash-high',
                            'cheaperinference/claude-sonnet-4.5-high',
                            'cheaperinference/claude-haiku-4.5-high',
                            'cheaperinference/claude-opus-5-high',
                            'cinf/claude-sonnet-4.5-high',
                            'auto/best-chat',
                            'auto/best-coding'
                        ];
                        
                        $allFetchedIds = array_column($json['data'], 'id');
                        $seen = [];

                        // 1. Add priority IDs if present in fetched
                        foreach ($priorityIds as $pId) {
                            if (in_array($pId, $allFetchedIds, true)) {
                                $liveModels[] = [
                                    'id'       => $pId,
                                    'name'     => $pId . ($pId === 'antigravity/gemini-3.7-flash-high' ? ' (موصى به)' : ''),
                                    'status'   => 'online',
                                    'selected' => ($pId === $selectedModel)
                                ];
                                $seen[$pId] = true;
                            }
                        }

                        // 2. Add selected model if not seen
                        if (!empty($selectedModel) && empty($seen[$selectedModel])) {
                            $liveModels[] = [
                                'id'       => $selectedModel,
                                'name'     => $selectedModel . ' (الحالي)',
                                'status'   => in_array($selectedModel, $allFetchedIds, true) ? 'online' : 'unknown',
                                'selected' => true
                            ];
                            $seen[$selectedModel] = true;
                        }

                        // 3. Add up to 35 additional models from the tunnel
                        foreach ($json['data'] as $mItem) {
                            $mId = $mItem['id'] ?? '';
                            if ($mId && empty($seen[$mId])) {
                                $liveModels[] = [
                                    'id'       => $mId,
                                    'name'     => $mId,
                                    'status'   => 'online',
                                    'selected' => ($mId === $selectedModel)
                                ];
                                $seen[$mId] = true;
                                if (count($liveModels) >= 40) break;
                            }
                        }
                    }
                }

                if (empty($liveModels)) {
                    $defaultOmni = [
                        'antigravity/gemini-3.7-flash-high' => 'antigravity/gemini-3.7-flash-high (Gemini 3.7 Flash High)',
                        'opencode/gemini-3.7-flash'          => 'opencode/gemini-3.7-flash',
                        'auto/gemini'                        => 'auto/gemini',
                        'auto/fast'                          => 'auto/fast',
                        'auto/best-fast'                     => 'auto/best-fast',
                        'cheaperinference/claude-sonnet-4.5-high' => 'cheaperinference/claude-sonnet-4.5-high'
                    ];
                    $hasKey = !empty($apiKey);
                    foreach ($defaultOmni as $id => $name) {
                        $liveModels[] = [
                            'id'       => $id,
                            'name'     => $name,
                            'status'   => $hasKey ? 'online' : 'offline',
                            'selected' => ($id === $selectedModel)
                        ];
                    }
                }
                $models = $liveModels;
                break;

            case 'gemini':
                $apiKey = $settings['gemini_api_key'] ?? '';
                $selectedModel = $settings['gemini_model'] ?? 'gemini-3.7-flash';
                $isKeySet = !empty($apiKey);
                $geminiList = [
                    'gemini-3.7-flash' => 'gemini-3.7-flash (Gemini 3.7 Flash - موصى به)',
                    'gemini-3.6-flash' => 'gemini-3.6-flash (Gemini 3.6 Flash - فائق السرعة)',
                    'gemini-3.5-flash' => 'gemini-3.5-flash (Gemini 3.5 Flash)',
                    'gemini-2.5-pro'   => 'gemini-2.5-pro (Gemini 2.5 Pro)',
                ];
                foreach ($geminiList as $id => $name) {
                    $models[] = [
                        'id'       => $id,
                        'name'     => $name,
                        'status'   => $isKeySet ? 'online' : 'offline',
                        'selected' => ($id === $selectedModel)
                    ];
                }
                break;

            case 'groq':
                $apiKey = $settings['groq_api_key'] ?? '';
                $selectedModel = $settings['groq_model'] ?? 'llama-3.3-70b-versatile';
                $isKeySet = !empty($apiKey);
                $groqList = [
                    'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (موصى به - ذكي وسريع)',
                    'llama-3.1-8b-instant'    => 'Llama 3.1 8B Instant (فائق الخفة والسرعة)',
                    'mixtral-8x7b-32768'      => 'Mixtral 8x7B (32k Context)',
                    'deepseek-r1-distill-llama-70b' => 'DeepSeek R1 Distill 70B'
                ];
                foreach ($groqList as $id => $name) {
                    $models[] = [
                        'id'       => $id,
                        'name'     => $name,
                        'status'   => $isKeySet ? 'online' : 'offline',
                        'selected' => ($id === $selectedModel)
                    ];
                }
                break;

            case 'deepseek':
                $apiKey = $settings['deepseek_api_key'] ?? '';
                $selectedModel = $settings['deepseek_model'] ?? 'deepseek-chat';
                $isKeySet = !empty($apiKey);
                $deepseekList = [
                    'deepseek-chat'     => 'DeepSeek-V3 (deepseek-chat - ذكي واقتصادي)',
                    'deepseek-reasoner' => 'DeepSeek-R1 (deepseek-reasoner - تفكير واستدلال)'
                ];
                foreach ($deepseekList as $id => $name) {
                    $models[] = [
                        'id'       => $id,
                        'name'     => $name,
                        'status'   => $isKeySet ? 'online' : 'offline',
                        'selected' => ($id === $selectedModel)
                    ];
                }
                break;

            case 'openai':
                $apiKey = $settings['openai_api_key'] ?? '';
                $selectedModel = $settings['openai_model'] ?? 'gpt-4o-mini';
                $isKeySet = !empty($apiKey);
                $openaiList = [
                    'gpt-4o-mini'   => 'GPT-4o Mini (سريع، اقتصادي، ذكي - موصى به)',
                    'gpt-4o'        => 'GPT-4o (أعلى جودة صياغة صحفية)',
                    'gpt-4-turbo'   => 'GPT-4 Turbo',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
                ];
                foreach ($openaiList as $id => $name) {
                    $models[] = [
                        'id'       => $id,
                        'name'     => $name,
                        'status'   => $isKeySet ? 'online' : 'offline',
                        'selected' => ($id === $selectedModel)
                    ];
                }
                break;

            case 'mymemory':
            case 'gtx':
                $models[] = [
                    'id'       => 'free_engine',
                    'name'     => 'المترجم المجاني المدمج (MyMemory + القاموس التقني)',
                    'status'   => 'online',
                    'selected' => true
                ];
                break;
        }

        return [
            'ok'             => true,
            'provider'       => $provider,
            'selected_model' => $selectedModel,
            'models'         => $models
        ];
    }

    /**
     * Live Test Specific AI Provider with Sample Tech News
     */
    public static function testProvider($provider, $overrides = [])
    {
        $db = new Database();
        $keys = [
            'ai_provider', 'openai_api_key', 'openai_model', 'gemini_api_key', 'gemini_model',
            'omniroute_endpoint', 'omniroute_api_key', 'omniroute_model',
            'groq_api_key', 'groq_model', 'deepseek_api_key', 'deepseek_model',
            'custom_api_endpoint', 'custom_api_key', 'custom_api_model',
            'opencode_fallback_enabled', 'opencode_model',
            'ai_fallback_enabled', 'ai_temperature', 'ai_system_prompt'
        ];
        $inClause = "'" . implode("','", $keys) . "'";
        $settingsRow = $db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ({$inClause})");
        $settings = array_column($settingsRow, 'value', 'key');
        if (!empty($overrides)) {
            $settings = array_merge($settings, $overrides);
        }

        $sampleTitle = "OpenAI and Google Announce Breakthrough in Autonomous AI Reasoning Models";
        $sampleContent = "Researchers have introduced next-generation AI architectures with advanced multi-step reasoning, self-correction algorithms, and optimized latency for enterprise deployment.";

        $startTime = microtime(true);
        $res = null;

        switch ($provider) {
            case 'omniroute':
                if (empty($settings['omniroute_endpoint'])) {
                    return ['ok' => false, 'error' => 'عنوان نفق Omniroute Endpoint غير محدد في الإعدادات.'];
                }
                $res = self::callOmniroute($sampleTitle, $sampleContent, $settings);
                break;

            case 'gemini':
                if (empty($settings['gemini_api_key'])) {
                    return ['ok' => false, 'error' => 'مفتاح Google Gemini API Key غير مسجل في الإعدادات.'];
                }
                $res = self::callGemini($sampleTitle, $sampleContent, $settings);
                break;

            case 'groq':
                if (empty($settings['groq_api_key'])) {
                    return ['ok' => false, 'error' => 'مفتاح Groq API Key غير مسجل في الإعدادات.'];
                }
                $res = self::callGroq($sampleTitle, $sampleContent, $settings);
                break;

            case 'deepseek':
                if (empty($settings['deepseek_api_key'])) {
                    return ['ok' => false, 'error' => 'مفتاح DeepSeek API Key غير مسجل في الإعدادات.'];
                }
                $res = self::callDeepSeek($sampleTitle, $sampleContent, $settings);
                break;

            case 'openai':
                if (empty($settings['openai_api_key'])) {
                    return ['ok' => false, 'error' => 'مفتاح OpenAI API Key غير مسجل في الإعدادات.'];
                }
                $res = self::callOpenAi($sampleTitle, $sampleContent, $settings);
                break;

            case 'custom_api':
                if (empty($settings['custom_api_endpoint'])) {
                    return ['ok' => false, 'error' => 'عنوان Custom API Endpoint غير محدد في الإعدادات.'];
                }
                $res = self::callCustomApi($sampleTitle, $sampleContent, $settings);
                break;

            case 'opencode':
                if (($settings['opencode_fallback_enabled'] ?? '1') != '1') {
                    return ['ok' => false, 'error' => 'الاحتياط المجاني OpenCode Zen معطل في الإعدادات.'];
                }
                $res = self::callOpencode($sampleTitle, $sampleContent, $settings);
                break;

            case 'mymemory':
            case 'gtx':
                $res = ['success' => true, 'data' => self::freeTranslateWithGlossary($sampleTitle, $sampleContent)];
                break;

            default:
                return ['ok' => false, 'error' => "المزود المحدد غير معروف ({$provider})."];
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($res && !empty($res['success'])) {
            return [
                'ok'          => true,
                'provider'    => $provider,
                'duration_ms' => $durationMs,
                'data'        => $res['data']
            ];
        }

        return [
            'ok'          => false,
            'provider'    => $provider,
            'duration_ms' => $durationMs,
            'error'       => $res['error'] ?? 'فشل الاتصال بالمزود أو الاستجابة غير صحيحة.'
        ];
    }

    /**
     * Strategic AI Translation & Tech News Re-authoring
     * Translates English title, lead, and content into high-grade journalistic Arabic.
     */
    public static function translateArticle($titleEn, $contentEn, $options = [])
    {
        $db = new Database();
        $keys = [
            'ai_provider', 'openai_api_key', 'openai_model', 'gemini_api_key', 'gemini_model',
            'omniroute_endpoint', 'omniroute_api_key', 'omniroute_model',
            'groq_api_key', 'groq_model', 'deepseek_api_key', 'deepseek_model',
            'custom_api_endpoint', 'custom_api_key', 'custom_api_model',
            'opencode_fallback_enabled', 'opencode_model',
            'ai_fallback_enabled', 'ai_temperature', 'ai_system_prompt'
        ];
        $inClause = "'" . implode("','", $keys) . "'";
        $settingsRow = $db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ({$inClause})");
        $settings = array_column($settingsRow, 'value', 'key');

        $activeProvider = $settings['ai_provider'] ?? 'omniroute';
        $fallbackOn     = ($settings['ai_fallback_enabled'] ?? '1') == '1';
        $articleId      = $options['article_id'] ?? null;

        $attempts = [];
        $providersStatus = [
            'omniroute'  => [
                'id'    => 'omniroute',
                'name'  => '🌐 نفق Omniroute (Omniroute Gateway & Tunnel)',
                'ready' => !empty($settings['omniroute_endpoint']),
                'desc'  => !empty($settings['omniroute_endpoint']) ? 'عنوان النفق مسجل: ' . $settings['omniroute_endpoint'] : 'عنوان النفق غير محدد'
            ],
            'gemini'     => [
                'id'    => 'gemini',
                'name'  => '⚡ Google Gemini (1.5 Flash / 2.0 Flash)',
                'ready' => !empty($settings['gemini_api_key']),
                'desc'  => !empty($settings['gemini_api_key']) ? 'المفتاح مسجل في النظام' : 'مفتاح API غير مدخل في الإعدادات'
            ],
            'groq'       => [
                'id'    => 'groq',
                'name'  => '🚀 Groq Cloud (Llama 3.3 70B / 8B)',
                'ready' => !empty($settings['groq_api_key']),
                'desc'  => !empty($settings['groq_api_key']) ? 'المفتاح مسجل في النظام' : 'مفتاح API غير مدخل في الإعدادات'
            ],
            'deepseek'   => [
                'id'    => 'deepseek',
                'name'  => '🧠 DeepSeek (DeepSeek-V3 / R1)',
                'ready' => !empty($settings['deepseek_api_key']),
                'desc'  => !empty($settings['deepseek_api_key']) ? 'المفتاح مسجل في النظام' : 'مفتاح API غير مدخل في الإعدادات'
            ],
            'openai'     => [
                'id'    => 'openai',
                'name'  => '✨ OpenAI (GPT-4o / GPT-4o-mini)',
                'ready' => !empty($settings['openai_api_key']),
                'desc'  => !empty($settings['openai_api_key']) ? 'المفتاح مسجل في النظام' : 'مفتاح API غير مدخل في الإعدادات'
            ],
            'custom_api' => [
                'id'    => 'custom_api',
                'name'  => '🛠️ خادم API مخصص (Custom Endpoint)',
                'ready' => !empty($settings['custom_api_endpoint']),
                'desc'  => !empty($settings['custom_api_endpoint']) ? 'عنوان الخادم مسجل' : 'الرابط غير محدد في الإعدادات'
            ],
            'opencode'   => [
                'id'    => 'opencode',
                'name'  => '🤖 OpenCode Zen (احتياط مجاني - بدون مفتاح)',
                'ready' => ($settings['opencode_fallback_enabled'] ?? '1') == '1',
                'desc'  => 'بوابة OpenCode Zen المجانية عبر هيدر جلسة، محدود بحصة IP يومية'
            ],
            'mymemory'   => [
                'id'    => 'mymemory',
                'name'  => '🌐 المترجم المجاني الفوري (Google Web + القاموس التقني)',
                'ready' => true,
                'desc'  => 'مجاني ومتاح دائماً بدون أي مفاتيح أو اشتراكات'
            ],
        ];

        // Helper to attach diagnostics to result
        $buildResult = function ($data, $used) use ($activeProvider, $attempts, $providersStatus) {
            $data['diagnostics'] = [
                'active_provider'  => $activeProvider,
                'used_provider'    => $used,
                'attempts'         => $attempts,
                'providers_status' => $providersStatus
            ];
            return $data;
        };

        // --- Step 1: Execute Primary Selected Provider ---
        if ($activeProvider === 'omniroute') {
            if (!empty($settings['omniroute_endpoint'])) {
                $res = self::callOmniroute($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'omniroute');
                $attempts['omniroute'] = $res['error'] ?? 'فشل الاتصال بنفق Omniroute.';
            } else {
                $attempts['omniroute'] = 'عنوان نفق Omniroute Endpoint غير محدد في لوحة الإعدادات.';
            }
        } elseif ($activeProvider === 'gemini') {
            if (!empty($settings['gemini_api_key'])) {
                $res = self::callGemini($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'gemini');
                $attempts['gemini'] = $res['error'] ?? 'فشل الاتصال بخادم Google Gemini.';
            } else {
                $attempts['gemini'] = 'مفتاح Google Gemini API Key غير مدخل في لوحة الإعدادات.';
            }
        } elseif ($activeProvider === 'groq') {
            if (!empty($settings['groq_api_key'])) {
                $res = self::callGroq($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'groq');
                $attempts['groq'] = $res['error'] ?? 'فشل الاتصال بخادم Groq Cloud.';
            } else {
                $attempts['groq'] = 'مفتاح Groq API Key غير مدخل في لوحة الإعدادات.';
            }
        } elseif ($activeProvider === 'deepseek') {
            if (!empty($settings['deepseek_api_key'])) {
                $res = self::callDeepSeek($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'deepseek');
                $attempts['deepseek'] = $res['error'] ?? 'فشل الاتصال بخادم DeepSeek.';
            } else {
                $attempts['deepseek'] = 'مفتاح DeepSeek API Key غير مدخل في لوحة الإعدادات.';
            }
        } elseif ($activeProvider === 'openai') {
            if (!empty($settings['openai_api_key'])) {
                $res = self::callOpenAi($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'openai');
                $attempts['openai'] = $res['error'] ?? 'فشل الاتصال بخادم OpenAI.';
            } else {
                $attempts['openai'] = 'مفتاح OpenAI API Key غير مدخل في لوحة الإعدادات.';
            }
        } elseif ($activeProvider === 'custom_api') {
            if (!empty($settings['custom_api_endpoint'])) {
                $res = self::callCustomApi($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'custom_api');
                $attempts['custom_api'] = $res['error'] ?? 'فشل الاتصال بنقطة النهاية المخصصة.';
            } else {
                $attempts['custom_api'] = 'عنوان Custom API Endpoint غير مدخل في إعدادات المنصة.';
            }
        } elseif ($activeProvider === 'opencode') {
            if (($settings['opencode_fallback_enabled'] ?? '1') != '1') {
                $attempts[$activeProvider] = 'OpenCode Zen معطل في الإعدادات.';
            } else {
                $res = self::callOpencode($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'opencode');
                $attempts[$activeProvider] = $res['error'] ?? 'فشل الاتصال بـ OpenCode Zen.';
            }
        } elseif ($activeProvider === 'mymemory' || $activeProvider === 'gtx') {
            $res = self::freeTranslateWithGlossary($titleEn, $contentEn, $articleId);
            return $buildResult($res, 'mymemory');
        }

        // --- Step 2: Fallback Chain if enabled ---
        if ($fallbackOn) {
            if ($activeProvider !== 'omniroute' && !empty($settings['omniroute_endpoint'])) {
                $res = self::callOmniroute($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'omniroute');
                $attempts['fallback_omniroute'] = $res['error'] ?? 'تعذر استخدام Omniroute كخيار احتياطي.';
            }
            if ($activeProvider !== 'gemini' && !empty($settings['gemini_api_key'])) {
                $res = self::callGemini($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'gemini');
                $attempts['fallback_gemini'] = $res['error'] ?? 'تعذر استخدام Google Gemini كخيار احتياطي.';
            }
            if ($activeProvider !== 'groq' && !empty($settings['groq_api_key'])) {
                $res = self::callGroq($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'groq');
                $attempts['fallback_groq'] = $res['error'] ?? 'تعذر استخدام Groq كخيار احتياطي.';
            }
            if ($activeProvider !== 'deepseek' && !empty($settings['deepseek_api_key'])) {
                $res = self::callDeepSeek($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'deepseek');
                $attempts['fallback_deepseek'] = $res['error'] ?? 'تعذر استخدام DeepSeek كخيار احتياطي.';
            }
            if ($activeProvider !== 'openai' && !empty($settings['openai_api_key'])) {
                $res = self::callOpenAi($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'openai');
                $attempts['fallback_openai'] = $res['error'] ?? 'تعذر استخدام OpenAI كخيار احتياطي.';
            }
            if ($activeProvider !== 'custom_api' && !empty($settings['custom_api_endpoint'])) {
                $res = self::callCustomApi($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'custom_api');
                $attempts['fallback_custom_api'] = $res['error'] ?? 'تعذر استخدام Custom API كخيار احتياطي.';
            }
            if ($activeProvider !== 'opencode' && ($settings['opencode_fallback_enabled'] ?? '1') == '1') {
                $res = self::callOpencode($titleEn, $contentEn, $settings, $articleId);
                if ($res['success']) return $buildResult($res['data'], 'opencode');
                $attempts['fallback_opencode'] = $res['error'] ?? 'تعذر استخدام OpenCode Zen كخيار احتياطي.';
            }

            // Final Fallback: Free Google Web + Tech Glossary Engine
            $res = self::freeTranslateWithGlossary($titleEn, $contentEn, $articleId);
            return $buildResult($res, 'mymemory');
        }

        // If fallback is disabled and primary provider failed:
        $res = self::freeTranslateWithGlossary($titleEn, $contentEn, $articleId);
        return $buildResult($res, 'mymemory');
    }

    private static function callOpenAi($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $apiKey = $cfg['openai_api_key'] ?? '';
        $model = !empty($cfg['openai_model']) ? $cfg['openai_model'] : 'gpt-4o-mini';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;
        $systemPrompt = !empty($cfg['ai_system_prompt']) 
            ? $cfg['ai_system_prompt'] . "\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح: title_ar, excerpt, content_ar, reading_time_minutes."
            : "أنت كبير محرري الأخبار التقنية باللغة العربية في منصة عالمية رائدة. مهمتك صياغة وترجمة الخبر التقني الإنجليزي إلى خبر عربي احترافي وجذاب بصياغة صحفية رصينة ودقيقة للمصطلحات التقنية، مع تجنب الترجمة الحرفية تماماً.\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح التالية:\n- title_ar: عنوان صحفي جذاب ومختصر باللغة العربية.\n- excerpt: ملخص تنفيذي مركز (TL;DR) في سطرين.\n- content_ar: محتوى الخبر كاملاً باللغة العربية منسقاً بفقرات واضحة.\n- reading_time_minutes: عدد دقائق القراءة المتوقع.";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3000);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => $temp
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT        => 22
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $parsed = json_decode($data['choices'][0]['message']['content'] ?? '{}', true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "openai_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'openai',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        // Parse OpenAI error details
        $errorSummary = "فشل استجابة OpenAI ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "OpenAI Error [{$errData['error']['type']}]: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'openai',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    private static function callGemini($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $apiKey = $cfg['gemini_api_key'] ?? '';
        $selectedModel = !empty($cfg['gemini_model']) ? $cfg['gemini_model'] : 'gemini-3.7-flash';
        if (in_array($selectedModel, ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-2.0-flash-exp'], true)) {
            $selectedModel = 'gemini-3.7-flash';
        }
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;

        // Build candidate models list starting with selected model, then auto-fallback if 503/high-demand
        $candidateModels = [$selectedModel];
        $backupModels = ['gemini-3.6-flash', 'gemini-3.5-flash', 'gemini-3.7-flash', 'gemini-3.1-flash-lite', 'gemini-2.5-flash-lite'];
        foreach ($backupModels as $bm) {
            if (!in_array($bm, $candidateModels, true)) {
                $candidateModels[] = $bm;
            }
        }

        $prompt = (!empty($cfg['ai_system_prompt']) ? $cfg['ai_system_prompt'] . "\n\n" : "أنت كبير محرري الأخبار التقنية باللغة العربية. ترجم وصِغ الخبر الإنجليزي التالي إلى خبر تقني عربي احترافي غير حرفي وبأعلى معايير المصطلحات التقنية:\n\n")
            . "Title: {$titleEn}\nContent: " . substr(strip_tags($contentEn), 0, 3000) . "\n\nأرجع النتيجة بصيغة JSON فقط: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature'      => $temp
            ]
        ];

        $lastError = 'فشل الاتصال بخوادم Google Gemini';
        $lastHttpCode = 0;

        foreach ($candidateModels as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 18
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            $lastHttpCode = $httpCode;

            if ($httpCode === 200 && $res) {
                $data = json_decode($res, true);
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $parsed = json_decode($rawText, true);
                if (!empty($parsed['title_ar'])) {
                    $durationMs = (int) round((microtime(true) - $startTime) * 1000);
                    $parsed['mode'] = "gemini_{$model}";
                    self::logOperation([
                        'article_id'       => $articleId,
                        'article_title_en' => $titleEn,
                        'provider'         => 'gemini',
                        'status'           => 'success',
                        'http_code'        => $httpCode,
                        'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                        'duration_ms'      => $durationMs
                    ]);
                    return ['success' => true, 'data' => $parsed];
                }
            }

            // If this model had 503 (High Demand) or 429 or 404, capture error and loop to next candidate
            $errorSummary = "فشل استجابة Google Gemini ({$model})";
            if ($curlErr) {
                $errorSummary = "cURL Error: {$curlErr}";
            } elseif ($res) {
                $errData = json_decode($res, true);
                if (!empty($errData['error']['message'])) {
                    $errorSummary = "Gemini Error [{$errData['error']['code']}]: {$errData['error']['message']}";
                }
            }
            $lastError = $errorSummary;
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'gemini',
            'status'           => 'failed',
            'http_code'        => $lastHttpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $lastError,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $lastError];
    }

    /**
     * Universal Custom OpenAI-compatible Provider (DeepSeek, Groq, OpenRouter, Local Ollama, etc.)
     */
    private static function callCustomApi($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $endpoint = rtrim($cfg['custom_api_endpoint'] ?? '', '/');
        $apiKey = $cfg['custom_api_key'] ?? '';
        $model = !empty($cfg['custom_api_model']) ? $cfg['custom_api_model'] : 'deepseek-chat';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;

        if (!str_ends_with($endpoint, '/chat/completions')) {
            $endpoint .= '/chat/completions';
        }

        $systemPrompt = !empty($cfg['ai_system_prompt']) 
            ? $cfg['ai_system_prompt'] . "\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح: title_ar, excerpt, content_ar, reading_time_minutes."
            : "أنت كبير محرري الأخبار التقنية باللغة العربية. ترجم وصِغ الخبر الإنجليزي التالي إلى خبر تقني عربي احترافي غير حرفي وبأعلى معايير المصطلحات التقنية.\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3000);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
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
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $rawContent = $data['choices'][0]['message']['content'] ?? '';
            // Strip any markdown fences if present
            $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            $parsed = json_decode($rawContent, true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "custom_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'custom_api',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        $errorSummary = "فشل الاتصال بالمزود المخصص ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "Custom API Error: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'custom_api',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    /**
     * 🤖 OpenCode Zen Free Fallback Provider (no API key required)
     * Uses the same session-header identity trick as the OpenCode CLI.
     */
    private static function callOpencode($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $model = !empty($cfg['opencode_model']) ? $cfg['opencode_model'] : 'big-pickle';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;
        $endpoint = 'https://opencode.ai/zen/v1/chat/completions';

        $systemPrompt = !empty($cfg['ai_system_prompt'])
            ? $cfg['ai_system_prompt'] . "\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح: title_ar, excerpt, content_ar, reading_time_minutes."
            : "أنت كبير محرري الأخبار التقنية باللغة العربية. ترجم وصِغ الخبر الإنجليزي التالي إلى خبر تقني عربي احترافي غير حرفي وبأعلى معايير المصطلحات التقنية.\nقم بالرد بصيغة JSON فقط متضمناً المفاتيح: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3000);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temp
        ];

        $sessionId = 'ses_' . bin2hex(random_bytes(32));
        $headers = [
            'Content-Type: application/json',
            'x-opencode-session: ' . $sessionId,
            'x-opencode-client: tui',
            'x-opencode-request: usr_' . substr($sessionId, 4, 8),
            'User-Agent: opencode/0.1.0',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($curlErr) {
            self::logOperation([
                'article_id'       => $articleId,
                'article_title_en' => $titleEn,
                'provider'         => 'opencode',
                'status'           => 'failed',
                'http_code'        => $httpCode,
                'error_raw'        => $res ?: $curlErr,
                'error_summary'    => "OpenCode Zen Network: {$curlErr} (ربما الحصة اليومية لكل IP وصلت حدها — جرّب لاحقاً أو غيّر مزوداً)",
                'duration_ms'      => $durationMs
            ]);
            return ['success' => false, 'error' => 'OpenCode Zen: ' . $curlErr . ' (حصة IP المجانية قد تكون مستنفدة)'];
        }

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $rawContent = $data['choices'][0]['message']['content'] ?? '';
            // Strip any markdown fences if present
            $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            $parsed = json_decode($rawContent, true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "opencode_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'opencode',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        $errorSummary = "فشل الاتصال بـ OpenCode Zen ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (in_array($httpCode, [401, 403, 429], true)) {
                $errorSummary = "OpenCode Zen رفض الطلب (HTTP {$httpCode}): الحصة المجانية لـ IP الوصول قد تكون مستنفدة أو الطريق غير متاح. " . ($errData['error']['message'] ?? '');
            } elseif (!empty($errData['error']['message'])) {
                $errorSummary = "OpenCode Error: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'opencode',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    /**
     * 🌐 Omniroute Tunnel & AI Gateway Provider
     */
    private static function callOmniroute($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $endpoint = rtrim($cfg['omniroute_endpoint'] ?? 'http://127.0.0.1:8080/v1', '/');
        $apiKey = $cfg['omniroute_api_key'] ?? '';
        $model = !empty($cfg['omniroute_model']) ? $cfg['omniroute_model'] : 'omniroute/gemini-3.7-flash-high';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;

        if (!str_ends_with($endpoint, '/chat/completions')) {
            $endpoint .= '/chat/completions';
        }

        $systemPrompt = (!empty($cfg['ai_system_prompt']) ? $cfg['ai_system_prompt'] . "\n" : "أنت كبير محرري الأخبار التقنية باللغة العربية.\n")
            . "قم بالترجمة والصياغة الصحفية والرد بصيغة JSON فقط متضمناً المفاتيح: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3500);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
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
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $rawContent = $data['choices'][0]['message']['content'] ?? '';
            $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            $parsed = json_decode($rawContent, true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "omniroute_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'omniroute',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        $errorSummary = "فشل الاتصال بنفق Omniroute ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "Omniroute Error: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'omniroute',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    /**
     * ⚡ Groq Cloud Provider (Llama 3.3 / Mixtral)
     */
    private static function callGroq($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $apiKey = $cfg['groq_api_key'] ?? '';
        $model = !empty($cfg['groq_model']) ? $cfg['groq_model'] : 'llama-3.3-70b-versatile';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;

        $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

        $systemPrompt = (!empty($cfg['ai_system_prompt']) ? $cfg['ai_system_prompt'] . "\n" : "أنت كبير محرري الأخبار التقنية باللغة العربية.\n")
            . "قم بالترجمة والصياغة والرد بصيغة JSON فقط متضمناً المفاتيح: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3500);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temp,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT        => 20
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $rawContent = $data['choices'][0]['message']['content'] ?? '';
            $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            $parsed = json_decode($rawContent, true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "groq_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'groq',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        $errorSummary = "فشل الاتصال بخادم Groq ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "Groq Error: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'groq',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    /**
     * 🧠 DeepSeek Provider (DeepSeek-V3 / DeepSeek-R1)
     */
    private static function callDeepSeek($titleEn, $contentEn, array $cfg, $articleId = null)
    {
        $startTime = microtime(true);
        $apiKey = $cfg['deepseek_api_key'] ?? '';
        $model = !empty($cfg['deepseek_model']) ? $cfg['deepseek_model'] : 'deepseek-chat';
        $temp = isset($cfg['ai_temperature']) && is_numeric($cfg['ai_temperature']) ? (float)$cfg['ai_temperature'] : 0.3;

        $endpoint = 'https://api.deepseek.com/chat/completions';

        $systemPrompt = (!empty($cfg['ai_system_prompt']) ? $cfg['ai_system_prompt'] . "\n" : "أنت كبير محرري الأخبار التقنية باللغة العربية.\n")
            . "قم بالترجمة والصياغة والرد بصيغة JSON فقط متضمناً المفاتيح: {\"title_ar\": \"...\", \"excerpt\": \"...\", \"content_ar\": \"...\", \"reading_time_minutes\": 3}";

        $userPrompt = "Title: {$titleEn}\n\nContent:\n" . substr(strip_tags($contentEn), 0, 3500);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temp,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT        => 25
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $res) {
            $data = json_decode($res, true);
            $rawContent = $data['choices'][0]['message']['content'] ?? '';
            $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            $parsed = json_decode($rawContent, true);
            if (!empty($parsed['title_ar'])) {
                $parsed['mode'] = "deepseek_{$model}";
                self::logOperation([
                    'article_id'       => $articleId,
                    'article_title_en' => $titleEn,
                    'provider'         => 'deepseek',
                    'status'           => 'success',
                    'http_code'        => $httpCode,
                    'response_preview' => mb_substr($parsed['title_ar'] . ' - ' . ($parsed['excerpt'] ?? ''), 0, 400, 'UTF-8'),
                    'duration_ms'      => $durationMs
                ]);
                return ['success' => true, 'data' => $parsed];
            }
        }

        $errorSummary = "فشل الاتصال بخادم DeepSeek ({$model})";
        if ($curlErr) {
            $errorSummary = "cURL Error: {$curlErr}";
        } elseif ($res) {
            $errData = json_decode($res, true);
            if (!empty($errData['error']['message'])) {
                $errorSummary = "DeepSeek Error: {$errData['error']['message']}";
            }
        }

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'deepseek',
            'status'           => 'failed',
            'http_code'        => $httpCode,
            'error_raw'        => $res ?: $curlErr,
            'error_summary'    => $errorSummary,
            'duration_ms'      => $durationMs
        ]);

        return ['success' => false, 'error' => $errorSummary];
    }

    /**
     * 100% Free Translation Engine: MyMemory API + Smart HTML Chunking + Tech Glossary Normalizer
     */
    public static function freeTranslateWithGlossary($titleEn, $contentEn, $articleId = null)
    {
        $startTime = microtime(true);
        $cleanTitle = self::cleanRawText(strip_tags($titleEn));

        // 1. Translate Title using free engine
        $translatedTitle = self::freeTranslateText($cleanTitle);
        $titleSuccess = !empty($translatedTitle) && $translatedTitle !== $cleanTitle;
        if (empty($translatedTitle)) {
            $translatedTitle = $cleanTitle;
        }

        // 2. Extract structured paragraphs from HTML or raw content
        $rawBlocks = preg_split('/(<\/(?:p|div|section|article|h[1-6]|li)>|<br\s*\/?>|[\r\n]+)/i', (string) $contentEn);
        $paragraphs = [];
        foreach ($rawBlocks as $block) {
            $clean = trim(self::cleanRawText(strip_tags($block)));
            if (mb_strlen($clean, 'UTF-8') > 3) {
                $paragraphs[] = $clean;
            }
        }

        if (empty($paragraphs)) {
            $clean = trim(self::cleanRawText(strip_tags($contentEn)));
            if (!empty($clean)) {
                $paragraphs = [$clean];
            }
        }

        // 3. Translate all paragraphs with sentence-level chunking for long blocks
        $translatedParagraphs = [];
        foreach ($paragraphs as $p) {
            // If paragraph is predominantly Arabic and has no long English words
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $p) && !preg_match('/[a-zA-Z]{6,}/', $p)) {
                $translatedParagraphs[] = $p;
                continue;
            }

            // If paragraph is very long (> 1000 chars), split by sentence boundaries
            if (mb_strlen($p, 'UTF-8') > 1000) {
                $sentences = preg_split('/(?<=[.?!])\s+/u', $p);
                $chunk = '';
                $pTranslated = '';
                foreach ($sentences as $s) {
                    if (mb_strlen($chunk . ' ' . $s, 'UTF-8') > 800) {
                        $tr = self::freeTranslateText($chunk);
                        $pTranslated .= ($pTranslated ? ' ' : '') . ($tr ?: $chunk);
                        $chunk = $s;
                    } else {
                        $chunk .= ($chunk ? ' ' : '') . $s;
                    }
                }
                if (!empty($chunk)) {
                    $tr = self::freeTranslateText($chunk);
                    $pTranslated .= ($pTranslated ? ' ' : '') . ($tr ?: $chunk);
                }
                $translatedParagraphs[] = $pTranslated ?: $p;
            } else {
                $tr = self::freeTranslateText($p);
                $translatedParagraphs[] = $tr ?: $p;
            }
        }

        // 4. Post-process with specialized Tech Glossary to enforce journalistic terms
        $finalTitle = self::cleanRawText(self::applyTechGlossary($translatedTitle));
        $formattedHtml = '';
        foreach ($translatedParagraphs as $tp) {
            $tp = self::cleanRawText(self::applyTechGlossary($tp));
            $formattedHtml .= "<p>" . $tp . "</p>\n";
        }

        $excerpt = mb_strimwidth(strip_tags($formattedHtml), 0, 240, '…', 'UTF-8');
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $status = $titleSuccess ? 'fallback' : 'failed';
        $summary = $titleSuccess 
            ? 'تمت الترجمة عبر المزود الاحتياطي (MyMemory + القاموس التقني)'
            : 'لم يتمكن المزود الاحتياطي من ترجمة النص بالكامل (محدودية الاستجابة)';

        self::logOperation([
            'article_id'       => $articleId,
            'article_title_en' => $titleEn,
            'provider'         => 'mymemory',
            'status'           => $status,
            'http_code'        => $titleSuccess ? 200 : 429,
            'error_summary'    => $titleSuccess ? null : $summary,
            'response_preview' => mb_substr($finalTitle . ' - ' . $excerpt, 0, 400, 'UTF-8'),
            'duration_ms'      => $durationMs
        ]);

        return [
            'title_ar'             => $finalTitle,
            'excerpt'              => $excerpt,
            'content_ar'           => $formattedHtml ?: "<p>{$finalTitle}</p>",
            'reading_time_minutes' => max(2, (int) ceil(str_word_count(strip_tags($contentEn)) / 160)),
            'mode'                 => 'free_gtx_glossary'
        ];
    }

    /**
     * Deep decode and clean all broken HTML entities like &amp;#8211; or &#8211;
     */
    public static function cleanRawText($text)
    {
        if (empty($text)) return '';

        // 1. Double html_entity_decode to handle double/triple escaped entities
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 2. Normalize and replace common typographic and feed entities
        $replacements = [
            '&#8211;'    => '–',
            '&#8212;'    => '—',
            '&#8216;'    => "'",
            '&#8217;'    => "'",
            '&#8220;'    => '"',
            '&#8221;'    => '"',
            '&#039;'     => "'",
            '&#39;'      => "'",
            '&quot;'     => '"',
            '&amp;'      => '&',
            '&nbsp;'     => ' ',
            '&ndash;'    => '–',
            '&mdash;'    => '—',
            'amp;#8211;' => '–',
            'amp;#8212;' => '—',
            'amp;#039;'  => "'",
            'amp;#39;'   => "'",
            'amp;quot;'  => '"',
            'amp;amp;'   => '&',
        ];
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // 3. Clean any remaining broken entity remnants
        $text = preg_replace('/&?amp;#(\d+);?/i', ' ', $text);
        $text = preg_replace('/&#(\d+);?/', ' ', $text);

        return trim($text);
    }

    /**
     * Free Translation — MyMemory API (primary) with GTx fallback
     * MyMemory: https://mymemory.translated.net/api/get?q=...&langpair=en|ar
     * Free tier: 1000 words/day without API key, 10k with email param
     */
    public static function freeTranslateText($text, $sourceLang = 'auto', $targetLang = 'ar')
    {
        $text = trim($text);
        if (empty($text)) return '';

        // If text is already predominantly Arabic without long English words, return it
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text) && !preg_match('/[a-zA-Z]{6,}/', $text)) {
            return $text;
        }

        // --- Engine 1 (Primary High Speed & Zero Quota Limit): Google Translate Web ---
        $googleMUrl = 'https://translate.google.com/m?sl=' . $sourceLang . '&tl=' . $targetLang . '&q=' . rawurlencode($text);
        $ch = curl_init($googleMUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9,ar;q=0.8'
            ]
        ]);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $html && preg_match('/class="result-container">(.*?)<\/div>/s', $html, $m)) {
            $translated = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            if (!empty($translated) && preg_match('/[\x{0600}-\x{06FF}]/u', $translated)) {
                return $translated;
            }
        }

        // --- Engine 2 (Fallback): Lingva Public Mirror ---
        $cleanSlice = mb_substr($text, 0, 800, 'UTF-8');
        $lingvaUrl = 'https://lingva.ml/api/v1/' . ($sourceLang === 'auto' ? 'en' : $sourceLang) . '/' . $targetLang . '/' . rawurlencode($cleanSlice);
        $ch2 = curl_init($lingvaUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'
            ]
        ]);
        $res2 = curl_exec($ch2);
        $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($httpCode2 === 200 && $res2) {
            $data2 = json_decode($res2, true);
            if (!empty($data2['translation']) && preg_match('/[\x{0600}-\x{06FF}]/u', $data2['translation'])) {
                return trim($data2['translation']);
            }
        }

        // --- Engine 3 (Fallback): MyMemory API with strict 3s timeout ---
        $randomEmail = 'user' . rand(1000, 9999) . '@technews.org';
        $mmUrl = 'https://api.mymemory.translated.net/get?'
            . 'q=' . rawurlencode(mb_substr($text, 0, 500, 'UTF-8'))
            . '&langpair=' . ($sourceLang === 'auto' ? 'en' : $sourceLang) . '|' . $targetLang
            . '&de=' . urlencode($randomEmail);

        $ch3 = curl_init($mmUrl);
        curl_setopt_array($ch3, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Mozilla/5.0 (compatible; TechNewsPlatform/1.0)'
            ]
        ]);
        $res3 = curl_exec($ch3);
        $httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
        curl_close($ch3);

        if ($httpCode3 === 200 && $res3) {
            $data3 = json_decode($res3, true);
            $translated = $data3['responseData']['translatedText'] ?? '';
            if (!empty($translated) && $translated !== $text && preg_match('/[\x{0600}-\x{06FF}]/u', $translated)) {
                return trim($translated);
            }
        }

        return '';
    }

    /**
     * Helper to log translation attempt into translation_logs table
     */
    public static function logOperation(array $data)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO translation_logs 
                (article_id, article_title_en, provider, status, http_code, error_raw, error_summary, response_preview, duration_ms)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['article_id'] ?? null,
                mb_substr((string)($data['article_title_en'] ?? ''), 0, 500, 'UTF-8'),
                $data['provider'] ?? 'none',
                $data['status'] ?? 'failed',
                !empty($data['http_code']) ? (int)$data['http_code'] : null,
                !empty($data['error_raw']) ? (string)$data['error_raw'] : null,
                !empty($data['error_summary']) ? mb_substr((string)$data['error_summary'], 0, 500, 'UTF-8') : null,
                !empty($data['response_preview']) ? mb_substr((string)$data['response_preview'], 0, 500, 'UTF-8') : null,
                !empty($data['duration_ms']) ? (int)$data['duration_ms'] : null
            ]);
        } catch (Throwable $e) {
            // Fail silently to never break the main app
            error_log('AiTranslator::logOperation failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize with precise tech glossary
     */
    public static function applyTechGlossary($text)
    {
        foreach (self::$techGlossary as $en => $ar) {
            $text = str_ireplace($en, $ar, $text);
        }
        return $text;
    }
}
