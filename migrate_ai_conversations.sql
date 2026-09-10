-- Migration: conversation log for the AI Assistant («مرشد عصب التقنية»).
-- One row per successful exchange (user question + assistant answer) OR per
-- failed attempt (status='error' with the provider error), so admins can review
-- assistant behaviour and fix mistakes. Also stores the member's reaction
-- (like / dislike / love) collected from the chat UI.
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
    reaction VARCHAR(10) NULL COMMENT ''like | dislike | love | NULL'',
    reaction_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_conversations_user (user_id),
    KEY idx_conversations_status (status),
    KEY idx_conversations_created (created_at),
    CONSTRAINT fk_conversations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT ''AI assistant conversation log''',
  'SELECT ''''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col_reaction = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ai_conversations'
    AND COLUMN_NAME = 'reaction'
);
SET @sql2 = IF(@col_reaction = 0,
  'ALTER TABLE ai_conversations ADD COLUMN reaction VARCHAR(10) NULL COMMENT ''like | dislike | love | NULL'' AFTER page_slug',
  'SELECT ''''');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;

SET @col_reaction_at = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ai_conversations'
    AND COLUMN_NAME = 'reaction_at'
);
SET @sql3 = IF(@col_reaction_at = 0,
  'ALTER TABLE ai_conversations ADD COLUMN reaction_at TIMESTAMP NULL AFTER reaction',
  'SELECT ''''');
PREPARE s3 FROM @sql3; EXECUTE s3; DEALLOCATE PREPARE s3;