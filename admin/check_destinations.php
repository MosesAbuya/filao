<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query('DESCRIBE destinations');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
