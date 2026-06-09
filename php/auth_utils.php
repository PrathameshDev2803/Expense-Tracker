<?php
include_once 'db.php';
require_once __DIR__ . '/../vendor/autoload.php';

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

        // 3. Run Zxcvbn (background check for extremely weak passwords)
        // We removed the strict 'user input' rejection loop as requested.
        // We just pass inputs to help score, but don't force fail on partial matches.
        $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
        $strength = $zxcvbn->passwordStrength($password, [$username, $email]);

        // 4. Score Check (Relaxed to avoid blocking valid complex passwords)
        // Score 0 or 1 is very weak. 2+ is acceptable given the explicit rules are met.
        if ($strength['score'] < 2) {
             return "Password is too weak. Please make it more unique.";
        }

        return true; // Valid
    }

    // Initialize Rate Limit Table
    private static function initRateLimitTable($conn) {
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
        self::initRateLimitTable($conn);
        
        // Clean up old logs (optional optimization, run occasionally)
        // For performance, maybe run probability or separate cron, but doing here for simplicity
        if (rand(1, 100) == 1) {
             $conn->query("DELETE FROM attempt_log WHERE attempt_time < NOW() - INTERVAL 1 DAY"); // Keep 1 day just in case
        }

        // Count attempts in window
        $stmt = $conn->prepare("SELECT COUNT(*) FROM attempt_log WHERE ip_address = ? AND attempt_type = ? AND attempt_time > NOW() - INTERVAL ? MINUTE");
        $stmt->bind_param("ssi", $ip, $type, $window_minutes);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count < $max_attempts;
    }

    // Log an attempt (usually failed attempt)
    public static function logAttempt($conn, $ip, $type = 'login') {
        self::initRateLimitTable($conn);
        $stmt = $conn->prepare("INSERT INTO attempt_log (ip_address, attempt_type, attempt_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $ip, $type);
        $stmt->execute();
        $stmt->close();
    }
    
    // Clear attempts on success
    public static function clearAttempts($conn, $ip, $type = 'login') {
         // Optionally clear attempts to allow user to retry if they succeed? 
         // Actually, standard practice doesn't necessarily clear HISTORY, but for a simple counter it's nice.
         // But brute force is brute force. If I guessed right on 6th try, I am still an attacker. 
         // So normally we DON'T clear attempts on success for security analysis, 
         // but for "locking out" we might reset the counter. 
         // Let's NOT clear for now, enforcing strict window.
    }
}
?>
