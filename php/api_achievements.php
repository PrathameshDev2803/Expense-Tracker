<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get Earned Badges
$sql = "SELECT b.name, b.icon, b.description, b.rarity, ua.earned_at
        FROM user_achievements ua
        JOIN badges b ON ua.badge_id = b.id
        WHERE ua.user_id = ?
        ORDER BY ua.earned_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$badges = [];
while ($row = $result->fetch_assoc()) {
    $badges[] = $row;
}

// Get User Points
$stmt = $conn->prepare("SELECT points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$points = $stmt->get_result()->fetch_assoc()['points'];

echo json_encode(['points' => $points, 'achievements' => $badges]);
?>
