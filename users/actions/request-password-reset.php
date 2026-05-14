<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['password_reset_error'] = 'Masukkan alamat email yang valid.';
    header('Location: ../index.php?page=forgot_password');
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND role = 'user' LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Kalau akun belum terdaftar, beri tahu user secara eksplisit (sesuai
// permintaan owner). Ini mengorbankan perlindungan account-enumeration
// demi UX yang lebih jelas.
if (!$user) {
    $_SESSION['password_reset_error'] = 'Akun belum terdaftar. Silakan daftar akun terlebih dahulu.';
    $_SESSION['password_reset_email'] = $email;
    header('Location: ../index.php?page=forgot_password');
    exit;
}

$token = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);

$pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())");
$stmt->execute([$user['id'], $tokenHash]);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('/actions', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
$resetLink = $scheme . '://' . $host . $scriptDir . '/index.php?page=reset_password&token=' . urlencode($token);
$logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';

$settings = fetchStoreSettings($pdo);
$storeName = $settings['store_name'] ?? 'Anyeong Gift';
$subject = "Permintaan Reset Password - {$storeName}";
$body = "
    <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
        <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
        <h2 style=\"margin: 0 0 8px;\">Permintaan Reset Password</h2>
        <p>Halo <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
        <p>Kami menerima permintaan reset password untuk akun kamu di {$storeName}.</p>
        <p>Gunakan tombol berikut untuk membuat password baru:</p>
        <p><a href=\"{$resetLink}\" style=\"display:inline-block;padding:12px 18px;background:#f59e0b;color:#111;text-decoration:none;border-radius:8px;font-weight:bold;\">Buat Password Baru</a></p>
        <p>Link ini berlaku selama 1 jam. Jika kamu tidak merasa meminta reset password, abaikan email ini.</p>
        <p style=\"margin-top:16px;\">Salam hangat,<br>{$storeName}</p>
    </div>
";
$textBody = "Halo {$user['name']},\n";
$textBody .= "Kami menerima permintaan reset password untuk akun kamu di {$storeName}.\n";
$textBody .= "Gunakan link berikut untuk membuat password baru (berlaku 1 jam): {$resetLink}\n";
$textBody .= "Jika kamu tidak merasa meminta reset password, abaikan email ini.\n";
$textBody .= "Salam, {$storeName}.";

$_SESSION['password_reset_success'] = 'Link reset password sudah dikirim ke email kamu. Cek inbox / folder spam.';

header('Location: ../index.php?page=forgot_password');

flushResponseAndContinue();

try {
    sendConfiguredEmail($pdo, $user['email'], $user['name'], $subject, $body, $textBody);
} catch (Throwable $e) {
    error_log('Password reset email failed for ' . $user['email'] . ': ' . $e->getMessage());
}
exit;
