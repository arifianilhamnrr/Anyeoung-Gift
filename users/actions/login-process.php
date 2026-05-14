<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['error'] = 'Email dan password wajib diisi.';
    $_SESSION['active_auth_view'] = 'login';
    header('Location: ../index.php?page=login');
    exit;
}

// CEK USER
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// VALIDASI
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Email atau password salah.';
    $_SESSION['active_auth_view'] = 'login';
    header('Location: ../index.php?page=login');
    exit;
}

// LOGIN SUCCESS
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

// REDIRECT KE HOME
header('Location: ../index.php?page=home');
exit;