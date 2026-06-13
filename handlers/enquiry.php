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
require_once __DIR__ . '/../includes/mailer.php';
$pdo = getPDO();

// Only accept POST
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

$type = trim($input['type'] ?? $input['form_type'] ?? 'contact');

// --- Helper: sanitize ---
function clean($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

$stmtSettings = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
$adminEmail = $stmtSettings->fetchColumn() ?: 'info@filaoadventures.co.ke';

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

// Silent upgrades for older live DB schemas
$cols = [
    "last_name VARCHAR(120) NOT NULL DEFAULT ''",
    "destination VARCHAR(255) DEFAULT NULL",
    "tour_id INT DEFAULT NULL",
    "tour_title VARCHAR(255) DEFAULT NULL",
    "travel_month VARCHAR(30) DEFAULT NULL",
    "travel_year YEAR DEFAULT NULL",
    "duration_days VARCHAR(30) DEFAULT NULL",
    "budget_usd INT DEFAULT NULL",
    "activities TEXT DEFAULT NULL",
    "trip_purpose TEXT DEFAULT NULL",
    "travelled_before ENUM('yes','no') DEFAULT NULL",
    "referred ENUM('yes','no') DEFAULT NULL"
];
foreach($cols as $col) {
    try {
        $pdo->exec("ALTER TABLE enquiries ADD COLUMN $col");
    } catch(PDOException $e) {}
}

if ($type === 'contact') {
    // ---- CONTACT FORM ----
    $fname  = clean($input['fname'] ?? $input['first_name'] ?? '');
    $lname  = clean($input['lname'] ?? $input['last_name'] ?? '');
    $email  = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone  = clean($input['phone'] ?? '');
    $dest   = clean($input['destination'] ?? '');
    $adults = max(1, (int)($input['adults'] ?? 2));
    $children = max(0, (int)($input['children'] ?? 0));
    $tdate  = clean($input['travel_date'] ?? '');
    $subject= clean($input['subject'] ?? 'New Contact Form Enquiry');
    $msg    = clean($input['message'] ?? '');

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

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries 
            (type, first_name, last_name, email, phone, destination, travel_month, travel_year, adults, children, message)
            VALUES ('contact', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fname, $lname, $email, $phone, $dest, $travel_month, $travel_year, $adults, $children, $msg]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error (contact): ' . $e->getMessage()]);
        exit;
    }

    // Send Emails
    $emailMsg = '';
    try {
        $adminBody = "<h3>New Contact Enquiry</h3>
                      <p><strong>Name:</strong> $fname $lname</p>
                      <p><strong>Email:</strong> $email</p>
                      <p><strong>Phone:</strong> $phone</p>
                      <p><strong>Subject:</strong> $subject</p>
                      <p><strong>Message:</strong><br/>$msg</p>";
        sendSiteEmail($adminEmail, "Filao Admin", "New Contact Enquiry: $subject", $adminBody);

        $userBody = "<h3>Thank you for reaching out!</h3>
                     <p>Hi $fname,</p>
                     <p>We have received your message. Our safari specialists will review it and get back to you shortly.</p>
                     <p>Best Regards,<br>Filao Adventures Team</p>";
        sendSiteEmail($email, "$fname $lname", "We have received your enquiry", $userBody);
    } catch (\Exception $e) {
        $emailMsg = " [MAIL DEBUG: " . $e->getMessage() . "]";
    }

    echo json_encode(['success' => true, 'message' => "Thank you, {$fname}! Your enquiry has been received." . $emailMsg]);
    exit;
}

if ($type === 'tour_enquiry') {
    // ---- TOUR ENQUIRY FORM ----
    $fname  = clean($input['first_name'] ?? '');
    $email  = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $tour_id= (int)($input['tour_id'] ?? 0);
    $tour_title = clean($input['tour_title'] ?? '');
    $tdate  = clean($input['travel_date'] ?? '');
    $adults = max(1, (int)($input['adults'] ?? 2));
    $children = max(0, (int)($input['children'] ?? 0));
    $msg    = clean($input['message'] ?? '');

    if (!$fname || !$email) {
        echo json_encode(['success' => false, 'message' => 'Please fill in your name and email.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enquiries 
            (type, first_name, email, tour_id, tour_title, travel_month, travel_year, adults, children, message)
            VALUES ('tour_enquiry', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fname, $email, $tour_id, $tour_title, $tdate, null, $adults, $children, $msg]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error (tour_enquiry): ' . $e->getMessage()]);
        exit;
    }

    // Send Emails
    $emailMsg = '';
    try {
        $adminBody = "<h3>New Tour Enquiry</h3>
                      <p><strong>Name:</strong> $fname</p>
                      <p><strong>Email:</strong> $email</p>
                      <p><strong>Tour:</strong> $tour_title</p>
                      <p><strong>When:</strong> $tdate</p>
                      <p><strong>Guests:</strong> $adults Adults, $children Children</p>
                      <p><strong>Message:</strong><br/>$msg</p>";
        sendSiteEmail($adminEmail, "Filao Admin", "Tour Enquiry: $tour_title", $adminBody);

        $userBody = "<h3>We have received your tour enquiry!</h3>
                     <p>Hi $fname,</p>
                     <p>Thank you for enquiring about <strong>$tour_title</strong>. Our safari specialists will reach out to you shortly.</p>
                     <p>Best Regards,<br>Filao Adventures Team</p>";
        sendSiteEmail($email, $fname, "We have received your tour enquiry", $userBody);
    } catch (\Exception $e) {
        $emailMsg = " [MAIL DEBUG: " . $e->getMessage() . "]";
    }

    echo json_encode(['success' => true, 'message' => "Thank you, {$fname}! Your enquiry has been received." . $emailMsg]);
    exit;
}

if ($type === 'start_planning') {
    // ---- START PLANNING STEPPER ----
    $fname      = clean($input['first_name'] ?? '');
    $lname      = clean($input['last_name'] ?? '');
    $email      = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone      = clean($input['phone'] ?? '');
    $dest       = clean($input['destination'] ?? '');
    $tour_id    = (int)($input['tour_id'] ?? 0) ?: null;
    $tour_title = clean($input['tour_title'] ?? '');
    $activities = clean(is_array($input['activities'] ?? '') ? implode(', ', $input['activities']) : ($input['activities'] ?? ''));
    $purpose    = clean($input['custom_purpose'] ?? '');
    $travel_month = clean($input['travel_month'] ?? '');
    $travel_year  = (int)($input['travel_year'] ?? 0) ?: null;
    $duration   = clean($input['duration'] ?? '');
    $adults     = max(1, (int)($input['adults'] ?? 2));
    $children   = max(0, (int)($input['children'] ?? 0));
    $budget     = (int)($input['budget'] ?? 0) ?: null;
    $travelled  = clean($input['travelled_before'] ?? '');
    $referred   = clean($input['referred'] ?? '');
    $msg        = clean($input['message'] ?? '');

    if (!$fname || !$email) {
        echo json_encode(['success' => false, 'message' => 'Please fill in your name and email.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    try {
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
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error (start_planning): ' . $e->getMessage()]);
        exit;
    }

    if (!empty($input['newsletter_optin'])) {
        try {
            $stmtNL = $pdo->prepare('SELECT id FROM newsletters WHERE email = ?');
            $stmtNL->execute([$email]);
            if (!$stmtNL->fetch()) {
                $stmtNL2 = $pdo->prepare('INSERT INTO newsletters (email, created_at) VALUES (?, NOW())');
                $stmtNL2->execute([$email]);
            }
        } catch (Exception $e) {}
    }

    $exact_date = clean($input['exact_travel_date'] ?? '');
    
    // ... skipping some validation lines ...

    // Send Emails
    $emailMsg = '';
    try {
        $whenStr = $exact_date ? $exact_date : "$travel_month $travel_year for $duration";
        $adminBody = "<h3>New Trip Planning Request</h3>
                      <p><strong>Name:</strong> $fname $lname</p>
                      <p><strong>Email:</strong> $email</p>
                      <p><strong>Phone:</strong> $phone</p>
                      <p><strong>Destination:</strong> $dest</p>
                      <p><strong>Tour:</strong> $tour_title</p>
                      <p><strong>When:</strong> $whenStr</p>
                      <p><strong>Guests:</strong> $adults Adults, $children Children</p>
                      <p><strong>Budget:</strong> \$$budget</p>
                      <p><strong>Activities:</strong> $activities</p>
                      <p><strong>Message:</strong><br/>$msg</p>";
        sendSiteEmail($adminEmail, "Filao Admin", "New Trip Planning Request from $fname $lname", $adminBody);

        $userBody = "<h3>We're crafting your perfect journey!</h3>
                     <p>Hi $fname,</p>
                     <p>Thank you for submitting your trip planning request. Our safari specialists will review your details and reach out to you within 24 hours.</p>
                     <p>Best Regards,<br>Filao Adventures Team</p>";
        sendSiteEmail($email, "$fname $lname", "We've received your safari plan", $userBody);
    } catch (\Exception $e) {
        $emailMsg = " [MAIL DEBUG: " . $e->getMessage() . "]";
    }

    echo json_encode(['success' => true, 'message' => "Thank you, {$fname}! We've received your safari plan. Our specialist will reach out to you within 24 hours." . $emailMsg]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown form type.']);
