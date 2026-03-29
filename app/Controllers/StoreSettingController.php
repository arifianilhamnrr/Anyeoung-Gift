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
            $model->updateSettings($data['store_name'], $data['whatsapp_admin'], $data['whatsapp_message_template'] ?? '');
            
            return $this->jsonResponse(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}