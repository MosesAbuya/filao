<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/helpers.php';

$pdo = getPDO();

// 1. Create Tables
$pdo->exec("
CREATE TABLE IF NOT EXISTS `regions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `featured_image` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `countries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `region_id` INT NULL,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `featured_image` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`region_id`) REFERENCES `regions`(`id`) ON DELETE SET NULL
);
");

// Helper to get first image from a folder
function getFirstImage($folderPath) {
    $fullPath = __DIR__ . '/../' . $folderPath;
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        foreach ($files as $f) {
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
                return $folderPath . '/' . $f;
            }
        }
    }
    return null;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

// 2. Insert Regions
$regionsToCreate = [
    'Africa' => 'images/Filao/East Africa',
    'Asia' => 'images/Filao/Bali',
    'Middle East' => 'images/Filao/Dubai',
    'Indian Ocean' => 'images/Filao/Indian Ocean',
    'Europe' => 'images/Filao/Italy'
];

$stmtRegion = $pdo->prepare("INSERT IGNORE INTO regions (name, slug, featured_image) VALUES (?, ?, ?)");
foreach ($regionsToCreate as $name => $folder) {
    $slug = slugify($name);
    $img = getFirstImage($folder);
    $stmtRegion->execute([$name, $slug, $img]);
}

// 3. Extract Countries and Map to Regions
$countries = $pdo->query("SELECT DISTINCT country FROM destinations WHERE country IS NOT NULL AND country != '' UNION SELECT DISTINCT country FROM tours WHERE country IS NOT NULL AND country != ''")->fetchAll(PDO::FETCH_COLUMN);

$stmtCountry = $pdo->prepare("INSERT IGNORE INTO countries (region_id, name, slug, featured_image) VALUES (?, ?, ?, ?)");
$stmtGetRegion = $pdo->prepare("SELECT id FROM regions WHERE name = ?");

foreach ($countries as $cName) {
    $cName = trim($cName);
    if (empty($cName)) continue;
    
    $regionName = 'Africa';
    $cLower = strtolower($cName);
    if (in_array($cLower, ['maldives', 'sri lanka', 'indonesia', 'bali'])) {
        $regionName = 'Asia';
    } elseif (in_array($cLower, ['dubai', 'uae', 'oman', 'qatar'])) {
        $regionName = 'Middle East';
    } elseif (in_array($cLower, ['seychelles', 'mauritius', 'madagascar'])) {
        $regionName = 'Indian Ocean';
    } elseif (in_array($cLower, ['france', 'paris', 'greece', 'santorini', 'italy'])) {
        $regionName = 'Europe';
    }
    
    $stmtGetRegion->execute([$regionName]);
    $regionId = $stmtGetRegion->fetchColumn();
    
    // Find image
    // Try folder specific to country first
    $img = getFirstImage('images/Filao/' . $cName);
    if (!$img) {
        // Fallback to MAX(featured_image) from destinations
        $stmtMax = $pdo->prepare("SELECT MAX(featured_image) FROM destinations WHERE country = ? AND featured_image IS NOT NULL AND featured_image != ''");
        $stmtMax->execute([$cName]);
        $maxImg = $stmtMax->fetchColumn();
        if ($maxImg) {
            $img = str_starts_with($maxImg, 'destinations/') ? $maxImg : 'destinations/' . $maxImg;
        }
    }
    
    $slug = slugify($cName);
    $stmtCountry->execute([$regionId, $cName, $slug, $img]);
}

echo "Migration completed successfully!";
