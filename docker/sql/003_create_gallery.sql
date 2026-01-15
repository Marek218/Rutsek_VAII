-- MariaDB create table for gallery images
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `category` VARCHAR(100) NULL,
  -- Local path relative to public/ (e.g. "uploads/gallery/1.jpg")
  `path_url` VARCHAR(1000) NOT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_public_sort` (`is_public`, `sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional seed rows (replace paths with real files under public/)
INSERT INTO `gallery` (`title`, `category`, `path_url`, `is_public`, `sort_order`) VALUES
('Pánsky strih', 'Pánske', 'uploads/gallery1.jpg', 1, 10),
('Dámsky strih', 'Dámske', 'uploads/gallery2.jpg', 1, 20);
