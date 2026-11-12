<?php
// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Reuse existing MySQLi connection if already established
if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
    $conn = $GLOBALS['conn'];
    return;
}

$rootDir = dirname(__DIR__);

// Load Composer autoloader if present
$autoloadFile = $rootDir . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
}

// Load environment variables from root .env file
$envFile = $rootDir . '/.env';
if (file_exists($envFile)) {
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable($rootDir);
        $dotenv->safeLoad();
    } else {
        // Fallback parser if vendor autoloader is not present on shared hosting
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (
                        (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                        (str_starts_with($value, "'") && str_ends_with($value, "'"))
                    ) {
                        $value = substr($value, 1, -1);
                    }
                    if (!array_key_exists($key, $_ENV)) {
                        $_ENV[$key] = $value;
                    }
                    if (!array_key_exists($key, $_SERVER)) {
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }
    }
}

// Helper to safely retrieve an environment variable
$getEnvVar = function (string $key): ?string {
    if (array_key_exists($key, $_ENV)) {
        return (string)$_ENV[$key];
    }
    if (array_key_exists($key, $_SERVER)) {
        return (string)$_SERVER[$key];
    }
    $val = getenv($key);
    if ($val !== false) {
        return (string)$val;
    }
    return null;
};

// Validate APP_ENV
$rawAppEnv = $getEnvVar('APP_ENV');
if ($rawAppEnv === null || trim($rawAppEnv) === '') {
    error_log('Database configuration error: APP_ENV is not set in environment or .env file.');
    die('A system error occurred. Please try again later.');
}

$appEnv = strtolower(trim($rawAppEnv));
if ($appEnv !== 'development' && $appEnv !== 'live') {
    error_log("Database configuration error: Invalid APP_ENV '{$rawAppEnv}'. Allowed values are 'development' or 'live'.");
    die('A system error occurred. Please try again later.');
}

// Select database credentials based on APP_ENV
$suffix = ($appEnv === 'live') ? 'LIVE' : 'DEVELOPMENT';

$hostKey = "DB_HOST_{$suffix}";
$portKey = "DB_PORT_{$suffix}";
$nameKey = "DB_NAME_{$suffix}";
$userKey = "DB_USER_{$suffix}";
$passKey = "DB_PASS_{$suffix}";

$dbHost = $getEnvVar($hostKey);
$dbPortRaw = $getEnvVar($portKey);
$dbName = $getEnvVar($nameKey);
$dbUser = $getEnvVar($userKey);
$dbPass = $getEnvVar($passKey);

$missingVars = [];
if ($dbHost === null || trim($dbHost) === '') {
    $missingVars[] = $hostKey;
}
if ($dbName === null || trim($dbName) === '') {
    $missingVars[] = $nameKey;
}
if ($dbUser === null || trim($dbUser) === '') {
    $missingVars[] = $userKey;
}
// Password variable must be defined; on live it must not be empty
if ($dbPass === null || ($appEnv === 'live' && trim($dbPass) === '')) {
    $missingVars[] = $passKey;
}

if (!empty($missingVars)) {
    error_log("Database configuration error: Missing required environment variable(s) [" . implode(', ', $missingVars) . "] for environment '{$appEnv}'.");
    die('A system error occurred. Please try again later.');
}

$dbPort = (!empty($dbPortRaw) && is_numeric($dbPortRaw)) ? (int)$dbPortRaw : 3306;

// Enable strict error reporting to catch connection failures safely
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
} catch (Throwable $e) {
    error_log("Database connection failed [{$appEnv}]: " . $e->getMessage());
    die('A system error occurred. Please try again later.');
}

// Set charset to utf8mb4
if (!$conn->set_charset('utf8mb4')) {
    error_log('Database charset error: Failed to set character set utf8mb4: ' . $conn->error);
}

// Set MySQL session timezone to Asia/Kolkata (+05:30)
try {
    $conn->query("SET time_zone = '+05:30'");
} catch (Throwable $tzException) {
    error_log('Database timezone warning: Failed to set session time_zone: ' . $tzException->getMessage());
}

$GLOBALS['conn'] = $conn;
