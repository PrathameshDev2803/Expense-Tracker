<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'auth_utils.php';

// Safe server-side diagnostic logging (never logs passwords, hashes, session IDs, or credentials)
function logRegistrationFailure(string $category, string $details = ''): void {
    $endpoint = $_SERVER['SCRIPT_NAME'] ?? '/php/register_process.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $msg = sprintf(
        "[Registration Diagnostic] Endpoint: %s | Method: %s | IP: %s | Category: %s | Details: %s",
        $endpoint,
        $method,
        $ip,
        $category,
        $details
    );
    error_log($msg);
}

// Ensure no stray whitespace or warning text corrupts JSON output
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 1. Enforce POST Request Method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    logRegistrationFailure('Method Not Allowed', 'Expected POST, received ' . ($_SERVER['REQUEST_METHOD'] ?? 'NONE'));
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed. POST request required."
    ]);
    exit;
}

// 2. Validate Database Connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    logRegistrationFailure('Database Unavailable', 'DB connection object is not initialized or invalid');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database service unavailable. Please try again later."
    ]);
    exit;
}

// 3. Rate Limiting Protection (5 attempts per 60 minutes per IP)
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!AuthUtils::checkRateLimit($conn, $ip_address, 'register', 5, 60)) { 
    logRegistrationFailure('Rate Limit Exceeded', "IP {$ip_address} reached registration rate limit");
    http_response_code(429);
    echo json_encode([
        "success" => false,
        "message" => "Too many registration attempts. Please try again later."
    ]);
    exit;
}

// 4. Safe Input Reading & Sanitization
$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

// 5. Input Validation
if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
    logRegistrationFailure('Validation Error', 'Missing required fields');
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "field" => "general",
        "message" => "All fields are required."
    ]);
    exit;
}

// Username format validation
if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    logRegistrationFailure('Validation Error', 'Username format check failed');
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "field" => "username",
        "message" => "Username must be 3-30 characters (letters, numbers, underscores only)."
    ]);
    exit;
}

// Email format validation
$email = strtolower($email);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logRegistrationFailure('Validation Error', 'Invalid email format');
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "field" => "email",
        "message" => "Invalid email format."
    ]);
    exit;
}

// Confirm password matching
if ($password !== $confirm_password) {
    logRegistrationFailure('Validation Error', 'Passwords do not match');
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "field" => "confirm_password",
        "message" => "Passwords do not match."
    ]);
    exit;
}

// 6. Secure Password Strength Validation
$pwd_check = AuthUtils::validatePassword($password, $username, $email);
if ($pwd_check !== true) {
    AuthUtils::logAttempt($conn, $ip_address, 'register_fail_pwd'); 
    logRegistrationFailure('Validation Error', 'Password strength validation failed');
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "field" => "password",
        "message" => $pwd_check
    ]);
    exit;
}

// 7. Check if Email Already Exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    logRegistrationFailure('Database Error', 'Prepare failed for email uniqueness: ' . $conn->error);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "A database query error occurred. Please try again later."
    ]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    AuthUtils::logAttempt($conn, $ip_address, 'register_fail_exists');
    logRegistrationFailure('Duplicate Conflict', 'Email already registered');
    $stmt->close();
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "field" => "email",
        "message" => "An account with this email already exists."
    ]);
    exit;
}
$stmt->close();

// 8. Check if Username Already Taken
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    logRegistrationFailure('Database Error', 'Prepare failed for username uniqueness: ' . $conn->error);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "A database query error occurred. Please try again later."
    ]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    AuthUtils::logAttempt($conn, $ip_address, 'register_fail_exists');
    logRegistrationFailure('Duplicate Conflict', 'Username already taken');
    $stmt->close();
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "field" => "username",
        "message" => "Username already taken."
    ]);
    exit;
}
$stmt->close();

// 9. Hash Password
$algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$hashed_password = password_hash($password, $algo);

// 10. Insert User Record
$insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
if (!$insert_stmt) {
    logRegistrationFailure('Database Error', 'Prepare failed for user insert: ' . $conn->error);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "System error creating account. Please try again later."
    ]);
    exit;
}

$insert_stmt->bind_param("sss", $username, $email, $hashed_password);

if ($insert_stmt->execute()) {
    $new_user_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // Authenticated session creation
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['username'] = $username;

    // Clear old flash or stale input
    unset($_SESSION['flash_error'], $_SESSION['old_input']);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Account created successfully.",
        "redirect" => "menu.php"
    ]);
    exit;
} else {
    logRegistrationFailure('Database Error', 'User insert execution failed: ' . $insert_stmt->error);
    $insert_stmt->close();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to create account. Please try again."
    ]);
    exit;
}
?>