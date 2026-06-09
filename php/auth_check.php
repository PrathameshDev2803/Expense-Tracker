<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function attempt_cookie_login() {
    // Only attempt if we have a cookie and no session
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        
        // We need database connection. 
        // This file is in /php/, so db.php is in the same directory.
        include_once __DIR__ . '/db.php';
        global $conn;

        $token = $_COOKIE['remember_me'];
        $token_hash = hash('sha256', $token);
        
        $stmt = $conn->prepare("SELECT id, username, currency, theme FROM users WHERE remember_token = ?");
        if ($stmt) {
            $stmt->bind_param("s", $token_hash);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
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
                $update->bind_param("si", $new_token_hash, $user['id']);
                $update->execute();
                
                // Update cookie (30 days)
                setcookie("remember_me", $new_token, time() + (30 * 24 * 60 * 60), "/");
                
                return true;
            }
        }
    }
    return false;
}

function get_currency_symbol($code) {
    $code = strtoupper($code);
    $symbols = ['INR'=>'₹', 'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£'];
    return $symbols[$code] ?? '₹';
}

// Perform the check
if (!isset($_SESSION['user_id'])) {
    attempt_cookie_login();
}

// Ensure preferences are loaded if user is logged in
if (isset($_SESSION['user_id']) && (!isset($_SESSION['currency']) || !isset($_SESSION['theme']))) {
    include_once __DIR__ . '/db.php';
    if(isset($conn)) {
        $stmt = $conn->prepare("SELECT currency, theme FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $_SESSION['currency'] = $row['currency'] ?? 'INR';
            $_SESSION['theme'] = $row['theme'] ?? 'light';
        }
        $stmt->close();
    }
}

// Always ensure symbol is set if currency is set
if (isset($_SESSION['currency']) && !isset($_SESSION['currency_symbol'])) {
    $_SESSION['currency_symbol'] = get_currency_symbol($_SESSION['currency']);
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
