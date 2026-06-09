<?php
require_once('db.php');

$summary = [
    'monthlyExpense' => 0,
    'yearlyExpense' => 0,
    'monthlyEarning' => 0,
    'yearlyEarning' => 0
];

$month = date('m');
$year = date('Y');

// Monthly Expense
$sql = "SELECT SUM(amount) AS total FROM entries WHERE type='expense' AND MONTH(created_at)=? AND YEAR(created_at)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $month, $year);
$stmt->execute();
$stmt->bind_result($monthlyExpense);
$stmt->fetch();
$summary['monthlyExpense'] = $monthlyExpense ?? 0;
$stmt->close();

// Yearly Expense
$sql = "SELECT SUM(amount) AS total FROM entries WHERE type='expense' AND YEAR(created_at)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $year);
$stmt->execute();
$stmt->bind_result($yearlyExpense);
$stmt->fetch();
$summary['yearlyExpense'] = $yearlyExpense ?? 0;
$stmt->close();

// Monthly Earning
$sql = "SELECT SUM(amount) AS total FROM entries WHERE type='income' AND MONTH(created_at)=? AND YEAR(created_at)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $month, $year);
$stmt->execute();
$stmt->bind_result($monthlyEarning);
$stmt->fetch();
$summary['monthlyEarning'] = $monthlyEarning ?? 0;
$stmt->close();

// Yearly Earning
$sql = "SELECT SUM(amount) AS total FROM entries WHERE type='income' AND YEAR(created_at)=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $year);
$stmt->execute();
$stmt->bind_result($yearlyEarning);
$stmt->fetch();
$summary['yearlyEarning'] = $yearlyEarning ?? 0;
$stmt->close();

$conn->close();

header('Content-Type: application/json');
echo json_encode($summary);
