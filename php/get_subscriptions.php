<?php
// php/get_subscriptions.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM subscriptions WHERE user_id = ? AND status != 'ignored' ORDER BY average_amount DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $subscriptions[] = $row;
}

// Grouping and Totals
$grouped = [
    'weekly' => [],
    'monthly' => [],
    'yearly' => [],
    'unknown' => []
];

$totalMonthlyCost = 0;
$totalYearlyCost = 0;

foreach ($subscriptions as $sub) {
    // Add to group
    $cycle = $sub['billing_cycle'];
    if (!isset($grouped[$cycle])) $cycle = 'unknown';
    $grouped[$cycle][] = $sub;

    // Calculate costs if Active
    if ($sub['status'] === 'active') {
        $amt = (float)$sub['average_amount'];
        $monthly = 0;
        $yearly = 0;

        switch ($sub['billing_cycle']) {
            case 'weekly':
                $monthly = $amt * 4.33; // Average weeks in month
                $yearly = $amt * 52;
                break;
            case 'monthly':
                $monthly = $amt;
                $yearly = $amt * 12;
                break;
            case 'yearly':
                $monthly = $amt / 12;
                $yearly = $amt;
                break;
        }

        $totalMonthlyCost += $monthly;
        $totalYearlyCost += $yearly;
    }
}

echo json_encode([
    'success' => true,
    'data' => $grouped,
    'summary' => [
        'total_monthly_cost' => round($totalMonthlyCost, 2),
        'total_yearly_cost' => round($totalYearlyCost, 2),
        'active_count' => count(array_filter($subscriptions, fn($s) => $s['status'] === 'active'))
    ]
]);
?>
