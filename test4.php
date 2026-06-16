<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '');
$tours = $pdo->query("SELECT id, title, slug, LEFT(highlights, 50) as hl, LEFT(inclusions, 50) as inc FROM tours")->fetchAll(PDO::FETCH_ASSOC);
print_r($tours);
