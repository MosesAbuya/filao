<?php
if (!function_exists('getPDO')) {
    require_once __DIR__ . '/db.php';
}

try {
    $headTagsPdo = getPDO();
    $activeTags = $headTagsPdo->query("SELECT code FROM head_tags WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
    if ($activeTags) {
        echo "<!-- Dynamic Head Tags -->\n";
        foreach ($activeTags as $tag) {
            echo $tag['code'] . "\n";
        }
        echo "<!-- End Dynamic Head Tags -->\n";
    }
} catch (Exception $e) {
    // Fail silently if table doesn't exist or DB is down so we don't break the frontend
}
?>
