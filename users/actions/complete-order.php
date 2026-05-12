<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = (int) ($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    $_SESSION['order_error'] = 'Pesanan tidak valid.';
    header('Location: ../index.php?page=orders');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, status FROM orders
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['order_error'] = 'Pesanan tidak ditemukan.';
    header('Location: ../index.php?page=orders');
    exit;
}

// Pesanan hanya bisa diselesaikan pelanggan jika sudah berstatus 'ready_pickup'.
if ($order['status'] !== 'ready_pickup') {
    $_SESSION['order_error'] = 'Pesanan ini belum bisa diselesaikan.';
    header('Location: ../index.php?page=orders');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);

    $_SESSION['order_success'] = 'Terima kasih! Pesanan kamu sudah ditandai sebagai selesai.';
    header('Location: ../index.php?page=orders');
    exit;

} catch (Exception $e) {
    $_SESSION['order_error'] = 'Gagal menyelesaikan pesanan.';
    header('Location: ../index.php?page=orders');
    exit;
}
