<?php
$pdo = new PDO('mysql:host=localhost;dbname=filao_adventures;charset=utf8mb4', 'root', '');
$res = $pdo->query("SELECT id, title, slug, status FROM tours WHERE slug='6-days-safari-cape-town'")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
