<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = $_POST;
if (empty($input)) {
    $json = file_get_contents('php://input');
    if ($json) {
        $input = json_decode($json, true) ?: [];
    }
}

function clean($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

$stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('contact_email', 'agent_notify_email')");
$settings_raw = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$adminEmail = !empty($settings_raw['agent_notify_email']) ? $settings_raw['agent_notify_email'] : ($settings_raw['contact_email'] ?? 'info@filaoadventures.co.ke');

// CREATE TABLE
$pdo->exec("CREATE TABLE IF NOT EXISTS partner_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) NOT NULL,
    company_reg_number VARCHAR(100) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    
    agent_name VARCHAR(255) NOT NULL,
    agent_phone VARCHAR(60) NOT NULL,
    agent_email VARCHAR(255) NOT NULL,
    agent_mobile VARCHAR(60) DEFAULT NULL,
    
    emergency_name VARCHAR(255) NOT NULL,
    emergency_phone VARCHAR(60) NOT NULL,
    emergency_email VARCHAR(255) NOT NULL,
    
    agent_type ENUM('RETAIL', 'WHOLESALE') NOT NULL,
    product_updates ENUM('YES', 'NO') NOT NULL DEFAULT 'NO',
    updates_email VARCHAR(255) DEFAULT NULL,
    
    status ENUM('new', 'reviewed', 'approved', 'rejected') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$company_name = clean($input['company_name']);
$street_address = clean($input['street_address']);
$city = clean($input['city']);
$state = clean($input['state']);
$country = clean($input['country']);
$company_reg_number = clean($input['company_reg_number']);
$website = clean($input['website']);

$agent_name = clean($input['agent_name']);
$agent_phone = clean($input['agent_phone']);
$agent_email = filter_var(trim($input['agent_email']), FILTER_SANITIZE_EMAIL);
$agent_mobile = clean($input['agent_mobile']);

$emergency_name = clean($input['emergency_name']);
$emergency_phone = clean($input['emergency_phone']);
$emergency_email = filter_var(trim($input['emergency_email']), FILTER_SANITIZE_EMAIL);

$agent_type = in_array($input['agent_type'], ['RETAIL', 'WHOLESALE']) ? $input['agent_type'] : 'RETAIL';
$product_updates = in_array($input['product_updates'], ['YES', 'NO']) ? $input['product_updates'] : 'NO';
$updates_email = filter_var(trim($input['updates_email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!$company_name || !$agent_name || !$agent_email) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($agent_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid agent email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO partner_agents 
        (company_name, street_address, city, state, country, company_reg_number, website, 
         agent_name, agent_phone, agent_email, agent_mobile, 
         emergency_name, emergency_phone, emergency_email, 
         agent_type, product_updates, updates_email) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $company_name, $street_address, $city, $state, $country, $company_reg_number, $website,
        $agent_name, $agent_phone, $agent_email, $agent_mobile,
        $emergency_name, $emergency_phone, $emergency_email,
        $agent_type, $product_updates, $updates_email
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    exit;
}

// Emails
$emailMsg = '';
try {
    $adminBody = "<h3>New Agent Application</h3>
                  <p><strong>Company:</strong> $company_name</p>
                  <p><strong>Country:</strong> $country</p>
                  <p><strong>Agent Name:</strong> $agent_name</p>
                  <p><strong>Agent Email:</strong> $agent_email</p>
                  <p><strong>Agent Phone:</strong> $agent_phone</p>
                  <p><strong>Type:</strong> $agent_type</p>
                  <hr>
                  <p>Please log in to the admin dashboard to review the full details of this application.</p>";
    sendSiteEmail($adminEmail, "Filao Admin", "New Agent Application: $company_name", $adminBody, true, 'agent_');

    $userBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <div style='text-align: center; padding: 20px 0;'>
                <h2 style='color: #C49018; margin-bottom: 5px;'>Hello {$agent_name},</h2>
                <h3 style='margin-top: 0; color: #555;'>Thank you for applying to be an agent with Filao Adventures!</h3>
            </div>
            <div style='background-color: #f9f9f9; padding: 25px; border-radius: 8px; font-size: 15px; line-height: 1.6;'>
                <p>We have successfully received your agent application for <strong>{$company_name}</strong>.</p>
                <p>Our team is currently reviewing your details. We will be in touch shortly to finalize the onboarding process and provide you with access to our agent portal and exclusive rates.</p>
            </div>
            <div style='text-align: center; padding-top: 30px; font-size: 14px; color: #777;'>
                <p>Best Regards,<br><strong style='color: #333;'>Filao Adventures Team</strong></p>
                <p><a href='https://filaoadventures.co.ke' style='color: #C49018; text-decoration: none;'>www.filaoadventures.co.ke</a></p>
            </div>
        </div>";
    sendSiteEmail($agent_email, $agent_name, "We've received your agent application", $userBody, true, 'agent_');
} catch (\Exception $e) {
    $emailMsg = "";
}

echo json_encode(['success' => true, 'message' => "Thank you, {$agent_name}! Your application for {$company_name} has been received successfully." . $emailMsg]);
