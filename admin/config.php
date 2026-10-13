<?php
// Environment Detection (Localhost vs Live Production)
$isLocal = false;

if (!empty($_SERVER['HTTP_HOST'])) {
    $host = strtolower($_SERVER['HTTP_HOST']);
    if (
        strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, '::1') !== false ||
        strpos($host, '192.168.') !== false ||
        substr($host, -6) === '.local' ||
        substr($host, -5) === '.test'
    ) {
        $isLocal = true;
    }
} elseif (php_sapi_name() === 'cli') {
    // CLI execution (e.g., local command line scripts / migrations)
    if (DIRECTORY_SEPARATOR === '\\' || (isset($_SERVER['USER']) && $_SERVER['USER'] !== 'filaoadv')) {
        $isLocal = true;
    }
}

define('IS_LOCAL', $isLocal);

if ($isLocal) {
    // Localhost / XAMPP Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'filao_adventures');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Live Production Database Configuration (filaoadventures.co.ke)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'filaoadv_filao_adventures');
    define('DB_USER', 'filaoadv_filao_adventures');
    define('DB_PASS', 'Filao@2026');
    define('DB_CHARSET', 'utf8mb4');

    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// CARTO Basemaps API Key / Token
define('CARTO_API_KEY', 'cb1_4iyw_1_b00d62afad8028539981e974');

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
