<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($name === '' || $email === '' || $password === '') {
    $_SESSION['error'] = 'Semua field wajib diisi.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password minimal 6 karakter.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    $_SESSION['error'] = 'Email sudah terdaftar.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

// Generate kode OTP 6 digit. Hindari random_int kalau OS belum support
// (sangat jarang), fallback ke mt_rand untuk konsistensi.
try {
    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
} catch (Throwable $e) {
    $otp = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

$otpHash = hash('sha256', $otp);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Hapus OTP lama untuk email yang sama (kalau ada) supaya UNIQUE KEY
// tidak konflik dan pendaftaran ulang bisa dilakukan setelah expired.
$pdo->prepare("DELETE FROM registration_otps WHERE email = ?")->execute([$email]);

$stmt = $pdo->prepare("
    INSERT INTO registration_otps (name, email, password_hash, otp_hash, attempts, expires_at, created_at)
    VALUES (?, ?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())
");

$success = $stmt->execute([$name, $email, $hashedPassword, $otpHash]);

if (!$success) {
    $_SESSION['error'] = 'Pendaftaran gagal. Coba lagi.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

// Simpan email pendaftaran di session supaya halaman verifikasi OTP tahu
// akun mana yang sedang diverifikasi. Password mentah TIDAK pernah disimpan
// di session — sudah di-hash dan ditampung di registration_otps.
$_SESSION['pending_register_email'] = $email;
if ($remember) {
    $_SESSION['remember_email'] = $email;
}

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
        <p>Halo <strong>" . htmlspecialchars($name) . "</strong>,</p>
        <p>Terima kasih sudah mendaftar di {$storeName}. Gunakan kode OTP berikut untuk menyelesaikan pendaftaran kamu:</p>
        <p style=\"font-size:32px; letter-spacing:8px; font-weight:bold; background:#f5f5f5; padding:16px 24px; display:inline-block; border-radius:12px; color:#111;\">{$otp}</p>
        <p>Kode ini berlaku selama <strong>10 menit</strong>. Jangan bagikan kode ini ke siapa pun.</p>
        <p>Kalau kamu merasa tidak mendaftar di {$storeName}, abaikan email ini.</p>
        <p style=\"margin-top:16px;\">Salam hangat,<br>{$storeName}</p>
    </div>
";
$textBody = "Halo {$name},\n";
$textBody .= "Kode OTP verifikasi akun kamu di {$storeName} adalah: {$otp}\n";
$textBody .= "Kode berlaku 10 menit. Abaikan email ini jika kamu tidak merasa mendaftar.\n";
$textBody .= "Salam, {$storeName}.";

// Kirim ke browser dulu baru SMTP/API jalan di background supaya halaman
// tidak menggantung saat hosting respon SMTP lambat. (Pola sama seperti
// checkout-process.php.)
$_SESSION['success'] = 'Kode OTP sudah dikirim ke email kamu. Cek inbox / folder spam, lalu masukkan kode di bawah.';

header('Location: ../index.php?page=verify_otp');

flushResponseAndContinue();

try {
    sendConfiguredEmail($pdo, $email, $name, $subject, $body, $textBody);
} catch (Throwable $e) {
    error_log('Register OTP email failed for ' . $email . ': ' . $e->getMessage());
}
exit;
