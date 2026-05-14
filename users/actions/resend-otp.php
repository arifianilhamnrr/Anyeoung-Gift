<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit;
}

$email = $_SESSION['pending_register_email'] ?? '';

if ($email === '') {
    $_SESSION['error'] = 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM registration_otps WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$row = $stmt->fetch();

if (!$row) {
    unset($_SESSION['pending_register_email']);
    $_SESSION['error'] = 'Sesi pendaftaran tidak ditemukan. Silakan daftar ulang.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

try {
    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
} catch (Throwable $e) {
    $otp = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

$otpHash = hash('sha256', $otp);

$update = $pdo->prepare("
    UPDATE registration_otps
    SET otp_hash = ?, attempts = 0, expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
    WHERE id = ?
");
$update->execute([$otpHash, $row['id']]);

$settings = fetchStoreSettings($pdo);
$storeName = $settings['store_name'] ?? 'Anyeong Gift';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';

$subject = "Kode Verifikasi Akun {$storeName}";
$body = "
    <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
        <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
        <h2 style=\"margin: 0 0 8px;\">Kode Verifikasi Akun</h2>
        <p>Halo <strong>" . htmlspecialchars($row['name']) . "</strong>,</p>
        <p>Kode OTP baru kamu untuk verifikasi akun di {$storeName}:</p>
        <p style=\"font-size:32px; letter-spacing:8px; font-weight:bold; background:#f5f5f5; padding:16px 24px; display:inline-block; border-radius:12px; color:#111;\">{$otp}</p>
        <p>Kode ini berlaku selama <strong>10 menit</strong>. Jangan bagikan kode ini ke siapa pun.</p>
        <p style=\"margin-top:16px;\">Salam hangat,<br>{$storeName}</p>
    </div>
";
$textBody = "Halo {$row['name']},\n";
$textBody .= "Kode OTP baru untuk verifikasi akun kamu di {$storeName}: {$otp}\n";
$textBody .= "Kode berlaku 10 menit.\n";
$textBody .= "Salam, {$storeName}.";

$_SESSION['otp_success'] = 'Kode OTP baru sudah dikirim ke email kamu.';

header('Location: ../index.php?page=verify_otp');

flushResponseAndContinue();

try {
    sendConfiguredEmail($pdo, $row['email'], $row['name'], $subject, $body, $textBody);
} catch (Throwable $e) {
    error_log('Resend OTP email failed for ' . $row['email'] . ': ' . $e->getMessage());
}
exit;
