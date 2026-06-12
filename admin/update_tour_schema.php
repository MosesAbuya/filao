<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

$columnsToAdd = [
    'price_1_pax' => 'DECIMAL(10,2) NULL',
    'price_2_pax' => 'DECIMAL(10,2) NULL',
    'price_3_pax' => 'DECIMAL(10,2) NULL',
    'price_4_pax' => 'DECIMAL(10,2) NULL',
    'price_5_pax' => 'DECIMAL(10,2) NULL',
    'price_6_pax' => 'DECIMAL(10,2) NULL',
    'price_child_1_pax' => 'DECIMAL(10,2) NULL',
    'price_child_2_pax' => 'DECIMAL(10,2) NULL',
    'price_child_3_pax' => 'DECIMAL(10,2) NULL',
    'price_child_4_pax' => 'DECIMAL(10,2) NULL',
    'price_child_5_pax' => 'DECIMAL(10,2) NULL',
    'price_child_6_pax' => 'DECIMAL(10,2) NULL',
    'is_hot_offer' => 'TINYINT(1) DEFAULT 0',
    'is_active_ad' => 'TINYINT(1) DEFAULT 0',
    'is_joining_tour' => 'TINYINT(1) DEFAULT 0'
];

$output = "Checking tours table schema...\n<br>";

foreach ($columnsToAdd as $col => $def) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `tours` LIKE '$col'");
        $exists = $stmt->fetch();
        if (!$exists) {
            $pdo->exec("ALTER TABLE `tours` ADD COLUMN `$col` $def");
            $output .= "Added missing column: `$col` successfully.<br>";
        } else {
            $output .= "Column `$col` already exists. Skipping.<br>";
        }
    } catch (Exception $e) {
        $output .= "Error checking/adding `$col`: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>Schema Update Complete!</h3>";
echo $output;
echo "<p>You can now safely delete this script (update_tour_schema.php).</p>";
