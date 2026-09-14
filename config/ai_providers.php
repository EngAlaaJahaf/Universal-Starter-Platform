<?php
/**
 * AI Providers Configuration & Model Directory
 * Configures all supported AI endpoints, models, default parameters, and failover order.
 */

return [
    'default_provider' => 'omniroute',
    'fallback_enabled' => true,
    
    // Priority order for automatic failover if the primary provider fails
    'failover_order' => [
        'omniroute',
        'gemini',
        'groq',
        'deepseek',
        'openai',
        'custom_api',
        'opencode',
        'mymemory'
    ],

    // Provider Definitions & Endpoints
    'providers' => [
        'omniroute' => [
            'name'        => 'Omniroute Gateway & Tunnel',
            'type'        => 'openai_compatible',
            'default_url' => 'http://127.0.0.1:8080/v1',
            'models'      => [
                'antigravity/gemini-3.7-flash-high' => 'Gemini 3.7 Flash High (Recommended)',
                'opencode/gemini-3.7-flash'         => 'Gemini 3.7 Flash',
                'auto/gemini'                       => 'Auto Gemini (Fastest Available)',
                'auto/fast'                         => 'Auto Fast (Lowest Latency)',
                'auto/best-fast'                    => 'Auto Best-Fast (Balanced)',
                'dva/gemini-3-7-flash-high'         => 'Gemini 3.7 High',
                'cheaperinference/claude-sonnet-4.5-high' => 'Claude Sonnet 4.5 High',
                'cheaperinference/claude-haiku-4.5-high'  => 'Claude Haiku 4.5 High',
            ],
            'default_model' => 'antigravity/gemini-3.7-flash-high',
            'requires_key'  => false,
        ],

        'gemini' => [
            'name'        => 'Google Gemini API',
            'type'        => 'google_gemini',
            'default_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'models'      => [
                'gemini-3.7-flash'      => 'Gemini 3.7 Flash (Recommended)',
                'gemini-3.6-flash'      => 'Gemini 3.6 Flash (Ultra Fast)',
                'gemini-3.5-flash'      => 'Gemini 3.5 Flash',
                'gemini-2.5-pro'        => 'Gemini 2.5 Pro (High Reasoning)',
                'gemini-3.1-flash-lite' => 'Gemini 3.1 Flash Lite',
            ],
            'default_model' => 'gemini-3.7-flash',
            'requires_key'  => true,
        ],

        'groq' => [
            'name'        => 'Groq Cloud (Ultra Fast Llama)',
            'type'        => 'openai_compatible',
            'default_url' => 'https://api.groq.com/openai/v1',
            'models'      => [
                'llama-3.3-70b-versatile'      => 'Llama 3.3 70B Versatile (Recommended)',
                'llama-3.1-8b-instant'         => 'Llama 3.1 8B Instant (Blazing Fast)',
                'mixtral-8x7b-32768'           => 'Mixtral 8x7B (32k Context)',
                'deepseek-r1-distill-llama-70b'=> 'DeepSeek R1 Distill 70B',
            ],
            'default_model' => 'llama-3.3-70b-versatile',
            'requires_key'  => true,
        ],

        'deepseek' => [
            'name'        => 'DeepSeek API',
            'type'        => 'openai_compatible',
            'default_url' => 'https://api.deepseek.com/v1',
            'models'      => [
                'deepseek-chat'     => 'DeepSeek-V3 (deepseek-chat - Smart & Economical)',
                'deepseek-reasoner' => 'DeepSeek-R1 (deepseek-reasoner - Deep Thinking)',
            ],
            'default_model' => 'deepseek-chat',
            'requires_key'  => true,
        ],

        'openai' => [
            'name'        => 'OpenAI (ChatGPT)',
            'type'        => 'openai_compatible',
            'default_url' => 'https://api.openai.com/v1',
            'models'      => [
                'gpt-4o-mini'   => 'GPT-4o Mini (Fast, Cheap & Smart - Recommended)',
                'gpt-4o'        => 'GPT-4o (High Quality Editorial Reasoning)',
                'gpt-4-turbo'   => 'GPT-4 Turbo',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            ],
            'default_model' => 'gpt-4o-mini',
            'requires_key'  => true,
        ],

        'custom_api' => [
            'name'        => 'Custom OpenAI-Compatible API / Ollama / Local',
            'type'        => 'openai_compatible',
            'default_url' => 'http://localhost:11434/v1',
            'models'      => [
                'custom' => 'Custom Endpoint Model',
            ],
            'default_model' => 'custom',
            'requires_key'  => false,
        ],

        'opencode' => [
            'name'        => 'OpenCode Zen Gateway (Zero Config)',
            'type'        => 'opencode',
            'default_url' => 'https://api.opencode.zen',
            'models'      => [
                'default' => 'OpenCode Default Fast AI',
            ],
            'default_model' => 'default',
            'requires_key'  => false,
        ],

        'mymemory' => [
            'name'        => 'Free Translation & Glossary Engine (Offline/Zero-Key)',
            'type'        => 'free_web',
            'models'      => [
                'free_engine' => 'Built-in Free Engine + Arabic Tech Glossary',
            ],
            'default_model' => 'free_engine',
            'requires_key'  => false,
        ],
    ]
];
