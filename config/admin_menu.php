<?php
/**
 * Admin Panel Navigation Menu Configuration
 *
 * Customize sidebar sections, menu items, icons, badges, and roles.
 * Developers can add any new resource/section here in seconds.
 *
 * Each section and item may carry a stable `key`. The same keys are used by
 * the admin settings group `admin_menu` to let admins show/hide every section
 * and every item from the dashboard without touching code.
 */

return [
    [
        'section' => 'الرئيسية والتحليلات',
        'key'     => 'dashboard',
        'items'   => [
            [
                'key'   => 'dashboard',
                'title' => 'الرئيسية (Dashboard)',
                'path'  => 'admin',
                'icon'  => 'bi bi-speedometer2',
            ],
            [
                'key'   => 'analytics',
                'title' => 'التحليلات والإحصاءات',
                'path'  => 'admin/analytics',
                'icon'  => 'bi bi-graph-up-arrow',
            ],
        ]
    ],

    [
        'section' => 'إدارة المحتوى والبيانات',
        'key'     => 'content',
        'items'   => [
            [
                'key'   => 'articles',
                'title' => 'المقالات والمحتوى',
                'path'  => 'admin/articles',
                'icon'  => 'bi bi-journal-richtext',
            ],
            [
                'key'   => 'categories',
                'title' => 'التصنيفات',
                'path'  => 'admin/categories',
                'icon'  => 'bi bi-tags',
            ],
            [
                'key'   => 'media',
                'title' => 'مكتبة الوسائط',
                'path'  => 'admin/media',
                'icon'  => 'bi bi-images',
            ],
            [
                'key'   => 'pages',
                'title' => 'الصفحات الثابتة',
                'path'  => 'admin/pages',
                'icon'  => 'bi bi-file-earmark-text',
            ],
            [
                'key'   => 'menus',
                'title' => 'القوائم والروابط',
                'path'  => 'admin/menus',
                'icon'  => 'bi bi-list-nested',
            ],
            [
                'key'   => 'comments',
                'title' => 'التعليقات والمراجعة',
                'path'  => 'admin/comments',
                'icon'  => 'bi bi-chat-dots',
            ],
        ]
    ],

    [
        'section' => 'التواصل والتفاعل',
        'key'     => 'interaction',
        'items'   => [
            [
                'key'   => 'messages',
                'title' => 'صندوق الرسائل والاتصالات',
                'path'  => 'admin/messages',
                'icon'  => 'bi bi-inbox text-info',
                'badge' => function() {
                    if (class_exists('Database')) {
                        try {
                            $cnt = (int) ((new Database())->fetch("SELECT COUNT(*) as cnt FROM contact_messages WHERE status = 'unread'")['cnt'] ?? 0);
                            return $cnt > 0 ? ['text' => (string)$cnt, 'class' => 'bg-danger'] : null;
                        } catch (Throwable $e) {}
                    }
                    return null;
                }
            ],
            [
                'key'   => 'newsletter',
                'title' => 'النشرة البريدية',
                'path'  => 'admin/newsletter',
                'icon'  => 'bi bi-envelope-paper',
            ],
            [
                'key'   => 'ads',
                'title' => 'المساحات الإعلانية',
                'path'  => 'admin/ads',
                'icon'  => 'bi bi-badge-ad',
            ],
            [
                'key'   => 'polls',
                'title' => 'استطلاعات الرأي (Polls)',
                'path'  => 'admin/polls',
                'icon'  => 'bi bi-bar-chart-line-fill text-warning',
            ],
            [
                'key'   => 'tutorials',
                'title' => 'استوديو الشروحات والدروس',
                'path'  => 'admin/tutorials',
                'icon'  => 'bi bi-journal-code text-info',
            ],
            [
                'key'   => 'live_blog',
                'title' => 'التغطيات الحية (Live Blog)',
                'path'  => 'admin/live-blog',
                'icon'  => 'bi bi-broadcast text-danger',
            ],
        ]
    ],

    [
        'section' => 'الأتمتة والذكاء الاصطناعي',
        'key'     => 'automation',
        'items'   => [
            [
                'key'   => 'news_feeds',
                'title' => 'استيراد ونشر الأخبار (RSS)',
                'path'  => 'admin/news-feeds',
                'icon'  => 'bi bi-lightning-charge-fill text-warning',
            ],
            [
                'key'   => 'rss_sources',
                'title' => 'مصادر الـ RSS (CRUD)',
                'path'  => 'admin/rss-sources',
                'icon'  => 'bi bi-rss-fill text-warning',
            ],
            [
                'key'   => 'cron',
                'title' => 'النشر التلقائي (Cron Jobs)',
                'path'  => 'admin/cron',
                'icon'  => 'bi bi-clock-history text-success',
            ],
            [
                'key'   => 'classifier_rules',
                'title' => 'مصطلحات التصنيف الذكي',
                'path'  => 'admin/classifier-rules',
                'icon'  => 'bi bi-diagram-3-fill text-primary',
            ],
            [
                'key'   => 'translation_logs',
                'title' => 'سجلات وأخطاء الترجمة والـ AI',
                'path'  => 'admin/translation-logs',
                'icon'  => 'bi bi-translate text-success',
            ],
            [
                'key'   => 'ai_logs',
                'title' => 'محادثات المرشد الذكي والحصص',
                'path'  => 'admin/ai-logs',
                'icon'  => 'bi bi-robot text-primary',
            ],
        ]
    ],

    [
        'section' => 'الأمان والتشخيص والنظام',
        'key'     => 'system',
        'items'   => [
            [
                'key'   => 'users',
                'title' => 'المستخدمون والصلاحيات',
                'path'  => 'admin/users',
                'icon'  => 'bi bi-people',
            ],
            [
                'key'   => 'api_keys',
                'title' => 'نقاط النهاية والـ API Keys',
                'path'  => 'admin/api-keys',
                'icon'  => 'bi bi-key-fill text-info',
            ],
            [
                'key'   => 'activity_log',
                'title' => 'سجل العمليات (Audit Log)',
                'path'  => 'admin/activity-log',
                'icon'  => 'bi bi-shield-check',
            ],
            [
                'key'   => 'traffic_radar',
                'title' => 'رادار الزوار والعناكب (Bots)',
                'path'  => 'admin/traffic-radar',
                'icon'  => 'bi bi-broadcast-pin text-info',
            ],
            [
                'key'   => 'security_alerts',
                'title' => 'تنبيهات الأمان والاختراق',
                'path'  => 'admin/security-alerts',
                'icon'  => 'bi bi-shield-exclamation text-danger',
                'badge' => function() {
                    if (class_exists('Database')) {
                        try {
                            $cnt = (int) ((new Database())->fetch("SELECT COUNT(*) as cnt FROM security_alerts WHERE is_resolved = 0")['cnt'] ?? 0);
                            return $cnt > 0 ? ['text' => (string)$cnt, 'class' => 'bg-danger'] : null;
                        } catch (Throwable $e) {}
                    }
                    return null;
                }
            ],
            [
                'key'   => 'backup',
                'title' => 'النسخ الاحتياطي والاستيراد والتصدير',
                'path'  => 'admin/backup',
                'icon'  => 'bi bi-database-down text-warning',
            ],
            [
                'key'   => 'diagnostics',
                'title' => 'مركز تشخيص النظام الشامل',
                'path'  => 'admin/diagnostics',
                'icon'  => 'bi bi-heart-pulse-fill text-info',
            ],
            [
                'key'   => 'settings',
                'title' => 'الإعدادات الشاملة للمنصة',
                'path'  => 'admin/settings',
                'icon'  => 'bi bi-sliders2',
            ],
        ]
    ],
];