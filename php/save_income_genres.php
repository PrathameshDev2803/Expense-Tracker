<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['genres']) && is_array($input['genres'])) {
        foreach ($input['genres'] as $genre) {
            $genre = $conn->real_escape_string($genre);
            // Check if exists
            $check = $conn->query("SELECT id FROM income_genres WHERE user_id = '$user_id' AND category_name = '$genre'");
            if ($check->num_rows == 0) {
                $sql = "INSERT INTO income_genres (user_id, category_name) VALUES ('$user_id', '$genre')";
                $conn->query($sql);
            }
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No genres provided']);
    }
}
?>