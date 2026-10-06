<?php
/**
 * Atlas Shop Configuration File (تنظیمات اصلی اطلس شاپ)
 */

// Secure Session Cookie Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', '1');
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Tehran');

// Production Error Reporting (Errors hidden from end-users for security)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'atlashop_db');
define('DB_USER', 'atlashop_admin');
$db_pass = getenv('DB_PASSWORD') ?: 'CHANGE_ME';
define('DB_PASS', $db_pass);
define('DB_CHARSET', 'utf8mb4');

// Automatic Base URL Detection
$serverPort = $_SERVER['SERVER_PORT'] ?? 80;
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $serverPort == 443 || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
$protocol = $isHttps ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';

$allowed_hosts = ['localhost', 'atlashop.ir', 'www.atlashop.ir'];
if (!in_array($domainName, $allowed_hosts, true)) {
    die('Invalid Host header');
}

$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$baseUrl = $protocol . $domainName . ($scriptName === '/' ? '' : $scriptName);
$baseUrl = rtrim(str_replace(['/api', '/admin', '/panel-adminweb'], '', $baseUrl), '/');

define('SITE_URL', $baseUrl);
define('SITE_NAME', 'اطلس شاپ');
define('ASSETS_VER', '1.0.0');

// App Paths
define('PATH_ROOT', dirname(__DIR__));
define('PATH_INC', PATH_ROOT . '/inc');
define('PATH_UPLOADS', PATH_ROOT . '/uploads');
define('PATH_ASSETS', PATH_ROOT . '/assets');

// HTTP Security Headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}
