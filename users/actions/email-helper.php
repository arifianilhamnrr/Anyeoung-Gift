<?php
use App\Services\MailerService;

require_once __DIR__ . '/../../app/Services/MailerService.php';

function fetchStoreSettings(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM store_settings LIMIT 1");
    $settings = $stmt ? $stmt->fetch() : false;
    return $settings ?: [];
}

function fetchStoreAddress(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM addresses WHERE type = 'store' ORDER BY is_default DESC, id DESC LIMIT 1");
    $address = $stmt ? $stmt->fetch() : false;
    return $address ?: [];
}

function fetchAdminRecipients(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'admin' AND email <> ''");
    $rows = $stmt ? $stmt->fetchAll() : false;
    return $rows ?: [];
}


function formatOrderStatusLabel(string $status): string
{
    $map = [
        'waiting_payment' => 'Menunggu Pembayaran',
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Pembayaran Diterima',
        'ready_pickup' => 'Pesanan Siap',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];

    return $map[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function formatRupiah(int $amount): string
{
    return number_format($amount, 0, ',', '.');
}

function sendConfiguredEmail(PDO $pdo, string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $settings = fetchStoreSettings($pdo);
    $mailer = new MailerService($settings);
    return $mailer->send($toEmail, $toName, $subject, $htmlBody, $textBody);
}
