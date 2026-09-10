<?php

/**
 * Read-only database health check shown in the admin settings panel.
 * Lets a fresh deploy verify (without phpMyAdmin access) that the hosting DB
 * is up to date: core settings rows present per group, and the columns that
 * optional SQL migrations add. All checks are non-mutating.
 */
class DbHealth
{
    /** Expected core settings rows = the same set ensureCoreRows() seeds. */
    private static function expectedSettings()
    {
        return [
            'appearance' => [
                'breaking_ticker_enabled',
            ],
            'ai_assistant' => [
                'ai_assistant_enabled',
                'ai_assistant_pages',
                'ai_assistant_provider',
                'ai_assistant_model',
                'ai_assistant_temperature',
                'ai_assistant_tone',
                'ai_assistant_context_articles',
                'ai_assistant_include_page',
                'ai_assistant_fallback_enabled',
                'ai_assistant_free_limit',
                'ai_assistant_welcome_message',
                'ai_assistant_placeholder',
                'ai_assistant_suggestions_enabled',
                'ai_assistant_suggestion_1',
                'ai_assistant_suggestion_2',
                'ai_assistant_suggestion_3',
                'ai_assistant_sources_enabled',
                'ai_assistant_privacy_note',
            ],
            'ai_translation' => [
                'ai_provider',
                'openai_api_key',
                'openai_model',
                'gemini_api_key',
                'gemini_model',
                'omniroute_endpoint',
                'custom_api_endpoint',
                'omniroute_api_key',
                'custom_api_key',
                'omniroute_model',
                'custom_api_model',
                'groq_api_key',
                'ai_fallback_enabled',
                'groq_model',
                'ai_temperature',
                'deepseek_api_key',
                'ai_system_prompt',
                'deepseek_model',
            ],
        ];
    }

    public static function check(Database $db)
    {
        $pdo = $db->getPdo();
        $checks = [];
        $missingSettings = [];

        foreach (self::expectedSettings() as $group => $keys) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `group` = ? AND `key` = ?");
            foreach ($keys as $key) {
                $stmt->execute([$group, $key]);
                $ok = (int) $stmt->fetchColumn() > 0;
                if (!$ok) {
                    $missingSettings[] = $group . '/' . $key;
                }
                $checks[] = [
                    'ok'   => $ok,
                    'label'=> 'إعداد «' . $key . '» في مجموعة ' . $group,
                    'hint' => $ok
                        ? 'موجود في قاعدة البيانات'
                        : 'يُزرع تلقائياً: حدّث الكود ثم افتح هذه الصفحة مرة واحدة.',
                    'required' => true,
                ];
            }
        }

        // Columns added by optional SQL migrations on rss_sources.
        $migrationColumns = ['new_items_count', 'new_items_last_at', 'last_item_count'];
        $foundCols = [];
        try {
            $stmt = $pdo->query(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rss_sources'
                 AND COLUMN_NAME IN ('new_items_count','new_items_last_at','last_item_count')"
            );
            $foundCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $foundCols = [];
        }
        foreach ($migrationColumns as $col) {
            $ok = in_array($col, $foundCols, true);
            $checks[] = [
                'ok'   => $ok,
                'label'=> 'عمود rss_sources.' . $col,
                'hint' => $ok
                    ? 'موجود (ترحيل مطبق)'
                    : 'نفّذ ملف migrate_rss_new_items_count.sql من SQL Tab في لوحة استضافة (يُنفَّذ مرة واحدة فقط).',
                'required' => $col !== 'last_item_count',
            ];
        }

        // Columns added by migrate_ai_daily_quota.sql: member daily chat quota
        // + one-time admin boosts + per-user override.
        $quotaCols = ['ai_quota_date', 'ai_quota_used', 'ai_quota_boost', 'ai_quota_daily'];
        $foundQuota = [];
        try {
            $stmt = $pdo->query(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
                 AND COLUMN_NAME IN ('ai_quota_date','ai_quota_used','ai_quota_boost','ai_quota_daily')"
            );
            $foundQuota = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $foundQuota = [];
        }
        foreach ($quotaCols as $col) {
            $ok = in_array($col, $foundQuota, true);
            $checks[] = [
                'ok'       => $ok,
                'label'    => 'عمود users.' . $col,
                'hint'     => $ok
                    ? 'موجود (ترحيل مطبق)'
                    : 'نفّذ ملف migrate_ai_daily_quota.sql من SQL Tab (مطلوب للحصة اليومية والإضافية للمسجلين).',
                'required' => true,
            ];
        }

        // Table added by migrate_ai_conversations.sql: assistant conversation log.
        $convTableOk = false;
        try {
            $stmt = $pdo->query(
                "SELECT TABLE_NAME FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_conversations'"
            );
            $convTableOk = (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            $convTableOk = false;
        }
        $checks[] = [
            'ok'       => $convTableOk,
            'label'    => 'جدول ai_conversations',
            'hint'     => $convTableOk
                ? 'موجود (ترحيل مطبق) — تُسجَّل هنا محادثات المرشد مع الأعضاء'
                : 'نفّذ ملف migrate_ai_conversations.sql من SQL Tab (مطلوب لسجلات المحادثات في اللوحة).',
            'required' => true,
        ];

        // Reaction columns added to ai_conversations by the same migration.
        $convCols = ['reaction', 'reaction_at'];
        $foundConvCols = [];
        try {
            $stmt = $pdo->query(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_conversations'
                 AND COLUMN_NAME IN ('reaction','reaction_at')"
            );
            $foundConvCols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $foundConvCols = [];
        }
        foreach ($convCols as $col) {
            $ok = in_array($col, $foundConvCols, true);
            $checks[] = [
                'ok'       => $ok,
                'label'    => 'عمود ai_conversations.' . $col,
                'hint'     => $ok
                    ? 'موجود (يتلقّى تقييم الأعضاء 👍/👎 على رسائل المرشد)'
                    : 'أعد تنفيذ ملف migrate_ai_conversations.sql من SQL Tab لإضافة عمود التقييم.',
                'required' => true,
            ];
        }

        $requiredOk = true;
        foreach ($checks as $c) {
            if ($c['required'] && !$c['ok']) {
                $requiredOk = false;
                break;
            }
        }

        $total = 0;
        $groups = 0;
        try {
            $total = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
            $groups = (int) $pdo->query('SELECT COUNT(DISTINCT `group`) FROM settings')->fetchColumn();
        } catch (Exception $e) {
            // table missing entirely -> covered by the checks below
        }

        return [
            'ok'              => $requiredOk,
            'settingsTotal'   => $total,
            'settingsGroups'  => $groups,
            'checks'          => $checks,
            'missingSettings' => $missingSettings,
        ];
    }
}