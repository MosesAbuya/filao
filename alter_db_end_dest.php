<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures', 'root', '');
$pdo->exec('ALTER TABLE tours ADD COLUMN end_destination_id INT DEFAULT NULL');
echo "Column added successfully.";
