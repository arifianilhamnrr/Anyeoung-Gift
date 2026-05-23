<?php
require_once '../config/database.php';
require_once __DIR__ . '/actions/cart-helper.php';

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

// Sinkronkan keranjang dari DB ke session di setiap request. Pakai try/catch
// supaya error DB tidak menggagalkan render halaman -- kalau gagal, biarkan
// session cart yang lama (kalau ada) atau kosong.
try {
    syncCartSession($pdo);
} catch (Exception $e) {
    // Abaikan: navbar/halaman cart akan menampilkan keranjang kosong saja.
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
    'forgot_password',
    'reset_password',
    'verify_otp',
    'profile',
    'orders',
    'addresses',
    'payment_upload'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// Bucket "Bayar Sekarang" hanya hidup di halaman checkout. Begitu user pindah
// ke halaman lain (kembali ke produk, cart, dll), batalkan intent buy now.
if ($page !== 'checkout' && isset($_SESSION['buy_now'])) {
    unset($_SESSION['buy_now']);
}

// halaman yang TIDAK perlu login
$public_pages = ['home', 'products', 'product', 'login', 'register', 'forgot_password', 'reset_password', 'verify_otp'];

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
$auth_pages = ['login', 'register', 'forgot_password', 'reset_password', 'verify_otp'];

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

    <?php if ($page === 'home'): ?>
        <?php include 'components/footer.php'; ?>
    <?php endif; ?>

</body>

</html>

<?php endif; ?>
