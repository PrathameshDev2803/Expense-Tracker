<?php
// php/currency_service.php
// Centralized Currency & Exchange Rate Service for ExpenseTracker

if (!defined('EXPENSE_TRACKER_BASE_CURRENCY')) {
    define('EXPENSE_TRACKER_BASE_CURRENCY', 'INR');
}

class CurrencyService {
    private static $cachedRates = null;
    private static $cacheDurationSeconds = 3600; // 1 hour

    /**
     * Ensure the currency_rates table exists.
     */
    public static function ensureTable($conn) {
        if (!($conn instanceof mysqli)) return false;
        $sql = "CREATE TABLE IF NOT EXISTS currency_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            base_currency VARCHAR(10) NOT NULL,
            target_currency VARCHAR(10) NOT NULL,
            rate DECIMAL(18, 8) NOT NULL,
            last_updated DATETIME NOT NULL,
            source VARCHAR(50) DEFAULT 'open.er-api.com',
            UNIQUE KEY uq_pair (base_currency, target_currency)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return $conn->query($sql);
    }

    /**
     * Retrieve supported currencies with metadata.
     */
    public static function getSupportedCurrencies() {
        return [
            'INR' => [
                'code' => 'INR',
                'symbol' => '₹',
                'name' => 'Indian Rupee',
                'locale' => 'en-IN',
                'decimals' => 2
            ],
            'USD' => [
                'code' => 'USD',
                'symbol' => '$',
                'name' => 'US Dollar',
                'locale' => 'en-US',
                'decimals' => 2
            ],
            'EUR' => [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => 'Euro',
                'locale' => 'de-DE',
                'decimals' => 2
            ],
            'GBP' => [
                'code' => 'GBP',
                'symbol' => '£',
                'name' => 'British Pound',
                'locale' => 'en-GB',
                'decimals' => 2
            ]
        ];
    }

    /**
     * Get symbol for currency code.
     */
    public static function getCurrencySymbol($code) {
        $currencies = self::getSupportedCurrencies();
        $code = strtoupper((string)$code);
        return $currencies[$code]['symbol'] ?? '₹';
    }

    /**
     * Fallback rates in case network and cache are both unavailable.
     */
    private static function getEmergencyFallbackRates() {
        return [
            'INR' => 1.0,
            'USD' => 0.010643,
            'EUR' => 0.009675,
            'GBP' => 0.008072
        ];
    }

    /**
     * Fetch live rates from open.er-api.com.
     */
    private static function fetchLiveRates($base = 'INR') {
        $base = strtoupper($base);
        $url = "https://open.er-api.com/v6/latest/{$base}";

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "User-Agent: ExpenseTracker/2.0\r\n"
            ]
        ]);

        $json = @file_get_contents($url, false, $context);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);
        if (!$data || ($data['result'] ?? '') !== 'success' || empty($data['rates'])) {
            return null;
        }

        $supported = self::getSupportedCurrencies();
        $filteredRates = [];
        foreach ($supported as $code => $info) {
            if ($code === $base) {
                $filteredRates[$code] = 1.0;
            } elseif (isset($data['rates'][$code])) {
                $filteredRates[$code] = (float)$data['rates'][$code];
            }
        }

        $lastUpdated = date('Y-m-d H:i:s');
        if (!empty($data['time_last_update_utc'])) {
            $parsed = strtotime($data['time_last_update_utc']);
            if ($parsed) {
                $lastUpdated = date('Y-m-d H:i:s', $parsed);
            }
        }

        return [
            'base' => $base,
            'rates' => $filteredRates,
            'last_updated' => $lastUpdated,
            'source' => 'open.er-api.com'
        ];
    }

    /**
     * Retrieve exchange rates with multi-layer caching (Memory -> DB -> API -> Fallback).
     */
    public static function getExchangeRates($base = 'INR', $forceRefresh = false) {
        $base = strtoupper($base);

        if (!empty(self::$cachedRates) && !$forceRefresh) {
            return self::$cachedRates;
        }

        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) {
            @include_once __DIR__ . '/db.php';
        }

        $dbRates = [];
        $dbLastUpdated = null;
        $dbSource = 'open.er-api.com';

        if (isset($conn) && $conn instanceof mysqli) {
            self::ensureTable($conn);

            $stmt = $conn->prepare("SELECT target_currency, rate, last_updated, source FROM currency_rates WHERE base_currency = ?");
            if ($stmt) {
                $stmt->bind_param("s", $base);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $dbRates[$row['target_currency']] = (float)$row['rate'];
                    if (!$dbLastUpdated || strtotime($row['last_updated']) > strtotime($dbLastUpdated)) {
                        $dbLastUpdated = $row['last_updated'];
                        $dbSource = $row['source'];
                    }
                }
                $stmt->close();
            }
        }

        $isCacheValid = false;
        if ($dbLastUpdated && !empty($dbRates) && isset($dbRates['USD'], $dbRates['EUR'], $dbRates['GBP'])) {
            $ageSeconds = time() - strtotime($dbLastUpdated);
            if ($ageSeconds < self::$cacheDurationSeconds && !$forceRefresh) {
                $isCacheValid = true;
            }
        }

        if ($isCacheValid) {
            $dbRates[$base] = 1.0;
            self::$cachedRates = [
                'success' => true,
                'base' => $base,
                'rates' => $dbRates,
                'last_updated' => $dbLastUpdated,
                'source' => $dbSource,
                'is_cached' => true
            ];
            return self::$cachedRates;
        }

        // Attempt live fetch
        $liveData = self::fetchLiveRates($base);
        if ($liveData && !empty($liveData['rates'])) {
            $rates = $liveData['rates'];
            $lastUpdated = $liveData['last_updated'];

            // Persist into database cache
            if (isset($conn) && $conn instanceof mysqli) {
                $saveStmt = $conn->prepare("INSERT INTO currency_rates (base_currency, target_currency, rate, last_updated, source) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE rate = VALUES(rate), last_updated = VALUES(last_updated), source = VALUES(source)");
                if ($saveStmt) {
                    foreach ($rates as $target => $rateVal) {
                        $sourceStr = $liveData['source'];
                        $saveStmt->bind_param("ssdss", $base, $target, $rateVal, $lastUpdated, $sourceStr);
                        $saveStmt->execute();
                    }
                    $saveStmt->close();
                }
            }

            self::$cachedRates = [
                'success' => true,
                'base' => $base,
                'rates' => $rates,
                'last_updated' => $lastUpdated,
                'source' => $liveData['source'],
                'is_cached' => false
            ];
            return self::$cachedRates;
        }

        // Live fetch failed: Fallback to existing DB cache if present
        if (!empty($dbRates) && isset($dbRates['USD'])) {
            $dbRates[$base] = 1.0;
            self::$cachedRates = [
                'success' => true,
                'base' => $base,
                'rates' => $dbRates,
                'last_updated' => $dbLastUpdated ?: date('Y-m-d H:i:s'),
                'source' => $dbSource,
                'is_cached' => true,
                'warning' => 'Using previously cached rates due to network timeout'
            ];
            return self::$cachedRates;
        }

        // Emergency static fallback
        $fallback = self::getEmergencyFallbackRates();
        self::$cachedRates = [
            'success' => true,
            'base' => $base,
            'rates' => $fallback,
            'last_updated' => date('Y-m-d H:i:s'),
            'source' => 'static_fallback',
            'is_cached' => true,
            'warning' => 'Using emergency fallback exchange rates'
        ];
        return self::$cachedRates;
    }

    /**
     * Get single rate multiplier from $from to $to.
     */
    public static function getRate($from = 'INR', $to = 'USD') {
        $from = strtoupper((string)$from);
        $to = strtoupper((string)$to);
        if ($from === $to) return 1.0;

        $rateData = self::getExchangeRates(EXPENSE_TRACKER_BASE_CURRENCY);
        $rates = $rateData['rates'] ?? self::getEmergencyFallbackRates();

        $base = EXPENSE_TRACKER_BASE_CURRENCY;
        if ($from === $base) {
            return (float)($rates[$to] ?? 1.0);
        }
        if ($to === $base) {
            $r = (float)($rates[$from] ?? 1.0);
            return $r > 0 ? (1.0 / $r) : 1.0;
        }

        $rFrom = (float)($rates[$from] ?? 1.0);
        $rTo = (float)($rates[$to] ?? 1.0);
        return $rFrom > 0 ? ($rTo / $rFrom) : 1.0;
    }

    /**
     * Convert amount from $from to $to.
     * Converted Amount = Original Amount × Exchange Rate
     */
    public static function convert($amount, $from = 'INR', $to = 'USD') {
        $num = (float)$amount;
        $from = strtoupper((string)$from);
        $to = strtoupper((string)$to);
        if ($from === $to) return $num;

        $rate = self::getRate($from, $to);
        return $num * $rate;
    }

    /**
     * Format a monetary value according to currency rules.
     */
    public static function format($amount, $currencyCode = 'INR', $convertFromBase = false, $decimals = null) {
        $currencyCode = strtoupper((string)$currencyCode);
        $val = (float)$amount;

        if ($convertFromBase && $currencyCode !== EXPENSE_TRACKER_BASE_CURRENCY) {
            $val = self::convert($val, EXPENSE_TRACKER_BASE_CURRENCY, $currencyCode);
        }

        $configs = self::getSupportedCurrencies();
        $cfg = $configs[$currencyCode] ?? $configs['INR'];
        $dec = $decimals !== null ? $decimals : ($cfg['decimals'] ?? 2);

        $symbol = $cfg['symbol'];
        return $symbol . number_format($val, $dec, '.', ',');
    }
}
