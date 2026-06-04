<?php
require_once 'includes/db.php';
$pdo = getPDO();

try {
    // 1. Add columns to taxonomies if they don't exist
    $pdo->exec("ALTER TABLE taxonomies ADD COLUMN description TEXT NULL");
    $pdo->exec("ALTER TABLE taxonomies ADD COLUMN featured_image VARCHAR(500) NULL");
} catch (Exception $e) {
    // Ignore if columns already exist
}

// 2. Insert Global Destinations
$newDests = [
    ['name' => 'Bali', 'slug' => 'bali', 'country' => 'Indonesia', 'region' => 'Luxury Escape', 'lat' => -8.3405, 'lng' => 115.0920, 'img' => 'images/Filao/Bali/pexels-airlangga-36913571.jpg', 'desc' => 'Bali is a breathtaking Indonesian island known for its forested volcanic mountains, iconic rice paddies, beaches and coral reefs. Experience absolute luxury in private villas, rich cultural temples, and vibrant coastal towns.'],
    ['name' => 'Dubai', 'slug' => 'dubai', 'country' => 'UAE', 'region' => 'Luxury City', 'lat' => 25.2048, 'lng' => 55.2708, 'img' => 'images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg', 'desc' => 'Dubai is a city of superlatives, blending modern architecture, luxury shopping, and lively nightlife. From towering skyscrapers like the Burj Khalifa to traditional gold souks and desert safaris, it offers an unmatched modern escape.'],
    ['name' => 'Santorini', 'slug' => 'santorini', 'country' => 'Greece', 'region' => 'Luxury Escape', 'lat' => 36.3932, 'lng' => 25.4615, 'img' => 'images/Filao/Santorini/pexels-dimi-6604243.jpg', 'desc' => 'Famed for its dramatic views, stunning sunsets, and white-washed houses clinging to cliffs above an underwater caldera, Santorini is the ultimate romantic getaway and a jewel of the Aegean Sea.'],
    ['name' => 'Paris', 'slug' => 'paris', 'country' => 'France', 'region' => 'City of Light', 'lat' => 48.8566, 'lng' => 2.3522, 'img' => 'images/Filao/Paris/pexels-alexazabache-3228811.jpg', 'desc' => 'Paris, France\'s capital, is a major European city and a global center for art, fashion, gastronomy and culture. Its 19th-century cityscape is crisscrossed by wide boulevards and the River Seine.'],
    ['name' => 'Italy', 'slug' => 'italy', 'country' => 'Italy', 'region' => 'European Romance', 'lat' => 41.8719, 'lng' => 12.5674, 'img' => 'images/Filao/Italy/pexels-chait-goli-1918096-3693245.jpg', 'desc' => 'From the historical ruins of Rome and the art of Florence to the romantic canals of Venice and the stunning Amalfi coast, Italy is a country that offers endless beauty, world-renowned cuisine, and rich history.'],
    ['name' => 'Thailand', 'slug' => 'thailand', 'country' => 'Thailand', 'region' => 'Tropical Paradise', 'lat' => 15.8700, 'lng' => 100.9925, 'img' => 'images/Filao/Thailand/pexels-jairmacedo-29215091.jpg', 'desc' => 'Thailand is known for tropical beaches, opulent royal palaces, ancient ruins and ornate temples displaying figures of Buddha. It offers a perfect mix of bustling city life in Bangkok and serene island retreats in Phuket or Koh Samui.']
];

$stmt = $pdo->prepare("INSERT IGNORE INTO destinations (name, slug, country, region_type, description, latitude, longitude, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($newDests as $d) {
    // Move image path relative to root to uploads logic if needed, but for now we can store the direct relative path
    $path = str_replace('images/Filao/', '../images/Filao/', $d['img']); // Make relative to uploads so it resolves or just use raw path
    // Actually, in the frontend we check if it has "uploads/" or just use it directly. 
    // Wait, the frontend code usually does `uploads/` prepended. Let's modify the frontend to check if it starts with 'images/'.
    // For now, save the raw path:
    $stmt->execute([$d['name'], $d['slug'], $d['country'], $d['region'], $d['desc'], $d['lat'], $d['lng'], $d['img']]);
}

// Update existing destinations descriptions
$updates = [
    'Maasai Mara National Reserve' => 'The Maasai Mara is one of the most famous and important wildlife conservation and wilderness areas in Africa, world-renowned for its exceptional populations of lion, African leopard, cheetah and African bush elephant.',
    'Amboseli National Park' => 'Crowned by Mount Kilimanjaro, Africa\'s highest peak, the Amboseli National Park is one of Kenya\'s most popular parks. The name "Amboseli" comes from a Maasai word meaning "salty dust". It is the best place in the world to get close to free-ranging elephants.',
    'Zanzibar Island' => 'Zanzibar is a Tanzanian archipelago off the coast of East Africa. It is characterized by beautiful sandy beaches with fringing coral reefs, and the historic Stone Town.',
    'Diani Beach' => 'Diani Beach is a major beach on the Indian Ocean coast of Kenya. It is renowned for its flawless, long stretch of white-sand beach hugged by lush forest and kissed by surfable waves.',
    'Nairobi' => 'Nairobi is Kenya\'s capital city. In addition to its urban core, the city has Nairobi National Park, a large game reserve known for breeding endangered black rhinos and home to giraffes, zebras and lions.',
    'Tsavo East National Park' => 'Tsavo East is one of the oldest and largest parks in Kenya. Known for its vast elephant herds covered in red dust, it offers a raw and untamed wilderness experience.'
];

$updDest = $pdo->prepare("UPDATE destinations SET description = ? WHERE name = ?");
foreach ($updates as $name => $desc) {
    $updDest->execute([$desc, $name]);
}

// Update activities
$actUpdates = [
    'Game Drives' => ['Desc' => 'Encounter Africa\'s most iconic wildlife from the comfort of our custom 4x4 Land Cruisers, guided by expert naturalists.', 'Img' => 'images/Filao/East Africa/Maasai Mara/pexels-photo-8150758 (7).jpg'],
    'Bush Walks' => ['Desc' => 'Step out of the vehicle and experience the African bush on foot. Learn to track animals and understand the micro-ecosystems.', 'Img' => 'images/Filao/East Africa/Amboseli/Sarova-Shaba-Safari-breakfast-in-the-wild.jpg'],
    'Hot Air Balloon' => ['Desc' => 'Float silently above the savannah at sunrise, witnessing the spectacular landscape and wildlife from a breathtaking aerial perspective.', 'Img' => 'images/Filao/East Africa/pexels-droneafrica-15373902.jpg'],
    'Cultural Visits' => ['Desc' => 'Engage with local communities like the Maasai. Learn about their traditions, dances, and daily life in an authentic, respectful setting.', 'Img' => 'images/Filao/East Africa/Maasai Mara/free-photo-of-portrait-of-a-maasai-man-in-traditional-attire (6).jpeg'],
    'Photography' => ['Desc' => 'Specially designed safaris for photography enthusiasts, providing the best lighting, angles, and unhurried time with the wildlife.', 'Img' => 'images/Filao/East Africa/Maasai Mara/free-photo-of-leopard-resting-in-tree-masai-mara-kenya (4).jpeg'],
    // Adding the Great Migration as an activity to ensure it exists
];

$updTax = $pdo->prepare("UPDATE taxonomies SET description = ?, featured_image = ? WHERE name = ? AND type='activity'");
foreach ($actUpdates as $name => $data) {
    $updTax->execute([$data['Desc'], $data['Img'], $name]);
}

// Insert The Great Migration as an activity if missing
$migCheck = $pdo->prepare("SELECT id FROM taxonomies WHERE slug='great-migration'");
$migCheck->execute();
if (!$migCheck->fetch()) {
    $pdo->exec("INSERT INTO taxonomies (type, name, slug, description, featured_image) VALUES ('activity', 'The Great Migration', 'great-migration', 'Witness one of nature\'s greatest spectacles   over 1.5 million wildebeest crossing the plains and rivers of the Serengeti and Maasai Mara.', 'images/Filao/East Africa/Maasai Mara/free-photo-of-wildebeest-grazing-in-the-kenyan-savannah (6).jpeg')");
}

echo "Database updated successfully.";
