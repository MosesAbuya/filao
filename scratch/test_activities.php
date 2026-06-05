<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();
$stmt = $pdo->query("DESCRIBE activities");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
