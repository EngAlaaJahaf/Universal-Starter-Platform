-- ==============================================================================
-- SH-09 (PART C) — Performance indexes.
--
-- Recommended one-time index additions for MySQL 5.7 / 8. Every column below is
-- VERIFIED against database/schema.sql. Indexes replace nothing; they only add
-- lookup/order coverage for the hottest read paths. Apply on a maintenance
-- window:  mysql -u USER -p starter_platform_db < database/migrations_sh09_indexes.sql
-- ==============================================================================

-- articles: the canonical public listing query is
--   WHERE status='published' ORDER BY published_at DESC → composite covers it.
ALTER TABLE `articles` ADD INDEX `idx_articles_status_published` (`status`, `published_at`);

-- comments: "approved comments for this article" list views.
ALTER TABLE `comments` ADD INDEX `idx_comments_article_status` (`article_id`, `status`);
ALTER TABLE `comments` ADD INDEX `idx_comments_user` (`user_id`);

-- notifications: "unread badges for a user".
ALTER TABLE `notifications` ADD INDEX `idx_notifications_created` (`created_at`);

-- rss_sources: the auto-sync picker wants stale + active sources first.
ALTER TABLE `rss_sources` ADD INDEX `idx_rss_sources_active_fetched` (`is_active`, `last_fetched_at`);

-- api_keys: expiry pruning + revoke sweeps.
ALTER TABLE `api_keys` ADD INDEX `idx_api_keys_expires` (`expires_at`);

-- reading_history: per-user per-article lookups (trends + dedupe).
ALTER TABLE `reading_history` ADD INDEX `idx_reading_user_article` (`user_id`, `article_id`);

-- user_sessions: session garbage-collection sweeps.
ALTER TABLE `user_sessions` ADD INDEX `idx_user_sessions_created` (`created_at`);

-- newsletter_campaigns: the sender process scans (status, scheduled_at).
ALTER TABLE `newsletter_campaigns` ADD INDEX `idx_nl_campaigns_status_sched` (`status`, `scheduled_at`);

-- traffic_radar: admin analytics group by visitor_type over a date slice.
ALTER TABLE `traffic_radar` ADD INDEX `idx_traffic_type_created` (`visitor_type`, `created_at`);