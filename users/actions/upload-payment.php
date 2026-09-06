<?php
session_start();
require_once '../../config/database.php';
require_once __DIR__ . '/email-helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$log = static function ($event, $data = []) { error_log('anyeong_upload ' . $event . ' ' . json_encode($data, JSON_UNESCAPED_SLASHES)); };
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { $log('invalid_method', ['method' => $_SERVER['REQUEST_METHOD'] ?? '']); $_SESSION['upload_error'] = 'Permintaan upload tidak valid.'; header('Location: ../index.php?page=orders'); exit; }
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

if (!isset($_FILES['proof_image'])) {
    $log('missing_file', ['order_id' => $orderId, 'user_id' => $userId]);
    $_SESSION['upload_error'] = 'File bukti pembayaran wajib diupload.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

$file = $_FILES['proof_image'];
if ($file['error'] !== UPLOAD_ERR_OK) { $codes = [UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas server.', UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar.', UPLOAD_ERR_PARTIAL => 'Upload terputus.', UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara server tidak tersedia.', UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file.']; $msg = $codes[$file['error']] ?? ('Upload gagal (kode ' . (int)$file['error'] . ').'); $log('file_error', ['order_id'=>$orderId, 'user_id'=>$userId, 'code'=>$file['error']]); $_SESSION['upload_error']=$msg; header('Location: ../index.php?page=payment_upload&order_id=' . $orderId); exit; }
$maxSize = 5 * 1024 * 1024;
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
    $_SESSION['upload_error'] = 'Ukuran file maksimal 5MB.';
    header('Location: ../index.php?page=payment_upload&order_id=' . $orderId);
    exit;
}

// 🔥 PERUBAHAN DI SINI: Path diubah mengarah ke folder public/uploads/payments/
$uploadDir = dirname(__DIR__, 2) . '/public/uploads/payments/';

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

// Hapus file lama jika ada (juga diarahkan ke path public)
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
$log('success', ['order_id'=>$orderId,'user_id'=>$userId,'size'=>$fileSize,'file'=>$newFileName]);

// Siapkan email notifikasi (dikirim setelah response di-flush ke browser).
$emailJob = null;
try {
    $storeSettings = fetchStoreSettings($pdo);
    $storeName = $storeSettings['store_name'] ?? 'Anyeong Gift';
    $adminRecipients = fetchAdminRecipients($pdo);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';

    if (!empty($adminRecipients)) {
        $stmt = $pdo->prepare("
            SELECT o.id, o.total_price, u.name AS customer_name, u.email AS customer_email,
                   pm.name AS method_name
            FROM orders o
            JOIN users u ON u.id = o.user_id
            JOIN payments p ON p.order_id = o.id
            JOIN payment_methods pm ON pm.id = p.payment_method_id
            WHERE o.id = ?
            LIMIT 1
        ");
        $stmt->execute([$orderId]);
        $info = $stmt->fetch();

        $orderNumber = 'ORD-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);
        $total = isset($info['total_price']) ? number_format((int) $info['total_price'], 0, ',', '.') : '0';
        $customerName = $info['customer_name'] ?? 'Pelanggan';
        $customerEmail = $info['customer_email'] ?? '';
        $methodName = $info['method_name'] ?? 'Pembayaran Online';

        $subject = "Bukti Pembayaran Masuk {$orderNumber} - {$storeName}";
        $body = "
            <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
                <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
                <h2 style=\"margin: 0 0 8px;\">Bukti Pembayaran Diterima</h2>
                <p>Pembeli telah mengunggah bukti pembayaran. Silakan lakukan verifikasi.</p>
                <table style=\"width:100%;border-collapse:collapse;margin:12px 0 8px;\">
                    <tr><td style=\"padding:6px 0;\">Nomor Pesanan</td><td style=\"padding:6px 0;text-align:right;\"><strong>{$orderNumber}</strong></td></tr>
                    <tr><td style=\"padding:6px 0;\">Nama Pembeli</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($customerName) . "</td></tr>
                    <tr><td style=\"padding:6px 0;\">Email Pembeli</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($customerEmail) . "</td></tr>
                    <tr><td style=\"padding:6px 0;\">Metode Pembayaran</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($methodName) . "</td></tr>
                    <tr><td style=\"padding:6px 0;font-weight:bold;\">Total</td><td style=\"padding:6px 0;text-align:right;font-weight:bold;\">Rp {$total}</td></tr>
                </table>
                <p>Silakan cek dashboard admin untuk memverifikasi pembayaran.</p>
            </div>
        ";
        $textBody = "Bukti pembayaran masuk untuk {$orderNumber}.\n";
        $textBody .= "Nama pembeli: {$customerName}. Email: {$customerEmail}.\n";
        $textBody .= "Metode pembayaran: {$methodName}. Total: Rp {$total}.\n";
        $textBody .= "Silakan verifikasi di dashboard admin.";

        $emailJob = [
            'recipients' => $adminRecipients,
            'subject' => $subject,
            'body' => $body,
            'text' => $textBody,
        ];
    }
} catch (Exception $e) {
    error_log('Upload payment email prep failed: ' . $e->getMessage());
}

$_SESSION['order_success'] = 'Bukti pembayaran berhasil diupload. Silakan tunggu konfirmasi admin.';
header('Location: ../index.php?page=orders');

// Lepas response ke browser dulu, lalu kirim email di background supaya
// halaman tidak nge-loading lama menunggu SMTP.
flushResponseAndContinue();

if ($emailJob !== null) {
    try {
        foreach ($emailJob['recipients'] as $admin) {
            $adminEmail = $admin['email'] ?? '';
            if ($adminEmail === '') {
                continue;
            }
            $adminName = $admin['name'] ?? 'Admin';
            sendConfiguredEmail(
                $pdo,
                $adminEmail,
                $adminName,
                $emailJob['subject'],
                $emailJob['body'],
                $emailJob['text']
            );
        }
    } catch (Exception $e) {
        error_log('Upload payment background email failed: ' . $e->getMessage());
    }
}

exit;