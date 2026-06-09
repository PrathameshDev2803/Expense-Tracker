<?php
session_start();
include 'php/db.php';

// Clear DB Token
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $conn->query("UPDATE users SET remember_token = NULL WHERE id = '$uid'");
}

// Clear Cookies
setcookie("remember_me", "", time() - 3600, "/");
setcookie("user_pass", "", time() - 3600, "/"); // Clear legacy insecure cookie

// Clear Session
session_unset();
session_destroy();


header("Location: login.php");
exit();
