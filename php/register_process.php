<?php
session_start();
include 'db.php';
include 'auth_utils.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');

    // 1. Rate Limiting
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!AuthUtils::checkRateLimit($conn, $ip_address, 'register', 5, 60)) { 
        http_response_code(429);
        echo json_encode(["message" => "Too many registration attempts. Please try again later."]);
        exit;
    }

    // 2. Input Sanitize
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 3. Basic Validation
    if (empty($username) || empty($email) || empty($password)) {
         http_response_code(400);
         echo json_encode(["field" => "general", "message" => "All fields are required."]);
         exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["field" => "email", "message" => "Invalid email format."]);
        exit;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(["field" => "confirm_password", "message" => "Passwords do not match."]);
        exit;
    }

    // 4. Secure Password Validation
    $pwd_check = AuthUtils::validatePassword($password, $username, $email);
    if ($pwd_check !== true) {
        AuthUtils::logAttempt($conn, $ip_address, 'register_fail_pwd'); 
        http_response_code(400);
        echo json_encode(["field" => "password", "message" => $pwd_check]);
        exit;
    }

    // 5. Check if email exists (Double check for safety)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        AuthUtils::logAttempt($conn, $ip_address, 'register_fail_exists');
        $stmt->close();
        http_response_code(409);
        echo json_encode(["field" => "email", "message" => "Account already exists with this email."]);
        exit;
    }
    $stmt->close();
    
    // Check username as well just in case
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->fetch()) {
        $stmt->close();
        http_response_code(409);
        echo json_encode(["field" => "username", "message" => "Username already taken."]);
        exit;
    }
    $stmt->close();

    // 6. Hash Password
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
    $hashed_password = password_hash($password, $algo);

    // 7. Insert User
    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($insert_stmt) {
        $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($insert_stmt->execute()) {
            // Success
            $_SESSION['user_id'] = $insert_stmt->insert_id;
            $_SESSION['username'] = $username;


            
            // Clear any old flash or input (ignoring session trash)
            if(isset($_SESSION['flash_error'])) unset($_SESSION['flash_error']);
            if(isset($_SESSION['old_input'])) unset($_SESSION['old_input']);
            
            echo json_encode(["success" => true, "redirect" => "../menu.php"]);
        } else {
             error_log("Registration DB Error: " . $insert_stmt->error);
             http_response_code(500);
             echo json_encode(["message" => "An internal error occurred. Please try again."]);
        }
        $insert_stmt->close();
    } else {
        error_log("Prepare Error: " . $conn->error);
        http_response_code(500);
        echo json_encode(["message" => "System error. Please try again later."]);
    }
}
?>
