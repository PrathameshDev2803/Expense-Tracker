<?php
// php/get_analytics.php

// Ensure DB connection exists
if (!isset($conn)) {
    include 'db.php';
}
if (!isset($user_id)) {
    return;
}

// 1. Fetch all transactions ordered by date
$sql = "SELECT created_at, amount, type, description, category FROM transactions WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// 2. Helper to compute balance history
// milestones: array of 'YYYY-MM-DD' keys.
// Returns array of balances corresponding to milestones.
function get_balance_history($transactions, $milestones) {
    $balances = [];
    $current_bal = 0;
    $tx_idx = 0;
    $total_tx = count($transactions);
    
    // Sort milestones just in case
    ksort($milestones);
    
    foreach ($milestones as $date => $key) {
        // Process transactions up to this date
        while ($tx_idx < $total_tx && $transactions[$tx_idx]['created_at'] <= $date . ' 23:59:59') {
            $t = $transactions[$tx_idx];
            if ($t['type'] == 'income') $current_bal += $t['amount'];
            else $current_bal -= $t['amount'];
            $tx_idx++;
        }
        $balances[$key] = $current_bal;
    }
    
    // Fill array in correct order
    $result = [];
    foreach ($balances as $k => $v) {
        $result[$k] = $v;
    }
    return $result;
}

// --- 1Y Data (Monthly) ---
$this_year = date('Y');
$last_year = $this_year - 1;
$y_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$milestones_y = [];

for ($i = 0; $i < 12; $i++) {
    $m = $i + 1;
    $milestones_y[date('Y-m-t', strtotime("$this_year-$m-01"))] = "c_$i";
    $milestones_y[date('Y-m-t', strtotime("$last_year-$m-01"))] = "p_$i";
}

$history_y = get_balance_history($transactions, $milestones_y);

$y_current = [];
$y_previous = [];
for ($i = 0; $i < 12; $i++) {
    $y_current[] = $history_y["c_$i"] ?? 0;
    $y_previous[] = $history_y["p_$i"] ?? 0;
}

// --- 1M Data (Weekly for current month) ---
// We'll show 4 weeks of the current month vs last month
$m_labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
$milestones_m = [];
$curr_month_start = date('Y-m-01');
$prev_month_start = date('Y-m-01', strtotime('-1 month'));

for ($i = 1; $i <= 4; $i++) {
    // End of week $i
    $d_curr = date('Y-m-d', strtotime("$curr_month_start + " . ($i * 7) . " days"));
    $d_prev = date('Y-m-d', strtotime("$prev_month_start + " . ($i * 7) . " days"));
    
    $milestones_m[$d_curr] = "c_" . ($i-1);
    $milestones_m[$d_prev] = "p_" . ($i-1);
}

$history_m = get_balance_history($transactions, $milestones_m);
$m_current = [];
$m_previous = [];
for ($i = 0; $i < 4; $i++) {
    $m_current[] = $history_m["c_$i"] ?? 0;
    $m_previous[] = $history_m["p_$i"] ?? 0;
}


// --- 1W Data (Last 7 Days) ---
$w_labels = [];
$milestones_w = [];
$today = date('Y-m-d');

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $d_prev = date('Y-m-d', strtotime("-$i days -7 days")); // Compare to previous week
    
    $w_labels[] = date('D', strtotime($d));
    $milestones_w[$d] = "c_" . (6-$i);
    $milestones_w[$d_prev] = "p_" . (6-$i);
}

$history_w = get_balance_history($transactions, $milestones_w);
$w_current = [];
$w_previous = [];
for ($i = 0; $i < 7; $i++) {
    $w_current[] = $history_w["c_$i"] ?? 0;
    $w_previous[] = $history_w["p_$i"] ?? 0;
}

// --- 1D Data (Hourly - Simplified) ---
// Note: This is computationally heavier if we do strict hourly. 
// For now, let's just do 4 chunks of the day: Morning, Noon, Evening, Night
$d_labels = ['6 AM', '12 PM', '6 PM', '12 AM'];
$milestones_d = [];
$today_str = date('Y-m-d');
$yest_str = date('Y-m-d', strtotime('-1 day'));

$times = ['06:00:00', '12:00:00', '18:00:00', '23:59:59'];
foreach ($times as $idx => $time) {
    $milestones_d["$today_str $time"] = "c_$idx";
    $milestones_d["$yest_str $time"] = "p_$idx";
}

$history_d = get_balance_history($transactions, $milestones_d);
$d_current = [];
$d_previous = [];
for ($i = 0; $i < 4; $i++) {
    $d_current[] = $history_d["c_$i"] ?? 0;
    $d_previous[] = $history_d["p_$i"] ?? 0;
}


// Final Object
$analyticsData = [
    '1D' => [
        'labels' => $d_labels,
        'current' => $d_current,
        'previous' => $d_previous,
        'events' => [], // TODO: extract significant events
        'tension' => 0.4
    ],
    '1W' => [
        'labels' => $w_labels,
        'current' => $w_current,
        'previous' => $w_previous,
        'events' => [],
        'tension' => 0.4
    ],
    '1M' => [
        'labels' => $m_labels,
        'current' => $m_current,
        'previous' => $m_previous,
        'events' => [],
        'tension' => 0.3
    ],
    '1Y' => [
        'labels' => $y_labels,
        'current' => $y_current,
        'previous' => $y_previous,
        'events' => [],
        'tension' => 0.4
    ]
];
?>
