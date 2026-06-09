<?php
require_once('db.php');

function getTotal($type, $year, $month = null) {
    global $conn;
    $query = "SELECT SUM(amount) AS total FROM entries WHERE type = ? AND YEAR(created_at) = ?";
    $params = [$type, $year];
    $types = "si";

    if ($month !== null) {
        $query .= " AND MONTH(created_at) = ?";
        $params[] = $month;
        $types .= "i";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

$year = date("Y");
$month = date("n");

echo json_encode([
    "monthly_expense" => getTotal("expense", $year, $month),
    "yearly_expense" => getTotal("expense", $year),
    "monthly_earning" => getTotal("income", $year, $month),
    "yearly_earning" => getTotal("income", $year)
]);
?>
