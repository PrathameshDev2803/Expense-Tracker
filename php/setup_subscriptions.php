<?php
// php/setup_subscriptions.php
include_once __DIR__ . '/db.php';

$sqlSubscriptions = "CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    merchant_name VARCHAR(255) NOT NULL,
    average_amount DECIMAL(10, 2) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    first_detected_date DATETIME NOT NULL,
    last_charged_date DATETIME NOT NULL,
    confidence_score INT DEFAULT 50,
    status ENUM('active', 'canceled', 'ignored') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$sqlTransactions = "CREATE TABLE IF NOT EXISTS subscription_transactions (
    subscription_id INT NOT NULL,
    transaction_id INT NOT NULL,
    PRIMARY KEY (subscription_id, transaction_id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
)";

if (!$conn->query($sqlSubscriptions)) {
    error_log("Error creating subscriptions table: " . $conn->error);
}
if (!$conn->query($sqlTransactions)) {
    error_log("Error creating subscription_transactions table: " . $conn->error);
}
