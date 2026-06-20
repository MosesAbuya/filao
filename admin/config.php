<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration for Filao Adventures Admin
// Adjust credentials to match your XAMPP MySQL setup

define('DB_HOST', 'localhost');
define('DB_NAME', 'filaoadv_filao_adventures');
define('DB_USER', 'filaoadv_filao_adventures');
define('DB_PASS', 'Filao@2026');
define('DB_CHARSET', 'utf8mb4');

// define('DB_HOST', 'localhost');
// define('DB_NAME', 'filao_adventures');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_CHARSET', 'utf8mb4');

// App paths
define('ADMIN_ROOT', __DIR__);
define('UPLOADS_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOADS_URL', '../uploads/'); // relative from pages

// PDO singleton
function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this rather than displaying
            http_response_code(500);
            die($e->getMessage());
        }
    }
    return $pdo;
}
