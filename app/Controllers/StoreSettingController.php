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
            
            return $this->jsonResponse(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
