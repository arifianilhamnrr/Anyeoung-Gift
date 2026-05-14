<?php
session_start();
require_once '../../config/database.php';

$token = $_POST['token'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($token === '') {
    $_SESSION['reset_password_error'] = 'Token reset tidak ditemukan.';
    header('Location: ../index.php?page=reset_password');
    exit;
}

if ($newPassword === '' || $confirmPassword === '') {
    $_SESSION['reset_password_error'] = 'Semua field password wajib diisi.';
    header('Location: ../index.php?page=reset_password&token=' . urlencode($token));
    exit;
}

if (strlen($newPassword) < 6) {
    $_SESSION['reset_password_error'] = 'Password baru minimal 6 karakter.';
    header('Location: ../index.php?page=reset_password&token=' . urlencode($token));
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['reset_password_error'] = 'Konfirmasi password tidak cocok.';
    header('Location: ../index.php?page=reset_password&token=' . urlencode($token));
    exit;
}

$tokenHash = hash('sha256', $token);
$stmt = $pdo->prepare("
    SELECT pr.user_id, u.email
    FROM password_resets pr
    JOIN users u ON u.id = pr.user_id
    WHERE pr.token_hash = ? AND pr.expires_at > NOW()
    LIMIT 1
");
$stmt->execute([$tokenHash]);
$reset = $stmt->fetch();

if (!$reset) {
    $_SESSION['reset_password_error'] = 'Link reset tidak valid atau sudah kadaluarsa.';
    header('Location: ../index.php?page=reset_password&token=' . urlencode($token));
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$hashedPassword, $reset['user_id']]);

$pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);

$_SESSION['success'] = 'Password berhasil direset. Silakan login kembali.';
$_SESSION['active_auth_view'] = 'login';
header('Location: ../index.php?page=login');
exit;
