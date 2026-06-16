<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '');
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
