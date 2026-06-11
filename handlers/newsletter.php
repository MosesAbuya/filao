<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM newsletters WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter!']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO newsletters (email, created_at) VALUES (?, NOW())');
    $stmt->execute([$email]);
    
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to our newsletter!']);
} catch (Exception $e) {
    error_log("Newsletter error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
