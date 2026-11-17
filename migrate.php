<?php
/**
 * migrate.php
 * 
 * Standalone Database Migration Script for Expense Tracker.
 * Safe and idempotent: Uses `CREATE TABLE IF NOT EXISTS` so it can be executed
 * safely on local (development) or live hosting (e.g., InfinityFree).
 * 
 * Can be run via:
 *   1. Browser: https://yourdomain.com/migrate.php
 *   2. Terminal / CLI: php migrate.php
 */

// Load centralized database configuration
require_once __DIR__ . '/php/db.php';

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
}

// Helper to format output
function renderMessage(string $type, string $title, string $details = ''): void {
    global $isCli;
    if ($isCli) {
        $prefix = match ($type) {
            'success' => '[OK] ',
            'error'   => '[ERROR] ',
            'info'    => '[INFO] ',
            default   => '[-] ',
        };
        echo $prefix . $title . ($details ? " - $details" : "") . PHP_EOL;
    } else {
        $color = match ($type) {
            'success' => '#10b981',
            'error'   => '#ef4444',
            'info'    => '#3b82f6',
            default   => '#6b7280',
        };
        echo "<div style='margin-bottom: 8px; padding: 10px 14px; background: #ffffff; border-left: 4px solid $color; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);'>";
        echo "<strong style='color: $color;'>$title</strong>";
        if ($details) {
            echo "<span style='color: #4b5563; margin-left: 8px; font-size: 0.95em;'>$details</span>";
        }
        echo "</div>";
    }
}

// Start HTML output if viewed in browser
if (!$isCli) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Expense Tracker</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; padding: 30px 20px; line-height: 1.5; }
        .container { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 24px 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h1 { font-size: 1.5rem; margin-top: 0; margin-bottom: 4px; color: #1f2937; }
        .meta { font-size: 0.875rem; color: #6b7280; margin-bottom: 20px; }
        .meta strong { color: #374151; }
        .summary-box { margin-top: 24px; padding: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; color: #166534; font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>Database Migration Runner</h1>
    <div class="meta">
        Environment: <strong>' . htmlspecialchars($appEnv ?? 'unknown') . '</strong> | 
        Database: <strong>' . htmlspecialchars($dbName ?? 'unknown') . '</strong>
    </div>';
}

if ($isCli) {
    echo "========================================================\n";
    echo " EXPENSE TRACKER - DATABASE MIGRATION RUNNER\n";
    echo "========================================================\n";
    echo "Environment : " . ($appEnv ?? 'unknown') . "\n";
    echo "Database    : " . ($dbName ?? 'unknown') . "\n";
    echo "Host        : " . ($dbHost ?? 'unknown') . "\n";
    echo "--------------------------------------------------------\n";
}

// Migration queries designed for compatibility across all hosting environments
$migrations = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        currency VARCHAR(10) DEFAULT 'INR',
        theme VARCHAR(20) DEFAULT 'light',
        remember_token VARCHAR(255) NULL,
        notification_prefs TEXT NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    'income_genres' => "CREATE TABLE IF NOT EXISTS income_genres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category_name VARCHAR(100) NOT NULL,
        INDEX idx_income_user (user_id)
    )",

    'expense_genres' => "CREATE TABLE IF NOT EXISTS expense_genres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category_name VARCHAR(100) NOT NULL,
        INDEX idx_expense_user (user_id)
    )",

    'transactions' => "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('income', 'expense') NOT NULL,
        category VARCHAR(100) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        description VARCHAR(255),
        created_at DATETIME NOT NULL,
        INDEX idx_tx_user (user_id)
    )",

    'goals' => "CREATE TABLE IF NOT EXISTS goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        goal_type ENUM('saving', 'no_spend', 'budget') NOT NULL,
        target_amount DECIMAL(15, 2) NULL,
        target_days INT NULL,
        current_amount DECIMAL(15, 2) DEFAULT 0.00,
        completed_days INT DEFAULT 0,
        start_date DATE NOT NULL,
        end_date DATE NULL,
        status ENUM('active', 'completed', 'failed') DEFAULT 'active',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_goals_user (user_id)
    )",

    'savings_goals' => "CREATE TABLE IF NOT EXISTS savings_goals (
        goal_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        goal_name VARCHAR(255) NOT NULL,
        target_amount DECIMAL(15, 2) NOT NULL,
        saved_amount DECIMAL(15, 2) DEFAULT 0.00,
        deadline DATE NULL,
        icon VARCHAR(50) NULL,
        status ENUM('active', 'completed', 'archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_savings_user (user_id)
    )",

    'subscriptions' => "CREATE TABLE IF NOT EXISTS subscriptions (
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
    )",

    'subscription_transactions' => "CREATE TABLE IF NOT EXISTS subscription_transactions (
        subscription_id INT NOT NULL,
        transaction_id INT NOT NULL,
        PRIMARY KEY (subscription_id, transaction_id),
        INDEX idx_sub_tx_sub (subscription_id),
        INDEX idx_sub_tx_tx (transaction_id)
    )",

    'attempt_log' => "CREATE TABLE IF NOT EXISTS attempt_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_action_time (ip_address, action_type, attempt_time)
    )",

    'currency_rates' => "CREATE TABLE IF NOT EXISTS currency_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        base_currency VARCHAR(10) NOT NULL,
        target_currency VARCHAR(10) NOT NULL,
        rate DECIMAL(18, 8) NOT NULL,
        last_updated DATETIME NOT NULL,
        source VARCHAR(50) DEFAULT 'open.er-api.com',
        UNIQUE KEY uq_pair (base_currency, target_currency)
    )",
];

$successCount = 0;
$errorCount = 0;

foreach ($migrations as $tableName => $sql) {
    try {
        if ($conn->query($sql) === true) {
            renderMessage('success', "Table `$tableName`", 'Verified / Created successfully');
            $successCount++;
        } else {
            renderMessage('error', "Table `$tableName`", $conn->error);
            $errorCount++;
        }
    } catch (Throwable $e) {
        renderMessage('error', "Table `$tableName`", $e->getMessage());
        $errorCount++;
    }
}

// Attempt optional foreign key constraints if supported by the host's table engines
$foreignKeys = [
    "ALTER TABLE income_genres ADD CONSTRAINT fk_income_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE expense_genres ADD CONSTRAINT fk_expense_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE transactions ADD CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE goals ADD CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE subscriptions ADD CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE subscription_transactions ADD CONSTRAINT fk_sub_trans_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE",
    "ALTER TABLE subscription_transactions ADD CONSTRAINT fk_sub_trans_trans FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE",
];

// Ensure existing users table has all required columns
$userColumns = [
    'currency'           => "ALTER TABLE users ADD COLUMN currency VARCHAR(10) DEFAULT 'INR'",
    'theme'              => "ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'light'",
    'remember_token'     => "ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL",
    'notification_prefs' => "ALTER TABLE users ADD COLUMN notification_prefs TEXT NULL DEFAULT NULL",
];

foreach ($userColumns as $colName => $alterSql) {
    try {
        $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE '$colName'");
        if ($colCheck && $colCheck->num_rows === 0) {
            if ($conn->query($alterSql)) {
                renderMessage('success', "Column `users.$colName`", 'Added column successfully');
            }
        }
    } catch (Throwable $ignored) {}
}

foreach ($foreignKeys as $fkSql) {
    try {
        @$conn->query($fkSql);
    } catch (Throwable $ignored) {
        // Silently skip if already existing or if hosting engine does not allow FK
    }
}

// Summary message
if ($errorCount === 0) {
    $msg = "All $successCount tables verified successfully! Database is ready.";
    if ($isCli) {
        echo "--------------------------------------------------------\n";
        echo "[SUCCESS] $msg\n";
        echo "========================================================\n";
    } else {
        echo "<div class='summary-box'><strong>Success:</strong> $msg<br><small style='color:#15803d;'>You may safely keep or delete migrate.php after verification.</small></div>";
    }
} else {
    $msg = "$successCount succeeded, $errorCount failed. Check the errors above.";
    if ($isCli) {
        echo "--------------------------------------------------------\n";
        echo "[WARNING] $msg\n";
        echo "========================================================\n";
    } else {
        echo "<div class='summary-box' style='background:#fef2f2; border-color:#fecaca; color:#991b1b;'><strong>Warning:</strong> $msg</div>";
    }
}

if (!$isCli) {
    echo '</div></body></html>';
}
