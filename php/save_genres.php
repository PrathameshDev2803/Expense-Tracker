<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $genres = json_decode(file_get_contents('php://input'), true);

    if (!empty($genres) && is_array($genres)) {
        $errors = [];

        foreach ($genres as $genre) {
            if (is_string($genre)) {
                $genre = $conn->real_escape_string($genre);
                $sql = "INSERT INTO income_genres (genre_name) VALUES ('$genre')";
                if (!$conn->query($sql)) {
                    $errors[] = $conn->error;
                }
            }
        }

        if (empty($errors)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB errors', 'errors' => $errors]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or empty input']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Only POST requests allowed']);
}
?>
