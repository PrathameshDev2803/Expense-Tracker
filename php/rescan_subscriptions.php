<?php
// php/rescan_subscriptions.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include 'db.php';
include 'SubscriptionDetector.php';

$user_id = $_SESSION['user_id'];
$detector = new SubscriptionDetector($conn, $user_id);
$detected = $detector->detect();

$added = 0;
$updated = 0;

foreach ($detected as $sub) {
    // Check if subscription for this merchant exists for this user
    // We match by normalized merchant_name
    $stmt = $conn->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND merchant_name = ?");
    $stmt->bind_param("is", $user_id, $sub['merchant_name']);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $subscription_id = null;
    
    if ($row = $res->fetch_assoc()) {
        // Exists: Update details (unless ignored/canceled?)
        // The requirement says "Re-runs detection...". 
        // We usually don't want to reactivate canceled ones automatically, 
        // but we might want to update amounts.
        $subscription_id = $row['id'];
        
        $update = $conn->prepare("UPDATE subscriptions SET average_amount = ?, billing_cycle = ?, last_charged_date = ?, confidence_score = ? WHERE id = ? AND status != 'ignored'");
        $update->bind_param("dssii", 
            $sub['average_amount'], 
            $sub['billing_cycle'], 
            $sub['last_charged_date'], 
            $sub['confidence_score'],
            $subscription_id
        );
        $update->execute();
        $updated++;
        $update->close();
    } else {
        // Insert new
        $insert = $conn->prepare("INSERT INTO subscriptions (user_id, merchant_name, average_amount, billing_cycle, first_detected_date, last_charged_date, confidence_score, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isdsssss", 
            $user_id, 
            $sub['merchant_name'], 
            $sub['average_amount'], 
            $sub['billing_cycle'], 
            $sub['first_detected_date'], 
            $sub['last_charged_date'], 
            $sub['confidence_score'],
            $sub['status']
        );
        if ($insert->execute()) {
            $subscription_id = $insert->insert_id;
            $added++;
        }
        $insert->close();
    }
    $stmt->close();

    // Link transactions
    if ($subscription_id) {
        $linkStmt = $conn->prepare("INSERT IGNORE INTO subscription_transactions (subscription_id, transaction_id) VALUES (?, ?)");
        foreach ($sub['transaction_ids'] as $tid) {
            $linkStmt->bind_param("ii", $subscription_id, $tid);
            $linkStmt->execute();
        }
        $linkStmt->close();
    }
}

echo json_encode([
    'success' => true, 
    'message' => "Scan complete. Added $added new, updated $updated subscriptions.",
    'stats' => ['added' => $added, 'updated' => $updated]
]);
?>
