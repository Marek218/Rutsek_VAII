-- MariaDB create table for services
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial price list (from provided photo)
INSERT INTO `services` (`name`, `price`) VALUES
('Pánsky strih', 9.00),
('Dámsky strih', 13.00),
('Umývanie', 2.00),
('Kondicionér', 2.00),
('Zábal', 8.00),
('Tužidlo', 1.50),
('Gel, Vosk', 1.50),
('Lak', 1.50),
('Sušenie vlasov', 8.00),
('Fúkanie', 10.00),
('Vodová ondulácia', 12.00),
('Vlastná práca', 6.00),
('Farbenie', 15.00),
('Farbenie dlhých vlasov', 18.00),
('Farbenie odrastajúcich vlasov', 12.00),
('Trvalá krátke vlasy', 15.00),
('Trvalá dlhé vlasy', 20.00),
('Melír cez čiapku', 20.00),
('Melír fóliový krátke vlasy', 24.00),
('Melír fóliový dlhé vlasy', 30.00),
('Vypínanie vlasov', 30.00),
('Svadobný účes', 80.00);

