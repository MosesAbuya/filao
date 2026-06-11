<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '');
$res = $pdo->query("SELECT * FROM blogs WHERE status='published' ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
