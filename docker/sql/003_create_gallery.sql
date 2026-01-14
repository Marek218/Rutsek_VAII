-- MariaDB create table for gallery images
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  `category` VARCHAR(100) NULL,
  `image_url` VARCHAR(1000) NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_public_sort` (`is_public`, `sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional seed rows (replace paths with real uploaded files)
INSERT INTO `gallery` (`title`, `category`, `image_path`, `image_url`, `is_public`, `sort_order`) VALUES
('Pánsky strih', 'Pánske', 'images/gallery1.jpg', NULL, 1, 10),
('Dámsky strih', 'Dámske', 'images/gallery2.jpg', NULL, 1, 20)
ON DUPLICATE KEY UPDATE `image_path`=`image_path`;
