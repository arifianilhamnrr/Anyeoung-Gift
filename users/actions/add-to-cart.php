<?php
session_start();
require_once '../../config/database.php';

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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][] = $item;

header("Location: ../index.php?page=cart");
exit;