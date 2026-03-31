<?php
require_once '../config/database.php';

try {
    $pdo->exec("
        UPDATE orders 
        SET status = 'cancelled' 
        WHERE status = 'waiting_payment' 
        AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
} catch (PDOException $e) {
    // Abaikan error agar tidak mengganggu halaman utama jika ada masalah
}

if (!isset($_SESSION)) {
    session_start();
}

$page = $_GET['page'] ?? 'home';

$allowed_pages = [
    'home',
    'products',
    'product',
    'cart',
    'checkout',
    'login',
    'register',
    'profile',
    'orders',
    'addresses',
    'payment_upload'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// halaman yang TIDAK perlu login
$public_pages = ['home', 'products', 'product', 'login', 'register'];

// halaman yang WAJIB login
$protected_pages = [
    'cart',
    'checkout',
    'profile',
    'orders',
    'addresses',
    'payment_upload'
];

// 🔥 AUTH GUARD
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    // simpan tujuan biar bisa balik setelah login (optional)
    $_SESSION['redirect_after_login'] = $page;

    header('Location: index.php?page=login');
    exit;
}

// halaman auth (tanpa layout)
$auth_pages = ['login', 'register'];

$use_sidebar = in_array($page, ['profile', 'orders', 'addresses']);
?>

<?php if (in_array($page, $auth_pages)): ?>

    <?php include "pages/$page.php"; ?>

<?php else: ?>

    <?php include 'components/header.php'; ?>

    <div class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-8">

            <?php if ($use_sidebar): ?>
                <div class="flex gap-0">
                    <main class="flex-1">
                        <?php include "pages/$page.php"; ?>
                    </main>
                </div>
            <?php else: ?>
                <main>
                    <?php include "pages/$page.php"; ?>
                </main>
            <?php endif; ?>

        </div>
    </div>

    <?php include 'components/footer.php'; ?>

<?php endif; ?>