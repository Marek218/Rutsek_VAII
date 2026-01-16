-- MariaDB create table for contact form messages ("Napíšte nám")
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `subject` VARCHAR(200) NULL,
  `message` TEXT NOT NULL,

  -- Optional metadata (useful for admin)
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,

  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_contact_messages_created_at` (`created_at`),
  INDEX `idx_contact_messages_is_read_created_at` (`is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
