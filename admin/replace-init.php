<?php
$files = [
    'e:/xampp/htdocs/filao/admin/create-accommodation.php',
    'e:/xampp/htdocs/filao/admin/edit-accommodation.php',
    'e:/xampp/htdocs/filao/admin/create-destination.php',
    'e:/xampp/htdocs/filao/admin/edit-destination.php',
];

$summernoteInit = "
    $(document).ready(function() {
        $('.editor-simple').summernote({
            height: 250,
            toolbar: [
                ['font', ['bold', 'italic', 'clear']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    });
";

foreach($files as $f) {
    if(!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Replace tinymce init block with summernote init block
    $c = preg_replace('/tinymce\.init\(\{\s*selector: \'.editor-simple\'.*?\}\);/s', $summernoteInit, $c);
    
    file_put_contents($f, $c);
}
echo "Replaced init blocks in all 4 files.";
