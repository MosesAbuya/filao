<?php
require_once 'includes/db.php';
$pdo = getPDO();
echo "<h2>Tours</h2><pre>";
print_r($pdo->query("SHOW COLUMNS FROM tours")->fetchAll(PDO::FETCH_ASSOC));
echo "</pre><h2>Accommodations</h2><pre>";
print_r($pdo->query("SHOW COLUMNS FROM accommodations")->fetchAll(PDO::FETCH_ASSOC));
echo "</pre><h2>Itinerary Steps</h2><pre>";
print_r($pdo->query("SHOW COLUMNS FROM itinerary_steps")->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
