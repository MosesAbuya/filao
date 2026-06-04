<?php
require_once __DIR__ . '/config.php';

function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function csrfGenerate(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    $token = csrfGenerate();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrfVerify(string $token): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function generateSlug(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if(function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function uniqueSlug(string $table, string $baseSlug, ?int $excludeId = null): string {
    $pdo = getPDO();
    $slug = $baseSlug;
    $counter = 2;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = :slug";
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $params['id'] = $excludeId;
        }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function isAdmin(): bool {
    $user = currentUser();
    return $user && ($user['role'] === 'admin');
}

function requireRole(string ...$roles): void {
    $user = currentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        die('403 Forbidden: You do not have permission to access this resource.');
    }
}

function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

function handleImageUpload(string $inputName, string $subdir = 'tours'): string|false {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $file = $_FILES[$inputName];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowedMimes, true)) {
        return false;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($ext)) {
        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $ext = $mimeToExt[$file['type']] ?? 'jpg';
    }
    
    // UUID Generation
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    $filename = $uuid . '.' . $ext;
    
    $targetDir = rtrim(UPLOADS_PATH, '/\\') . '/' . trim($subdir, '/\\');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $targetPath = $targetDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return trim($subdir, '/\\') . '/' . $filename;
    }
    return false;
}

function handleMultipleImageUpload(string $inputName, string $subdir = 'tours'): array {
    $uploadedPaths = [];
    if (!isset($_FILES[$inputName]) || !is_array($_FILES[$inputName]['name'])) {
        return $uploadedPaths;
    }
    
    $fileCount = count($_FILES[$inputName]['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES[$inputName]['error'][$i] !== UPLOAD_ERR_OK) continue;
        
        $type = $_FILES[$inputName]['type'][$i];
        $size = $_FILES[$inputName]['size'][$i];
        $tmpName = $_FILES[$inputName]['tmp_name'][$i];
        $name = $_FILES[$inputName]['name'][$i];
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($type, $allowedMimes, true) || $size > 5 * 1024 * 1024) continue;
        
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (empty($ext)) {
            $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext = $mimeToExt[$type] ?? 'jpg';
        }
        
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        $filename = $uuid . '.' . $ext;
        
        $targetDir = rtrim(UPLOADS_PATH, '/\\') . '/' . trim($subdir, '/\\');
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        
        if (move_uploaded_file($tmpName, $targetDir . '/' . $filename)) {
            $uploadedPaths[] = trim($subdir, '/\\') . '/' . $filename;
        }
    }
    return $uploadedPaths;
}

function timeSince(string $datetime): string {
    $time = strtotime($datetime);
    $time = time() - $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
    return 'just now';
}

function truncate(string $text, int $length = 150): string {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function monthName(int $n): string {
    return date("F", mktime(0, 0, 0, $n, 10));
}
