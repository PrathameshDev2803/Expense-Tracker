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
    INDEX idx_sub_user (user_id)
)";

$sqlTransactions = "CREATE TABLE IF NOT EXISTS subscription_transactions (
    subscription_id INT NOT NULL,
    transaction_id INT NOT NULL,
    PRIMARY KEY (subscription_id, transaction_id),
    INDEX idx_sub_tx_sub (subscription_id),
    INDEX idx_sub_tx_tx (transaction_id)
)";

if ($conn->query($sqlSubscriptions)) {
    try {
        @$conn->query("ALTER TABLE subscriptions ADD CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
    } catch (Throwable $e) {}
}

if ($conn->query($sqlTransactions)) {
    try {
        @$conn->query("ALTER TABLE subscription_transactions ADD CONSTRAINT fk_sub_trans_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE");
    } catch (Throwable $e) {}
    try {
        @$conn->query("ALTER TABLE subscription_transactions ADD CONSTRAINT fk_sub_trans_trans FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE");
    } catch (Throwable $e) {}
}
