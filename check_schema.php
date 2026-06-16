<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures', 'root', '');
$stmt = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'tours' AND TABLE_SCHEMA = 'filao_adventures'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['COLUMN_NAME'] . " - " . $col['COLUMN_TYPE'] . "\n";
}
