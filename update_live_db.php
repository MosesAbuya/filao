<?php
/**
 * Live Database Updater Script
 * Run this once on the live server to synchronize all database structure changes.
 * Afterwards, you should delete this file for security.
 */

require_once __DIR__ . '/includes/db.php';

try {
    $pdo = getPDO();
    echo "<h2>Running Database Updates...</h2>";

    // 1. CREATE NEW TABLES
    $tables = [
        "settings" => "CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) UNIQUE NOT NULL,
            `setting_value` TEXT
        )",
        "enquiries" => "CREATE TABLE IF NOT EXISTS `enquiries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type` VARCHAR(50) NOT NULL DEFAULT 'contact',
            `first_name` VARCHAR(120) NOT NULL,
            `last_name` VARCHAR(120) NOT NULL DEFAULT '',
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(60) DEFAULT NULL,
            `destination` VARCHAR(255) DEFAULT NULL,
            `tour_id` INT DEFAULT NULL,
            `tour_title` VARCHAR(255) DEFAULT NULL,
            `travel_month` VARCHAR(30) DEFAULT NULL,
            `travel_year` YEAR DEFAULT NULL,
            `duration_days` VARCHAR(30) DEFAULT NULL,
            `adults` TINYINT UNSIGNED DEFAULT 2,
            `children` TINYINT UNSIGNED DEFAULT 0,
            `budget_usd` INT DEFAULT NULL,
            `activities` TEXT DEFAULT NULL,
            `additional_info` TEXT DEFAULT NULL,
            `status` VARCHAR(50) DEFAULT 'new',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $name => $query) {
        $pdo->exec($query);
        echo "<p style='color: green;'>&#10004; Table `$name` ensured.</p>";
    }

    // 2. ADD DEFAULT SETTINGS IF THEY DONT EXIST
    $defaultSettings = [
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_user' => 'your_email@example.com',
        'smtp_pass' => 'your_password',
        'contact_email' => 'info@filaoadventures.co.ke'
    ];

    $stmtInsertSetting = $pdo->prepare("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
    foreach ($defaultSettings as $key => $val) {
        $stmtInsertSetting->execute([$key, $val]);
    }
    echo "<p style='color: green;'>&#10004; Default SMTP settings ensured.</p>";

    // 3. ALTER TABLES (Safely add columns if they don't exist)
    $columnsToAdd = [
        // Table -> [Column Name, Definition]
        'tours' => [
            ['is_hot_offer', 'TINYINT(1) DEFAULT 0'],
            ['is_recommended', 'TINYINT(1) DEFAULT 0'],
            ['recommended_activity', 'VARCHAR(150) DEFAULT NULL'],
            ['country', 'VARCHAR(100) DEFAULT NULL'],
            ['price_child_1_pax', 'DECIMAL(10,2) NULL'],
            ['price_child_2_pax', 'DECIMAL(10,2) NULL'],
            ['price_child_3_pax', 'DECIMAL(10,2) NULL'],
            ['price_child_4_pax', 'DECIMAL(10,2) NULL'],
            ['price_child_5_pax', 'DECIMAL(10,2) NULL'],
            ['price_child_6_pax', 'DECIMAL(10,2) NULL']
        ],
        'destinations' => [
            ['region', 'VARCHAR(100) DEFAULT NULL']
        ],
        'taxonomies' => [
            ['image', 'VARCHAR(500) DEFAULT NULL'],
            ['description', 'TEXT DEFAULT NULL'],
            ['featured_image', 'VARCHAR(500) DEFAULT NULL']
        ]
    ];

    foreach ($columnsToAdd as $table => $columns) {
        foreach ($columns as $colInfo) {
            $columnName = $colInfo[0];
            $columnDef = $colInfo[1];

            // Check if column exists
            $checkStmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$columnName'");
            if ($checkStmt->rowCount() === 0) {
                // Add column
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$columnName` $columnDef");
                echo "<p style='color: blue;'>&#10004; Added column `$columnName` to `$table`.</p>";
            } else {
                echo "<p style='color: gray;'>- Column `$columnName` already exists in `$table`.</p>";
            }
        }
    }

    echo "<h3 style='color: darkgreen;'>All database updates have been applied successfully!</h3>";
    echo "<p><strong>Important:</strong> Please delete this file (`update_live_db.php`) from your server now to prevent unauthorized access.</p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Database Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
