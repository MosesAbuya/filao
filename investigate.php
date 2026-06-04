<?php
require 'includes/db.php';
$pdo = getPDO();

echo "DESTINATIONS COLUMNS:\n";
print_r($pdo->query("SHOW COLUMNS FROM destinations")->fetchAll(PDO::FETCH_COLUMN));

echo "\nDESTINATIONS COUNTRIES:\n";
print_r($pdo->query("SELECT DISTINCT country FROM destinations")->fetchAll(PDO::FETCH_COLUMN));

echo "\nTAXONOMIES:\n";
print_r($pdo->query("SELECT * FROM taxonomies")->fetchAll(PDO::FETCH_ASSOC));

?>
