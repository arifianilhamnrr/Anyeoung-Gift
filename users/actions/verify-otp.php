<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit;
}

$email = $_SESSION['pending_register_email'] ?? '';
$otp = trim($_POST['otp'] ?? '');

if ($email === '') {
    $_SESSION['error'] = 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

if ($otp === '' || !preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['otp_error'] = 'Kode OTP harus berupa 6 digit angka.';
    header('Location: ../index.php?page=verify_otp');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, name, email, password_hash, otp_hash, attempts, expires_at
    FROM registration_otps
    WHERE email = ?
    LIMIT 1
");
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row) {
    unset($_SESSION['pending_register_email']);
    $_SESSION['error'] = 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

// Cek expired.
if (strtotime($row['expires_at']) < time()) {
    $pdo->prepare("DELETE FROM registration_otps WHERE id = ?")->execute([$row['id']]);
    unset($_SESSION['pending_register_email']);
    $_SESSION['error'] = 'Kode OTP sudah kadaluarsa. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

// Limit percobaan ke 5x supaya tidak bisa di-brute force.
$maxAttempts = 5;
if ((int) $row['attempts'] >= $maxAttempts) {
    $pdo->prepare("DELETE FROM registration_otps WHERE id = ?")->execute([$row['id']]);
    unset($_SESSION['pending_register_email']);
    $_SESSION['error'] = 'Kamu sudah salah memasukkan kode OTP terlalu sering. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

if (!hash_equals($row['otp_hash'], hash('sha256', $otp))) {
    $pdo->prepare("UPDATE registration_otps SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
    $remaining = $maxAttempts - ((int) $row['attempts'] + 1);
    $_SESSION['otp_error'] = $remaining > 0
        ? "Kode OTP salah. Sisa percobaan: {$remaining}."
        : 'Kode OTP salah.';
    header('Location: ../index.php?page=verify_otp');
    exit;
}

// OTP benar. Buat akun user untuk pertama kalinya.
$insertUser = $pdo->prepare("
    INSERT INTO users (name, email, password, role, created_at, updated_at)
    VALUES (?, ?, ?, 'user', NOW(), NOW())
");
$success = $insertUser->execute([$row['name'], $row['email'], $row['password_hash']]);

if (!$success) {
    $_SESSION['otp_error'] = 'Gagal menyelesaikan pendaftaran. Coba lagi.';
    header('Location: ../index.php?page=verify_otp');
    exit;
}

$pdo->prepare("DELETE FROM registration_otps WHERE id = ?")->execute([$row['id']]);
unset($_SESSION['pending_register_email']);

$_SESSION['success'] = 'Akun berhasil diverifikasi. Silakan login.';
$_SESSION['active_auth_view'] = 'login';
header('Location: ../index.php?page=login');
exit;
