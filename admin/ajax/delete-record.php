<?php
require_once __DIR__ . '/../auth_guard.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!csrfVerify($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Security token invalid or expired.']);
    exit;
}

$allowedTables = ['tours', 'destinations', 'accommodations', 'taxonomies', 'enquiries', 'newsletters', 'regions', 'countries', 'activities', 'blogs', 'head_tags', 'partner_agents'];
$table = $_POST['table'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid table.']);
    exit;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlash('success', 'Record deleted successfully.');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000 || strpos($e->getMessage(), '1451') !== false) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete this record because it is currently linked to one or more tours. Please remove it from all tour itineraries first.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
