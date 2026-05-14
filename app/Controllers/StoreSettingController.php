<?php
namespace App\Controllers;

use App\Core\Controller;
require_once __DIR__ . '/../Models/StoreSettingModel.php';
use App\Models\StoreSettingModel;

class StoreSettingController extends Controller {

    /**
     * Daftar driver email yang didukung. SMTP tetap jadi default fallback.
     */
    private const SUPPORTED_DRIVERS = ['smtp', 'brevo', 'mailersend', 'sendpulse'];

    // Method GET: Mengambil data untuk ditampilkan di form
    public function index() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        try {
            $model = new StoreSettingModel();
            $data = $model->getSettings();
            // Kosongkan semua nilai sensitif (password & API key) supaya
            // tidak ikut ke client. Frontend cukup tahu apakah field sudah
            // terisi atau belum lewat flag *_set di bawah.
            if ($data) {
                $sensitiveFields = [
                    'email_smtp_password',
                    'email_brevo_api_key',
                    'email_mailersend_api_key',
                    'email_sendpulse_client_secret',
                ];
                foreach ($sensitiveFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $data[$field . '_set'] = $data[$field] !== null && $data[$field] !== '';
                        $data[$field] = '';
                    }
                }
                if (empty($data['email_driver'])) {
                    $data['email_driver'] = 'smtp';
                }
            }
            $adminUser = $model->getAdminUser();
            $storeAddress = $model->getStoreAddress();
            if (!$data) {
                $data = [];
            }
            $data['admin_name'] = $adminUser['name'] ?? '';
            $data['admin_email'] = $adminUser['email'] ?? '';
            $data['store_address_text'] = $storeAddress['address_text'] ?? '';
            return $this->jsonResponse(['status' => 'success', 'data' => $data ?: []]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Method POST: Menyimpan data dari form
    public function update() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        $data = $this->getJsonInput();

        if (empty($data['store_name']) || empty($data['whatsapp_admin'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Nama Toko dan WhatsApp wajib diisi!'], 400);
        }

        try {
            $model = new StoreSettingModel();
            $existing = $model->getSettings() ?: [];
            $emailEnabled = !empty($data['email_enabled']) ? 1 : 0;

            // Driver email yang dipilih admin. Default smtp.
            $driver = strtolower(trim($data['email_driver'] ?? ($existing['email_driver'] ?? 'smtp')));
            if (!in_array($driver, self::SUPPORTED_DRIVERS, true)) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Driver email tidak dikenali.'], 400);
            }

            // Helper: kalau field credential di form kosong, pertahankan
            // nilai yang sudah ada di DB (jadi admin tidak harus paste ulang
            // setiap kali simpan).
            $keepExisting = function (string $key) use ($data, $existing): string {
                $new = trim((string) ($data[$key] ?? ''));
                if ($new !== '') {
                    return $new;
                }
                return (string) ($existing[$key] ?? '');
            };

            // --- SMTP ---
            $emailPassword = $keepExisting('email_smtp_password');
            $emailEncryption = strtolower(trim($data['email_smtp_encryption'] ?? ($existing['email_smtp_encryption'] ?? 'tls')));
            if (!in_array($emailEncryption, ['tls', 'ssl', ''], true)) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Enkripsi email tidak valid.'], 400);
            }
            $emailHost = trim($data['email_smtp_host'] ?? ($existing['email_smtp_host'] ?? ''));
            $emailPort = (int) ($data['email_smtp_port'] ?? ($existing['email_smtp_port'] ?? 0));
            $emailUsername = trim($data['email_smtp_username'] ?? ($existing['email_smtp_username'] ?? ''));

            // --- Sender (dipakai semua driver) ---
            $emailFromName = trim($data['email_from_name'] ?? ($existing['email_from_name'] ?? $data['store_name']));
            $emailFromAddress = trim($data['email_from_address'] ?? ($existing['email_from_address'] ?? $emailUsername));

            // --- API drivers ---
            $brevoKey = $keepExisting('email_brevo_api_key');
            $mailersendKey = $keepExisting('email_mailersend_api_key');
            $sendpulseClientId = trim($data['email_sendpulse_client_id'] ?? ($existing['email_sendpulse_client_id'] ?? ''));
            $sendpulseClientSecret = $keepExisting('email_sendpulse_client_secret');

            // Validasi: kalau email diaktifkan, field driver yang dipilih
            // wajib lengkap. Driver lain tidak divalidasi supaya admin bisa
            // tetap menyimpan credential untuk dipakai nanti.
            if ($emailEnabled) {
                if ($emailFromAddress === '' || !filter_var($emailFromAddress, FILTER_VALIDATE_EMAIL)) {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Email pengirim tidak valid.'], 400);
                }
                if ($driver === 'smtp') {
                    if ($emailHost === '' || $emailPort <= 0 || $emailUsername === '' || $emailPassword === '') {
                        return $this->jsonResponse(['status' => 'error', 'message' => 'Lengkapi konfigurasi SMTP sebelum diaktifkan.'], 400);
                    }
                } elseif ($driver === 'brevo') {
                    if ($brevoKey === '') {
                        return $this->jsonResponse(['status' => 'error', 'message' => 'API Key Brevo wajib diisi.'], 400);
                    }
                } elseif ($driver === 'mailersend') {
                    if ($mailersendKey === '') {
                        return $this->jsonResponse(['status' => 'error', 'message' => 'API Key MailerSend wajib diisi.'], 400);
                    }
                } elseif ($driver === 'sendpulse') {
                    if ($sendpulseClientId === '' || $sendpulseClientSecret === '') {
                        return $this->jsonResponse(['status' => 'error', 'message' => 'Client ID & Client Secret SendPulse wajib diisi.'], 400);
                    }
                }
            }

            $model->updateSettings([
                'store_name' => $data['store_name'],
                'whatsapp_admin' => $data['whatsapp_admin'],
                'whatsapp_message_template' => $data['whatsapp_message_template'] ?? '',
                'email_enabled' => $emailEnabled,
                'email_driver' => $driver,
                'email_smtp_host' => $emailHost,
                'email_smtp_port' => $emailPort,
                'email_smtp_username' => $emailUsername,
                'email_smtp_password' => $emailPassword,
                'email_smtp_encryption' => $emailEncryption,
                'email_brevo_api_key' => $brevoKey,
                'email_mailersend_api_key' => $mailersendKey,
                'email_sendpulse_client_id' => $sendpulseClientId,
                'email_sendpulse_client_secret' => $sendpulseClientSecret,
                'email_from_name' => $emailFromName,
                'email_from_address' => $emailFromAddress,
            ]);

            // --- Update profil admin (nama & email toko) ---
            // Hanya dijalankan kalau payload menyertakan field-nya. Email
            // divalidasi karena dipakai juga untuk login.
            $existingAdmin = $model->getAdminUser();
            if ($existingAdmin) {
                $adminName = isset($data['admin_name']) ? trim((string) $data['admin_name']) : ($existingAdmin['name'] ?? '');
                $adminEmail = isset($data['admin_email']) ? trim((string) $data['admin_email']) : ($existingAdmin['email'] ?? '');
                if ($adminEmail !== '' && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Email toko tidak valid.'], 400);
                }
                if ($adminName === '') {
                    $adminName = $existingAdmin['name'] ?? '';
                }
                if ($adminEmail === '') {
                    $adminEmail = $existingAdmin['email'] ?? '';
                }
                if ($adminName !== ($existingAdmin['name'] ?? '') || $adminEmail !== ($existingAdmin['email'] ?? '')) {
                    try {
                        $model->updateAdminUser((int) $existingAdmin['id'], $adminName, $adminEmail);
                        // Sinkronkan ke session supaya invoice / sidebar pakai nilai terbaru.
                        $_SESSION['admin_name'] = $adminName;
                        $_SESSION['admin_email'] = $adminEmail;
                    } catch (\PDOException $e) {
                        // Email unik. Kasih pesan ramah ke admin.
                        if ((int) $e->getCode() === 23000 || stripos($e->getMessage(), 'duplicate') !== false) {
                            return $this->jsonResponse(['status' => 'error', 'message' => 'Email tersebut sudah dipakai akun lain.'], 400);
                        }
                        throw $e;
                    }
                }
            }

            // --- Update alamat toko (type=store) ---
            if (array_key_exists('store_address_text', $data)) {
                $addressText = trim((string) $data['store_address_text']);
                $existingAddress = $model->getStoreAddress();
                $recipientName = $existingAddress['recipient_name'] ?? ($data['store_name'] ?? 'Toko');
                $whatsappNumber = $existingAddress['whatsapp_number'] ?? ($data['whatsapp_admin'] ?? '');
                if ($addressText !== '' || $existingAddress) {
                    $model->upsertStoreAddressText($addressText, $recipientName, $whatsappNumber);
                }
            }

            return $this->jsonResponse(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ambil pemakaian kuota harian Brevo (transactional email).
     *
     * Memanggil dua endpoint Brevo:
     *  - GET /v3/account                              → plan info (free / paid).
     *  - GET /v3/smtp/statistics/aggregatedReport     → jumlah email hari ini.
     *
     * Hasil dikembalikan dalam bentuk JSON yang bisa dirender langsung di
     * card "Notifikasi Email" di halaman Settings. Daily limit default 300
     * (kuota gratis Brevo). Admin yang berlangganan paket lebih besar bisa
     * mengabaikan limit; UI tetap menampilkan jumlah yang terpakai.
     */
    public function brevoUsage()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error'], 401);
        }

        try {
            $model = new StoreSettingModel();
            $settings = $model->getSettings() ?: [];
            $apiKey = trim((string) ($settings['email_brevo_api_key'] ?? ''));
            if ($apiKey === '') {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'API Key Brevo belum diatur.',
                ], 400);
            }

            $today = date('Y-m-d');
            $account = $this->httpGetBrevo('https://api.brevo.com/v3/account', $apiKey);
            $stats = $this->httpGetBrevo(
                'https://api.brevo.com/v3/smtp/statistics/aggregatedReport?startDate=' . $today . '&endDate=' . $today,
                $apiKey
            );

            // Validasi dasar -- kalau API key salah, Brevo balas 401.
            if (!$account['ok']) {
                $message = $account['status'] === 401
                    ? 'API Key Brevo tidak valid atau tidak punya izin akses akun.'
                    : 'Gagal mengambil info akun Brevo (HTTP ' . $account['status'] . ').';
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => $message,
                ], 502);
            }

            $accountData = json_decode($account['body'], true) ?: [];
            $statsData = $stats['ok'] ? (json_decode($stats['body'], true) ?: []) : [];

            // Brevo aggregatedReport kadang membungkus hasil dalam key `range`,
            // kadang langsung berisi counters. Ambil counter teratas dengan
            // fallback yang aman.
            $requests = (int) ($statsData['requests'] ?? 0);
            $delivered = (int) ($statsData['delivered'] ?? 0);

            // Ambil nama plan (kalau ada beberapa, prioritaskan tipe email).
            $planName = '';
            $planType = '';
            $planCredits = null;
            if (!empty($accountData['plan']) && is_array($accountData['plan'])) {
                foreach ($accountData['plan'] as $plan) {
                    $type = strtolower((string) ($plan['type'] ?? ''));
                    $creditsType = strtolower((string) ($plan['creditsType'] ?? ''));
                    if ($type === 'free' || $creditsType === 'sendlimit' || $creditsType === 'email') {
                        $planName = (string) ($plan['type'] ?? '');
                        $planType = $type;
                        if (isset($plan['credits'])) {
                            $planCredits = (int) $plan['credits'];
                        }
                        break;
                    }
                }
                if ($planName === '' && isset($accountData['plan'][0])) {
                    $planName = (string) ($accountData['plan'][0]['type'] ?? '');
                    $planType = strtolower($planName);
                }
            }

            // Kuota harian default Brevo gratis adalah 300/hari. Untuk paket
            // berbayar, tampilkan limit dari `credits` (bulanan) sebagai info
            // saja; UI tetap menampilkan pemakaian harian.
            $dailyLimit = $planType === 'free' || $planName === '' ? 300 : 0;

            $remaining = $dailyLimit > 0 ? max(0, $dailyLimit - $requests) : null;
            $percent = $dailyLimit > 0 ? min(100, round(($requests / $dailyLimit) * 100, 1)) : null;

            return $this->jsonResponse([
                'status' => 'success',
                'data' => [
                    'date' => $today,
                    'plan_name' => $planName,
                    'plan_type' => $planType,
                    'monthly_credits' => $planCredits,
                    'daily_limit' => $dailyLimit,
                    'used_today' => $requests,
                    'delivered_today' => $delivered,
                    'remaining_today' => $remaining,
                    'percent_used' => $percent,
                    'account_email' => $accountData['email'] ?? '',
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Gagal memuat pemakaian Brevo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper GET ke Brevo dengan header api-key. Timeout dibatasi supaya
     * permintaan admin tidak menggantung lama saat jaringan lambat.
     *
     * @return array{ok: bool, status: int, body: string}
     */
    private function httpGetBrevo(string $url, string $apiKey): array
    {
        $headers = [
            'Accept: application/json',
            'api-key: ' . $apiKey,
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body === false) {
                return ['ok' => false, 'status' => 0, 'body' => ''];
            }
            return [
                'ok' => $status >= 200 && $status < 300,
                'status' => $status,
                'body' => (string) $body,
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 12,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                    $status = (int) $m[1];
                    break;
                }
            }
        }
        if ($body === false) {
            return ['ok' => false, 'status' => 0, 'body' => ''];
        }
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'body' => (string) $body,
        ];
    }
}
