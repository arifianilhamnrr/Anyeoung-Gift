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
    public function updateSettings(array $data) {
        $existing = $this->getSettings();
        
        if ($existing) {
            // Jika sudah ada data, kita Update
            $this->query("UPDATE store_settings
                SET store_name = :name,
                    whatsapp_admin = :wa,
                    whatsapp_message_template = :template,
                    email_enabled = :email_enabled,
                    email_smtp_host = :email_smtp_host,
                    email_smtp_port = :email_smtp_port,
                    email_smtp_username = :email_smtp_username,
                    email_smtp_password = :email_smtp_password,
                    email_smtp_encryption = :email_smtp_encryption,
                    email_from_name = :email_from_name,
                    email_from_address = :email_from_address
                WHERE id = :id");
            $this->bind(':id', $existing['id']);
        } else {
            // Jika tabel masih kosong, kita Insert baru
            $this->query("INSERT INTO store_settings
                (store_name, whatsapp_admin, whatsapp_message_template, email_enabled, email_smtp_host, email_smtp_port,
                 email_smtp_username, email_smtp_password, email_smtp_encryption, email_from_name, email_from_address)
                VALUES (:name, :wa, :template, :email_enabled, :email_smtp_host, :email_smtp_port, :email_smtp_username,
                 :email_smtp_password, :email_smtp_encryption, :email_from_name, :email_from_address)");
        }
        
        $this->bind(':name', $data['store_name']);
        $this->bind(':wa', $data['whatsapp_admin']);
        $this->bind(':template', $data['whatsapp_message_template']);
        $this->bind(':email_enabled', $data['email_enabled']);
        $this->bind(':email_smtp_host', $data['email_smtp_host']);
        $this->bind(':email_smtp_port', $data['email_smtp_port']);
        $this->bind(':email_smtp_username', $data['email_smtp_username']);
        $this->bind(':email_smtp_password', $data['email_smtp_password']);
        $this->bind(':email_smtp_encryption', $data['email_smtp_encryption']);
        $this->bind(':email_from_name', $data['email_from_name']);
        $this->bind(':email_from_address', $data['email_from_address']);
        
        return $this->execute();
    }

    public function getAdminUser(): ?array
    {
        $this->query("SELECT id, name, email FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
        return $this->single();
    }

    public function getStoreAddress(): ?array
    {
        $this->query("SELECT * FROM addresses WHERE type = 'store' ORDER BY is_default DESC, id DESC LIMIT 1");
        return $this->single();
    }
}
