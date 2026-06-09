<?php
session_start();
header('Content-Type: application/json');
require_once('db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  echo json_encode(["error" => "User not logged in"]);
  exit;
}

// Dates
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$yearStart = date('Y-01-01');
$yearEnd = date('Y-12-31 23:59:59');

// Prepare total values
$todayExpense = 0;
$monthExpense = 0;
$todayEarning = 0;
$monthEarning = 0;

// --- Today's Expense ---
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense' AND DATE(created_at) = ?");
if (!$stmt) {
  echo json_encode(["error" => "Prepare failed: " . $conn->error]);
  exit;
}
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($todayExpense);
$stmt->fetch();
$stmt->close();

// --- Monthly Expense ---
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense' AND created_at BETWEEN ? AND ?");
$stmt->bind_param("iss", $user_id, $monthStart, $monthEnd);
$stmt->execute();
$stmt->bind_result($monthExpense);
$stmt->fetch();
$stmt->close();

// --- Today's Earning ---
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income' AND DATE(created_at) = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($todayEarning);
$stmt->fetch();
$stmt->close();

// --- Monthly Earning ---
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income' AND created_at BETWEEN ? AND ?");
$stmt->bind_param("iss", $user_id, $monthStart, $monthEnd);
$stmt->execute();
$stmt->bind_result($monthEarning);
$stmt->fetch();
$stmt->close();

// --- Top Expense Categories ---
$categoryExpenseData = [];
$stmt = $conn->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense' GROUP BY category ORDER BY total DESC LIMIT 6");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $categoryExpenseData[] = $row;
}
$stmt->close();

// Yearly Expense
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense' AND created_at BETWEEN ? AND ?");
$stmt->bind_param("iss", $user_id, $yearStart, $yearEnd);
$stmt->execute();
$stmt->bind_result($yearExpense);
$stmt->fetch();
$stmt->close();

// Yearly Income
$stmt = $conn->prepare("SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income' AND created_at BETWEEN ? AND ?");
$stmt->bind_param("iss", $user_id, $yearStart, $yearEnd);
$stmt->execute();
$stmt->bind_result($yearIncome);
$stmt->fetch();
$stmt->close();


// --- Top Income Categories ---
$categoryIncomeData = [];
$stmt = $conn->prepare("SELECT category, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'income' GROUP BY category ORDER BY total DESC LIMIT 6");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $categoryIncomeData[] = $row;
}
$stmt->close();

// --- Final JSON Output ---
echo json_encode([
  "todayExpense" => $todayExpense ?: 0,
  "monthExpense" => $monthExpense ?: 0,
  "todayEarning" => $todayEarning ?: 0,
  "monthEarning" => $monthEarning ?: 0,
  "categoryExpense" => $categoryExpenseData,
  "categoryIncome" => $categoryIncomeData,
  "yearExpense" => $yearExpense ?: 0,
  "yearEarning" => $yearIncome ?: 0,
]);
