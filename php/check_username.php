<?php
include 'db.php';
header('Content-Type: application/json');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (!$username) {
    http_response_code(400);
    echo json_encode([
        "available" => false, 
        "field" => "username", 
        "message" => "Username is required"
    ]);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "available" => false, 
        "field" => "username", 
        "message" => "Username already taken"
    ]);
} else {
    echo json_encode([
        "available" => true
    ]);
}

$stmt->close();
?>
