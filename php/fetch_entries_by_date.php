<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if (isset($_GET['date'])) {
    $selectedDate = trim($_GET['date']);
    
    // Validate date format YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid date format. Expected YYYY-MM-DD.']);
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT id, type, category, amount, description, created_at FROM transactions WHERE user_id = ? AND DATE(created_at) = ? ORDER BY created_at ASC");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("is", $user_id, $selectedDate);
        $stmt->execute();

        $result = $stmt->get_result();
        $entries = [];

        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }

        echo json_encode(['success' => true, 'entries' => $entries]);
        $stmt->close();
    } catch (Throwable $e) {
        error_log("Error in fetch_entries_by_date.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'An internal error occurred while fetching transactions.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing date parameter']);
}

$conn->close();
?>