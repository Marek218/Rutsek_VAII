-- One-row table for contact page settings (phone, address, map, opening hours, etc.)
CREATE TABLE IF NOT EXISTS `contact_info` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salon_name` VARCHAR(200) NOT NULL,
  `person_name` VARCHAR(200) NULL,
  `phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `address_line` VARCHAR(255) NOT NULL,
  `opening_hours` TEXT NULL,
  `map_embed_url` VARCHAR(1000) NULL,
  `logo_path` VARCHAR(500) NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure exactly one row exists (id=1)
INSERT INTO `contact_info` (
  `id`, `salon_name`, `person_name`, `phone`, `email`, `address_line`, `opening_hours`, `map_embed_url`, `logo_path`
) VALUES (
  1,
  'Kaderníctvo Luxer',
  'Lucia Mojžitová',
  '0903 842 887',
  'info@luxer.sk',
  'Beskydská 5006/1, 974 11 Banská Bystrica',
  'Pondelok – Piatok: 08:00 – 18:00\nSobota: 08:00 – 13:00\nNedeľa: Zatvorené',
  'https://www.google.com/maps?q=Besky%CC%81dska+5006%2F1+97411+Banska+Bystrica&output=embed',
  'images/logo.png'
)
ON DUPLICATE KEY UPDATE
  `salon_name`=VALUES(`salon_name`),
  `person_name`=VALUES(`person_name`),
  `phone`=VALUES(`phone`),
  `email`=VALUES(`email`),
  `address_line`=VALUES(`address_line`),
  `opening_hours`=VALUES(`opening_hours`),
  `map_embed_url`=VALUES(`map_embed_url`),
  `logo_path`=VALUES(`logo_path`);
