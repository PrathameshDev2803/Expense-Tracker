<?php
define('API_MODE', true);
require 'auth_check.php';
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = $_POST['form_id'] ?? '';

    if ($form_id === 'profileForm') {
        $username = trim($_POST['username'] ?? '');
        if (empty($username)) {
            echo json_encode(['success' => false, 'error' => 'Name cannot be empty']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $user_id);
        if ($stmt->execute()) {
            $_SESSION['username'] = $username; // Update session
            $response = ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            $response = ['success' => false, 'error' => 'Database error'];
        }
    } 
    elseif ($form_id === 'preferencesForm') {
        $currency = $_POST['currency'] ?? 'INR';
        // Validate currency
        if (!in_array($currency, ['INR', 'USD', 'EUR', 'GBP'])) {
             echo json_encode(['success' => false, 'error' => 'Invalid currency']);
             exit;
        }
        $stmt = $conn->prepare("UPDATE users SET currency = ? WHERE id = ?");
        $stmt->bind_param("si", $currency, $user_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Preferences saved'];
        } else {
            $response = ['success' => false, 'error' => 'Database error'];
        }
    }
    elseif ($form_id === 'appearanceForm') {
        $theme = $_POST['theme'] ?? 'light';
        // Validate theme
        if (!in_array($theme, ['light', 'dark'])) {
             echo json_encode(['success' => false, 'error' => 'Invalid theme']);
             exit;
        }
        $stmt = $conn->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->bind_param("si", $theme, $user_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Theme updated'];
        }
    }
    elseif ($form_id === 'notificationsForm') {
        $prefs = $_POST['prefs'] ?? '{}';
        // Validate JSON
        json_decode($prefs);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE users SET notification_prefs = ? WHERE id = ?");
        $stmt->bind_param("si", $prefs, $user_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Notification preferences saved'];
        }
    }
    elseif ($form_id === 'securityForm') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        
        if (empty($current) || empty($new)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            exit;
        }
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($current, $user['password'])) {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hash, $user_id);
            
            if ($update->execute()) {
                $response = ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                $response = ['success' => false, 'error' => 'Update failed'];
            }
        } else {
            $response = ['success' => false, 'error' => 'Incorrect current password'];
        }
    }
    else {
        $response = ['success' => false, 'error' => 'Invalid form ID'];
    }
}

echo json_encode($response);
?>
