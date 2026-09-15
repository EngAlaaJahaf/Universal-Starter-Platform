-- ==============================================================================
-- SH-09 (PART C) — Performance indexes.
--
-- Recommended one-time index additions for MySQL 5.7 / 8. Every column below is
-- VERIFIED against database/schema.sql. Indexes replace nothing; they only add
-- lookup/order coverage for the hottest read paths. Apply on a maintenance
-- window:  mysql -u USER -p starter_platform_db < database/migrations_sh09_indexes.sql
--
-- Note: each statement is conditional — it adds the index only if it is not
-- already present. Fresh installs get the hot indexes straight from
-- database/schema.sql, so this file is a no-op there; legacy databases that
-- predate the canonical indexes are upgraded in place.
-- Runs under `php craft db:migrate` (ledger) or directly via mysql CLI.
-- ==============================================================================

-- articles: the canonical public listing query is
--   WHERE status='published' ORDER BY published_at DESC → composite covers it.
SET @idx_articles := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'articles' AND INDEX_NAME = 'idx_articles_status_published');
SET @sql := IF(@idx_articles, 'SELECT 1', 'ALTER TABLE `articles` ADD INDEX `idx_articles_status_published` (`status`, `published_at`)');
PREPARE stmt_articles FROM @sql;
EXECUTE stmt_articles;
DEALLOCATE PREPARE stmt_articles;

-- comments: "approved comments for this article" list views.
SET @idx_ca := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comments' AND INDEX_NAME = 'idx_comments_article_status');
SET @sql := IF(@idx_ca, 'SELECT 1', 'ALTER TABLE `comments` ADD INDEX `idx_comments_article_status` (`article_id`, `status`)');
PREPARE stmt_ca FROM @sql;
EXECUTE stmt_ca;
DEALLOCATE PREPARE stmt_ca;

SET @idx_cu := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comments' AND INDEX_NAME = 'idx_comments_user');
SET @sql := IF(@idx_cu, 'SELECT 1', 'ALTER TABLE `comments` ADD INDEX `idx_comments_user` (`user_id`)');
PREPARE stmt_cu FROM @sql;
EXECUTE stmt_cu;
DEALLOCATE PREPARE stmt_cu;

-- notifications: "unread badges for a user".
SET @idx_not := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND INDEX_NAME = 'idx_notifications_created');
SET @sql := IF(@idx_not, 'SELECT 1', 'ALTER TABLE `notifications` ADD INDEX `idx_notifications_created` (`created_at`)');
PREPARE stmt_not FROM @sql;
EXECUTE stmt_not;
DEALLOCATE PREPARE stmt_not;

-- rss_sources: the auto-sync picker wants stale + active sources first.
SET @idx_rss := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rss_sources' AND INDEX_NAME = 'idx_rss_sources_active_fetched');
SET @sql := IF(@idx_rss, 'SELECT 1', 'ALTER TABLE `rss_sources` ADD INDEX `idx_rss_sources_active_fetched` (`is_active`, `last_fetched_at`)');
PREPARE stmt_rss FROM @sql;
EXECUTE stmt_rss;
DEALLOCATE PREPARE stmt_rss;

-- api_keys: expiry pruning + revoke sweeps.
SET @idx_ak := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'api_keys' AND INDEX_NAME = 'idx_api_keys_expires');
SET @sql := IF(@idx_ak, 'SELECT 1', 'ALTER TABLE `api_keys` ADD INDEX `idx_api_keys_expires` (`expires_at`)');
PREPARE stmt_ak FROM @sql;
EXECUTE stmt_ak;
DEALLOCATE PREPARE stmt_ak;

-- reading_history: per-user per-article lookups (trends + dedupe).
SET @idx_rh := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reading_history' AND INDEX_NAME = 'idx_reading_user_article');
SET @sql := IF(@idx_rh, 'SELECT 1', 'ALTER TABLE `reading_history` ADD INDEX `idx_reading_user_article` (`user_id`, `article_id`)');
PREPARE stmt_rh FROM @sql;
EXECUTE stmt_rh;
DEALLOCATE PREPARE stmt_rh;

-- user_sessions: session garbage-collection sweeps.
SET @idx_us := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_sessions' AND INDEX_NAME = 'idx_user_sessions_created');
SET @sql := IF(@idx_us, 'SELECT 1', 'ALTER TABLE `user_sessions` ADD INDEX `idx_user_sessions_created` (`login_at`)');
PREPARE stmt_us FROM @sql;
EXECUTE stmt_us;
DEALLOCATE PREPARE stmt_us;

-- newsletter_campaigns: the sender process scans (status, scheduled_at).
SET @idx_nl := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'newsletter_campaigns' AND INDEX_NAME = 'idx_nl_campaigns_status_sched');
SET @sql := IF(@idx_nl, 'SELECT 1', 'ALTER TABLE `newsletter_campaigns` ADD INDEX `idx_nl_campaigns_status_sched` (`status`, `scheduled_at`)');
PREPARE stmt_nl FROM @sql;
EXECUTE stmt_nl;
DEALLOCATE PREPARE stmt_nl;

-- traffic_radar: admin analytics group by visitor_type over a date slice.
SET @idx_tr := (SELECT COUNT(*) >= 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'traffic_radar' AND INDEX_NAME = 'idx_traffic_type_created');
SET @sql := IF(@idx_tr, 'SELECT 1', 'ALTER TABLE `traffic_radar` ADD INDEX `idx_traffic_type_created` (`visitor_type`, `created_at`)');
PREPARE stmt_tr FROM @sql;
EXECUTE stmt_tr;
DEALLOCATE PREPARE stmt_tr;