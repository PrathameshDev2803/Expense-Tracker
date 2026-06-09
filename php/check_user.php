<?php
require_once 'db.php';
$res = $conn->query("SELECT * FROM users LIMIT 1");
if ($row = $res->fetch_assoc()) {
    echo "Found User ID: " . $row['id'] . "\n";
} else {
    // Create dummy user
    $conn->query("INSERT INTO users (username, email, password) VALUES ('TestUser', 'test@example.com', '123456')");
    echo "Created User ID: " . $conn->insert_id . "\n";
}
?>
