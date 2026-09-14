-- ==============================================================================
-- SH-09 (PART B) — background_jobs table for the zero-dependency job queue.
--
-- This is the storage half of core/Queue.php. Applying it is OPTIONAL until the
-- AI/RSS flows are actually moved to the background (planned follow-up that keeps
-- today's synchronous behaviour). The consumer command is:  php craft queue:work
--
-- Apply with:  mysql -u USER -p starter_platform_db < database/migrations_sh09_queue.sql
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `background_jobs` (
  `id`            bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue`         varchar(40)  NOT NULL DEFAULT 'default',
  `handler`       varchar(255) NOT NULL COMMENT 'callable identifier resolved by queue:work',
  `payload`       longtext     NOT NULL COMMENT 'JSON payload',
  `status`        enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `attempts`      tinyint(3) unsigned NOT NULL DEFAULT 0,
  `max_attempts`  tinyint(3) unsigned NOT NULL DEFAULT 3,
  `available_at`  timestamp NULL DEFAULT NULL,
  `locked_at`     timestamp NULL DEFAULT NULL,
  `error`         longtext NULL,
  `result`        longtext NULL COMMENT 'JSON result',
  `created_at`    timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at`    timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bg_jobs_claim` (`queue`,`status`,`available_at`),
  KEY `idx_bg_jobs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;