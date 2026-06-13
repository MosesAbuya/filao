CREATE DATABASE IF NOT EXISTS `filao_adventures` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `filao_adventures`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (`email`)
);

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES
('Filao Admin', 'admin@filaoadventures.co.ke', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`; -- password: password

CREATE TABLE IF NOT EXISTS `taxonomies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` ENUM('activity','feature','age','language','facility') NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_type_slug (`type`, `slug`)
);

INSERT INTO `taxonomies` (`type`, `name`, `slug`) VALUES
('activity', 'Game Drives', 'game-drives'),
('activity', 'Bush Walks', 'bush-walks'),
('activity', 'Hot Air Balloon', 'hot-air-balloon'),
('activity', 'Birdwatching', 'birdwatching'),
('activity', 'Cultural Visits', 'cultural-visits'),
('activity', 'Snorkeling', 'snorkeling'),
('activity', 'Scuba Diving', 'scuba-diving'),
('activity', 'Hiking', 'hiking'),
('activity', 'Photography', 'photography'),
('activity', 'Sundowner', 'sundowner'),
('feature', 'Family Friendly', 'family-friendly'),
('feature', 'Honeymoon', 'honeymoon'),
('feature', 'Solo Traveler', 'solo-traveler'),
('feature', 'Photography Focus', 'photography-focus'),
('feature', 'Eco-Friendly', 'eco-friendly'),
('feature', 'Off-The-Beaten-Path', 'off-the-beaten-path'),
('age', 'All Ages', 'all-ages'),
('age', 'Adults Only (18+)', 'adults-only-18'),
('age', 'Young Adults (12+)', 'young-adults-12'),
('age', 'Children Welcome (5+)', 'children-welcome-5'),
('facility', 'Swimming Pool', 'swimming-pool'),
('facility', 'Wi-Fi', 'wi-fi'),
('facility', 'Spa', 'spa'),
('facility', 'Restaurant', 'restaurant'),
('facility', 'Bar', 'bar'),
('facility', 'Air Conditioning', 'air-conditioning'),
('facility', 'Private Plunge Pool', 'private-plunge-pool'),
('facility', 'Laundry Service', 'laundry-service')
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `tour_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(100) NULL,
    `display_order` INT DEFAULT 0
);

INSERT INTO `tour_categories` (`name`, `slug`, `icon`, `display_order`) VALUES
('Safari Tours', 'safari-tours', 'bi-binoculars', 1),
('Beach Holidays', 'beach-holidays', 'bi-sunset', 2),
('City Tours', 'city-tours', 'bi-building', 3),
('Mountain & Adventure', 'mountain-adventure', 'bi-mountains', 4),
('International Luxury', 'international-luxury', 'bi-airplane', 5),
('Corporate & MICE', 'corporate-mice', 'bi-briefcase', 6),
('Day Trips & Excursions', 'day-trips-excursions', 'bi-compass', 7),
('Hybrid Tours', 'hybrid-tours', 'bi-journal-richtext', 8)
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `destinations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `country` VARCHAR(100) NOT NULL,
    `region_type` VARCHAR(100) DEFAULT 'National Park',
    `description` TEXT NULL,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `featured_image` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `destinations` (`name`, `slug`, `country`, `region_type`, `latitude`, `longitude`) VALUES
('Maasai Mara National Reserve', 'maasai-mara-national-reserve', 'Kenya', 'National Reserve', -1.48946900, 35.10144500),
('Amboseli National Park', 'amboseli-national-park', 'Kenya', 'National Park', -2.65271600, 37.25607800),
('Tsavo East National Park', 'tsavo-east-national-park', 'Kenya', 'National Park', -2.43197400, 38.51768200),
('Lake Nakuru National Park', 'lake-nakuru-national-park', 'Kenya', 'National Park', -0.36556600, 36.08444600),
('Serengeti National Park', 'serengeti-national-park', 'Tanzania', 'National Park', -2.33333300, 34.83333300),
('Ngorongoro Crater', 'ngorongoro-crater', 'Tanzania', 'Conservation Area', -3.17407600, 35.58700300),
('Zanzibar Island', 'zanzibar-island', 'Tanzania', 'Island', -6.16534600, 39.20268800),
('Diani Beach', 'diani-beach', 'Kenya', 'Beach', -4.27686200, 39.59430200),
('Nairobi', 'nairobi', 'Kenya', 'City', -1.28633300, 36.81721700),
('Mombasa Old Town', 'mombasa-old-town', 'Kenya', 'City', -4.05466100, 39.66359000)
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `accommodations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `featured_image` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `accommodations` (`name`, `slug`) VALUES
('Mara Serena Safari Lodge', 'mara-serena-safari-lodge'),
('Amboseli Serena Safari Lodge', 'amboseli-serena-safari-lodge'),
('Neptune Mara Rianta', 'neptune-mara-rianta'),
('Diani Reef Beach Resort & Spa', 'diani-reef-beach-resort-spa'),
('Ole Sereni Nairobi', 'ole-sereni-nairobi')
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `accommodation_facilities` (
    `accommodation_id` INT NOT NULL,
    `taxonomy_id` INT NOT NULL,
    PRIMARY KEY (`accommodation_id`, `taxonomy_id`),
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`taxonomy_id`) REFERENCES `taxonomies`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `tours` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `excerpt` TEXT NULL,
    `duration_days` INT NOT NULL DEFAULT 1,
    `price_from_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `featured_image` VARCHAR(500) NULL,
    `focus_keyphrase` VARCHAR(255) NULL,
    `seo_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `highlights` LONGTEXT NULL,
    `inclusions` LONGTEXT NULL,
    `exclusions` LONGTEXT NULL,
    `is_hot_offer` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active_ad` TINYINT(1) NOT NULL DEFAULT 0,
    `is_joining_tour` TINYINT(1) NOT NULL DEFAULT 0,
    `price_1_pax` DECIMAL(10,2) NULL,
    `price_2_pax` DECIMAL(10,2) NULL,
    `price_3_pax` DECIMAL(10,2) NULL,
    `price_4_pax` DECIMAL(10,2) NULL,
    `price_5_pax` DECIMAL(10,2) NULL,
    `price_6_pax` DECIMAL(10,2) NULL,
    `price_child_1_pax` DECIMAL(10,2) NULL,
    `price_child_2_pax` DECIMAL(10,2) NULL,
    `price_child_3_pax` DECIMAL(10,2) NULL,
    `price_child_4_pax` DECIMAL(10,2) NULL,
    `price_child_5_pax` DECIMAL(10,2) NULL,
    `price_child_6_pax` DECIMAL(10,2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (`slug`),
    INDEX idx_status (`status`),
    INDEX idx_created_at (`created_at`)
);

CREATE TABLE IF NOT EXISTS `tour_category_pivot` (
    `tour_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    PRIMARY KEY (`tour_id`, `category_id`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `tour_categories`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `tour_taxonomy_pivot` (
    `tour_id` INT NOT NULL,
    `taxonomy_id` INT NOT NULL,
    PRIMARY KEY (`tour_id`, `taxonomy_id`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`taxonomy_id`) REFERENCES `taxonomies`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `itinerary_steps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tour_id` INT NOT NULL,
    `step_number` INT NOT NULL,
    `destination_id` INT NOT NULL,
    `nights_count` INT NOT NULL DEFAULT 1,
    `step_title` VARCHAR(255) NOT NULL,
    `step_description` LONGTEXT NOT NULL,
    `accommodation_id` INT NULL,
    `transit_mode` VARCHAR(100) DEFAULT '4x4 Safari Vehicle',
    `transit_duration` VARCHAR(100) NULL,
    `step_image` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`),
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations`(`id`) ON DELETE SET NULL,
    UNIQUE KEY unique_tour_step (`tour_id`, `step_number`)
);

CREATE TABLE IF NOT EXISTS `tour_seasonality` (
    `tour_id` INT NOT NULL,
    `month_number` TINYINT NOT NULL,
    `rating` ENUM('Best','Good','Mixed','Low') NOT NULL DEFAULT 'Good',
    PRIMARY KEY (`tour_id`, `month_number`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE
);

-- Tables created: users, taxonomies, tour_categories, destinations, accommodations, accommodation_facilities, tours, tour_category_pivot, tour_taxonomy_pivot, itinerary_steps, tour_seasonality
