<?php
// Base URL Aplikasi (otomatis mengikuti domain & folder saat diakses)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https'
    : ($_SERVER['REQUEST_SCHEME'] ?? 'http');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = $scriptName !== '' ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/') : '';
if ($basePath === '.') {
    $basePath = '';
}
$baseUrl = $scheme . '://' . $host . $basePath;
define('BASE_URL', $baseUrl);

// Konfigurasi Database MariaDB
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'anyeoung_gift');

// Konfigurasi Aplikasi
define('APP_NAME', 'Anyeong Gift Admin');
define('TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(TIMEZONE);
