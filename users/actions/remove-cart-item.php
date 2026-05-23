<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/cart-helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

// Field utama sekarang `cart_item_id` (id baris di tabel cart_items). Tetap
// terima `index` lama sebagai fallback supaya transisi tidak memutus form lama
// yang sudah ter-render di browser user.
$cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;

if ($cartItemId <= 0 && isset($_POST['index']) && isset($_SESSION['cart'][$_POST['index']]['cart_item_id'])) {
    $cartItemId = (int) $_SESSION['cart'][$_POST['index']]['cart_item_id'];
}

if ($cartItemId > 0) {
    removeCartItem($pdo, (int) $_SESSION['user_id'], $cartItemId);
    syncCartSession($pdo);
}

header('Location: ../index.php?page=cart');
exit;
