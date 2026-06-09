<?php
include 'db.php';
header('Content-Type: application/json');

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if (!$email) {
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

// Prepare statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "available" => false, 
        "field" => "email", 
        "message" => "This email is already registered"
    ]);
} else {
    echo json_encode([
        "available" => true
    ]);
}

$stmt->close();
?>
