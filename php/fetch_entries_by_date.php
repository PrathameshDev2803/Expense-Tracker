<?php
require_once('db.php');

if (isset($_GET['date'])) {
    $selectedDate = $_GET['date'];

    $stmt = $conn->prepare("SELECT * FROM entries WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();

    $result = $stmt->get_result();
    $entries = [];

    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }

    echo json_encode($entries);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>
