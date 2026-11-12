<?php
/**
 * Direct FTPS/FTP deployment script for InfinityFree / Shared Hosting.
 * Uses native PHP ftp_* extensions with automatic directory creation and SSL fallback.
 */

$host = getenv('FTP_HOST') ?: '';
$user = getenv('FTP_USERNAME') ?: '';
$pass = getenv('FTP_PASSWORD') ?: '';
$remoteDir = getenv('FTP_REMOTE_DIR') ?: '';

if (empty($host) || empty($user) || empty($pass)) {
    echo "::error::Missing FTP credentials. Deployment aborted.\n";
    exit(1);
}

// Normalize remote dir
$remoteDir = trim(str_replace('\\', '/', $remoteDir));
if ($remoteDir === '' || $remoteDir === '/') {
    $remoteDir = 'htdocs/';
}
if (!str_ends_with($remoteDir, '/')) {
    $remoteDir .= '/';
}
if (str_starts_with($remoteDir, '/')) {
    $remoteDir = ltrim($remoteDir, '/');
}

echo "=== Starting FTP Deployment ===\n";
echo "Host: {$host}\n";
echo "User: {$user}\n";
echo "Configured Target Directory: {$remoteDir}\n\n";

// Connect (try FTPS first, then plain FTP)
$conn = false;
$isSsl = false;
if (function_exists('ftp_ssl_connect')) {
    echo "Attempting secure FTPS connection...\n";
    $conn = @ftp_ssl_connect($host, 21, 30);
    if ($conn) {
        $isSsl = true;
        echo "Connected via FTPS (control channel encrypted).\n";
    }
}

if (!$conn) {
    echo "Connecting via standard FTP...\n";
    $conn = @ftp_connect($host, 21, 30);
}

if (!$conn) {
    echo "::error::Could not connect to FTP host '{$host}'.\n";
    exit(1);
}

echo "Authenticating...\n";
$login = @ftp_login($conn, $user, $pass);
if (!$login) {
    echo "::error::FTP authentication failed for user '{$user}'. Check INFINITYFREE_FTP_PASSWORD.\n";
    ftp_close($conn);
    exit(1);
}
echo "✓ Authenticated successfully.\n";

// Turn on passive mode
ftp_pasv($conn, true);

// Discover root listing
echo "\n--- Remote Root Listing ---\n";
$rootList = @ftp_nlist($conn, '.') ?: [];
foreach ($rootList as $item) {
    echo "  - {$item}\n";
}
echo "---------------------------\n\n";

// Validate / navigate to target directory
$targetDir = $remoteDir;
if (!@ftp_chdir($conn, '/' . $targetDir)) {
    if (!@ftp_chdir($conn, $targetDir)) {
        echo "Warning: Target '{$targetDir}' could not be chdir'd directly.\n";
        // Check if rootList contains domain directory or htdocs
        $domainDir = 'myexp.prathameshjadhav.com/htdocs/';
        if (@ftp_chdir($conn, '/' . $domainDir) || @ftp_chdir($conn, $domainDir)) {
            echo "✓ Found and switched to domain document root: {$domainDir}\n";
            $targetDir = $domainDir;
        } elseif (@ftp_chdir($conn, '/htdocs') || @ftp_chdir($conn, 'htdocs')) {
            echo "✓ Found and switched to default root: htdocs/\n";
            $targetDir = 'htdocs/';
        } else {
            echo "::error::Could not find or enter document root directory.\n";
            ftp_close($conn);
            exit(1);
        }
    }
}

$currentDir = ftp_pwd($conn);
echo "Current working directory on remote server: {$currentDir}\n\n";

// Files to deploy
$rootDir = dirname(__DIR__);
$excludedPatterns = [
    '^\.git',
    '^\.github',
    '^\.vscode',
    '^\.env',
    '^vendor',
    '^tests',
    '^test',
    '^scratch',
    '^node_modules',
    '^scripts',
    '\.sql$',
    '\.log$',
    'README\.md$',
    'composer\.(json|lock)$'
];

$filesToUpload = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    if ($item->isDir()) {
        continue;
    }
    $relPath = str_replace('\\', '/', substr($item->getPathname(), strlen($rootDir) + 1));
    
    // Check exclusions
    $excluded = false;
    foreach ($excludedPatterns as $pattern) {
        if (preg_match('/' . $pattern . '/i', $relPath)) {
            $excluded = true;
            break;
        }
    }
    if (!$excluded) {
        $filesToUpload[] = $relPath;
    }
}

echo "Found " . count($filesToUpload) . " project files to sync.\n";

// Helper to ensure remote directory exists
function ensureRemoteDir($conn, string $remotePath): void {
    $parts = explode('/', trim($remotePath, '/'));
    $current = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $current .= '/' . $part;
        if (!@ftp_chdir($conn, $current)) {
            @ftp_mkdir($conn, $current);
        }
    }
}

$uploadedCount = 0;
$failCount = 0;

foreach ($filesToUpload as $file) {
    $localPath = $rootDir . '/' . $file;
    $remoteFilePath = $currentDir . '/' . $file;
    $remoteFileDir = dirname($remoteFilePath);

    ensureRemoteDir($conn, $remoteFileDir);
    @ftp_chdir($conn, $currentDir);

    // Upload file
    $mode = FTP_BINARY;
    if (@ftp_put($conn, $remoteFilePath, $localPath, $mode)) {
        $size = filesize($localPath);
        echo "✓ Uploaded: {$file} ({$size} bytes)\n";
        $uploadedCount++;
    } else {
        echo "✗ Failed to upload: {$file}\n";
        $failCount++;
    }
}

ftp_close($conn);

echo "\n=== Deployment Summary ===\n";
echo "Successfully uploaded: {$uploadedCount} files\n";
echo "Failed: {$failCount} files\n";

if ($failCount > 0) {
    echo "::error::Some files failed to upload.\n";
    exit(1);
}

echo "✓ Deployment complete! All changes are live on InfinityFree.\n";
exit(0);
