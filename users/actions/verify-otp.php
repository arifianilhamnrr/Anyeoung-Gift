<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

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

$userName = $row['name'];
$userEmail = $row['email'];

$_SESSION['success'] = 'Akun berhasil diverifikasi. Silakan login.';
$_SESSION['active_auth_view'] = 'login';
header('Location: ../index.php?page=login');

flushResponseAndContinue();

try {
    $settings = fetchStoreSettings($pdo);
    $storeName = $settings['store_name'] ?? 'Anyeong Gift';

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('/actions', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $loginLink = $scheme . '://' . $host . $scriptDir . '/index.php?page=login';
    $logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';

    $subject = "Selamat Datang di {$storeName}!";
    $body = "
        <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
            <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
            <h2 style=\"margin: 0 0 8px;\">Akun Berhasil Dibuat</h2>
            <p>Halo <strong>" . htmlspecialchars($userName) . "</strong>,</p>
            <p>Selamat! Akun kamu di <strong>{$storeName}</strong> sudah berhasil diverifikasi dan siap digunakan.</p>
            <table style=\"width:100%;border-collapse:collapse;margin:12px 0 8px;\">
                <tr><td style=\"padding:6px 0;\">Nama</td><td style=\"padding:6px 0;text-align:right;\"><strong>" . htmlspecialchars($userName) . "</strong></td></tr>
                <tr><td style=\"padding:6px 0;\">Email</td><td style=\"padding:6px 0;text-align:right;\"><strong>" . htmlspecialchars($userEmail) . "</strong></td></tr>
            </table>
            <p>Kamu sekarang bisa masuk, menjelajahi katalog hadiah, dan membuat pesanan kapan saja.</p>
            <p><a href=\"{$loginLink}\" style=\"display:inline-block;padding:12px 18px;background:#f59e0b;color:#111;text-decoration:none;border-radius:8px;font-weight:bold;\">Masuk ke Akun</a></p>
            <p style=\"margin-top:16px;\">Terima kasih sudah bergabung dengan kami.<br>Salam hangat,<br>{$storeName}</p>
        </div>
    ";
    $textBody = "Halo {$userName},\n";
    $textBody .= "Selamat! Akun kamu di {$storeName} sudah berhasil diverifikasi dan siap digunakan.\n\n";
    $textBody .= "Nama: {$userName}\n";
    $textBody .= "Email: {$userEmail}\n\n";
    $textBody .= "Kamu sekarang bisa masuk, menjelajahi katalog hadiah, dan membuat pesanan kapan saja.\n";
    $textBody .= "Masuk ke akun: {$loginLink}\n\n";
    $textBody .= "Terima kasih sudah bergabung dengan kami.\n";
    $textBody .= "Salam, {$storeName}.";

    sendConfiguredEmail($pdo, $userEmail, $userName, $subject, $body, $textBody);
} catch (Throwable $e) {
    error_log('Register welcome email failed for ' . $userEmail . ': ' . $e->getMessage());
}
exit;
