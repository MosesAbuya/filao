-- Database updates for Google Ads and Joining Tours, plus newsletter
ALTER TABLE `tours` ADD COLUMN `is_active_ad` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_recommended`;
ALTER TABLE `tours` ADD COLUMN `is_joining_tour` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active_ad`;

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
