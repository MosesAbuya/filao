<?php
// Prevent direct access if needed, but usually safe
if (!isset($pageTitle)) $pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | Filao Adventures Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Filao Brand CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
</head>
<body class="<?= isset($bodyClass) ? sanitize($bodyClass) : '' ?>">
<?php
// Display flash messages globally if present
$flash = getFlash();
if ($flash):
?>
<div class="flash-container">
    <div class="flash-toast flash-<?= sanitize($flash['type']) ?>">
        <i class="bi bi-info-circle-fill"></i>
        <span><?= sanitize($flash['message']) ?></span>
    </div>
</div>
<?php endif; ?>
