-- ============================================================
-- Universal Starter Platform â€” Schema v2 migration
-- Adds feature tables + columns that the application code
-- actually uses but were missing from the trimmed dev schema.
-- Idempotent (safe to run multiple times).
-- ============================================================

-- 1. Article micro-reactions (ReactionController)
CREATE TABLE IF NOT EXISTS `reactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `ip_hash` varchar(64) DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reaction` (`article_id`,`user_id`,`type`),
  KEY `fk_reactions_user` (`user_id`),
  CONSTRAINT `fk_reactions_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Article tags (models/Article.php getTags)
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_ar` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `slug` varchar(120) NOT NULL,
  `articles_count` int(10) unsigned NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tags_name` (`name`),
  UNIQUE KEY `uq_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `article_tags` (
  `article_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`),
  KEY `fk_article_tags_tag` (`tag_id`),
  CONSTRAINT `fk_article_tags_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_article_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cron run history (controllers/admin/CronController.php, cron/rss_auto_publish.php)
CREATE TABLE IF NOT EXISTS `cron_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL DEFAULT 'rss_auto_publish',
  `status` varchar(40) NOT NULL DEFAULT 'success',
  `message` text DEFAULT NULL,
  `articles_published` int(11) NOT NULL DEFAULT 0,
  `articles_skipped` int(11) NOT NULL DEFAULT 0,
  `articles_failed` int(11) NOT NULL DEFAULT 0,
  `sources_processed` int(11) NOT NULL DEFAULT 0,
  `execution_time_ms` int(11) NOT NULL DEFAULT 0,
  `triggered_by` varchar(40) NOT NULL DEFAULT 'cron',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_job_name` (`job_name`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. In-app notifications (core/NotificationService.php, core/SecurityGuard.php)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `title_ar` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `message_ar` text DEFAULT NULL,
  `message_en` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`,`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Web Push subscriptions (controllers/PushController.php)
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `content_encoding` varchar(30) NOT NULL DEFAULT 'aesgcm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_push_user` (`user_id`),
  CONSTRAINT `fk_push_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Content reports (controllers/CommentController.php)
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `reportable_type` varchar(50) NOT NULL,
  `reportable_id` int(10) unsigned NOT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reports_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. rss_sources columns used by API/admin views/aggregator
ALTER TABLE `rss_sources` ADD COLUMN IF NOT EXISTS `auto_fetch` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `rss_sources` ADD COLUMN IF NOT EXISTS `last_http_code` varchar(20) DEFAULT NULL;
ALTER TABLE `rss_sources` ADD COLUMN IF NOT EXISTS `new_items_last_at` datetime DEFAULT NULL;

-- 8. Align menus schema with the admin MenusController / AdminSimpleController
--    (code expects: menus.name + menus.status; menu_items.title_ar/title_en/status/item_type/target_id)
--    Legacy databases (original dev schema) used menus.title / menu_items.title;
--    rename those only if the legacy columns exist — canonical schema.sql already
--    ships the final names, so these statements are no-ops on fresh installs.
SET @has_menus_title := (SELECT COUNT(*) = 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'title');
SET @menus_align := IF(@has_menus_title, 'ALTER TABLE `menus` CHANGE COLUMN `title` `name` varchar(150) NOT NULL', 'SELECT 1');
PREPARE menus_align_stmt FROM @menus_align;
EXECUTE menus_align_stmt;
DEALLOCATE PREPARE menus_align_stmt;
ALTER TABLE `menus` ADD COLUMN IF NOT EXISTS `status` varchar(20) NOT NULL DEFAULT 'active';
SET @has_items_title := (SELECT COUNT(*) = 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menu_items' AND COLUMN_NAME = 'title');
SET @items_align := IF(@has_items_title, 'ALTER TABLE `menu_items` CHANGE COLUMN `title` `title_ar` varchar(190) NOT NULL', 'SELECT 1');
PREPARE items_align_stmt FROM @items_align;
EXECUTE items_align_stmt;
DEALLOCATE PREPARE items_align_stmt;
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `title_en` varchar(190) DEFAULT NULL;
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `status` varchar(20) NOT NULL DEFAULT 'active';
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `item_type` varchar(40) NOT NULL DEFAULT 'custom';
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `target_id` int(10) unsigned DEFAULT NULL;

-- =====================================================================
-- Part 2: Sync live schema up to the full canonical feature set
-- (created from the complete schema definition; idempotent)
-- =====================================================================

SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE IF NOT EXISTS `analytics_daily` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `page_views` int(10) unsigned NOT NULL DEFAULT 0,
  `unique_visitors` int(10) unsigned NOT NULL DEFAULT 0,
  `new_users` int(10) unsigned NOT NULL DEFAULT 0,
  `top_articles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_articles`)),
  `top_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_categories`)),
  `top_referrers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_referrers`)),
  `devices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`devices`)),
  `countries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`countries`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_analytics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `article_analytics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `unique_views` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_read_time` int(10) unsigned NOT NULL DEFAULT 0,
  `scroll_depth_avg` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `shares` int(10) unsigned NOT NULL DEFAULT 0,
  `comments_count` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_article_analytics` (`article_id`,`date`),
  CONSTRAINT `fk_analytics_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `article_revisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `title_ar` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `content_ar` longtext DEFAULT NULL,
  `content_en` longtext DEFAULT NULL,
  `revision_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_revisions_article` (`article_id`),
  CONSTRAINT `fk_revisions_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `bookmark_collections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_collections_user` (`user_id`),
  CONSTRAINT `fk_collections_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `cache_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `ttl_minutes` int(11) NOT NULL DEFAULT 60,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cache_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `media_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_media_folders_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(150) NOT NULL,
  `name_en` varchar(150) NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` decimal(10,2) NOT NULL DEFAULT 0.00,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `max_articles_per_month` int(11) NOT NULL DEFAULT -1,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `reading_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `article_id` int(10) unsigned NOT NULL,
  `read_percentage` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `time_spent_seconds` int(10) unsigned NOT NULL DEFAULT 0,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reading_history_user` (`user_id`),
  KEY `idx_reading_history_article` (`article_id`),
  CONSTRAINT `fk_history_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `redirects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_url` varchar(500) NOT NULL,
  `to_url` varchar(500) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '301',
  `hits_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_redirects_from` (`from_url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `scheduled_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `command` varchar(255) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `seo_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(80) NOT NULL,
  `model_id` int(10) unsigned NOT NULL,
  `meta_title_ar` varchar(255) DEFAULT NULL,
  `meta_title_en` varchar(255) DEFAULT NULL,
  `meta_description_ar` text DEFAULT NULL,
  `meta_description_en` text DEFAULT NULL,
  `og_image` varchar(500) DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `robots` varchar(50) NOT NULL DEFAULT 'index,follow',
  `schema_markup` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schema_markup`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_model` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `social_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `provider` varchar(50) NOT NULL,
  `provider_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_social_provider_id` (`provider`,`provider_id`),
  KEY `idx_social_user` (`user_id`),
  CONSTRAINT `fk_social_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subscriptions_user` (`user_id`),
  KEY `fk_subscriptions_plan` (`plan_id`),
  CONSTRAINT `fk_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `device_type` varchar(30) DEFAULT NULL,
  `location` varchar(190) DEFAULT NULL,
  `login_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_sessions_user` (`user_id`),
  CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `activity_logs` ADD COLUMN IF NOT EXISTS `model_type` varchar(80) DEFAULT NULL;
ALTER TABLE `activity_logs` ADD COLUMN IF NOT EXISTS `model_id` bigint(20) unsigned DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `image_path` varchar(500) DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `html_code` text DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `impressions_count` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `clicks_count` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `start_at` datetime DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `end_at` datetime DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `start_date` date DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `end_date` date DEFAULT NULL;
ALTER TABLE `ads` ADD COLUMN IF NOT EXISTS `target_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_categories`));
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `content_en` longtext DEFAULT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `excerpt_en` text DEFAULT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`));
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `video_url` varchar(500) DEFAULT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `audio_url` varchar(500) DEFAULT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `co_authors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`co_authors`));
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `article_type` varchar(30) NOT NULL DEFAULT 'standard';
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `is_fact_checked` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `fact_checker_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `is_premium` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `articles` ADD COLUMN IF NOT EXISTS `scheduled_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `bookmarks` ADD COLUMN IF NOT EXISTS `collection_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `name_ar` varchar(150) DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `description_ar` text DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `description_en` text DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `featured_image` varchar(1024) DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `is_visible` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `meta_title_ar` varchar(255) DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `meta_title_en` varchar(255) DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `meta_description_ar` text DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `meta_description_en` text DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `articles_count` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `guest_name` varchar(100) DEFAULT NULL;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `guest_email` varchar(190) DEFAULT NULL;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `likes_count` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `is_pinned` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `is_author_reply` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `replied_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `live_blog_entries` ADD COLUMN IF NOT EXISTS `content_en` longtext DEFAULT NULL;
ALTER TABLE `live_blog_entries` ADD COLUMN IF NOT EXISTS `entry_type` varchar(30) NOT NULL DEFAULT 'text';
ALTER TABLE `live_blog_entries` ADD COLUMN IF NOT EXISTS `media_url` varchar(500) DEFAULT NULL;
ALTER TABLE `live_blogs` ADD COLUMN IF NOT EXISTS `title_en` varchar(255) DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `folder` varchar(190) NOT NULL DEFAULT 'default';
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `original_name` varchar(255) NOT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `mime_type` varchar(100) NOT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `dimensions` varchar(50) DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `width` int(10) unsigned DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `height` int(10) unsigned DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `alt_text_ar` varchar(255) DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `alt_text_en` varchar(255) DEFAULT NULL;
ALTER TABLE `media` ADD COLUMN IF NOT EXISTS `thumbnail_path` varchar(500) DEFAULT NULL;
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `label_ar` varchar(190) NOT NULL;
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `label_en` varchar(190) DEFAULT NULL;
ALTER TABLE `menu_items` ADD COLUMN IF NOT EXISTS `is_visible` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `newsletter_campaigns` ADD COLUMN IF NOT EXISTS `subject_ar` varchar(255) DEFAULT NULL;
ALTER TABLE `newsletter_campaigns` ADD COLUMN IF NOT EXISTS `subject_en` varchar(255) DEFAULT NULL;
ALTER TABLE `newsletter_campaigns` ADD COLUMN IF NOT EXISTS `content_ar` longtext DEFAULT NULL;
ALTER TABLE `newsletter_campaigns` ADD COLUMN IF NOT EXISTS `content_en` longtext DEFAULT NULL;
ALTER TABLE `newsletters` ADD COLUMN IF NOT EXISTS `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`));
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `template` varchar(50) NOT NULL DEFAULT 'default';
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `meta_title_ar` varchar(255) DEFAULT NULL;
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `meta_title_en` varchar(255) DEFAULT NULL;
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `meta_description_ar` text DEFAULT NULL;
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `meta_description_en` text DEFAULT NULL;
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `sort_order` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `roles` ADD COLUMN IF NOT EXISTS `is_default` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `security_alerts` ADD COLUMN IF NOT EXISTS `target_uri` varchar(500) NOT NULL;
ALTER TABLE `series` ADD COLUMN IF NOT EXISTS `description_en` text DEFAULT NULL;
ALTER TABLE `series` ADD COLUMN IF NOT EXISTS `cover_image` varchar(255) DEFAULT NULL;
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `image_url` varchar(500) DEFAULT NULL;
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `image_caption` varchar(255) DEFAULT NULL;
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `callout_type` enum('none','tip','warning','important','note') DEFAULT 'none';
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `callout_text` text DEFAULT NULL;
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `sort_order` int(11) DEFAULT 0;
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp();
ALTER TABLE `tutorial_steps` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `password_hash` varchar(255) NOT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `bio_ar` text DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `bio_en` text DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `phone` varchar(40) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`));
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `login_count` int(10) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `preferred_language` varchar(5) NOT NULL DEFAULT 'ar';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `theme_preference` varchar(10) NOT NULL DEFAULT 'auto';
SET FOREIGN_KEY_CHECKS=1;
