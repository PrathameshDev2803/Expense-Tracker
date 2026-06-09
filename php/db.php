<?php
// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$conn = new mysqli(
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? 'expensetracker',
    $_ENV['DB_PORT'] ?? '3306'
);

if ($conn->connect_error) {
    error_log('DB Connection failed: ' . $conn->connect_error);
    die('A system error occurred. Please try again later.');
}
?>