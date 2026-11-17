<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db.php';
include_once 'auth_utils.php';

// Safe server-side diagnostic logging (never logs passwords, hashes, session IDs, or sensitive credentials)
function logLoginDiagnostic(string $category, string $details = ''): void {
    $endpoint = $_SERVER['SCRIPT_NAME'] ?? '/php/login_process.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $msg = sprintf(
        "[Login Diagnostic] Endpoint: %s | Method: %s | IP: %s | Category: %s | Details: %s",
        $endpoint,
        $method,
        $ip,
        $category,
        $details
    );
    error_log($msg);
}

// Detect if caller expects a JSON response
$isJson = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
          (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
          (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

// Unified response sender supporting both JSON APIs and Post/Redirect/Get pattern
function sendAuthResponse(bool $success, string $message, string $redirect = '', string $field = 'general', int $statusCode = 200, string $email = ''): void {
    global $isJson;

    if ($isJson) {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        $clientRedirect = ltrim(preg_replace('#^(\.\./)+#', '', $redirect), '/');
        if ($clientRedirect === '') {
            $clientRedirect = 'main.php';
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'field' => $field,
            'redirect' => $clientRedirect
        ]);
        exit;
    }

    // Standard Form Post/Redirect/Get pattern
    if ($success) {
        unset($_SESSION['login_error'], $_SESSION['login_old_email'], $_SESSION['login_field']);
        $target = ($redirect !== '') ? $redirect : '../main.php';
        header("Location: " . $target);
        exit;
    } else {
        $_SESSION['login_error'] = $message;
        $_SESSION['login_field'] = $field;
        if ($email !== '') {
            $_SESSION['login_old_email'] = $email;
        }
        header("Location: ../login.php");
        exit;
    }
}

// 1. Enforce POST Request Method (reject unexpected HTTP methods)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    logLoginDiagnostic('Method Not Allowed', 'Expected POST, received ' . ($_SERVER['REQUEST_METHOD'] ?? 'NONE'));
    sendAuthResponse(false, 'Method not allowed. POST request required.', '', 'general', 405);
}

// 2. Validate Database Connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    logLoginDiagnostic('Database Unavailable', 'DB connection object is not initialized or invalid');
    sendAuthResponse(false, 'Database service unavailable. Please try again later.', '', 'general', 500);
}

$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// 3. Rate Limiting Protection (5 failed attempts per 15 minutes per IP)
if (!AuthUtils::checkRateLimit($conn, $ip_address, 'login_fail', 5, 15)) {
    logLoginDiagnostic('Rate Limit Exceeded', "IP {$ip_address} reached login failure limit");
    sendAuthResponse(false, 'Too many failed login attempts. Please try again in 15 minutes.', '', 'general', 429);
}

// 4. Safe Input Reading & Normalization
$rawEmail = isset($_POST['email']) ? (string)$_POST['email'] : '';
$rawPassword = isset($_POST['password']) ? (string)$_POST['password'] : '';

// Normalize email (lowercase and trimmed whitespace) consistent with registration
$email = strtolower(trim($rawEmail));

// Do NOT trim or alter password, as whitespace may legitimately be part of a password
$password = $rawPassword;

// 5. Input Validation
if ($email === '') {
    sendAuthResponse(false, 'Please enter your email address.', '', 'email', 400, $email);
}

if (strlen($email) > 254) {
    sendAuthResponse(false, 'Email address exceeds maximum length.', '', 'email', 400, substr($email, 0, 254));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendAuthResponse(false, 'Please enter a valid email address.', '', 'email', 400, $email);
}

if ($password === '') {
    sendAuthResponse(false, 'Please enter your password.', '', 'password', 400, $email);
}

if (strlen($password) > 4096) {
    sendAuthResponse(false, 'Password exceeds maximum allowed length.', '', 'password', 400, $email);
}

// 6. Prepared SQL Query Execution
$stmt = $conn->prepare("SELECT id, username, password, currency, theme FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    logLoginDiagnostic('SQL Prepare Error', $conn->error);
    sendAuthResponse(false, 'A system error occurred. Please try again.', '', 'general', 500, $email);
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    logLoginDiagnostic('SQL Execute Error', $stmt->error);
    $stmt->close();
    sendAuthResponse(false, 'A system error occurred. Please try again.', '', 'general', 500, $email);
}

$result = $stmt->get_result();
$user = null;
$authSuccess = false;

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // 7. Secure Password Verification (password_verify with BCrypt)
    if (password_verify($password, $user["password"])) {
        $authSuccess = true;
    }
}
$stmt->close();

// 8. Handle Authentication Outcome
if (!$authSuccess) {
    // Log failed attempt for rate limiting
    AuthUtils::logAttempt($conn, $ip_address, 'login_fail');
    logLoginDiagnostic('Authentication Failed', "Invalid credentials for email: {$email}");

    // Generic error message: never reveal whether the email exists
    sendAuthResponse(false, 'Invalid email or password. Please try again.', '', 'general', 401, $email);
}

// 9. Successful Login Handling
// Reset rate limiting attempts on successful login
AuthUtils::clearAttempts($conn, $ip_address, 'login_fail');

// Regenerate session ID to prevent session fixation attacks
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["username"] = $user["username"];
$userCurrency = !empty($user["currency"]) ? $user["currency"] : 'INR';
$_SESSION["currency"] = $userCurrency;
$_SESSION["theme"] = !empty($user["theme"]) ? $user["theme"] : 'light';
require_once __DIR__ . '/auth_check.php';
$_SESSION["currency_symbol"] = get_currency_symbol($userCurrency);
$_SESSION['preferences_loaded'] = true;

// 10. Handle Remember Me
if (!empty($_POST['remember'])) {
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $uid = (int)$user['id'];

    $upd_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    if ($upd_stmt) {
        $upd_stmt->bind_param("si", $token_hash, $uid);
        $upd_stmt->execute();
        $upd_stmt->close();
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie("remember_me", $token, [
        'expires' => time() + (30 * 86400),
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    setcookie("user_email", $email, [
        'expires' => time() + (30 * 86400),
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
} else {
    setcookie("remember_me", "", time() - 3600, "/");
    setcookie("user_email", "", time() - 3600, "/");

    $uid = (int)$user['id'];
    $clr_stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    if ($clr_stmt) {
        $clr_stmt->bind_param("i", $uid);
        $clr_stmt->execute();
        $clr_stmt->close();
    }
}

// Clear legacy insecure cookies if present
setcookie("user_pass", "", time() - 3600, "/");
setcookie("user_password", "", time() - 3600, "/");

// Redirect to dashboard or send JSON success
sendAuthResponse(true, 'Login successful.', '../main.php', 'general', 200);
