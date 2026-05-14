<?php

$host = 'localhost';
$db   = 'anyeoung_gift';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Selaraskan zona waktu sesi MySQL ke Asia/Jakarta (+07:00) supaya NOW(),
    // CURRENT_TIMESTAMP, dan default created_at tersimpan dalam waktu lokal.
    // Pakai offset numerik supaya tidak perlu tabel mysql.time_zone_name.
    try {
        $pdo->exec("SET time_zone = '+07:00'");
    } catch (PDOException $e) {
        // Server mungkin tidak mengizinkan SET time_zone -- diamkan.
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!isset($_SESSION)) {
    session_start();
}

// Helper global (storeName, storeSettings, dsb.) -- aman dipanggil dari user side.
require_once __DIR__ . '/../app/helpers.php';
