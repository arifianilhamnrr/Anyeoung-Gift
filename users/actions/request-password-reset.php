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

if ($user) {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())");
    $stmt->execute([$user['id'], $tokenHash]);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('/actions', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $resetLink = $scheme . '://' . $host . $scriptDir . '/index.php?page=reset_password&token=' . urlencode($token);

    $settings = fetchStoreSettings($pdo);
    $storeName = $settings['store_name'] ?? 'Anyeong Gift';
    $subject = "Reset Password {$storeName}";
    $body = "
        <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
            <h2 style=\"margin: 0 0 8px;\">Permintaan Reset Password</h2>
            <p>Halo <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
            <p>Kami menerima permintaan reset password untuk akun kamu di {$storeName}. Klik tombol di bawah ini untuk melanjutkan:</p>
            <p><a href=\"{$resetLink}\" style=\"display:inline-block;padding:12px 18px;background:#f59e0b;color:#111;text-decoration:none;border-radius:8px;font-weight:bold;\">Reset Password</a></p>
            <p>Link ini berlaku selama 1 jam. Jika kamu tidak merasa meminta reset password, abaikan email ini.</p>
        </div>
    ";
    $textBody = "Halo {$user['name']}, gunakan link berikut untuk reset password: {$resetLink}. Link berlaku 1 jam.";

    sendConfiguredEmail($pdo, $user['email'], $user['name'], $subject, $body, $textBody);
}

$_SESSION['password_reset_success'] = 'Jika email terdaftar, kami sudah mengirim link reset password.';
header('Location: ../index.php?page=forgot_password');
exit;
