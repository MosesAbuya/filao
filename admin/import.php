<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$pdo = getPDO();
$baseImageDir = dirname(__DIR__) . '/images/filao/';

function copyLocalImage($sourcePath, $subdir) {
    if (!file_exists($sourcePath)) return null;
    $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    $filename = $uuid . '.' . strtolower($ext);
    $targetDir = rtrim(UPLOADS_PATH, '/\\') . '/' . trim($subdir, '/\\');
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    copy($sourcePath, $targetDir . '/' . $filename);
    return trim($subdir, '/\\') . '/' . $filename;
}

// 1. Process Destinations
$folders = [
    'Bali' => ['Region' => 'Asia', 'Country' => 'Indonesia', 'Type' => 'Island'],
    'Dubai' => ['Region' => 'Middle East', 'Country' => 'UAE', 'Type' => 'City'],
    'Indian Ocean' => ['Region' => 'Indian Ocean', 'Country' => 'Various', 'Type' => 'Island'],
    'Italy' => ['Region' => 'Europe', 'Country' => 'Italy', 'Type' => 'Country'],
    'Paris' => ['Region' => 'Europe', 'Country' => 'France', 'Type' => 'City'],
    'Santorini' => ['Region' => 'Europe', 'Country' => 'Greece', 'Type' => 'Island'],
    'Thailand' => ['Region' => 'Asia', 'Country' => 'Thailand', 'Type' => 'Country']
];

foreach ($folders as $destName => $info) {
    $slug = generateSlug($destName);
    // Find an image in this folder
    $dir = $baseImageDir . $destName . '/';
    $featuredImg = null;
    if (is_dir($dir)) {
        $files = glob($dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        if (!empty($files)) {
            $featuredImg = copyLocalImage($files[0], 'destinations');
        }
    }
    
    $stmt = $pdo->prepare("SELECT id FROM destinations WHERE slug = ?");
    $stmt->execute([$slug]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO destinations (name, slug, region, country, region_type, featured_image, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
        $ins->execute([$destName, $slug, $info['Region'], $info['Country'], $info['Type'], $featuredImg]);
        echo "Inserted Destination: $destName\n";
    }
}

// Set Region = East Africa for existing Kenya/Tanzania ones
$pdo->exec("UPDATE destinations SET region = 'East Africa' WHERE region IS NULL");

// 2. Add Accommodations
$accomms = [
    'Amboseli Sopa Lodge', 'Kilaguni Serena Safari Lodge', 'Salt Lick Safari Lodge',
    'PrideInn Westlands Nairobi', 'Kibo Safari Camp Amboseli', 'PrideInn Mara Camp',
    'Mara Sopa Lodge', 'Lake Nakuru Sopa Lodge'
];

foreach ($accomms as $acc) {
    $slug = generateSlug($acc);
    $stmt = $pdo->prepare("SELECT id FROM accommodations WHERE slug = ?");
    $stmt->execute([$slug]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO accommodations (name, slug) VALUES (?, ?)");
        $ins->execute([$acc, $slug]);
    }
}

// Assign images to accommodations if available
function setAccommImage($accName, $folderPath) {
    global $pdo;
    $slug = generateSlug($accName);
    $stmt = $pdo->prepare("SELECT id, featured_image FROM accommodations WHERE slug = ?");
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row && empty($row['featured_image'])) {
        $files = glob($folderPath . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        if (!empty($files)) {
            $img = copyLocalImage($files[0], 'accommodations');
            $upd = $pdo->prepare("UPDATE accommodations SET featured_image = ? WHERE id = ?");
            $upd->execute([$img, $row['id']]);
        }
    }
}

setAccommImage('PrideInn Westlands Nairobi', $baseImageDir . 'East Africa/Pride Inn Westlands/');
setAccommImage('Kibo Safari Camp Amboseli', $baseImageDir . 'East Africa/Kibo Safari Camp/');
setAccommImage('Amboseli Sopa Lodge', $baseImageDir . 'East Africa/Sopa Lodges/');
setAccommImage('Mara Sopa Lodge', $baseImageDir . 'East Africa/Sopa Lodges/');
setAccommImage('Lake Nakuru Sopa Lodge', $baseImageDir . 'East Africa/Sopa Lodges/');

// 3. Import Tours

// Truncate tables to ensure clean slate during debug
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE tours;");
$pdo->exec("TRUNCATE TABLE itinerary_steps;");
$pdo->exec("TRUNCATE TABLE tour_images;");
$pdo->exec("TRUNCATE TABLE tour_category_pivot;");
$pdo->exec("TRUNCATE TABLE tour_taxonomy_pivot;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

$jsonData = file_get_contents(__DIR__ . '/tours_extracted.json');
$data = json_decode($jsonData, true);

foreach ($data as $filename => $text) {
    if (strpos($filename, '.pdf') === false) continue;
    
    // Extract Title (first non-empty lines)
    $lines = explode("\n", trim($text));
    $title = '';
    $overview = '';
    $inclusions = '';
    $exclusions = '';
    $days = [];
    
    $mode = 'title';
    $currentDay = null;
    
    foreach ($lines as $line) {
        $t = trim($line);
        if (empty($t)) continue;
        
        if (preg_match('/^(\d+)-Day/i', $t) && $mode == 'title') {
            $title = $t;
            $mode = 'overview';
            continue;
        }
        if (stripos($t, 'Safari Overview') !== false && $mode == 'title') {
            $mode = 'overview';
            continue;
        }
        
        // Matches e.g. "Day 1 – Arrival in Nairobi"
        if (preg_match('/^Day\s+(\d+)\s*[:\x{2013}\-]\s*(.*)/iu', $t, $m) || preg_match('/^Day\s+(\d+)\s*[\x{2013}\-]\s*(.*)/u', str_replace("\xe2\x80\x93", "-", $t), $m)) {
            $mode = 'day';
            $currentDay = [
                'number' => (int)$m[1],
                'title' => trim($m[2]),
                'text' => ''
            ];
            $days[] =& $currentDay;
            continue;
        }
        
        if (stripos($t, 'Inclusions') === 0) {
            $mode = 'inclusions';
            continue;
        }
        if (stripos($t, 'Exclusions') === 0) {
            $mode = 'exclusions';
            continue;
        }
        if (stripos($t, 'Seasons by Month') !== false || stripos($t, 'Season & What to Expect') !== false) {
            $mode = 'seasons';
            continue;
        }
        
        if ($mode == 'overview' && empty($title)) {
            $title = $t;
        } elseif ($mode == 'overview') {
            $overview .= $t . " ";
        } elseif ($mode == 'day' && $currentDay !== null) {
            $currentDay['text'] .= $t . "\n";
        } elseif ($mode == 'inclusions') {
            $inclusions .= "- " . ltrim($t, " \t\n\r\0\x0B\xEF\xBF\xBD\xE2\x80\xA2") . "\n";
        } elseif ($mode == 'exclusions') {
            $exclusions .= "- " . ltrim($t, " \t\n\r\0\x0B\xEF\xBF\xBD\xE2\x80\xA2") . "\n";
        }
    }
    
    if (empty($title)) {
        $title = str_replace('.pdf', '', $filename);
    }
    
    // Clean up lists
    $inclusions = str_replace("- -", "-", $inclusions);
    $exclusions = str_replace("- -", "-", $exclusions);
    $inclusions = str_replace("- \uf0b7", "-", $inclusions);
    $exclusions = str_replace("- \uf0b7", "-", $exclusions);
    
    // Insert Tour
    $slug = uniqueSlug('tours', generateSlug($title));
    $stmt = $pdo->prepare("SELECT id FROM tours WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) continue; // Already inserted
    
    // Attempt to pick a featured image from East Africa
    $tourImgDir = $baseImageDir . 'East Africa/';
    $tourImagesList = glob($tourImgDir . '*/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    $featuredImg = null;
    if (!empty($tourImagesList)) {
        shuffle($tourImagesList);
        $featuredImg = copyLocalImage($tourImagesList[0], 'tours');
    }
    
    $ins = $pdo->prepare("INSERT INTO tours (title, slug, description, duration_days, price_from_usd, status, featured_image, inclusions, exclusions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([
        $title, $slug, $overview, count($days) > 0 ? count($days) : 4, 1200.00, 'published', $featuredImg, $inclusions, $exclusions
    ]);
    
    $tourId = $pdo->lastInsertId();
    echo "Inserted Tour: $title\n";
    
    // Tour Gallery (pick 4 random images)
    for ($i=0; $i<4; $i++) {
        if (isset($tourImagesList[$i+1])) {
            $gPath = copyLocalImage($tourImagesList[$i+1], 'tours');
            if ($gPath) {
                $pdo->exec("INSERT INTO tour_images (tour_id, image_path, sort_order) VALUES ($tourId, '$gPath', $i)");
            }
        }
    }
    
    // Tour Categories (Safari)
    $pdo->exec("INSERT INTO tour_category_pivot (tour_id, category_id) VALUES ($tourId, 1)");
    
    // Insert Steps
    $insertedStepNumbers = [];
    foreach ($days as $idx => $day) {
        $stepTitle = $day['title'];
        $stepDesc = $day['text'];
        
        $sNum = $day['number'];
        if (in_array($sNum, $insertedStepNumbers)) {
            $sNum = max($insertedStepNumbers) + 1;
        }
        $insertedStepNumbers[] = $sNum;
        
        // Try to match destination
        $destId = 1; // Default Maasai Mara
        if (stripos($stepTitle, 'Amboseli') !== false || stripos($stepDesc, 'Amboseli') !== false) $destId = 2;
        if (stripos($stepTitle, 'Tsavo') !== false || stripos($stepDesc, 'Tsavo') !== false) $destId = 3;
        if (stripos($stepTitle, 'Nakuru') !== false || stripos($stepDesc, 'Nakuru') !== false) $destId = 4;
        if (stripos($stepTitle, 'Nairobi') !== false || stripos($stepDesc, 'Nairobi') !== false) $destId = 9;
        
        // Try to match accommodation
        $accId = null;
        $accStmt = $pdo->query("SELECT id, name FROM accommodations");
        foreach ($accStmt as $accRow) {
            $accNameFirst = explode(' ', $accRow['name'])[0];
            if (strlen($accNameFirst) > 3 && stripos($stepDesc, $accNameFirst) !== false) {
                $accId = $accRow['id'];
                break;
            }
        }
        
        $stepImg = null;
        if (isset($tourImagesList[$idx + 5])) {
            $stepImg = copyLocalImage($tourImagesList[$idx + 5], 'steps');
        }
        
        $insStep = $pdo->prepare("INSERT INTO itinerary_steps (tour_id, step_number, destination_id, nights_count, step_title, step_description, accommodation_id, step_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insStep->execute([
            $tourId, $sNum, $destId, 1, $stepTitle, $stepDesc, $accId, $stepImg
        ]);
    }
}
echo "Import Finished Successfully!\n";
