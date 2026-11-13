<?php
/**
 * Centralized Asset & Cache Management Helper
 * Provides Laravel-style asset versioning (cache-busting via filemtime)
 * and environment-aware HTTP cache-control headers.
 */

if (!function_exists('get_app_env')) {
    /**
     * Resolve the current application environment ('development' | 'live')
     */
    function get_app_env(): string {
        static $appEnv = null;
        if ($appEnv !== null) {
            return $appEnv;
        }

        // 1. Check $_ENV and $_SERVER
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');
        if (!empty($env)) {
            $appEnv = strtolower(trim((string)$env));
            return $appEnv;
        }

        // 2. Read from .env file in project root if available
        $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || $trimmed[0] === '#' || strpos($trimmed, '=') === false) {
                        continue;
                    }
                    [$k, $v] = explode('=', $trimmed, 2);
                    if (trim($k) === 'APP_ENV') {
                        $appEnv = strtolower(trim($v, " \t\n\r\0\x0B\"'"));
                        return $appEnv;
                    }
                }
            }
        }

        // Default to development for local safety
        $appEnv = 'development';
        return $appEnv;
    }
}

if (!function_exists('is_dev_env')) {
    /**
     * Check if currently running in development mode
     */
    function is_dev_env(): bool {
        return get_app_env() === 'development';
    }
}

if (!function_exists('get_base_url')) {
    /**
     * Determine base URL of the application
     */
    function get_base_url(): string {
        static $baseUrl = null;
        if ($baseUrl !== null) {
            return $baseUrl;
        }

        if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            $baseUrl = '/';
            return $baseUrl;
        }

        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                  (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                  ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Find path of current script relative to doc root
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        // If script is in /php or /scripts, point base to parent
        if (basename($scriptDir) === 'php' || basename($scriptDir) === 'scripts') {
            $scriptDir = dirname($scriptDir);
        }
        $scriptDir = rtrim($scriptDir, '/');
        $baseUrl = $scheme . '://' . $host . ($scriptDir ? $scriptDir . '/' : '/');
        return $baseUrl;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a cache-busted asset URL using filemtime.
     * Supports relative paths, absolute paths, nested folders, and external URLs.
     *
     * @param string $path Asset path (e.g., 'css/menu.css', '/js/auth.js')
     * @param bool $absolute Whether to return a full absolute URL (e.g., http://.../css/...)
     * @return string Versioned asset URL
     */
    function asset(string $path, bool $absolute = false): string {
        $trimmedPath = trim($path);

        // 1. External URLs (HTTP/HTTPS/Protocol-relative) pass through directly
        if (preg_match('#^(https?:)?//#i', $trimmedPath)) {
            return $trimmedPath;
        }

        // 2. Separate query string or hash if already present in $path
        $urlParts = parse_url($trimmedPath);
        $cleanPath = $urlParts['path'] ?? $trimmedPath;
        $existingQuery = $urlParts['query'] ?? null;
        $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        // Normalize disk path
        $projectRoot = dirname(__DIR__);
        $relativeDiskPath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cleanPath), DIRECTORY_SEPARATOR);

        // Resolve absolute file path on disk
        $fullDiskPath = $projectRoot . DIRECTORY_SEPARATOR . $relativeDiskPath;

        $version = null;
        if (file_exists($fullDiskPath) && is_file($fullDiskPath)) {
            $version = filemtime($fullDiskPath);
        }

        // Construct web path
        $webPath = ltrim($cleanPath, '/');
        if ($absolute) {
            $webPath = get_base_url() . $webPath;
        }

        // Append version query parameter if file was found
        if ($version !== null) {
            $query = ($existingQuery !== null) ? $existingQuery . '&v=' . $version : 'v=' . $version;
            return $webPath . '?' . $query . $fragment;
        }

        // If file is missing or external, return clean path preserving query/fragment
        if ($existingQuery !== null) {
            return $webPath . '?' . $existingQuery . $fragment;
        }

        return $webPath . $fragment;
    }
}

if (!function_exists('send_page_cache_headers')) {
    /**
     * Send HTTP headers for dynamic PHP page responses.
     * In development: prevents browser/proxy caching of HTML pages.
     * In live/production: ensures private user content is protected without breaking static caching.
     */
    function send_page_cache_headers(): void {
        if (headers_sent() || php_sapi_name() === 'cli') {
            return;
        }

        if (is_dev_env()) {
            // Development: Force browsers to never cache HTML pages so live changes reflect immediately
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        } else {
            // Production: Dynamic authenticated pages should not be stored in shared proxies (CDN),
            // but can be cached conditionally by the client
            header('Cache-Control: private, no-cache');
        }
    }
}

// Automatically invoke dynamic page cache headers on PHP page loads
send_page_cache_headers();
