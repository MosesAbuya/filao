<?php
require_once dirname(__DIR__) . '/includes/db.php';
$pdo = getPDO();

$statements = [
    "CREATE TABLE IF NOT EXISTS `blogs` (
      `id`            INT AUTO_INCREMENT PRIMARY KEY,
      `title`         VARCHAR(255)  NOT NULL,
      `slug`          VARCHAR(255)  NOT NULL UNIQUE,
      `excerpt`       TEXT,
      `body`          LONGTEXT,
      `featured_image`VARCHAR(500),
      `author`        VARCHAR(100)  DEFAULT 'Filao Adventures',
      `category`      VARCHAR(100),
      `tags`          VARCHAR(500),
      `status`        ENUM('published','draft') DEFAULT 'draft',
      `seo_title`     VARCHAR(255),
      `meta_description` TEXT,
      `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Add is_recommended column to tours (ignore if exists)
    "ALTER TABLE `tours` ADD COLUMN `is_recommended` TINYINT(1) DEFAULT 0",
    "ALTER TABLE `tours` ADD COLUMN `recommended_activity` VARCHAR(150) DEFAULT NULL",
    "ALTER TABLE `tours` ADD COLUMN `country` VARCHAR(100) DEFAULT NULL",
];

echo "<h2>Running Migration...</h2>";
foreach ($statements as $sql) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ OK: " . htmlspecialchars(substr($sql, 0, 60)) . "...</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color:orange'>⚠ Column already exists (skipped): " . htmlspecialchars(substr($sql, 0, 60)) . "</p>";
        } else {
            echo "<p style='color:red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<hr><p>Done. <a href='seed_blogs.php'>Seed Blogs</a> | <a href='../blog.php'>View Blog</a></p>";
?>
