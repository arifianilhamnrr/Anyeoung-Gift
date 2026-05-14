<?php
// Mendeteksi otomatis Base URL berdasarkan environment (Localhost / Hosting)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

define('BASE_URL', $protocol . "://" . $host . $scriptDir);

// Konfigurasi Database MariaDB
define('DB_HOST', 'localhost');
define('DB_USER', 'anyeong-gift');
define('DB_PASS', 'hM8fkwKfxK34bLn4');
define('DB_NAME', 'anyeong-gift');

// Konfigurasi Aplikasi
define('APP_NAME', 'Anyeong Gift Admin');
define('TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(TIMEZONE);

// Helper global (storeName, storeSettings, dsb.)
require_once __DIR__ . '/app/helpers.php';