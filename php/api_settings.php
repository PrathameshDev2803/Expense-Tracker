<?php
define('API_MODE', true);
require 'auth_check.php';
require 'db.php';
require_once 'auth_utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!AuthUtils::verifyOrigin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Cross-origin request forbidden']);
        exit;
    }

    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!AuthUtils::verifyCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token']);
        exit;
    }

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
        $currency = strtoupper(trim($_POST['currency'] ?? 'INR'));
        $configs = get_currencies_config();
        // Validate currency
        if (!array_key_exists($currency, $configs)) {
             echo json_encode(['success' => false, 'error' => 'Invalid currency selection']);
             exit;
        }
        $stmt = $conn->prepare("UPDATE users SET currency = ? WHERE id = ?");
        $stmt->bind_param("si", $currency, $user_id);
        if ($stmt->execute()) {
            $_SESSION['currency'] = $currency;
            $_SESSION['currency_symbol'] = get_currency_symbol($currency);
            $_SESSION['preferences_loaded'] = true;

            require_once __DIR__ . '/currency_service.php';
            $ratesData = CurrencyService::getExchangeRates(EXPENSE_TRACKER_BASE_CURRENCY);
            $rate = CurrencyService::getRate(EXPENSE_TRACKER_BASE_CURRENCY, $currency);

            $response = [
                'success' => true,
                'message' => 'Currency preference updated successfully',
                'currency' => $currency,
                'symbol' => $_SESSION['currency_symbol'],
                'base_currency' => EXPENSE_TRACKER_BASE_CURRENCY,
                'exchange_rate' => $rate,
                'rates' => $ratesData['rates'] ?? [],
                'last_updated' => $ratesData['last_updated'] ?? date('Y-m-d H:i:s'),
                'is_cached' => $ratesData['is_cached'] ?? false
            ];
        } else {
            $response = ['success' => false, 'error' => 'Database error while saving currency preference'];
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
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['success' => false, 'error' => 'All password fields are required']);
            exit;
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'error' => 'New password and confirmation do not match']);
            exit;
        }

        if ($current === $new) {
            echo json_encode(['success' => false, 'error' => 'New password cannot be identical to current password']);
            exit;
        }
        
        // Verify current password
        $stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($current, $user['password'])) {
            $validation = AuthUtils::validatePassword($new, $user['username'] ?? '', $user['email'] ?? '');
            if ($validation !== true) {
                echo json_encode(['success' => false, 'error' => $validation]);
                exit;
            }

            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hash, $user_id);
            
            if ($update->execute()) {
                $response = ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                $response = ['success' => false, 'error' => 'Database error updating password'];
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
