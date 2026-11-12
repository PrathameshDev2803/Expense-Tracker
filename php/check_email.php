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

$email = isset($_GET['email']) ? trim((string)$_GET['email']) : '';

if ($email === '') {
    http_response_code(400);
    echo json_encode([
        "available" => false, 
        "field" => "email", 
        "message" => "Email is required"
    ]);
    exit;
}

// Normalize email
$email = strtolower($email);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "available" => false,
        "field" => "email",
        "message" => "Invalid email format"
    ]);
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("[check_email] Database connection unavailable.");
    http_response_code(500);
    echo json_encode([
        "available" => false,
        "message" => "Database connection error. Please try again later."
    ]);
    exit;
}

// Prepare statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    error_log("[check_email] Database prepare error: " . $conn->error);
    http_response_code(500);
    echo json_encode([
        "available" => false,
        "message" => "Database query failed. Please try again later."
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "available" => false, 
        "field" => "email", 
        "message" => "This email is already registered"
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        "available" => true,
        "message" => "Email is available"
    ]);
}

$stmt->close();
?>