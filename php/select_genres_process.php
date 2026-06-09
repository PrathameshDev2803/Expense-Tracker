<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$genres = $_POST["income_genres"] ?? [];
$custom = trim($_POST["custom_income"]);

if (!empty($custom)) {
    $genres[] = $custom;
}

foreach ($genres as $genre) {
    $stmt = $conn->prepare("INSERT INTO user_categories (user_id, type, label) VALUES (?, 'income', ?)");
    $stmt->bind_param("is", $user_id, $genre);
    $stmt->execute();
    $stmt->close();
}

// Redirect to next step: selecting expense types
header("Location: select_expenses.php");
exit;
?>
