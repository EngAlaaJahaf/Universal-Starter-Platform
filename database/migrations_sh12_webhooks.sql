-- SH-12c: outbound webhook registry.
CREATE TABLE IF NOT EXISTS `webhooks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '',
  `url` varchar(500) NOT NULL,
  `secret` varchar(190) DEFAULT NULL,
  `events` json DEFAULT NULL COMMENT 'JSON array of event keys; NULL = all',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;