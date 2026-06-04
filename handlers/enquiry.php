<?php
/**
 * handlers/enquiry.php
 * AJAX handler for:
 *  - Contact form (type=contact)
 *  - Start Planning stepper (type=start_planning)
 * Returns JSON {success, message}
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$type = trim($_POST['type'] ?? 'contact');

// --- Helper: sanitize ---
function clean($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

// ----------------------------------------------------------
// ENSURE tables exist
// ----------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL DEFAULT 'contact',
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(60) DEFAULT NULL,
    destination VARCHAR(255) DEFAULT NULL,
    tour_id INT DEFAULT NULL,
    tour_title VARCHAR(255) DEFAULT NULL,
    travel_month VARCHAR(30) DEFAULT NULL,
    travel_year YEAR DEFAULT NULL,
    duration_days VARCHAR(30) DEFAULT NULL,
    adults TINYINT UNSIGNED DEFAULT 2,
    children TINYINT UNSIGNED DEFAULT 0,
    budget_usd INT DEFAULT NULL,
    activities TEXT DEFAULT NULL,
    trip_purpose TEXT DEFAULT NULL,
    travelled_before ENUM('yes','no') DEFAULT NULL,
    referred ENUM('yes','no') DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('new','in_progress','closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($type === 'contact') {
    // ---- CONTACT FORM ----
    $fname  = clean($_POST['fname'] ?? '');
    $lname  = clean($_POST['lname'] ?? '');
    $email  = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone  = clean($_POST['phone'] ?? '');
    $dest   = clean($_POST['destination'] ?? '');
    $adults = max(1, (int)($_POST['adults'] ?? 2));
    $children = max(0, (int)($_POST['children'] ?? 0));
    $tdate  = clean($_POST['travel_date'] ?? ''); // YYYY-MM format
    $msg    = clean($_POST['message'] ?? '');

    if (!$fname || !$email) {
        echo json_encode(['success' => false, 'message' => 'Please fill in your name and email.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    $travel_month = $tdate ? date('F', strtotime($tdate . '-01')) : null;
    $travel_year  = $tdate ? date('Y', strtotime($tdate . '-01')) : null;

    $stmt = $pdo->prepare("INSERT INTO enquiries 
        (type, first_name, last_name, email, phone, destination, travel_month, travel_year, adults, children, message)
        VALUES ('contact', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fname, $lname, $email, $phone, $dest, $travel_month, $travel_year, $adults, $children, $msg]);

    echo json_encode(['success' => true, 'message' => "Thank you, {$fname}! Your enquiry has been received. A Filao safari specialist will be in touch shortly."]);
    exit;
}

if ($type === 'start_planning') {
    // ---- START PLANNING STEPPER ----
    $fname      = clean($_POST['first_name'] ?? '');
    $lname      = clean($_POST['last_name'] ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone      = clean($_POST['phone'] ?? '');
    $dest       = clean($_POST['destination'] ?? '');
    $tour_id    = (int)($_POST['tour_id'] ?? 0) ?: null;
    $tour_title = clean($_POST['tour_title'] ?? '');
    $activities = clean(implode(', ', (array)($_POST['activities'] ?? [])));
    $purpose    = clean($_POST['custom_purpose'] ?? '');
    $travel_month = clean($_POST['travel_month'] ?? '');
    $travel_year  = (int)($_POST['travel_year'] ?? 0) ?: null;
    $duration   = clean($_POST['duration'] ?? '');
    $adults     = max(1, (int)($_POST['adults'] ?? 2));
    $children   = max(0, (int)($_POST['children'] ?? 0));
    $budget     = (int)($_POST['budget'] ?? 0) ?: null;
    $travelled  = clean($_POST['travelled_before'] ?? '');
    $referred   = clean($_POST['referred'] ?? '');
    $msg        = clean($_POST['message'] ?? '');

    if (!$fname || !$email) {
        echo json_encode(['success' => false, 'message' => 'Please fill in your name and email.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO enquiries 
        (type, first_name, last_name, email, phone, destination, tour_id, tour_title,
         travel_month, travel_year, duration_days, adults, children, budget_usd,
         activities, trip_purpose, travelled_before, referred, message)
        VALUES ('start_planning', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $fname, $lname, $email, $phone, $dest, $tour_id, $tour_title,
        $travel_month, $travel_year, $duration, $adults, $children, $budget,
        $activities, $purpose,
        $travelled ?: null, $referred ?: null,
        $msg
    ]);

    echo json_encode(['success' => true, 'message' => "Thank you, {$fname}! We've received your safari plan. Our specialist will reach out to you within 24 hours to craft your perfect journey."]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown form type.']);
