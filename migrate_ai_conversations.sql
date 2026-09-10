-- Migration: conversation log for the AI Assistant («مرشد عصب التقنية»).
-- One row per successful exchange (user question + assistant answer) OR per
-- failed attempt (status='error' with the provider error), so admins can review
-- assistant behaviour and fix mistakes.
-- Run ONCE on the host via phpMyAdmin/SQL tab (or CLI):
--   mysql -u USER -p tech_news_db < migrate_ai_conversations.sql
-- Safe to run repeatedly (guarded by information_schema).
SET @tbl = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_conversations'
);
SET @sql = IF(@tbl = 0,
  'CREATE TABLE ai_conversations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NULL,
    provider VARCHAR(60) NULL,
    status VARCHAR(10) NOT NULL DEFAULT ''ok'' COMMENT ''ok | error'',
    error TEXT NULL,
    sources TEXT NULL COMMENT ''json: [{title,url}]'',
    page_slug VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_conversations_user (user_id),
    KEY idx_conversations_status (status),
    KEY idx_conversations_created (created_at),
    CONSTRAINT fk_conversations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT ''AI assistant conversation log''',
  'SELECT ''''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;