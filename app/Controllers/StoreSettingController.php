<?php
namespace App\Controllers;

use App\Core\Controller;
require_once __DIR__ . '/../Models/StoreSettingModel.php';
use App\Models\StoreSettingModel;

class StoreSettingController extends Controller {
    
    // Method GET: Mengambil data untuk ditampilkan di form
    public function index() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        try {
            $model = new StoreSettingModel();
            $data = $model->getSettings();
            if ($data && array_key_exists('email_smtp_password', $data)) {
                $data['email_smtp_password'] = '';
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
            $emailPassword = trim($data['email_smtp_password'] ?? '');
            if ($emailPassword === '' && !empty($existing['email_smtp_password'])) {
                $emailPassword = $existing['email_smtp_password'];
            }

            $emailEncryption = strtolower(trim($data['email_smtp_encryption'] ?? ($existing['email_smtp_encryption'] ?? 'tls')));
            if (!in_array($emailEncryption, ['tls', 'ssl', ''], true)) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Enkripsi email tidak valid.'], 400);
            }

            $emailHost = trim($data['email_smtp_host'] ?? ($existing['email_smtp_host'] ?? ''));
            $emailPort = (int) ($data['email_smtp_port'] ?? ($existing['email_smtp_port'] ?? 0));
            $emailUsername = trim($data['email_smtp_username'] ?? ($existing['email_smtp_username'] ?? ''));
            $emailFromName = trim($data['email_from_name'] ?? ($existing['email_from_name'] ?? $data['store_name']));
            $emailFromAddress = trim($data['email_from_address'] ?? ($existing['email_from_address'] ?? $emailUsername));

            if ($emailEnabled) {
                if ($emailHost === '' || $emailPort <= 0 || $emailUsername === '' || $emailPassword === '' || $emailFromAddress === '') {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Lengkapi semua konfigurasi email sebelum diaktifkan.'], 400);
                }
                if (!filter_var($emailFromAddress, FILTER_VALIDATE_EMAIL)) {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Email pengirim tidak valid.'], 400);
                }
            }


            $model->updateSettings([
                'store_name' => $data['store_name'],
                'whatsapp_admin' => $data['whatsapp_admin'],
                'whatsapp_message_template' => $data['whatsapp_message_template'] ?? '',
                'email_enabled' => $emailEnabled,
                'email_smtp_host' => $emailHost,
                'email_smtp_port' => $emailPort,
                'email_smtp_username' => $emailUsername,
                'email_smtp_password' => $emailPassword,
                'email_smtp_encryption' => $emailEncryption,
                'email_from_name' => $emailFromName,
                'email_from_address' => $emailFromAddress
            ]);
            
            return $this->jsonResponse(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
