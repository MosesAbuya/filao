<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query('SELECT id, name, country, region_type FROM destinations');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
