<?php
require_once 'includes/db.php';
$pdo = getPDO();

$tours = $pdo->query("SELECT id, title, excerpt FROM tours")->fetchAll(PDO::FETCH_ASSOC);
$destinations = $pdo->query("SELECT id, name FROM destinations")->fetchAll(PDO::FETCH_ASSOC);
$activities = $pdo->query("SELECT id, name FROM taxonomies WHERE type='activity'")->fetchAll(PDO::FETCH_ASSOC);

$pdo->exec("TRUNCATE TABLE itinerary_steps");
$pdo->exec("DELETE FROM tour_taxonomy_pivot WHERE taxonomy_id IN (SELECT id FROM taxonomies WHERE type='activity')");

foreach ($tours as $tour) {
    $text = strtolower($tour['title'] . ' ' . $tour['excerpt']);
    
    // Destinations
    $step = 1;
    $linkedDests = 0;
    foreach ($destinations as $dest) {
        $destName = strtolower($dest['name']);
        $match = false;
        
        if (str_contains($destName, 'mara') && str_contains($text, 'mara')) $match = true;
        elseif (str_contains($destName, 'amboseli') && str_contains($text, 'amboseli')) $match = true;
        elseif (str_contains($destName, 'nakuru') && str_contains($text, 'nakuru')) $match = true;
        elseif (str_contains($destName, 'zanzibar') && str_contains($text, 'zanzibar')) $match = true;
        elseif (str_contains($destName, 'diani') && str_contains($text, 'diani')) $match = true;
        elseif (str_contains($destName, 'nairobi') && str_contains($text, 'nairobi')) $match = true;
        elseif (str_contains($text, $destName)) $match = true;

        if ($match) {
            $stmt = $pdo->prepare("INSERT INTO itinerary_steps (tour_id, step_number, step_title, step_description, destination_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tour['id'], $step, "Visit " . $dest['name'], "Enjoy an amazing time at " . $dest['name'], $dest['id']]);
            $step++;
            $linkedDests++;
        }
    }
    
    // If no destination linked, link 2 random ones
    if ($linkedDests == 0) {
        shuffle($destinations);
        for ($i=0; $i<2; $i++) {
            $stmt = $pdo->prepare("INSERT INTO itinerary_steps (tour_id, step_number, step_title, step_description, destination_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tour['id'], $step, "Visit " . $destinations[$i]['name'], "Enjoy an amazing time at " . $destinations[$i]['name'], $destinations[$i]['id']]);
            $step++;
        }
    }

    // Activities
    $linkedActs = 0;
    foreach ($activities as $act) {
        $actName = strtolower($act['name']);
        $match = false;
        if (str_contains($actName, 'game') && str_contains($text, 'game')) $match = true;
        elseif (str_contains($actName, 'balloon') && str_contains($text, 'balloon')) $match = true;
        elseif (str_contains($actName, 'migration') && str_contains($text, 'migration')) $match = true;
        elseif (str_contains($actName, 'culture') || str_contains($actName, 'maasai')) {
             if (str_contains($text, 'maasai') || str_contains($text, 'culture')) $match = true;
        }
        elseif (str_contains($actName, 'beach') && str_contains($text, 'beach')) $match = true;

        if ($match) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO tour_taxonomy_pivot (tour_id, taxonomy_id) VALUES (?, ?)");
            $stmt->execute([$tour['id'], $act['id']]);
            $linkedActs++;
        }
    }
    
    // If no activity linked, link 1-2 random ones
    if ($linkedActs == 0) {
        shuffle($activities);
        $stmt = $pdo->prepare("INSERT IGNORE INTO tour_taxonomy_pivot (tour_id, taxonomy_id) VALUES (?, ?)");
        $stmt->execute([$tour['id'], $activities[0]['id']]);
        if (count($activities) > 1) {
            $stmt->execute([$tour['id'], $activities[1]['id']]);
        }
    }
}
echo "Database links updated successfully.";
?>
