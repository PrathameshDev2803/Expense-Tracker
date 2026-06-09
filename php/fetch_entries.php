<?php
session_start();
header('Content-Type: application/json');
require_once('db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
$user_id = $_SESSION['user_id'];

$dateFilter = $_GET['date'] ?? null;
$monthFilter = $_GET['month'] ?? null;
$yearFilter = $_GET['year'] ?? null;

$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Base query construction
$sql = "SELECT %s FROM transactions WHERE user_id = ?";
$params = [$user_id];
$types = "i";

// Add Date Filters
if ($dateFilter) {
    $sql .= " AND DATE(created_at) = ?";
    $params[] = $dateFilter;
    $types .= "s";
} elseif ($monthFilter) {
    $sql .= " AND DATE_FORMAT(created_at, '%%Y-%%m') = ?";
    $params[] = $monthFilter;
    $types .= "s";
} elseif ($yearFilter) {
    $sql .= " AND YEAR(created_at) = ?";
    $params[] = $yearFilter;
    $types .= "s";
}

// 1. Get Total Count
$countSql = sprintf($sql, "COUNT(*) as total");
$stmt = $conn->prepare($countSql);
if (!$stmt) { echo json_encode(['error' => 'Query failed: ' . $conn->error]); exit; }
$stmt->bind_param($types, ...$params);
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$totalRecords = $countResult['total'];
$stmt->close();

// 2. Get Data
$sql = sprintf($sql, "*");
$sql .= " ORDER BY created_at DESC";

// Apply Paging
if ($limit !== 'all') {
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
}

$stmt = $conn->prepare($sql);
if (!$stmt) { echo json_encode(['error' => 'Query failed: ' . $conn->error]); exit; }
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}

echo json_encode([
    'entries' => $entries,
    'pagination' => [
        'total' => $totalRecords,
        'page' => $page,
        'limit' => $limit
    ]
]);
?>
