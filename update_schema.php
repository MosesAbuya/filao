<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

$columns = [
    'is_hot_offer' => 'TINYINT(1) DEFAULT 0',
    'price_1_person' => 'DECIMAL(10,2) DEFAULT NULL',
    'price_2_people' => 'DECIMAL(10,2) DEFAULT NULL',
    'price_3_people' => 'DECIMAL(10,2) DEFAULT NULL',
    'price_4_people' => 'DECIMAL(10,2) DEFAULT NULL',
    'price_5_people' => 'DECIMAL(10,2) DEFAULT NULL',
    'price_6_people' => 'DECIMAL(10,2) DEFAULT NULL'
];

foreach ($columns as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE tours ADD COLUMN $col $def");
        echo "Added $col\n";
    } catch (PDOException $e) {
        // If it fails, probably means the column already exists, which is fine
        echo "Skipped $col: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
