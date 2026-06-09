<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$genres = $_POST["expense_genres"] ?? [];
$custom = trim($_POST["custom_expense"]);

if (!empty($custom)) {
    $genres[] = $custom;
}

foreach ($genres as $genre) {
    $stmt = $conn->prepare("INSERT INTO user_categories (user_id, type, label) VALUES (?, 'expense', ?)");
    $stmt->bind_param("is", $user_id, $genre);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../menu.php");
exit;
?>
