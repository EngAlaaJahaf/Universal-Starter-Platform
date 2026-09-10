-- Migration: per-user DAILY question quota for the AI Assistant («مرشد عصب التقنية»).
-- The assistant is login-only and each logged-in member gets N questions per day
-- (0 = unlimited; admins are always exempt).
-- Run ONCE on the host via phpMyAdmin/SQL tab (or CLI):
--   mysql -u USER -p tech_news_db < migrate_ai_daily_quota.sql
-- Safe to run repeatedly (guarded by information_schema).
SET @col_date = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'ai_quota_date'
);
SET @sql1 = IF(@col_date = 0,
  'ALTER TABLE users ADD COLUMN ai_quota_date DATE NULL COMMENT ''last assistant-quota date (site tz)''',
  'SELECT ''''');
PREPARE s1 FROM @sql1; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @col_used = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'ai_quota_used'
);
SET @sql2 = IF(@col_used = 0,
  'ALTER TABLE users ADD COLUMN ai_quota_used INT NOT NULL DEFAULT 0 COMMENT ''assistant questions used today''',
  'SELECT ''''');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;

-- One-time EXTRA messages granted by an admin (consumed first; never "permanent").
SET @col_boost = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'ai_quota_boost'
);
SET @sql3 = IF(@col_boost = 0,
  'ALTER TABLE users ADD COLUMN ai_quota_boost INT NOT NULL DEFAULT 0 COMMENT ''one-time extra assistant messages granted by admin''',
  'SELECT ''''');
PREPARE s3 FROM @sql3; EXECUTE s3; DEALLOCATE PREPARE s3;

-- Per-user permanent daily-allowance OVERRIDE (NULL = use the global setting; 0 = unlimited).
SET @col_daily = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'ai_quota_daily'
);
SET @sql4 = IF(@col_daily = 0,
  'ALTER TABLE users ADD COLUMN ai_quota_daily INT NULL COMMENT ''per-user daily allowance override (NULL = global, 0 = unlimited)''',
  'SELECT ''''');
PREPARE s4 FROM @sql4; EXECUTE s4; DEALLOCATE PREPARE s4;