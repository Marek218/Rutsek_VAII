-- MariaDB create table for orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `service_id` INT UNSIGNED NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_orders_date_time` (`date`, `time`),
  INDEX `idx_orders_service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
