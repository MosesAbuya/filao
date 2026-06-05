<?php
require_once 'includes/db.php';
$pdo = getPDO();

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// 1. Search Destinations
$stmt = $pdo->prepare("SELECT name as title, slug, 'Destination' as type, featured_image FROM destinations WHERE name LIKE ? OR description LIKE ? LIMIT 3");
$searchTerm = '%' . $q . '%';
$stmt->execute([$searchTerm, $searchTerm]);
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($destinations as $d) {
    $results[] = [
        'title' => $d['title'],
        'url' => 'destinations/' . $d['slug'],
        'type' => 'Destination',
        'image' => $d['featured_image'] ? (str_starts_with($d['featured_image'], 'images/') ? $d['featured_image'] : 'uploads/' . $d['featured_image']) : 'images/Filao/East Africa/pexels-kelly-17291020.jpg'
    ];
}

// 2. Search Tours
$stmt = $pdo->prepare("SELECT title, slug, 'Tour' as type, featured_image FROM tours WHERE status='published' AND (title LIKE ? OR seo_description LIKE ?) LIMIT 5");
$stmt->execute([$searchTerm, $searchTerm]);
$tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($tours as $t) {
    $results[] = [
        'title' => $t['title'],
        'url' => 'tours/' . $t['slug'],
        'type' => 'Tour',
        'image' => $t['featured_image'] ? (str_starts_with($t['featured_image'], 'images/') ? $t['featured_image'] : 'uploads/' . $t['featured_image']) : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg'
    ];
}

// 3. Search Activities
$stmt = $pdo->prepare("SELECT name as title, slug, 'Activity' as type, featured_image FROM activities WHERE name LIKE ? OR description LIKE ? LIMIT 3");
$stmt->execute([$searchTerm, $searchTerm]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($activities as $a) {
    $results[] = [
        'title' => $a['title'],
        'url' => 'activities/' . $a['slug'],
        'type' => 'Activity',
        'image' => $a['featured_image'] ? (str_starts_with($a['featured_image'], 'images/') ? $a['featured_image'] : 'uploads/' . $a['featured_image']) : 'images/Filao/East Africa/pexels-balazsimon-15993990.jpg'
    ];
}

// 4. Search Blog
$stmt = $pdo->prepare("SELECT title, slug, 'Blog' as type, featured_image FROM blogs WHERE status='published' AND (title LIKE ? OR body LIKE ?) LIMIT 3");
$stmt->execute([$searchTerm, $searchTerm]);
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($blogs as $b) {
    $results[] = [
        'title' => $b['title'],
        'url' => 'blog/' . $b['slug'],
        'type' => 'Blog Post',
        'image' => $b['featured_image'] ? (str_starts_with($b['featured_image'], 'images/') ? $b['featured_image'] : 'uploads/' . $b['featured_image']) : 'images/Filao/East Africa/pexels-droneafrica-13234382.jpg'
    ];
}

echo json_encode($results);
