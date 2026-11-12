<?php
include_once 'db.php';

// Safe dependency loading: load Composer autoloader if present on server
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

class AuthUtils {
    // Validate password strength
    public static function validatePassword($password, $username, $email) {
        // 1. Length Check
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }

        // 2. Complexity Checks
        $errors = [];
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "at least one uppercase letter";
        }
        if (!preg_match('/[\W_]/', $password)) {
             $errors[] = "at least one special character";
        }
        
        if (!empty($errors)) {
            return "Password must contain " . implode(' and ', $errors) . ".";
        }

        // 3. Run Zxcvbn if available (background check for extremely weak passwords)
        if (class_exists('\ZxcvbnPhp\Zxcvbn')) {
            try {
                $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
                $strength = $zxcvbn->passwordStrength($password, [$username, $email]);

                // Score 0 or 1 is very weak. 2+ is acceptable given explicit rules are met.
                if (isset($strength['score']) && $strength['score'] < 2) {
                    return "Password is too weak. Please make it more unique.";
                }
            } catch (Throwable $e) {
                error_log("[AuthUtils] Zxcvbn check warning: " . $e->getMessage());
            }
        }

        return true; // Valid
    }

    // Initialize Rate Limit Table
    private static function initRateLimitTable($conn) {
        if (!$conn || !($conn instanceof mysqli)) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS attempt_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempt_type VARCHAR(20) NOT NULL,
            attempt_time DATETIME NOT NULL,
            INDEX idx_limit (ip_address, attempt_type, attempt_time)
        )";
        $conn->query($sql);
    }

    // Check Rate Limit
    // Returns true if allowed, false if blocked
    public static function checkRateLimit($conn, $ip, $type = 'login', $max_attempts = 5, $window_minutes = 15) {
        if (!$conn || !($conn instanceof mysqli)) {
            return true;
        }
        self::initRateLimitTable($conn);
        
        // Clean up old logs (optional optimization, run occasionally)
        if (rand(1, 100) == 1) {
             $conn->query("DELETE FROM attempt_log WHERE attempt_time < NOW() - INTERVAL 1 DAY");
        }

        // Count attempts in window
        $stmt = $conn->prepare("SELECT COUNT(*) FROM attempt_log WHERE ip_address = ? AND attempt_type = ? AND attempt_time > NOW() - INTERVAL ? MINUTE");
        if (!$stmt) {
            error_log("[AuthUtils] checkRateLimit prepare error: " . $conn->error);
            return true;
        }
        $stmt->bind_param("ssi", $ip, $type, $window_minutes);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count < $max_attempts;
    }

    // Log an attempt (usually failed attempt)
    public static function logAttempt($conn, $ip, $type = 'login') {
        if (!$conn || !($conn instanceof mysqli)) {
            return;
        }
        self::initRateLimitTable($conn);
        $stmt = $conn->prepare("INSERT INTO attempt_log (ip_address, attempt_type, attempt_time) VALUES (?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ss", $ip, $type);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Clear attempts on success
    public static function clearAttempts($conn, $ip, $type = 'login') {
        // Enforce strict window
    }
}
?>
