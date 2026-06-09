<?php
session_start();
include 'db.php';
include 'auth_utils.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // 1. Rate Limiting
    if (!AuthUtils::checkRateLimit($conn, $ip_address, 'login', 5, 15)) { // 5 attempts per 15 mins
        // Generic error to avoid revealing it's a rate limit block if we want to be sneaky,
        // but for usability, telling them to wait is usually better.
        // However, tight security might just say "Login failed".
        // Let's be helpful but firm.
        die("Too many login attempts. Please try again in 15 minutes.");
    }

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    // 2. Secure Query (Prepared Statement)
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $login_successful = false;
        $user = null;

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // 3. Secure Hash Verification
            if (password_verify($password, $user["password"])) {
                $login_successful = true;
            }
        }

        if ($login_successful) {
            // Success
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            // Handle Remember Me
            if (!empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $uid = $user['id'];
                
                // Store hashed token
                $upd_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $upd_stmt->bind_param("si", $token_hash, $uid);
                $upd_stmt->execute();
                $upd_stmt->close();

                // Set Cookies (HttpOnly checks are good, but basic setcookie is here)
                setcookie("remember_me", $token, time() + (30 * 86400), "/", "", false, true); // HttpOnly
                setcookie("user_email", $email, time() + (30 * 86400), "/"); // Convenience, non-sensitive
            } else {
                setcookie("remember_me", "", time() - 3600, "/");
                setcookie("user_email", "", time() - 3600, "/");
                
                // Clear token from DB
                $uid = $user['id'];
                $clr_stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                $clr_stmt->bind_param("i", $uid);
                $clr_stmt->execute();
                $clr_stmt->close();
            }
            
            // Clear any old insecure cookies
            setcookie("user_pass", "", time() - 3600, "/");
            setcookie("user_password", "", time() - 3600, "/");

            header("Location: ../main.php");
            exit();

        } else {
            // Failed
            AuthUtils::logAttempt($conn, $ip_address, 'login_fail');
            // Generic Error
            echo "<script>alert('Invalid email or password.'); window.history.back();</script>";
            exit();
        }
        $stmt->close();
    } else {
        error_log("Login Prepare Error: " . $conn->error);
        echo "<script>alert('System error. Please try again.'); window.history.back();</script>";
    }
} else {
    echo "⚠️ Invalid request.";
}

$conn->close();

