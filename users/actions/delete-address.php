<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$addressId = (int) ($_POST['address_id'] ?? 0);

if ($addressId <= 0) {
    $_SESSION['address_error'] = 'Alamat tidak valid.';
    header('Location: ../index.php?page=addresses');
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM addresses
    WHERE id = ? AND user_id = ? AND type = 'user'
    LIMIT 1
");
$stmt->execute([$addressId, $userId]);
$address = $stmt->fetch();

if (!$address) {
    $_SESSION['address_error'] = 'Alamat tidak ditemukan.';
    header('Location: ../index.php?page=addresses');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ? AND type = 'user'");
$stmt->execute([$addressId, $userId]);

if ((int)$address['is_default'] === 1) {
    $stmt = $pdo->prepare("
        SELECT id FROM addresses
        WHERE user_id = ? AND type = 'user'
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $nextAddressId = $stmt->fetchColumn();

    if ($nextAddressId) {
        $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
        $stmt->execute([$nextAddressId]);
    }
}

$_SESSION['address_success'] = 'Alamat berhasil dihapus.';
header('Location: ../index.php?page=addresses');
exit;