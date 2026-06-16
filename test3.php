<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '');
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
print_r($tables);

echo "\nTours columns:\n";
$cols = $pdo->query("DESCRIBE tours")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);

$tour = $pdo->query("SELECT * FROM tours WHERE slug='6-days-safari-cape-town'")->fetch(PDO::FETCH_ASSOC);
echo "\nTour data:\n";
print_r($tour);
