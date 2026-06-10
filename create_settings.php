<?php
require 'includes/db.php';
$pdo = getPDO();
$pdo->exec('CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE NOT NULL, setting_value TEXT);');
echo 'Settings table created.';
