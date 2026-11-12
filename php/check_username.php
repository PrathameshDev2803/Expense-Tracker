<?php
ob_start();
include_once 'db.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "available" => false,
        "message" => "Method not allowed. GET required."
    ]);
    exit;
}

$username = isset($_GET['username']) ? trim((string)$_GET['username']) : '';

if ($username === '') {
    http_response_code(400);
    echo json_encode([
        "available" => false, 
        "field" => "username", 
        "message" => "Username is required"
    ]);
    exit;
}

if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    http_response_code(400);
    echo json_encode([
        "available" => false,
        "field" => "username",
        "message" => "Username must be 3-30 characters (letters, numbers, underscores only)."
    ]);
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("[check_username] Database connection unavailable.");
    http_response_code(500);
    echo json_encode([
        "available" => false,
        "message" => "Database connection error. Please try again later."
    ]);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    error_log("[check_username] Database prepare error: " . $conn->error);
    http_response_code(500);
    echo json_encode([
        "available" => false,
        "message" => "Database query failed. Please try again later."
    ]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "available" => false, 
        "field" => "username", 
        "message" => "Username already taken"
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        "available" => true,
        "message" => "Username is available"
    ]);
}

$stmt->close();
?>