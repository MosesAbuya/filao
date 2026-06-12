<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sql = "DROP TABLE IF EXISTS `countries`;\nDROP TABLE IF EXISTS `regions`;\n";

$sql .= "CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";

$stmt = $pdo->query('SELECT * FROM regions');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sql .= "INSERT INTO `regions` (`id`, `name`, `slug`, `featured_image`, `created_at`) VALUES ({$r['id']}, " . $pdo->quote($r['name']) . ", " . $pdo->quote($r['slug']) . ", " . ($r['featured_image'] ? $pdo->quote($r['featured_image']) : 'NULL') . ", " . $pdo->quote($r['created_at']) . ");\n";
}

$sql .= "CREATE TABLE `countries` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";

$stmt = $pdo->query('SELECT * FROM countries');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sql .= "INSERT INTO `countries` (`id`, `region_id`, `name`, `slug`, `featured_image`, `created_at`) VALUES ({$r['id']}, " . ($r['region_id'] ? $r['region_id'] : 'NULL') . ", " . $pdo->quote($r['name']) . ", " . $pdo->quote($r['slug']) . ", " . ($r['featured_image'] ? $pdo->quote($r['featured_image']) : 'NULL') . ", " . $pdo->quote($r['created_at']) . ");\n";
}

file_put_contents(__DIR__ . '/../regions_countries_export.sql', $sql);
echo "Export saved to regions_countries_export.sql\n";
