<?php
require_once 'includes/db.php';
$pdo = getPDO();

// Normalize Destinations
$dests = $pdo->query("SELECT id, featured_image FROM destinations WHERE featured_image IS NOT NULL AND featured_image != ''")->fetchAll();
foreach ($dests as $d) {
    $img = $d['featured_image'];
    if (!str_starts_with($img, 'http') && !str_starts_with($img, 'images/') && !str_starts_with($img, 'destinations/')) {
        // Prepend destinations/
        $newImg = 'destinations/' . $img;
        $pdo->prepare("UPDATE destinations SET featured_image = ? WHERE id = ?")->execute([$newImg, $d['id']]);
    }
}

// Normalize Activities
if (!is_dir('uploads/activities')) {
    mkdir('uploads/activities', 0777, true);
}

$acts = $pdo->query("SELECT id, featured_image FROM activities WHERE featured_image IS NOT NULL AND featured_image != ''")->fetchAll();
foreach ($acts as $a) {
    $img = $a['featured_image'];
    if (!str_starts_with($img, 'http') && !str_starts_with($img, 'images/') && !str_starts_with($img, 'activities/')) {
        $oldPath = 'uploads/destinations/' . $img;
        if (file_exists($oldPath)) {
            rename($oldPath, 'uploads/activities/' . $img);
        }
        $newImg = 'activities/' . $img;
        $pdo->prepare("UPDATE activities SET featured_image = ? WHERE id = ?")->execute([$newImg, $a['id']]);
    }
}
echo "Normalization complete.\n";
