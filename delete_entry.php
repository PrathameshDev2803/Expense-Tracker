<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        echo "Transaction deleted successfully";
    } else {
        echo "Error deleting transaction";
    }
    $stmt->close();
}
?>