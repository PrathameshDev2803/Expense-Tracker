<?php
session_start();
require_once('db.php');
require_once('auth_utils.php');

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        http_response_code(401);
        echo "❌ User not logged in.";
        exit;
    }

    if (!AuthUtils::verifyOrigin()) {
        http_response_code(403);
        echo "❌ Cross-origin request forbidden.";
        exit;
    }

    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!AuthUtils::verifyCsrfToken($token)) {
        http_response_code(403);
        echo "❌ Invalid or expired session token. Please reload the page.";
        exit;
    }

    $type = trim($_POST['type'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
    
    // Append current time to the date to ensure unique timestamps
    $dateInput = $_POST['created_at'] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)) {
        $dateInput = date('Y-m-d');
    }
    $created_at = $dateInput . ' ' . date('H:i:s');

    if ($type !== '' && $category !== '' && is_numeric($amount)) {
        $amount = (float)$amount;
        try {
            if ($id) {
                // UPDATE entry - also ensure user_id matches (security)
                $stmt = $conn->prepare("UPDATE transactions SET type = ?, category = ?, amount = ?, description = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ssdsii", $type, $category, $amount, $description, $id, $user_id);
                if ($stmt->execute()) {
                    echo "✅ Entry updated successfully!";
                } else {
                    error_log("Failed to update transaction {$id}: " . $stmt->error);
                    echo "❌ Failed to update entry. Please try again.";
                }
            } else {
                // INSERT new entry with user_id
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, description, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issdss", $user_id, $type, $category, $amount, $description, $created_at);
                if ($stmt->execute()) {
                    echo "✅ Entry saved successfully!";
                } else {
                    error_log("Failed to insert transaction: " . $stmt->error);
                    echo "❌ Failed to save entry. Please try again.";
                }
            }
            $stmt->close();
        } catch (Throwable $e) {
            error_log("Database error in add_entry.php: " . $e->getMessage());
            echo "❌ Database error occurred while saving.";
        }
    } else {
        echo "❗ Please fill in all required fields.";
    }

    $conn->close();
} else {
    http_response_code(405);
    echo "Method Not Allowed.";
}
