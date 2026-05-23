<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_POST['set_default_id'])) {
    $setDefaultId = (int) $_POST['set_default_id'];

    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ? AND type = 'user'");
    $stmt->execute([$userId]);

    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ? AND type = 'user'");
    $stmt->execute([$setDefaultId, $userId]);

    $_SESSION['address_success'] = 'Alamat utama berhasil diperbarui.';
    header('Location: ../index.php?page=addresses');
    exit;
}

$recipientName = trim($_POST['recipient_name'] ?? '');
$whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
$addressText = trim($_POST['address_text'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$isDefault = isset($_POST['is_default']) ? 1 : 0;

if ($recipientName === '' || $whatsappNumber === '' || $addressText === '') {
    $_SESSION['address_error'] = 'Nama penerima, nomor WhatsApp, dan alamat wajib diisi.';
    header('Location: ../index.php?page=addresses');
    exit;
}

if ($isDefault) {
    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ? AND type = 'user'");
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ? AND type = 'user'");
    $stmt->execute([$userId]);
    $count = (int) $stmt->fetchColumn();

    if ($count === 0) {
        $isDefault = 1;
    }
}

$stmt = $pdo->prepare("
    INSERT INTO addresses (
        user_id,
        type,
        recipient_name,
        whatsapp_number,
        address_text,
        notes,
        is_default,
        created_at,
        updated_at
    ) VALUES (?, 'user', ?, ?, ?, ?, ?, NOW(), NOW())
");

$stmt->execute([
    $userId,
    $recipientName,
    $whatsappNumber,
    $addressText,
    $notes !== '' ? $notes : null,
    $isDefault
]);

$_SESSION['address_success'] = 'Alamat berhasil ditambahkan.';
header('Location: ../index.php?page=addresses');
exit;