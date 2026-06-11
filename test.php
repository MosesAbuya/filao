<?php
require 'includes/db.php';
$pdo = getPDO();
$blogs = $pdo->query("SELECT id, title, status FROM blogs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
print_r($blogs);
