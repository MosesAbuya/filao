<?php
// Prevent server-side caching of PHP pages (LiteSpeed, Varnish, etc.)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('X-LiteSpeed-Cache-Control: no-cache');

if (!function_exists('getPDO')) {
    require_once dirname(__DIR__) . '/admin/config.php';
}
