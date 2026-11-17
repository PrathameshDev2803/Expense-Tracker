<?php
// php/api_currency.php
define('API_MODE', true);
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/currency_service.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? 'get_rates';
$base = $_GET['base'] ?? EXPENSE_TRACKER_BASE_CURRENCY;
$force = isset($_GET['refresh']) && $_GET['refresh'] === '1';

if ($action === 'get_rates') {
    $data = CurrencyService::getExchangeRates($base, $force);
    $data['active_currency'] = $_SESSION['currency'] ?? 'INR';
    $data['active_symbol'] = CurrencyService::getCurrencySymbol($data['active_currency']);
    echo json_encode($data);
    exit();
} elseif ($action === 'convert') {
    $amount = (float)($_GET['amount'] ?? 0);
    $from = $_GET['from'] ?? EXPENSE_TRACKER_BASE_CURRENCY;
    $to = $_GET['to'] ?? ($_SESSION['currency'] ?? 'INR');

    $converted = CurrencyService::convert($amount, $from, $to);
    $formatted = CurrencyService::format($converted, $to, false);

    echo json_encode([
        'success' => true,
        'original_amount' => $amount,
        'from_currency' => $from,
        'to_currency' => $to,
        'converted_amount' => $converted,
        'formatted' => $formatted,
        'rate' => CurrencyService::getRate($from, $to)
    ]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
