<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/php/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Allow: POST");
    echo "Method Not Allowed. POST request required.";
    exit();
}

// CSRF Validation
$submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    echo "Forbidden: Invalid or missing CSRF token.";
    exit();
}

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $user_id = (int)$_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            echo "Transaction deleted successfully";
        } else {
            http_response_code(500);
            echo "Error deleting transaction";
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "Database prepare error";
    }
} else {
    http_response_code(400);
    echo "Missing transaction ID";
}
?>
