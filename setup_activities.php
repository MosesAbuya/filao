<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();

try {
    // 1. Create activities table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            category VARCHAR(100) NOT NULL,
            description TEXT,
            featured_image VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Create activity_tour pivot table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_tour (
            activity_id INT NOT NULL,
            tour_id INT NOT NULL,
            PRIMARY KEY (activity_id, tour_id),
            FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
            FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Clear existing activities
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE activity_tour; TRUNCATE TABLE activities; SET FOREIGN_KEY_CHECKS = 1;");

    // Get all valid images from uploads/destinations
    $imagesDir = __DIR__ . '/uploads/destinations';
    $images = array_values(array_filter(scandir($imagesDir), function($file) use ($imagesDir) {
        return is_file($imagesDir . '/' . $file) && $file !== '.gitkeep';
    }));

    if (empty($images)) {
        die("No images found in uploads/destinations/\n");
    }

    // 4. Seed activities
    $activitiesData = [
        ['Game Drives', 'game-drives', 'Wildlife Experiences', 'Experience the thrill of traditional game drives across vast African savannahs.'],
        ['Bush Walks', 'bush-walks', 'Wildlife Experiences', 'Step out of the vehicle and connect intimately with nature on a guided walking safari.'],
        ['Hot Air Balloon Safaris', 'hot-air-balloon', 'Wildlife Experiences', 'Float gently over the Maasai Mara or Serengeti at dawn, witnessing wildlife from above.'],
        ['Birdwatching', 'birdwatching', 'Wildlife Experiences', 'Discover a paradise of diverse bird species in their natural habitats.'],
        ['Photography Safaris', 'photography-safaris', 'Wildlife Experiences', 'Capture the perfect shot under the guidance of expert wildlife photographers.'],
        ['Mountain Hiking', 'hiking', 'Adventure & Exploration', 'Conquer iconic peaks like Mount Kilimanjaro and Mount Kenya.'],
        ['Night Game Drives', 'night-game-drives', 'Adventure & Exploration', 'Venture out after dark to discover the secretive nocturnal creatures of the bush.'],
        ['Sundowner Experiences', 'sundowner', 'Adventure & Exploration', 'Sip refreshing drinks as you watch breathtaking African sunsets in the wilderness.'],
        ['Maasai Village Visits', 'maasai-village', 'Cultural Experiences', 'Engage with the Maasai people, learn about their traditions, and support local communities.'],
        ['Cultural Site Tours', 'cultural-visits', 'Cultural Experiences', 'Explore ancient ruins, historical sites, and vibrant local markets.'],
        ['Snorkeling', 'snorkeling', 'Water & Beach', 'Dive into crystal-clear waters and explore vibrant coral reefs.'],
        ['Scuba Diving', 'scuba-diving', 'Water & Beach', 'Plunge deeper into the ocean to encounter majestic marine life.'],
        ['Dhow Cruise', 'dhow-cruise', 'Water & Beach', 'Sail along the coast in a traditional wooden dhow, especially magical at sunset.']
    ];

    $stmt = $pdo->prepare("INSERT INTO activities (name, slug, category, description, featured_image) VALUES (?, ?, ?, ?, ?)");
    foreach ($activitiesData as $index => $act) {
        $img = $images[$index % count($images)];
        $stmt->execute([$act[0], $act[1], $act[2], $act[3], $img]);
    }

    // 5. Seed activity_tour relationships (Randomly assign 2-4 activities per tour)
    $tours = $pdo->query("SELECT id FROM tours WHERE status='published'")->fetchAll(PDO::FETCH_COLUMN);
    $activityIds = $pdo->query("SELECT id FROM activities")->fetchAll(PDO::FETCH_COLUMN);
    
    $pivotStmt = $pdo->prepare("INSERT IGNORE INTO activity_tour (activity_id, tour_id) VALUES (?, ?)");
    
    foreach ($tours as $tourId) {
        $numActivities = rand(2, 4);
        $randomKeys = array_rand($activityIds, $numActivities);
        foreach ((array)$randomKeys as $key) {
            $pivotStmt->execute([$activityIds[$key], $tourId]);
        }
    }

    // 6. Fix broken destination images
    // Get destinations that have 'images/Filao/' paths which we broke in fix_db.php
    $brokenDestinations = $pdo->query("SELECT id FROM destinations WHERE featured_image LIKE 'images/Filao/%'")->fetchAll(PDO::FETCH_COLUMN);
    $updateDest = $pdo->prepare("UPDATE destinations SET featured_image = ? WHERE id = ?");
    
    $imgIndex = 0;
    foreach ($brokenDestinations as $destId) {
        $img = $images[$imgIndex % count($images)];
        $updateDest->execute([$img, $destId]);
        $imgIndex++;
    }

    echo "Activities database setup, seeded, and destination images fixed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
