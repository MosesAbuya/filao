<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();
$sql = "SET FOREIGN_KEY_CHECKS = 0;\n";

$stmt = $pdo->query('SELECT * FROM destinations');
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sql .= "INSERT INTO `destinations` (`id`, `name`, `slug`, `country`, `region`, `region_type`, `description`, `latitude`, `longitude`, `featured_image`, `created_at`) VALUES ({$r['id']}, " . $pdo->quote($r['name']) . ", " . $pdo->quote($r['slug']) . ", " . $pdo->quote($r['country']) . ", " . ($r['region'] ? $pdo->quote($r['region']) : 'NULL') . ", " . ($r['region_type'] ? $pdo->quote($r['region_type']) : 'NULL') . ", " . ($r['description'] ? $pdo->quote($r['description']) : 'NULL') . ", " . $r['latitude'] . ", " . $r['longitude'] . ", " . ($r['featured_image'] ? $pdo->quote($r['featured_image']) : 'NULL') . ", " . $pdo->quote($r['created_at']) . ") ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `country`=VALUES(`country`), `region`=VALUES(`region`), `region_type`=VALUES(`region_type`), `description`=VALUES(`description`), `latitude`=VALUES(`latitude`), `longitude`=VALUES(`longitude`), `featured_image`=VALUES(`featured_image`);\n";
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/../destinations_export.sql', $sql);
echo "Export saved to destinations_export.sql\n";
