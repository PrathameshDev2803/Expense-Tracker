<?php
/**
 * scripts/seed_demo_data.php
 * 
 * Populates the local database with realistic Indian (Mumbai-based) demo financial data
 * spanning September 2026, October 2026, and November 2026.
 * 
 * Usage:
 *   php scripts/seed_demo_data.php                  # Seeds demo data for default user (ID 1)
 *   php scripts/seed_demo_data.php --user=1         # Seeds demo data for specific user ID
 *   php scripts/seed_demo_data.php --clean          # Safely removes only the demo records
 *   php scripts/seed_demo_data.php --stats          # Shows financial summary without modifying
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Forbidden: This script can only be executed via CLI.\n");
}

require_once __DIR__ . '/../php/db.php';

// Safety Guard: Never seed production
if (isset($appEnv) && in_array(strtolower($appEnv), ['live', 'prod', 'production'])) {
    die("ABORTED: Cannot seed demo data in production environment (APP_ENV = '$appEnv').\n");
}

// Parse CLI Arguments
$options = getopt('', ['user::', 'clean', 'clear', 'stats', 'help']);
$targetUserId = isset($options['user']) ? (int)$options['user'] : 1;
$isClean = isset($options['clean']) || isset($options['clear']);
$isStatsOnly = isset($options['stats']);

if (isset($options['help'])) {
    echo "Usage:\n";
    echo "  php scripts/seed_demo_data.php [--user=ID] [--clean] [--stats]\n";
    exit(0);
}

// Verify User Exists
$userStmt = $conn->prepare("SELECT id, username, email, currency FROM users WHERE id = ?");
$userStmt->bind_param("i", $targetUserId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
    die("ERROR: User with ID $targetUserId not found in database.\n");
}

echo "========================================================\n";
echo " EXPENSE TRACKER - REALISTIC DEMO DATA ENGINE\n";
echo "========================================================\n";
echo "Target User : #{$user['id']} - {$user['username']} ({$user['email']})\n";
echo "Currency    : {$user['currency']}\n";
echo "Environment : " . ($appEnv ?? 'development') . "\n";
echo "Date Focus  : Sept 2026 - Nov 2026 (Ref: Nov 13, 2026)\n";
echo "--------------------------------------------------------\n";

/**
 * Definition of the realistic 3-month dataset.
 * 
 * Profile: Tech professional & freelance developer in Mumbai.
 * Reference: Current date Nov 13, 2026.
 */
function getDemoDataset() {
    return [
        // ========================================================
        // SEPTEMBER 2026 — INCOME (5 records, Total: ₹36,685)
        // ========================================================
        ['type' => 'income', 'category' => 'Salary', 'amount' => 25000.00, 'description' => 'Monthly Salary - September', 'date' => '2026-09-01 10:15:00'],
        ['type' => 'income', 'category' => 'Freelancing', 'amount' => 2500.00, 'description' => 'Website Maintenance Payment', 'date' => '2026-09-08 14:30:00'],
        ['type' => 'income', 'category' => 'Freelancing', 'amount' => 4000.00, 'description' => 'Freelance Landing Page Project', 'date' => '2026-09-18 16:45:00'],
        ['type' => 'income', 'category' => 'Business', 'amount' => 5000.00, 'description' => 'Client Advance Payment - App Consulting', 'date' => '2026-09-25 11:20:00'],
        ['type' => 'income', 'category' => 'Interest Income', 'amount' => 185.00, 'description' => 'Quarterly Savings Bank Interest', 'date' => '2026-09-30 23:50:00'],

        // ========================================================
        // SEPTEMBER 2026 — EXPENSES (25 records, Total: ₹12,795)
        // ========================================================
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 450.00, 'description' => 'Mumbai Local Train Pass - Monthly', 'date' => '2026-09-01 08:30:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 1850.00, 'description' => 'D-Mart Grocery Shopping - Household Essentials', 'date' => '2026-09-02 11:45:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 180.00, 'description' => 'Milk and Dairy Supply', 'date' => '2026-09-03 07:15:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 1450.00, 'description' => 'Electricity Bill for the Month - Adani Power', 'date' => '2026-09-05 12:10:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 325.00, 'description' => 'Swiggy Food Order - Dinner', 'date' => '2026-09-06 20:30:00'],
        ['type' => 'expense', 'category' => 'Fuel', 'amount' => 500.00, 'description' => 'Petrol Refill - HP Petrol Pump', 'date' => '2026-09-07 09:15:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 799.00, 'description' => 'Broadband Bill - Jio Fiber 100Mbps', 'date' => '2026-09-10 15:20:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 120.00, 'description' => 'Auto Rickshaw to Metro Station', 'date' => '2026-09-11 18:45:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 280.00, 'description' => 'Vegetables from Local Market', 'date' => '2026-09-12 10:00:00'],
        ['type' => 'expense', 'category' => 'Entertainment', 'amount' => 650.00, 'description' => 'Weekend Outing with Colleagues', 'date' => '2026-09-13 21:00:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 299.00, 'description' => 'Mobile Recharge - Airtel 28 Days', 'date' => '2026-09-15 14:00:00'],
        ['type' => 'expense', 'category' => 'Personal Care', 'amount' => 180.00, 'description' => 'Haircut & Grooming - Local Salon', 'date' => '2026-09-16 17:30:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 745.00, 'description' => 'BigBasket Order - Fruits & Snacks', 'date' => '2026-09-17 19:15:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 300.00, 'description' => 'Mumbai Metro Card Recharge', 'date' => '2026-09-19 08:45:00'],
        ['type' => 'expense', 'category' => 'Healthcare', 'amount' => 245.00, 'description' => 'Pharmacy Purchase - Wellness Forever', 'date' => '2026-09-20 11:30:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 850.00, 'description' => 'Dinner with Friends over the Weekend', 'date' => '2026-09-20 21:30:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 600.00, 'description' => 'Gas Cylinder Contribution - Bharat Gas', 'date' => '2026-09-21 13:00:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 285.00, 'description' => 'Uber Ride from Bandra to Andheri', 'date' => '2026-09-22 22:15:00'],
        ['type' => 'expense', 'category' => 'Subscriptions', 'amount' => 149.00, 'description' => 'OTT Subscription - Disney+ Hotstar Mobile', 'date' => '2026-09-23 16:00:00'],
        ['type' => 'expense', 'category' => 'Clothing', 'amount' => 1299.00, 'description' => 'Clothing Purchase - Casual Shirts', 'date' => '2026-09-24 18:30:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 90.00, 'description' => 'Evening Tea and Snacks at Office Tapri', 'date' => '2026-09-25 17:00:00'],
        ['type' => 'expense', 'category' => 'Education', 'amount' => 499.00, 'description' => 'Online Course Subscription - Udemy Fullstack', 'date' => '2026-09-26 14:15:00'],
        ['type' => 'expense', 'category' => 'Householdsupplies', 'amount' => 650.00, 'description' => 'Household Supplies - Cleaning & Laundry', 'date' => '2026-09-27 12:00:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 240.00, 'description' => 'Fresh Vegetables - Weekly Market', 'date' => '2026-09-28 10:30:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 50.00, 'description' => 'BEST Bus Fare for Local Commute', 'date' => '2026-09-29 09:00:00'],

        // ========================================================
        // OCTOBER 2026 — INCOME (6 records, Total: ₹43,115)
        // ========================================================
        ['type' => 'income', 'category' => 'Salary', 'amount' => 25000.00, 'description' => 'Monthly Salary - October', 'date' => '2026-10-01 10:05:00'],
        ['type' => 'income', 'category' => 'Freelancing', 'amount' => 8500.00, 'description' => 'Freelance Website Development - Client Project', 'date' => '2026-10-09 15:10:00'],
        ['type' => 'income', 'category' => 'Business', 'amount' => 6500.00, 'description' => 'Project Completion Payment - E-commerce UI', 'date' => '2026-10-21 17:30:00'],
        ['type' => 'income', 'category' => 'Freelancing', 'amount' => 2500.00, 'description' => 'Website Maintenance Payment', 'date' => '2026-10-27 12:00:00'],
        ['type' => 'income', 'category' => 'Investments', 'amount' => 420.00, 'description' => 'Stock Dividend Payout - Tata Motors', 'date' => '2026-10-30 10:00:00'],
        ['type' => 'income', 'category' => 'Interest Income', 'amount' => 195.00, 'description' => 'Savings Account Interest Credited', 'date' => '2026-10-31 23:50:00'],

        // ========================================================
        // OCTOBER 2026 — EXPENSES (29 records, Total: ₹17,030)
        // ========================================================
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 450.00, 'description' => 'Mumbai Local Train Pass - October', 'date' => '2026-10-01 08:30:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 1980.00, 'description' => 'D-Mart Monthly Grocery Shopping', 'date' => '2026-10-02 11:30:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 190.00, 'description' => 'Milk and Dairy - Amul Gold', 'date' => '2026-10-03 07:30:00'],
        ['type' => 'expense', 'category' => 'Charity', 'amount' => 2000.00, 'description' => 'Family Contribution for Festival Expenses', 'date' => '2026-10-04 12:00:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 1520.00, 'description' => 'Electricity Bill for October', 'date' => '2026-10-05 14:20:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 340.00, 'description' => 'Swiggy Food Order - Biryani Dinner', 'date' => '2026-10-06 20:45:00'],
        ['type' => 'expense', 'category' => 'Fuel', 'amount' => 500.00, 'description' => 'Petrol for Bike Commute', 'date' => '2026-10-07 09:00:00'],
        ['type' => 'expense', 'category' => 'Healthcare', 'amount' => 500.00, 'description' => 'Doctor Consultation - Routine Checkup', 'date' => '2026-10-08 18:00:00'],
        ['type' => 'expense', 'category' => 'Healthcare', 'amount' => 320.00, 'description' => 'Medical Supplies & Vitamins', 'date' => '2026-10-08 19:15:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 799.00, 'description' => 'Broadband Bill - Fiber Internet', 'date' => '2026-10-10 11:00:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 300.00, 'description' => 'Mumbai Metro Recharge', 'date' => '2026-10-11 08:45:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 290.00, 'description' => 'Vegetables from Local Market', 'date' => '2026-10-12 10:15:00'],
        ['type' => 'expense', 'category' => 'Entertainment', 'amount' => 380.00, 'description' => 'Cafe Visit & Cold Coffee - Starbucks', 'date' => '2026-10-13 17:30:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 299.00, 'description' => 'Mobile Recharge - Monthly Plan', 'date' => '2026-10-14 15:00:00'],
        ['type' => 'expense', 'category' => 'Subscriptions', 'amount' => 899.00, 'description' => 'Domain Renewal for Portfolio Website', 'date' => '2026-10-15 16:40:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 110.00, 'description' => 'Auto Rickshaw to Station', 'date' => '2026-10-16 09:15:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 820.00, 'description' => 'BigBasket Order - Cooking Essentials', 'date' => '2026-10-17 18:30:00'],
        ['type' => 'expense', 'category' => 'Shopping', 'amount' => 899.00, 'description' => 'Footwear Purchase - Woodland Sale', 'date' => '2026-10-18 19:00:00'],
        ['type' => 'expense', 'category' => 'Entertainment', 'amount' => 450.00, 'description' => 'Movie Tickets - PVR Phoenix Kurla', 'date' => '2026-10-19 20:30:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 250.00, 'description' => 'Water Bill Contribution - Society Maintenance', 'date' => '2026-10-20 13:00:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 260.00, 'description' => 'Uber Ride home after client meet', 'date' => '2026-10-21 22:00:00'],
        ['type' => 'expense', 'category' => 'Subscriptions', 'amount' => 299.00, 'description' => 'Software Subscription - GitHub Copilot', 'date' => '2026-10-22 12:00:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 950.00, 'description' => 'Dining Out with Friends at Powai', 'date' => '2026-10-24 21:30:00'],
        ['type' => 'expense', 'category' => 'Personal Care', 'amount' => 350.00, 'description' => 'Toiletries & Skincare Products', 'date' => '2026-10-25 11:30:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 85.00, 'description' => 'Tea and Snacks with Teammates', 'date' => '2026-10-26 17:15:00'],
        ['type' => 'expense', 'category' => 'Maintainence', 'amount' => 450.00, 'description' => 'Home Maintenance - Plumbing Repair', 'date' => '2026-10-27 14:00:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 180.00, 'description' => 'Daily Milk Supply', 'date' => '2026-10-28 07:45:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 260.00, 'description' => 'Vegetables Local Market', 'date' => '2026-10-29 10:00:00'],
        ['type' => 'expense', 'category' => 'Emergency', 'amount' => 250.00, 'description' => 'Miscellaneous Household Expense', 'date' => '2026-10-30 16:30:00'],

        // ========================================================
        // NOVEMBER 2026 — INCOME (3 records, Total: ₹32,500)
        // ========================================================
        ['type' => 'income', 'category' => 'Salary', 'amount' => 25000.00, 'description' => 'Monthly Salary - November', 'date' => '2026-11-01 10:20:00'],
        ['type' => 'income', 'category' => 'Business', 'amount' => 5000.00, 'description' => 'Client Advance Payment - Landing Page Redesign', 'date' => '2026-11-06 14:45:00'],
        ['type' => 'income', 'category' => 'Freelancing', 'amount' => 2500.00, 'description' => 'Monthly Website Maintenance Retainer', 'date' => '2026-11-10 11:15:00'],

        // ========================================================
        // NOVEMBER 2026 — EXPENSES (16 records, Total: ₹7,723)
        // ========================================================
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 450.00, 'description' => 'Mumbai Local Train Pass - November', 'date' => '2026-11-01 08:30:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 1850.00, 'description' => 'D-Mart Monthly Grocery Refill', 'date' => '2026-11-02 11:15:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 180.00, 'description' => 'Milk and Dairy Supply', 'date' => '2026-11-03 07:30:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 1420.00, 'description' => 'Electricity Bill for Month of November', 'date' => '2026-11-05 13:10:00'],
        ['type' => 'expense', 'category' => 'Fuel', 'amount' => 500.00, 'description' => 'Petrol Refill - HP Petrol Pump', 'date' => '2026-11-06 09:00:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 310.00, 'description' => 'Swiggy Food Order - Dinner', 'date' => '2026-11-07 20:15:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 300.00, 'description' => 'Mumbai Metro Recharge', 'date' => '2026-11-08 08:45:00'],
        ['type' => 'expense', 'category' => 'Entertainment', 'amount' => 299.00, 'description' => 'Gaming Purchase - Steam Autumn Sale', 'date' => '2026-11-09 21:30:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 799.00, 'description' => 'Broadband Bill - High Speed Fiber', 'date' => '2026-11-10 10:30:00'],
        ['type' => 'expense', 'category' => 'Transportation', 'amount' => 130.00, 'description' => 'Auto Rickshaw to Ghatkopar Station', 'date' => '2026-11-10 18:20:00'],
        ['type' => 'expense', 'category' => 'Personal Care', 'amount' => 499.00, 'description' => 'Personal Care Products - Facewash & Shampoo', 'date' => '2026-11-11 17:00:00'],
        ['type' => 'expense', 'category' => 'Groceries', 'amount' => 270.00, 'description' => 'Vegetables from Local Market', 'date' => '2026-11-11 10:15:00'],
        ['type' => 'expense', 'category' => 'Education', 'amount' => 180.00, 'description' => 'Stationery - Notebooks and Pens', 'date' => '2026-11-12 15:30:00'],
        ['type' => 'expense', 'category' => 'Healthcare', 'amount' => 320.00, 'description' => 'Medical Supplies - First Aid & Medicines', 'date' => '2026-11-12 19:00:00'],
        ['type' => 'expense', 'category' => 'Bills', 'amount' => 299.00, 'description' => 'Mobile Recharge for Personal Number', 'date' => '2026-11-13 09:15:00'],
        ['type' => 'expense', 'category' => 'Diningout', 'amount' => 95.00, 'description' => 'Tea and Snacks with Office Team', 'date' => '2026-11-13 16:30:00'],
    ];
}

$dataset = getDemoDataset();

// Category Sync: Ensure user has these genres registered
$requiredIncomeGenres = ['Salary', 'Freelancing', 'Business', 'Investments', 'Interest Income'];
$requiredExpenseGenres = [
    'Groceries', 'Transportation', 'Bills', 'Shopping', 'Clothing',
    'Personal Care', 'Entertainment', 'Diningout', 'Healthcare',
    'Education', 'Subscriptions', 'Householdsupplies', 'Maintainence',
    'Fuel', 'Charity', 'Emergency'
];

// ----------------------------------------------------
// STATS ONLY
// ----------------------------------------------------
if ($isStatsOnly) {
    printStats($conn, $targetUserId, $user['currency']);
    exit(0);
}

// ----------------------------------------------------
// CLEAN ONLY
// ----------------------------------------------------
if ($isClean) {
    echo "Action: Cleaning demo data for user #$targetUserId...\n";
    $deletedCount = 0;
    
    $delStmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ? AND type = ? AND category = ? AND amount = ? AND created_at = ?");
    
    foreach ($dataset as $row) {
        $delStmt->bind_param("issds", $targetUserId, $row['type'], $row['category'], $row['amount'], $row['date']);
        $delStmt->execute();
        $deletedCount += $delStmt->affected_rows;
    }
    $delStmt->close();
    
    echo "SUCCESS: Removed $deletedCount demo transaction(s) for user #$targetUserId.\n";
    printStats($conn, $targetUserId, $user['currency']);
    exit(0);
}

// ----------------------------------------------------
// SEED EXECUTION (IDEMPOTENT)
// ----------------------------------------------------
echo "Action: Syncing category setup and seeding transactions...\n";

// 1. Sync Income Genres
$incCheckStmt = $conn->prepare("SELECT id FROM income_genres WHERE user_id = ? AND category_name = ?");
$incInsertStmt = $conn->prepare("INSERT INTO income_genres (user_id, category_name) VALUES (?, ?)");
$newIncGenres = 0;

foreach ($requiredIncomeGenres as $cat) {
    $incCheckStmt->bind_param("is", $targetUserId, $cat);
    $incCheckStmt->execute();
    if ($incCheckStmt->get_result()->num_rows === 0) {
        $incInsertStmt->bind_param("is", $targetUserId, $cat);
        $incInsertStmt->execute();
        $newIncGenres++;
    }
}
$incCheckStmt->close();
$incInsertStmt->close();

// 2. Sync Expense Genres
$expCheckStmt = $conn->prepare("SELECT id FROM expense_genres WHERE user_id = ? AND category_name = ?");
$expInsertStmt = $conn->prepare("INSERT INTO expense_genres (user_id, category_name) VALUES (?, ?)");
$newExpGenres = 0;

foreach ($requiredExpenseGenres as $cat) {
    $expCheckStmt->bind_param("is", $targetUserId, $cat);
    $expCheckStmt->execute();
    if ($expCheckStmt->get_result()->num_rows === 0) {
        $expInsertStmt->bind_param("is", $targetUserId, $cat);
        $expInsertStmt->execute();
        $newExpGenres++;
    }
}
$expCheckStmt->close();
$expInsertStmt->close();

if ($newIncGenres > 0 || $newExpGenres > 0) {
    echo "✓ Synced categories: +$newIncGenres income genres, +$newExpGenres expense genres.\n";
}

// 3. Seed Transactions Idempotently
$checkStmt = $conn->prepare("SELECT id FROM transactions WHERE user_id = ? AND type = ? AND category = ? AND amount = ? AND created_at = ? LIMIT 1");
$insertStmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, description, created_at) VALUES (?, ?, ?, ?, ?, ?)");

$insertedCount = 0;
$skippedCount = 0;
$incomeCount = 0;
$expenseCount = 0;
$totalIncomeAmt = 0.0;
$totalExpenseAmt = 0.0;

foreach ($dataset as $row) {
    $checkStmt->bind_param("issds", $targetUserId, $row['type'], $row['category'], $row['amount'], $row['date']);
    $checkStmt->execute();
    $res = $checkStmt->get_result();

    if ($res->num_rows > 0) {
        $skippedCount++;
    } else {
        $insertStmt->bind_param("issdss", $targetUserId, $row['type'], $row['category'], $row['amount'], $row['description'], $row['date']);
        $insertStmt->execute();
        $insertedCount++;
    }

    if ($row['type'] === 'income') {
        $incomeCount++;
        $totalIncomeAmt += (float)$row['amount'];
    } else {
        $expenseCount++;
        $totalExpenseAmt += (float)$row['amount'];
    }
}

$checkStmt->close();
$insertStmt->close();

echo "--------------------------------------------------------\n";
echo " SEEDING SUMMARY\n";
echo "--------------------------------------------------------\n";
echo "Inserted Records    : $insertedCount\n";
echo "Skipped (Already In): $skippedCount\n";
echo "Total In Dataset    : " . count($dataset) . " (Income: $incomeCount, Expense: $expenseCount)\n";
echo "Date Range          : 2026-09-01 to 2026-11-13\n";
echo "Total Demo Income   : ₹" . number_format($totalIncomeAmt, 2) . "\n";
echo "Total Demo Expenses : ₹" . number_format($totalExpenseAmt, 2) . "\n";
echo "Net Demo Balance    : ₹" . number_format($totalIncomeAmt - $totalExpenseAmt, 2) . "\n";
echo "--------------------------------------------------------\n";

printStats($conn, $targetUserId, $user['currency']);

/**
 * Helper to display current database totals for verification
 */
function printStats($conn, $userId, $currency) {
    echo "\n>>> CURRENT USER #$userId DATABASE STATUS <<<\n";
    
    $res = $conn->query("SELECT type, COUNT(*) as cnt, SUM(amount) as total FROM transactions WHERE user_id = $userId GROUP BY type");
    $incomeTot = 0;
    $expenseTot = 0;
    $incomeCnt = 0;
    $expenseCnt = 0;
    
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            if ($r['type'] === 'income') {
                $incomeCnt = $r['cnt'];
                $incomeTot = (float)$r['total'];
            } elseif ($r['type'] === 'expense') {
                $expenseCnt = $r['cnt'];
                $expenseTot = (float)$r['total'];
            }
        }
    }
    
    $net = $incomeTot - $expenseTot;
    $totalCnt = $incomeCnt + $expenseCnt;
    
    echo "Total Transactions : $totalCnt\n";
    echo "  - Income Records : $incomeCnt (Total: ₹" . number_format($incomeTot, 2) . ")\n";
    echo "  - Expense Records: $expenseCnt (Total: ₹" . number_format($expenseTot, 2) . ")\n";
    echo "Current Balance    : ₹" . number_format($net, 2) . "\n";
    
    // Top 5 Expense Categories
    echo "\nTop Expense Categories:\n";
    $topExp = $conn->query("SELECT category, SUM(amount) as sum_amt, COUNT(*) as cnt FROM transactions WHERE user_id = $userId AND type = 'expense' GROUP BY category ORDER BY sum_amt DESC LIMIT 5");
    if ($topExp) {
        while ($r = $topExp->fetch_assoc()) {
            echo "  • " . str_pad($r['category'], 16) . ": ₹" . number_format($r['sum_amt'], 2) . " ({$r['cnt']} tx)\n";
        }
    }
    
    // Monthly Breakdown
    echo "\nMonthly Summary (Last 3 Months):\n";
    $monthly = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as ym,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as inc,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as exp
        FROM transactions 
        WHERE user_id = $userId
        GROUP BY ym
        ORDER BY ym DESC
        LIMIT 3
    ");
    if ($monthly) {
        while ($r = $monthly->fetch_assoc()) {
            $monthNet = $r['inc'] - $r['exp'];
            echo "  • {$r['ym']} | Income: ₹" . number_format($r['inc'], 2) . " | Expense: ₹" . number_format($r['exp'], 2) . " | Net: ₹" . number_format($monthNet, 2) . "\n";
        }
    }
    echo "========================================================\n";
}
