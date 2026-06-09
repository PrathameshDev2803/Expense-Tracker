<?php
include_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS savings_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    saved_amount DECIMAL(15,2) DEFAULT 0.00,
    deadline DATE NULL,
    icon VARCHAR(50) NULL,
    status ENUM('active', 'completed', 'archived') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully or already exists
} else {
    error_log("Error creating table: " . $conn->error);
}
?>
