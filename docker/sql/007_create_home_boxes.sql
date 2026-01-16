-- Create editable homepage boxes
CREATE TABLE IF NOT EXISTS `home_boxes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `box_key` VARCHAR(64) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default boxes (only if table is empty)
INSERT INTO `home_boxes` (`box_key`, `title`, `description`, `sort_order`)
SELECT * FROM (
    SELECT 'damske' AS box_key, 'Dámske strihy' AS title, 'Od klasických po moderné účesy' AS description, 10 AS sort_order
    UNION ALL SELECT 'panske', 'Pánske strihy', 'Presné a štylizované strihy', 20
    UNION ALL SELECT 'farbenie', 'Farbenie', 'Profesionálne farbenie vlasov', 30
    UNION ALL SELECT 'trvala', 'Trvalá', 'Dlhotrvajúce kučery a vlny', 40
    UNION ALL SELECT 'melir', 'Melír', 'Cez čiapku alebo fóliový', 50
    UNION ALL SELECT 'ucesy', 'Účesy na príležitosť', 'Svadobné a slávnostné účesy', 60
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `home_boxes`);
