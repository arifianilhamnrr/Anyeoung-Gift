<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$formType = $_POST['form_type'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['profile_error'] = 'User tidak ditemukan.';
    header('Location: ../index.php?page=profile');
    exit;
}

if ($formType === 'update_name') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $_SESSION['profile_error'] = 'Nama tidak boleh kosong.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$name, $userId]);

    $_SESSION['user_name'] = $name;
    $_SESSION['profile_success'] = 'Profil berhasil diperbarui.';
    header('Location: ../index.php?page=profile');
    exit;
}

if ($formType === 'update_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $_SESSION['profile_error'] = 'Semua field password wajib diisi.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    if (!password_verify($currentPassword, $user['password'])) {
        $_SESSION['profile_error'] = 'Password lama tidak sesuai.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['profile_error'] = 'Password baru minimal 6 karakter.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['profile_error'] = 'Konfirmasi password baru tidak cocok.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    if (password_verify($newPassword, $user['password'])) {
        $_SESSION['profile_error'] = 'Password baru tidak boleh sama dengan password lama.';
        header('Location: ../index.php?page=profile');
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);

    $_SESSION['profile_success'] = 'Password berhasil diperbarui.';
    header('Location: ../index.php?page=profile');
    exit;
}

$_SESSION['profile_error'] = 'Permintaan tidak valid.';
header('Location: ../index.php?page=profile');
exit;