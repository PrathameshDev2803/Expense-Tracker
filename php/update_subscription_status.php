<?php
// php/update_subscription_status.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$sub_id = (int)$input['id'];
$status = $input['status'];

// Validate status
$valid_statuses = ['active', 'canceled', 'ignored'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

// Update
$stmt = $conn->prepare("UPDATE subscriptions SET status = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $status, $sub_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Subscription marked as $status"]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
$stmt->close();
?>
