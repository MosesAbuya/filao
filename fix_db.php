<?php
require_once __DIR__ . '/admin/config.php';
$pdo = getPDO();
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/Bali/pexels-airlangga-36913571.jpg' WHERE name = 'Bali'");
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/Dubai/pexels-axp-photography-500641970-16412106.jpg' WHERE name = 'Dubai'");
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/Santorini/pexels-pixabay-164435.jpg' WHERE name = 'Santorini'");
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/Thailand/pexels-streetwindy-2108831.jpg' WHERE name = 'Thailand'");
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/East Africa/pexels-diego-ferrari-33201434-13979356.jpg' WHERE name = 'Maasai Mara National Reserve'");
$pdo->query("UPDATE destinations SET featured_image = 'images/Filao/East Africa/pexels-gabriele-brancati-32566116-14881252.jpg' WHERE name = 'Amboseli National Park'");
echo "DB destinations updated successfully!\n";
