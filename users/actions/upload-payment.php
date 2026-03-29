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
    $_SESSION['upload_error'] = 'Order tidak valid.';
    header('Location: ../index.php?page=orders');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        o.id AS order_id,
        p.id AS payment_id,
        p.status AS payment_status,
        pm.type AS payment_method_type
    FROM orders o
    INNER JOIN payments p ON p.order_id = o.id
    INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['upload_error'] = 'Pesanan tidak ditemukan.';
    header('Location: ../index.php?page=orders');
    exit;
}

if ($order['payment_method_type'] === 'onsite') {
    $_SESSION['upload_error'] = 'Pesanan ini tidak memerlukan upload bukti pembayaran.';
    header('Location: ../index.php?page=orders');
    exit;
}

if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['upload_error'] = 'File bukti pembayaran wajib diupload.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

$file = $_FILES['proof_image'];
$maxSize = 2 * 1024 * 1024;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

$originalName = $file['name'];
$tmpName = $file['tmp_name'];
$fileSize = (int) $file['size'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    $_SESSION['upload_error'] = 'Format file tidak didukung.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

if ($fileSize > $maxSize) {
    $_SESSION['upload_error'] = 'Ukuran file maksimal 2MB.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/payments/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newFileName = 'payment_' . $orderId . '_' . time() . '.' . $extension;
$destination = $uploadDir . $newFileName;

if (!move_uploaded_file($tmpName, $destination)) {
    $_SESSION['upload_error'] = 'Gagal menyimpan file upload.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

$stmt = $pdo->prepare("
    SELECT proof_image FROM payments
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$order['payment_id']]);
$oldPayment = $stmt->fetch();

if ($oldPayment && !empty($oldPayment['proof_image'])) {
    $oldPath = $uploadDir . $oldPayment['proof_image'];
    if (is_file($oldPath)) {
        unlink($oldPath);
    }
}

$stmt = $pdo->prepare("
    UPDATE payments
    SET proof_image = ?, paid_at = NOW(), status = 'pending'
    WHERE id = ?
");
$stmt->execute([$newFileName, $order['payment_id']]);

$_SESSION['order_success'] = 'Bukti pembayaran berhasil diupload. Silakan tunggu konfirmasi admin.';
header('Location: ../index.php?page=orders');
exit;