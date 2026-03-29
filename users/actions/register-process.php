<?php
session_start();
require_once '../../config/database.php';

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

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password, role, created_at, updated_at)
    VALUES (?, ?, ?, 'user', NOW(), NOW())
");

$success = $stmt->execute([$name, $email, $hashedPassword]);

if (!$success) {
    $_SESSION['error'] = 'Pendaftaran gagal. Coba lagi.';
    $_SESSION['active_auth_view'] = 'register';
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $pdo->lastInsertId();

$_SESSION['success'] = 'Akun berhasil dibuat. Silakan login.';
$_SESSION['active_auth_view'] = 'login';

if ($remember) {
    $_SESSION['remember_email'] = $email;
}

header('Location: ../index.php?page=login');
exit;