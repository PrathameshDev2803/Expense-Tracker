<?php
session_start();
require_once('db.php'); // connect to DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';
    $id = $_POST['id'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    // Append current time to the date to ensure unique timestamps
    $created_at = ($_POST['created_at'] ?? date('Y-m-d')) . ' ' . date('H:i:s');

    if (!$user_id) {
        echo "❌ User not logged in.";
        exit;
    }

    if ($type && $category && $amount) {
        if ($id) {
            // UPDATE entry - also ensure user_id matches (security)
            $stmt = $conn->prepare("UPDATE transactions SET type = ?, category = ?, amount = ?, description = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssdssi", $type, $category, $amount, $description, $id, $user_id);
            if ($stmt->execute()) {
                echo "✅ Entry updated successfully!";
            } else {
                echo "❌ Error updating entry: " . $stmt->error;
            }
        } else {
            // INSERT new entry with user_id
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, description, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdss", $user_id, $type, $category, $amount, $description, $created_at);
            if ($stmt->execute()) {
                echo "✅ Entry saved successfully!";
            } else {
                echo "❌ Error saving entry: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        echo "❗ Please fill in all required fields.";
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
