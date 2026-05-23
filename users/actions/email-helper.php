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

/**
 * Kirim response (termasuk header Location) ke browser sekarang juga, lalu
 * biarkan script PHP terus berjalan di background untuk pekerjaan lama seperti
 * mengirim email via SMTP. Tanpa ini, request HTTP akan menggantung selama
 * proses SMTP berjalan (bisa puluhan detik di server produksi).
 */
function flushResponseAndContinue(): void
{
    // Pastikan script tidak dibunuh saat browser disconnect / redirect.
    ignore_user_abort(true);

    // Naikkan limit eksekusi untuk pekerjaan background (email SMTP).
    @set_time_limit(60);

    // Tutup session supaya request lain dari user yang sama tidak nge-block.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    // Beritahu browser jangan tunggu data lagi.
    if (!headers_sent()) {
        header('Connection: close');
        header('Content-Length: 0');
    }

    // Flush semua output buffer.
    while (ob_get_level() > 0) {
        @ob_end_flush();
    }
    @flush();

    // PHP-FPM: sinyal selesai ke web server, browser dapat redirect langsung.
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}
