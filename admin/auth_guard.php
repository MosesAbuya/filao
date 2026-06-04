<?php
require_once __DIR__ . '/helpers.php';
sessionStart();

if (empty($_SESSION['user'])) {
    setFlash('warning', 'Please log in to access the admin panel.');
    redirect('login.php');
}
