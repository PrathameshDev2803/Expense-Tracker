<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load asset & cache management helper
require_once __DIR__ . '/asset_helper.php';

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($code) {
        $code = strtoupper((string)$code);
        $symbols = ['INR'=>'₹', 'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£'];
        return $symbols[$code] ?? '₹';
    }
}

if (!function_exists('attempt_cookie_login')) {
    function attempt_cookie_login() {
        // Only attempt if we have a cookie and no session
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
            try {
                // We need database connection. 
                // This file is in /php/, so db.php is in the same directory.
                include_once __DIR__ . '/db.php';
                global $conn;

                if (!isset($conn) || !($conn instanceof mysqli)) {
                    return false;
                }

                $token = $_COOKIE['remember_me'];
                $token_hash = hash('sha256', $token);
                
                $stmt = $conn->prepare("SELECT id, username, currency, theme FROM users WHERE remember_token = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $token_hash);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Set session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['currency'] = $user['currency'] ?? 'INR';
                        $_SESSION['theme'] = $user['theme'] ?? 'light';
                        $_SESSION['currency_symbol'] = get_currency_symbol($_SESSION['currency']);
                        
                        // Rotate token
                        $new_token = bin2hex(random_bytes(32));
                        $new_token_hash = hash('sha256', $new_token);
                        
                        $update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        if ($update) {
                            $update->bind_param("si", $new_token_hash, $user['id']);
                            $update->execute();
                            $update->close();
                        }
                        
                        // Update cookie (30 days)
                        setcookie("remember_me", $new_token, time() + (30 * 24 * 60 * 60), "/");
                        
                        $stmt->close();
                        return true;
                    }
                    $stmt->close();
                }
            } catch (\Throwable $e) {
                error_log("[auth_check] Cookie login error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}

// Perform the check
if (!isset($_SESSION['user_id'])) {
    attempt_cookie_login();
}

// Ensure default preferences exist in session
if (isset($_SESSION['user_id'])) {
    if (!isset($_SESSION['currency'])) {
        $_SESSION['currency'] = 'INR';
    }
    if (!isset($_SESSION['theme'])) {
        $_SESSION['theme'] = 'light';
    }
    if (!isset($_SESSION['currency_symbol'])) {
        $_SESSION['currency_symbol'] = get_currency_symbol($_SESSION['currency']);
    }

    // Attempt to load saved preferences from database if needed, but never crash if query fails
    if (!isset($_SESSION['preferences_loaded'])) {
        try {
            include_once __DIR__ . '/db.php';
            if (isset($conn) && $conn instanceof mysqli) {
                $stmt = $conn->prepare("SELECT currency, theme FROM users WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $res->num_rows === 1) {
                        $row = $res->fetch_assoc();
                        if (!empty($row['currency'])) {
                            $_SESSION['currency'] = $row['currency'];
                            $_SESSION['currency_symbol'] = get_currency_symbol($row['currency']);
                        }
                        if (!empty($row['theme'])) {
                            $_SESSION['theme'] = $row['theme'];
                        }
                    }
                    $stmt->close();
                }
            }
            $_SESSION['preferences_loaded'] = true;
        } catch (\Throwable $e) {
            error_log("[auth_check] Non-fatal error loading user preferences: " . $e->getMessage());
            $_SESSION['preferences_loaded'] = true;
        }
    }
}

// Final check: If still not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    if (defined('API_MODE') && API_MODE === true) {
        // Do nothing, let the API handle the unauthorized response
    } else {
        header("Location: login.php");
        exit();
    }
}
?>
