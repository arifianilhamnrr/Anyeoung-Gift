<?php
namespace App\Models;

use App\Core\Model;

class StoreSettingModel extends Model {
    
    // Ambil data pengaturan (karena cuma 1 toko, kita ambil baris pertama)
    public function getSettings() {
        $this->query("SELECT * FROM store_settings LIMIT 1");
        return $this->single();
    }

    // Simpan atau Update pengaturan
    public function updateSettings($storeName, $waAdmin, $waTemplate) {
        $existing = $this->getSettings();
        
        if ($existing) {
            // Jika sudah ada data, kita Update
            $this->query("UPDATE store_settings SET store_name = :name, whatsapp_admin = :wa, whatsapp_message_template = :template WHERE id = :id");
            $this->bind(':id', $existing['id']);
        } else {
            // Jika tabel masih kosong, kita Insert baru
            $this->query("INSERT INTO store_settings (store_name, whatsapp_admin, whatsapp_message_template) VALUES (:name, :wa, :template)");
        }
        
        $this->bind(':name', $storeName);
        $this->bind(':wa', $waAdmin);
        $this->bind(':template', $waTemplate);
        
        return $this->execute();
    }
}