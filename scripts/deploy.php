<?php
/**
 * Direct FTPS/FTP deployment script for InfinityFree / Shared Hosting.
 * Automatically discovers all active document roots (including custom domain roots
 * like myexp.prathameshjadhav.com/htdocs and default htdocs) and synchronizes project files.
 */

$host = getenv('FTP_HOST') ?: '';
$user = getenv('FTP_USERNAME') ?: '';
$pass = getenv('FTP_PASSWORD') ?: '';
$remoteDirEnv = getenv('FTP_REMOTE_DIR') ?: '';

if (empty($host) || empty($user) || empty($pass)) {
    echo "::error::Missing FTP credentials. Deployment aborted.\n";
    exit(1);
}

echo "=== Starting Multi-Target FTP Deployment ===\n";
echo "Host: {$host}\n";
echo "User: {$user}\n";
echo "Configured Target Hint: {$remoteDirEnv}\n\n";

// Connect (try FTPS first, fallback to standard FTP)
$conn = false;
if (function_exists('ftp_ssl_connect')) {
    echo "Attempting FTPS connection...\n";
    $conn = @ftp_ssl_connect($host, 21, 30);
    if ($conn) {
        echo "✓ Connected via FTPS.\n";
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
    echo "::error::FTP authentication failed for user '{$user}'. Check credentials.\n";
    ftp_close($conn);
    exit(1);
}
echo "✓ Authenticated successfully.\n";

// Passive mode
ftp_pasv($conn, true);

// Discover root listing
echo "\n--- Remote Root Listing ---\n";
ftp_chdir($conn, '/');
$rootList = @ftp_nlist($conn, '.') ?: [];
foreach ($rootList as $item) {
    echo "  - {$item}\n";
}
echo "---------------------------\n\n";

// Discover all viable document roots
$targetCandidates = [];

// 1. Explicit domain folder if present
$domainCandidate = 'myexp.prathameshjadhav.com/htdocs';
ftp_chdir($conn, '/');
if (@ftp_chdir($conn, '/' . $domainCandidate) || @ftp_chdir($conn, $domainCandidate)) {
    $targetCandidates[] = $domainCandidate;
}

// 2. User configured directory from secret/env
if (!empty($remoteDirEnv)) {
    $cleaned = trim(str_replace('\\', '/', $remoteDirEnv), '/');
    if ($cleaned !== '' && !in_array($cleaned, $targetCandidates, true)) {
        ftp_chdir($conn, '/');
        if (@ftp_chdir($conn, '/' . $cleaned) || @ftp_chdir($conn, $cleaned)) {
            $targetCandidates[] = $cleaned;
        }
    }
}

// 3. Default htdocs root
if (!in_array('htdocs', $targetCandidates, true)) {
    ftp_chdir($conn, '/');
    if (@ftp_chdir($conn, '/htdocs') || @ftp_chdir($conn, 'htdocs')) {
        $targetCandidates[] = 'htdocs';
    }
}

if (empty($targetCandidates)) {
    echo "::error::No document root (htdocs or domain directory) could be accessed on remote server.\n";
    ftp_close($conn);
    exit(1);
}

echo "Active deployment targets found:\n";
foreach ($targetCandidates as $t) {
    echo "  -> /{$t}\n";
}
echo "\n";

// Collect project files
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

echo "Found " . count($filesToUpload) . " project files to synchronize.\n\n";

function ensureSubDir($conn, string $baseDir, string $subDir): void {
    if ($subDir === '' || $subDir === '.' || $subDir === '/') return;
    $parts = explode('/', trim(str_replace('\\', '/', $subDir), '/'));
    $current = rtrim($baseDir, '/');
    foreach ($parts as $part) {
        if ($part === '') continue;
        $current .= '/' . $part;
        if (!@ftp_chdir($conn, $current)) {
            @ftp_mkdir($conn, $current);
        }
    }
    @ftp_chdir($conn, $baseDir);
}

$grandUploaded = 0;
$grandFailed = 0;

foreach ($targetCandidates as $target) {
    $targetPath = '/' . trim($target, '/');
    ftp_chdir($conn, '/');
    if (!@ftp_chdir($conn, $targetPath) && !@ftp_chdir($conn, trim($target, '/'))) {
        echo "Warning: Could not switch into {$targetPath}, skipping.\n";
        continue;
    }
    
    $baseWorkingDir = ftp_pwd($conn);
    echo "--- Deploying to: {$baseWorkingDir} ---\n";
    $targetUploaded = 0;
    $targetFailed = 0;

    foreach ($filesToUpload as $file) {
        $localPath = $rootDir . '/' . $file;
        $fileSubDir = dirname($file);

        if ($fileSubDir !== '.' && $fileSubDir !== '') {
            ensureSubDir($conn, $baseWorkingDir, $fileSubDir);
        }
        @ftp_chdir($conn, $baseWorkingDir);

        if (@ftp_put($conn, $file, $localPath, FTP_BINARY)) {
            $size = filesize($localPath);
            echo "  ✓ {$file} ({$size} B)\n";
            $targetUploaded++;
        } else {
            echo "  ✗ Failed: {$file}\n";
            $targetFailed++;
        }
    }

    echo "Completed {$targetPath}: {$targetUploaded} uploaded, {$targetFailed} failed.\n\n";
    $grandUploaded += $targetUploaded;
    $grandFailed += $targetFailed;
}

ftp_close($conn);

echo "=== Overall Deployment Summary ===\n";
echo "Total uploads: {$grandUploaded}\n";
echo "Total failures: {$grandFailed}\n";

if ($grandFailed > 0) {
    echo "::error::Some files failed to upload.\n";
    exit(1);
}

echo "✓ All active document roots are successfully synchronized with latest code!\n";
exit(0);
