<?php
/**
 * php/export_data.php
 *
 * Secure financial data export endpoint for ExpenseTracker Data Portability.
 * Authenticates user, enforces tenant isolation, handles CSV and ZIP formats.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_utils.php';

// Base currency definition check
if (!defined('EXPENSE_TRACKER_BASE_CURRENCY')) {
    define('EXPENSE_TRACKER_BASE_CURRENCY', 'INR');
}

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Authentication required. Please log in to export your financial data.'
    ]);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// 2. Origin & CSRF Verification
if (!AuthUtils::verifyOrigin()) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Cross-origin export request forbidden.'
    ]);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!AuthUtils::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid or expired security token. Please refresh the page and try again.'
    ]);
    exit;
}

// 3. Request Parameters
$format = strtolower(trim($_REQUEST['format'] ?? 'csv'));
$dataset = strtolower(trim($_REQUEST['dataset'] ?? 'all'));
$dateRange = strtolower(trim($_REQUEST['date_range'] ?? 'all'));
$startDate = trim($_REQUEST['start_date'] ?? '');
$endDate = trim($_REQUEST['end_date'] ?? '');

// 4. Validate Format - CSV only
if ($format !== 'csv') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid export format specified. Only CSV format is supported.'
    ]);
    exit;
}

// 5. Validate Dataset
$validDatasets = ['all', 'transactions', 'goals', 'subscriptions', 'account'];
if (!in_array($dataset, $validDatasets, true)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid dataset selection. Please choose a valid financial dataset.'
    ]);
    exit;
}

// 6. Validate Date Range
$validRanges = ['all', 'last30', 'last90', 'last12m', 'custom'];
if (!in_array($dateRange, $validRanges, true)) {
    $dateRange = 'all';
}

$dateSqlCondition = '';
$dateParams = [];
$dateParamTypes = '';

if ($dateRange === 'last30') {
    $dateSqlCondition = " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($dateRange === 'last90') {
    $dateSqlCondition = " AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
} elseif ($dateRange === 'last12m') {
    $dateSqlCondition = " AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
} elseif ($dateRange === 'custom') {
    if (empty($startDate) || empty($endDate)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Please provide both Start Date and End Date for the custom date range.'
        ]);
        exit;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Dates must be formatted as YYYY-MM-DD.'
        ]);
        exit;
    }

    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);

    if ($startTime === false || $endTime === false) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid start or end date supplied.'
        ]);
        exit;
    }

    if ($startTime > $endTime) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Start date cannot be after end date.'
        ]);
        exit;
    }

    $dateSqlCondition = " AND created_at >= ? AND created_at <= ?";
    $dateParams = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
    $dateParamTypes = 'ss';
}

// 7. Data Retrieval (Enforcing strictly user-scoped queries)
try {
    // 7A. Account Data (Never export password or sensitive tokens)
    $stmtUser = $conn->prepare("SELECT id, username, email, currency, theme, notification_prefs, created_at FROM users WHERE id = ?");
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();

    if (!$userData) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'User profile records not found.'
        ]);
        exit;
    }

    $displayCurrency = !empty($userData['currency']) ? strtoupper($userData['currency']) : EXPENSE_TRACKER_BASE_CURRENCY;
    $theme = !empty($userData['theme']) ? $userData['theme'] : 'light';
    $notifPrefs = json_decode($userData['notification_prefs'] ?? '{}', true) ?: [];

    $accountData = [
        'user_id' => (int)$userData['id'],
        'username' => $userData['username'],
        'full_name' => $userData['username'],
        'email' => $userData['email'],
        'account_created_at' => $userData['created_at'],
        'display_currency' => $displayCurrency,
        'base_currency' => EXPENSE_TRACKER_BASE_CURRENCY,
        'theme' => $theme,
        'notification_preferences' => $notifPrefs
    ];

    // 7B. Transactions Data
    $transactions = [];
    if ($dataset === 'all' || $dataset === 'transactions') {
        $txSql = "SELECT id, type, category, amount, description, created_at 
                  FROM transactions 
                  WHERE user_id = ?" . $dateSqlCondition . " 
                  ORDER BY created_at DESC, id DESC";
        $stmtTx = $conn->prepare($txSql);
        if ($dateRange === 'custom') {
            $stmtTx->bind_param("i" . $dateParamTypes, $userId, $dateParams[0], $dateParams[1]);
        } else {
            $stmtTx->bind_param("i", $userId);
        }
        $stmtTx->execute();
        $resTx = $stmtTx->get_result();
        while ($row = $resTx->fetch_assoc()) {
            $dateOnly = !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '';
            $timeOnly = !empty($row['created_at']) ? date('H:i:s', strtotime($row['created_at'])) : '';
            $transactions[] = [
                'transaction_id' => (int)$row['id'],
                'description' => $row['description'] ?? '',
                'amount' => (float)$row['amount'],
                'currency' => EXPENSE_TRACKER_BASE_CURRENCY,
                'type' => ucfirst(strtolower($row['type'] ?? '')),
                'category' => $row['category'] ?? '',
                'date' => $dateOnly,
                'time' => $timeOnly,
                'created_at' => $row['created_at']
            ];
        }
        $stmtTx->close();
    }

    // 7C. Goals Data
    $goals = [];
    if ($dataset === 'all' || $dataset === 'goals') {
        // Primary savings_goals table
        $stmtGoals = $conn->prepare("SELECT goal_id, goal_name, target_amount, saved_amount, deadline, icon, status, created_at 
                                     FROM savings_goals 
                                     WHERE user_id = ? 
                                     ORDER BY created_at DESC, goal_id DESC");
        $stmtGoals->bind_param("i", $userId);
        $stmtGoals->execute();
        $resGoals = $stmtGoals->get_result();
        while ($row = $resGoals->fetch_assoc()) {
            $goals[] = [
                'goal_id' => (int)$row['goal_id'],
                'goal_name' => $row['goal_name'] ?? '',
                'target_amount' => (float)$row['target_amount'],
                'current_amount' => (float)($row['saved_amount'] ?? 0),
                'target_date' => $row['deadline'] ?? '',
                'status' => ucfirst(strtolower($row['status'] ?? 'active')),
                'currency' => EXPENSE_TRACKER_BASE_CURRENCY,
                'created_at' => $row['created_at'] ?? ''
            ];
        }
        $stmtGoals->close();

        // Also check if legacy/alternative goals table contains any additional records
        $stmtAltGoals = $conn->prepare("SELECT id, title, target_amount, current_amount, end_date, status, last_updated 
                                        FROM goals 
                                        WHERE user_id = ? 
                                        ORDER BY id DESC");
        if ($stmtAltGoals) {
            $stmtAltGoals->bind_param("i", $userId);
            $stmtAltGoals->execute();
            $resAlt = $stmtAltGoals->get_result();
            while ($row = $resAlt->fetch_assoc()) {
                $goals[] = [
                    'goal_id' => (int)$row['id'],
                    'goal_name' => $row['title'] ?? '',
                    'target_amount' => (float)($row['target_amount'] ?? 0),
                    'current_amount' => (float)($row['current_amount'] ?? 0),
                    'target_date' => $row['end_date'] ?? '',
                    'status' => ucfirst(strtolower($row['status'] ?? 'active')),
                    'currency' => EXPENSE_TRACKER_BASE_CURRENCY,
                    'created_at' => $row['last_updated'] ?? ''
                ];
            }
            $stmtAltGoals->close();
        }
    }

    // 7D. Subscriptions Data
    $subscriptions = [];
    if ($dataset === 'all' || $dataset === 'subscriptions') {
        $subSql = "SELECT s.id, s.merchant_name, s.average_amount, s.billing_cycle, s.first_detected_date, s.last_charged_date, s.confidence_score, s.status, s.created_at,
                          (SELECT t.category FROM subscription_transactions st JOIN transactions t ON st.transaction_id = t.id WHERE st.subscription_id = s.id LIMIT 1) AS category
                   FROM subscriptions s
                   WHERE s.user_id = ? 
                   ORDER BY s.created_at DESC, s.id DESC";
        $stmtSub = $conn->prepare($subSql);
        $stmtSub->bind_param("i", $userId);
        $stmtSub->execute();
        $resSub = $stmtSub->get_result();
        while ($row = $resSub->fetch_assoc()) {
            $cycle = strtolower($row['billing_cycle'] ?? 'monthly');
            $lastCharged = $row['last_charged_date'] ?? '';
            $nextBilling = '';

            if (!empty($lastCharged) && $lastCharged !== '0000-00-00 00:00:00') {
                $lastChargedTs = strtotime($lastCharged);
                if ($lastChargedTs !== false) {
                    $nextBilling = match ($cycle) {
                        'weekly' => date('Y-m-d', strtotime('+1 week', $lastChargedTs)),
                        'monthly' => date('Y-m-d', strtotime('+1 month', $lastChargedTs)),
                        'yearly' => date('Y-m-d', strtotime('+1 year', $lastChargedTs)),
                        default => date('Y-m-d', strtotime('+1 month', $lastChargedTs))
                    };
                }
            }

            $subscriptions[] = [
                'subscription_id' => (int)$row['id'],
                'name' => $row['merchant_name'] ?? '',
                'amount' => (float)$row['average_amount'],
                'currency' => EXPENSE_TRACKER_BASE_CURRENCY,
                'billing_cycle' => ucfirst($cycle),
                'next_billing_date' => $nextBilling ?: 'N/A',
                'category' => !empty($row['category']) ? $row['category'] : 'Subscription',
                'status' => ucfirst(strtolower($row['status'] ?? 'active')),
                'last_charged_date' => $lastCharged ?: 'N/A',
                'created_at' => $row['created_at'] ?? ''
            ];
        }
        $stmtSub->close();
    }

} catch (Throwable $e) {
    error_log("[ExportData Error] " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected server error occurred while retrieving export data. Please try again.'
    ]);
    exit;
}

// 8. Output Serialization

$today = date('Y-m-d');

/**
 * Escapes CSV values to protect against formula injection and ensure RFC 4180 compatibility.
 */
function sanitizeCsvValue($value): string {
    if ($value === null) {
        return '';
    }
    $str = (string)$value;
    // CSV formula injection protection: if value starts with =, +, -, or @, prepend a single quote
    if (strlen($str) > 0 && in_array($str[0], ['=', '+', '-', '@'], true)) {
        return "'" . $str;
    }
    return $str;
}

/**
 * Generates CSV string from header and data rows.
 */
function buildCsvString(array $headers, array $rows): string {
    $fp = fopen('php://temp', 'r+');
    // Prepend UTF-8 BOM so Excel opens UTF-8 encoded text properly
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $headers);
    foreach ($rows as $row) {
        $sanitizedRow = array_map('sanitizeCsvValue', $row);
        fputcsv($fp, $sanitizedRow);
    }
    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);
    return $csv;
}

// 8. CSV EXPORT

// Single Dataset CSV Generators
$transactionsCsv = null;
if ($dataset === 'all' || $dataset === 'transactions') {
    $txHeaders = ['Transaction ID', 'Description', 'Amount', 'Currency', 'Type', 'Category', 'Date', 'Time', 'Created At'];
    $txRows = [];
    foreach ($transactions as $t) {
        $txRows[] = [
            $t['transaction_id'],
            $t['description'],
            number_format($t['amount'], 2, '.', ''),
            $t['currency'],
            $t['type'],
            $t['category'],
            $t['date'],
            $t['time'],
            $t['created_at']
        ];
    }
    $transactionsCsv = buildCsvString($txHeaders, $txRows);
}

$goalsCsv = null;
if ($dataset === 'all' || $dataset === 'goals') {
    $goalHeaders = ['Goal ID', 'Goal Name', 'Target Amount', 'Current Amount', 'Target Date', 'Status', 'Currency', 'Created At'];
    $goalRows = [];
    foreach ($goals as $g) {
        $goalRows[] = [
            $g['goal_id'],
            $g['goal_name'],
            number_format($g['target_amount'], 2, '.', ''),
            number_format($g['current_amount'], 2, '.', ''),
            $g['target_date'],
            $g['status'],
            $g['currency'],
            $g['created_at']
        ];
    }
    $goalsCsv = buildCsvString($goalHeaders, $goalRows);
}

$subscriptionsCsv = null;
if ($dataset === 'all' || $dataset === 'subscriptions') {
    $subHeaders = ['Subscription ID', 'Name', 'Amount', 'Currency', 'Billing Cycle', 'Next Billing Date', 'Category', 'Status', 'Last Charged Date', 'Created At'];
    $subRows = [];
    foreach ($subscriptions as $s) {
        $subRows[] = [
            $s['subscription_id'],
            $s['name'],
            number_format($s['amount'], 2, '.', ''),
            $s['currency'],
            $s['billing_cycle'],
            $s['next_billing_date'],
            $s['category'],
            $s['status'],
            $s['last_charged_date'],
            $s['created_at']
        ];
    }
    $subscriptionsCsv = buildCsvString($subHeaders, $subRows);
}

$accountCsv = null;
if ($dataset === 'all' || $dataset === 'account') {
    $accHeaders = ['User ID', 'Username', 'Email', 'Account Created Date', 'Display Currency', 'Base Currency', 'Theme'];
    $accRows = [
        [
            $accountData['user_id'],
            $accountData['username'],
            $accountData['email'],
            $accountData['account_created_at'],
            $accountData['display_currency'],
            $accountData['base_currency'],
            $accountData['theme']
        ]
    ];
    $accountCsv = buildCsvString($accHeaders, $accRows);
}

// 8B-1. Individual Dataset CSV Download
if ($dataset === 'transactions') {
    $filename = "expensetracker-transactions-{$today}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $transactionsCsv;
    exit;
}

if ($dataset === 'goals') {
    $filename = "expensetracker-goals-{$today}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $goalsCsv;
    exit;
}

if ($dataset === 'subscriptions') {
    $filename = "expensetracker-subscriptions-{$today}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $subscriptionsCsv;
    exit;
}

if ($dataset === 'account') {
    $filename = "expensetracker-account-{$today}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    echo $accountCsv;
    exit;
}

// 8B-2. Complete Financial Data CSV (Packaged as a ZIP with separate CSV files)
if ($dataset === 'all') {
    $readme = "ExpenseTracker - Complete Financial Data Export\n"
            . "===============================================\n"
            . "Export Date      : " . gmdate('Y-m-d H:i:s') . " UTC\n"
            . "User Account     : " . $accountData['username'] . " (" . $accountData['email'] . ")\n"
            . "Base Currency    : " . EXPENSE_TRACKER_BASE_CURRENCY . "\n"
            . "Display Currency : " . $displayCurrency . "\n"
            . "Date Scope       : " . strtoupper($dateRange) . "\n\n"
            . "Included Files:\n"
            . "- transactions.csv : All recorded transactions (" . count($transactions) . " records)\n"
            . "- goals.csv        : Savings and financial goals (" . count($goals) . " records)\n"
            . "- subscriptions.csv: Recurring active and detected subscriptions (" . count($subscriptions) . " records)\n"
            . "- account.csv      : Non-sensitive account details and profile preferences\n\n"
            . "Note: Financial numbers are exported in the ledger's base currency (" . EXPENSE_TRACKER_BASE_CURRENCY . ") to preserve exact accounting precision.\n";

    $zipFiles = [
        'transactions.csv' => $transactionsCsv,
        'goals.csv' => $goalsCsv,
        'subscriptions.csv' => $subscriptionsCsv,
        'account.csv' => $accountCsv,
        'README.txt' => $readme
    ];

    $zipFilename = "expensetracker-complete-export-{$today}.zip";

    // Build ZIP: Try native ZipArchive if available, otherwise use pure PHP PKZIP builder
    $zipData = null;
    if (class_exists('ZipArchive')) {
        $tempZip = tempnam(sys_get_temp_dir(), 'et_zip_');
        $zip = new ZipArchive();
        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($zipFiles as $fName => $fContent) {
                $zip->addFromString($fName, $fContent);
            }
            $zip->close();
            $zipData = file_get_contents($tempZip);
            @unlink($tempZip);
        }
    }

    if ($zipData === null) {
        // Pure PHP PKZIP Builder (Zero external extension dependency, RFC 1951 Deflate)
        $ctrlDir = '';
        $dataBlocks = '';
        $offset = 0;

        foreach ($zipFiles as $fName => $fContent) {
            $name = str_replace('\\', '/', $fName);
            $uncData = (string)$fContent;
            $uncLen = strlen($uncData);
            $crc = crc32($uncData);

            $gzData = function_exists('gzdeflate') ? gzdeflate($uncData) : false;
            $cLen = ($gzData !== false) ? strlen($gzData) : $uncLen;
            $method = 8; // Deflate

            if ($gzData === false || $cLen >= $uncLen) {
                $cData = $uncData;
                $cLen = $uncLen;
                $method = 0; // Store
            } else {
                $cData = $gzData;
            }

            // Local file header (30 bytes)
            $localHeader = pack(
                'VvvvVVVVvv',
                0x04034b50, // Local file header signature
                20,         // Version needed to extract (2.0)
                0,          // General purpose bit flag
                $method,    // Compression method
                0,          // Last mod file time and date
                $crc,       // CRC-32
                $cLen,      // Compressed size
                $uncLen,    // Uncompressed size
                strlen($name),
                0
            ) . $name;

            // Central directory header (46 bytes)
            $ctrlHeader = pack(
                'VvvvvVVVVvvvvvVV',
                0x02014b50, // Central directory file header signature
                20,         // Version made by
                20,         // Version needed to extract
                0,          // General purpose bit flag
                $method,    // Compression method
                0,          // Last mod file time and date
                $crc,       // CRC-32
                $cLen,      // Compressed size
                $uncLen,    // Uncompressed size
                strlen($name),
                0,          // Extra field length
                0,          // File comment length
                0,          // Disk number start
                0,          // Internal file attributes
                32,         // External file attributes
                $offset     // Relative offset of local header
            ) . $name;

            $dataBlocks .= $localHeader . $cData;
            $ctrlDir .= $ctrlHeader;
            $offset = strlen($dataBlocks);
        }

        // End of central directory record (22 bytes)
        $eocd = pack(
            'VvvvvVVv',
            0x06054b50, // End of central dir signature
            0,          // Disk number
            0,          // Disk with central dir
            count($zipFiles), // Total entries on this disk
            count($zipFiles), // Total entries
            strlen($ctrlDir), // Size of central directory
            strlen($dataBlocks), // Offset of central directory
            0           // Comment length
        );

        $zipData = $dataBlocks . $ctrlDir . $eocd;
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . strlen($zipData));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $zipData;
    exit;
}
