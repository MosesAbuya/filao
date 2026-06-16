<?php
// Prevent server-side caching of PHP pages (LiteSpeed, Varnish, etc.)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('X-LiteSpeed-Cache-Control: no-cache');

if (!function_exists('getPDO')) {
    require_once dirname(__DIR__) . '/admin/config.php';
}

if (!function_exists('getTourCountries')) {
    function getTourCountries($pdo, $tourId) {
        $stmt = $pdo->prepare("SELECT DISTINCT d.country FROM itinerary_steps ist JOIN destinations d ON d.id=ist.destination_id WHERE ist.tour_id=? AND d.country IS NOT NULL AND d.country != ''");
        $stmt->execute([$tourId]);
        $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($countries)) return 'Multiple Countries';
        return implode(' - ', array_map('htmlspecialchars', $countries));
    }
}
