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
                    email_driver = :email_driver,
                    email_smtp_host = :email_smtp_host,
                    email_smtp_port = :email_smtp_port,
                    email_smtp_username = :email_smtp_username,
                    email_smtp_password = :email_smtp_password,
                    email_smtp_encryption = :email_smtp_encryption,
                    email_brevo_api_key = :email_brevo_api_key,
                    email_mailersend_api_key = :email_mailersend_api_key,
                    email_sendpulse_client_id = :email_sendpulse_client_id,
                    email_sendpulse_client_secret = :email_sendpulse_client_secret,
                    email_from_name = :email_from_name,
                    email_from_address = :email_from_address
                WHERE id = :id");
            $this->bind(':id', $existing['id']);
        } else {
            // Jika tabel masih kosong, kita Insert baru
            $this->query("INSERT INTO store_settings
                (store_name, whatsapp_admin, whatsapp_message_template, email_enabled, email_driver,
                 email_smtp_host, email_smtp_port, email_smtp_username, email_smtp_password,
                 email_smtp_encryption, email_brevo_api_key, email_mailersend_api_key,
                 email_sendpulse_client_id, email_sendpulse_client_secret,
                 email_from_name, email_from_address)
                VALUES (:name, :wa, :template, :email_enabled, :email_driver,
                 :email_smtp_host, :email_smtp_port, :email_smtp_username, :email_smtp_password,
                 :email_smtp_encryption, :email_brevo_api_key, :email_mailersend_api_key,
                 :email_sendpulse_client_id, :email_sendpulse_client_secret,
                 :email_from_name, :email_from_address)");
        }
        
        $this->bind(':name', $data['store_name']);
        $this->bind(':wa', $data['whatsapp_admin']);
        $this->bind(':template', $data['whatsapp_message_template']);
        $this->bind(':email_enabled', $data['email_enabled']);
        $this->bind(':email_driver', $data['email_driver']);
        $this->bind(':email_smtp_host', $data['email_smtp_host']);
        $this->bind(':email_smtp_port', $data['email_smtp_port']);
        $this->bind(':email_smtp_username', $data['email_smtp_username']);
        $this->bind(':email_smtp_password', $data['email_smtp_password']);
        $this->bind(':email_smtp_encryption', $data['email_smtp_encryption']);
        $this->bind(':email_brevo_api_key', $data['email_brevo_api_key']);
        $this->bind(':email_mailersend_api_key', $data['email_mailersend_api_key']);
        $this->bind(':email_sendpulse_client_id', $data['email_sendpulse_client_id']);
        $this->bind(':email_sendpulse_client_secret', $data['email_sendpulse_client_secret']);
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

    /**
     * Perbarui nama & email admin (untuk profil toko di dashboard).
     * Email unik secara database, jadi caller harus menangani exception.
     */
    public function updateAdminUser(int $adminId, string $name, string $email): bool
    {
        $this->query("UPDATE users SET name = :name, email = :email WHERE id = :id AND role = 'admin'");
        $this->bind(':name', $name);
        $this->bind(':email', $email);
        $this->bind(':id', $adminId);
        return $this->execute();
    }

    /**
     * Simpan atau update teks alamat toko default. Memakai recipient_name &
     * whatsapp_number yang sudah ada kalau row store sudah pernah dibuat.
     */
    public function upsertStoreAddressText(string $addressText, string $recipientName, string $whatsappNumber): bool
    {
        $existing = $this->getStoreAddress();
        if ($existing) {
            $this->query("UPDATE addresses SET address_text = :addr, recipient_name = :name, whatsapp_number = :wa WHERE id = :id");
            $this->bind(':addr', $addressText);
            $this->bind(':name', $recipientName);
            $this->bind(':wa', $whatsappNumber);
            $this->bind(':id', $existing['id']);
        } else {
            $this->query("INSERT INTO addresses (user_id, type, recipient_name, whatsapp_number, address_text, is_default) VALUES (NULL, 'store', :name, :wa, :addr, 1)");
            $this->bind(':addr', $addressText);
            $this->bind(':name', $recipientName);
            $this->bind(':wa', $whatsappNumber);
        }
        return $this->execute();
    }
}
