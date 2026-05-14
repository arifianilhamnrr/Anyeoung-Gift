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
    SELECT o.*, p.id AS payment_id, p.proof_image
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['order_error'] = 'Pesanan tidak ditemukan.';
    header('Location: ../index.php?page=orders');
    exit;
}

if ($order['status'] !== 'waiting_payment') {
    $_SESSION['order_error'] = 'Pesanan ini sudah tidak bisa dibatalkan.';
    header('Location: ../index.php?page=orders');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);

    if (!empty($order['payment_id'])) {
        $stmt = $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$order['payment_id']]);
    }

    $pdo->commit();

    $_SESSION['order_success'] = 'Pesanan berhasil dibatalkan.';
    header('Location: ../index.php?page=orders');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['order_error'] = 'Gagal membatalkan pesanan.';
    header('Location: ../index.php?page=orders');
    exit;
}