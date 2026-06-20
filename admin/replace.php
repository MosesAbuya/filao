<?php
$files = [
    'e:/xampp/htdocs/filao/admin/create-tour.php',
    'e:/xampp/htdocs/filao/admin/edit-tour.php',
    'e:/xampp/htdocs/filao/admin/create-accommodation.php',
    'e:/xampp/htdocs/filao/admin/edit-accommodation.php',
    'e:/xampp/htdocs/filao/admin/create-destination.php',
    'e:/xampp/htdocs/filao/admin/edit-destination.php',
];
foreach($files as $f) {
    if(!file_exists($f)) continue;
    $c = file_get_contents($f);
    $c = str_replace('tinymce-label', 'editor-label', $c);
    $c = str_replace('tinymce-wrapper', 'editor-wrapper', $c);
    file_put_contents($f, $c);
}
echo "Replaced CSS classes in all files.";
