<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();
$sql = "DROP TABLE IF EXISTS `destinations`;\n";

$sql .= "CREATE TABLE `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `region_type` varchar(100) DEFAULT 'National Park',
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";

$stmt = $pdo->query('SELECT * FROM destinations');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sql .= "INSERT INTO `destinations` (`id`, `name`, `slug`, `country`, `region`, `region_type`, `description`, `latitude`, `longitude`, `featured_image`, `created_at`) VALUES ({$r['id']}, " . $pdo->quote($r['name']) . ", " . $pdo->quote($r['slug']) . ", " . $pdo->quote($r['country']) . ", " . ($r['region'] ? $pdo->quote($r['region']) : 'NULL') . ", " . ($r['region_type'] ? $pdo->quote($r['region_type']) : 'NULL') . ", " . ($r['description'] ? $pdo->quote($r['description']) : 'NULL') . ", " . $r['latitude'] . ", " . $r['longitude'] . ", " . ($r['featured_image'] ? $pdo->quote($r['featured_image']) : 'NULL') . ", " . $pdo->quote($r['created_at']) . ");\n";
}

file_put_contents(__DIR__ . '/../destinations_export.sql', $sql);
echo "Export saved to destinations_export.sql\n";
