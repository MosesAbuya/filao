<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();

function getValidImages($dir) {
    $images = [];
    if (!is_dir($dir)) return $images;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $images = array_merge($images, getValidImages($path));
        } else {
            if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
                $images[] = $path;
            }
        }
    }
    return $images;
}

$sourceImages = getValidImages(__DIR__ . '/images/Filao');
if (empty($sourceImages)) {
    die("No source images found in images/Filao\n");
}
shuffle($sourceImages);

$uploadsDir = __DIR__ . '/uploads/destinations';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// 1. Update Destinations
$destinations = $pdo->query("SELECT id, name FROM destinations")->fetchAll();
$imgIndex = 0;
foreach ($destinations as $dest) {
    $srcImg = $sourceImages[$imgIndex % count($sourceImages)];
    $ext = pathinfo($srcImg, PATHINFO_EXTENSION);
    $uuid = generateUuid() . '.' . $ext;
    $destPath = $uploadsDir . '/' . $uuid;
    
    if (copy($srcImg, $destPath)) {
        $stmt = $pdo->prepare("UPDATE destinations SET featured_image = ? WHERE id = ?");
        $stmt->execute([$uuid, $dest['id']]);
        echo "Updated destination: {$dest['name']} with $uuid\n";
    }
    $imgIndex++;
}

// 2. Update Activities
$activities = $pdo->query("SELECT id, name FROM activities")->fetchAll();
foreach ($activities as $act) {
    $srcImg = $sourceImages[$imgIndex % count($sourceImages)];
    $ext = pathinfo($srcImg, PATHINFO_EXTENSION);
    $uuid = generateUuid() . '.' . $ext;
    $destPath = $uploadsDir . '/' . $uuid;
    
    if (copy($srcImg, $destPath)) {
        $stmt = $pdo->prepare("UPDATE activities SET featured_image = ? WHERE id = ?");
        $stmt->execute([$uuid, $act['id']]);
        echo "Updated activity: {$act['name']} with $uuid\n";
    }
    $imgIndex++;
}

echo "\nImage copy and DB update complete!\n";
