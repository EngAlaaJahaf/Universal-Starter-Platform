-- Migration: counters for fresh (un-published) news items per RSS source.
-- Run ONCE on the host via phpMyAdmin/SQL tab (or CLI):
--   mysql -u USER -p tech_news_db < migrate_rss_new_items_count.sql
-- Safe to run repeatedly (guarded by information_schema).
SET @col_count = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'rss_sources'
    AND COLUMN_NAME = 'new_items_count'
);
SET @sql = IF(@col_count = 0,
  'ALTER TABLE rss_sources ADD COLUMN new_items_count INT NOT NULL DEFAULT 0 COMMENT ''fresh unpublished items count''',
  'SELECT ''''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_last = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'rss_sources'
    AND COLUMN_NAME = 'new_items_last_at'
);
SET @sql2 = IF(@col_last = 0,
  'ALTER TABLE rss_sources ADD COLUMN new_items_last_at DATETIME NULL COMMENT ''latest fresh unpublished item time (UTC)''',
  'SELECT ''''');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- Initialise from current articles data so the badge is meaningful before the first fetch.
-- Only runs if the optional last_item_count column exists (added by a separate earlier upgrade).
SET @has_last = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'rss_sources'
    AND COLUMN_NAME = 'last_item_count'
);
SET @sql3 = IF(@has_last > 0,
  'UPDATE rss_sources s
   LEFT JOIN (
     SELECT source_name, COUNT(*) AS published_total
     FROM articles
     WHERE source_name IS NOT NULL AND source_name <> ''''
     GROUP BY source_name
   ) a ON a.source_name = s.name COLLATE utf8mb4_general_ci
   SET s.new_items_count = GREATEST(0, s.last_item_count - COALESCE(a.published_total, 0))
   WHERE s.last_item_count > 0',
  'SELECT ''''
');
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;