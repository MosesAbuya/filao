DROP TABLE IF EXISTS `countries`;
DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (1, 'Africa', 'africa', 'images/Filao/East Africa/pexels-akos-helgert-82252426-8804770.jpg', '2026-06-13 00:15:14');
INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (2, 'Asia', 'asia', 'images/Filao/Bali/pexels-airlangga-36913571.jpg', '2026-06-13 00:15:14');
INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (3, 'Middle East', 'middle-east', 'images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg', '2026-06-13 00:15:14');
INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (4, 'Indian Ocean', 'indian-ocean', 'images/Filao/Indian Ocean/pexels-abdullatif-bukeni-1296376-13887587.jpg', '2026-06-13 00:15:15');
INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (5, 'Europe', 'europe', 'images/Filao/Italy/pexels-camilacarneiro-6318793.jpg', '2026-06-13 00:15:15');
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `region_id` (`region_id`),
  CONSTRAINT `countries_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (1, 1, 'Kenya', 'kenya', 'destinations/eab8dd2b-1ba4-4049-aa20-af104d1f0132.jpg', '2026-06-13 00:15:15');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (2, 1, 'Tanzania', 'tanzania', 'destinations/c44813c2-70a9-48f7-9635-3869637a7b2f.jpg', '2026-06-13 00:15:15');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (3, 2, 'Indonesia', 'indonesia', 'destinations/ef566f89-2869-4004-8203-7d0906c656b2.jpg', '2026-06-13 00:15:15');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (4, 3, 'UAE', 'uae', 'destinations/23f5405f-8d6c-4547-9484-1b24da22ecc6.jpg', '2026-06-13 00:15:15');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (5, 1, 'Various', 'various', 'destinations/ae3be439-40ba-4778-ac51-18f7706767bf.jpg', '2026-06-13 00:15:16');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (6, 5, 'Italy', 'italy', 'images/Filao/Italy/pexels-camilacarneiro-6318793.jpg', '2026-06-13 00:15:16');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (7, 5, 'France', 'france', 'destinations/db401ef9-08dd-4c22-b480-71c7a2451b29.jpg', '2026-06-13 00:15:16');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (8, 5, 'Greece', 'greece', 'countries/66efc7cd-3d34-473d-b0f7-ab819d0c63de.jpg', '2026-06-13 00:15:16');
INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES (9, 1, 'Thailand', 'thailand', 'images/Filao/Thailand/pexels-andromeda99-29292274.jpg', '2026-06-13 00:15:16');
