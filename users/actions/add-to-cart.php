<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/cart-helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?page=login");
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$total_price = (int) ($_POST['total_price'] ?? 0);

if (!$product_id) {
    header("Location: ../index.php?page=home");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: ../index.php?page=home");
    exit;
}

$options = $_POST['options'] ?? [];
$custom_input = trim($_POST['custom_input'] ?? '');

$item = [
    'product_id' => (int) $product_id,
    'product_name' => $product['name'],
    'price' => $total_price,
    'options' => $options,
    'custom_input' => $custom_input !== '' ? $custom_input : null,
];

$buyNow = isset($_POST['buy_now']) && $_POST['buy_now'] === '1';

if ($buyNow) {
    // Mode "Bayar Sekarang": pakai bucket session terpisah agar checkout hanya
    // memproses produk ini saja dan tidak menggabung dengan isi keranjang.
    $_SESSION['buy_now'] = [$item];
    header("Location: ../index.php?page=checkout");
    exit;
}

// Mode "Tambah ke Keranjang": simpan ke DB agar tetap ada setelah logout,
// lalu refresh cache session dari DB.
$userId = (int) $_SESSION['user_id'];
addCartItem($pdo, $userId, $item);
syncCartSession($pdo);

header("Location: ../index.php?page=product&id=" . (int) $product_id . "&added=1");
exit;
