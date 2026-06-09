<?php
session_start();
include 'db.php';
include 'setup_goals.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'fetch_all') {
    $stmt = $conn->prepare("SELECT * FROM savings_goals WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $goals = [];
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $goals]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $post_action = $input['action'] ?? '';

    if ($post_action === 'create') {
        $name = $input['goal_name'];
        $target = $input['target_amount'];
        $saved = $input['saved_amount'] ?? 0;
        $deadline = !empty($input['deadline']) ? $input['deadline'] : NULL;
        $icon = $input['icon'] ?? 'target';

        $stmt = $conn->prepare("INSERT INTO savings_goals (user_id, goal_name, target_amount, saved_amount, deadline, icon) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddss", $user_id, $name, $target, $saved, $deadline, $icon);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Goal created']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }

    if ($post_action === 'update') {
        $goal_id = $input['goal_id'];
        $name = $input['goal_name'];
        $target = $input['target_amount'];
        // We typically don't update 'saved_amount' here directly unless it's a correction, 
        // but the prompt implies standard editing. 
        // User might want to correct "Starting Balance" if they made a mistake?
        // Let's stick to name, target, deadline, icon for now as those are "Edit Goal" properties.
        // Wait, if they click Edit, they see the form. The form has "Starting Balance" which maps to saved_amount initally.
        // If they edit that field, it should logically update saved_amount.
        // But if they have added funds since then, overwriting saved_amount with the "starting" value is wrong.
        // Best approach: Hide "Starting Balance" in Edit Mode, or Rename it to "Current Balance".
        // Let's UPDATE everything provided.
        
        $deadline = !empty($input['deadline']) ? $input['deadline'] : NULL;
        $icon = $input['icon'] ?? 'target';
        
        // If saved_amount is passed, update it (admin correction style), else leave it.
        // We'll read the form. If the form still sends 'saved_amount' (mapped from #startingAmount input),
        // we should probably decide if we want to allow rewriting history.
        // For simplicity and "Edit" expectations, let's update name, target, deadline, icon.
        // We will NOT update saved_amount here to avoid overwriting progress made via "Add Funds".
        
        $stmt = $conn->prepare("UPDATE savings_goals SET goal_name = ?, target_amount = ?, deadline = ?, icon = ? WHERE goal_id = ? AND user_id = ?");
        $stmt->bind_param("sdssii", $name, $target, $deadline, $icon, $goal_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Goal updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }

    if ($post_action === 'add_funds') {
        $goal_id = $input['goal_id'];
        $amount = (float)$input['amount']; // Amount to ADD

        // First get current amount
        $stmt = $conn->prepare("SELECT saved_amount, target_amount FROM savings_goals WHERE goal_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $goal_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $new_amount = $row['saved_amount'] + $amount;
            if ($new_amount < 0) $new_amount = 0;
            // Cap at target if desired, but user requirements say "Cap at 100% (visualization)" 
            // but "Saved amount exceeds target" is an edge case to handle. 
            // The prompt says "Saved amount exceeds target" is an edge case to handle.
            // I will allow it to exceed but visualization will invoke logic. 
            // Actually, prompt says "Cap at 100%" under "Progress Calculation Logic". 
            // That usually means the visual percentage. I will store the actual amount even if it exceeds.
            
            // Check completion
            $status = 'active';
            if ($new_amount >= $row['target_amount']) {
                $status = 'completed';
            }

            $update = $conn->prepare("UPDATE savings_goals SET saved_amount = ?, status = ? WHERE goal_id = ?");
            $update->bind_param("dsi", $new_amount, $status, $goal_id);
            if ($update->execute()) {
                echo json_encode(['status' => 'success', 'new_amount' => $new_amount, 'goal_status' => $status]);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Goal not found']);
        }
        exit();
    }
    
    if ($post_action === 'delete') {
        $goal_id = $input['goal_id'];
        $stmt = $conn->prepare("DELETE FROM savings_goals WHERE goal_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $goal_id, $user_id);
        if ($stmt->execute()) {
             echo json_encode(['status' => 'success']);
        } else {
             echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }
}
?>
