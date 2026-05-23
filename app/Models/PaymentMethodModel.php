<?php
namespace App\Models;

use App\Core\Model;

class PaymentMethodModel extends Model {
    
    public function getAll() {
        $this->query("SELECT * FROM payment_methods ORDER BY id DESC");
        $results = $this->resultSet();

        // Buka bungkusan JSON: payload disimpan sebagai {info, image?}.
        // `account_info` di-flatten jadi teks polos, gambar QRIS jadi field
        // terpisah `image` agar mudah dipakai konsumen frontend.
        foreach ($results as &$row) {
            $row['image'] = null;
            if (!empty($row['account_info'])) {
                $decoded = json_decode($row['account_info'], true);
                if (is_array($decoded)) {
                    if (isset($decoded['info'])) {
                        $row['account_info'] = $decoded['info'];
                    }
                    if (!empty($decoded['image'])) {
                        $row['image'] = $decoded['image'];
                    }
                }
            }
        }

        return $results;
    }

    // Helper internal: bungkus info + nama file gambar (opsional) ke JSON
    // yang akan disimpan di kolom `account_info`.
    private function packPayload($info, $image = null) {
        $payload = [];
        if ($info !== null && trim($info) !== '') {
            $payload['info'] = trim($info);
        }
        if ($image !== null && trim($image) !== '') {
            $payload['image'] = trim($image);
        }
        return empty($payload) ? '{}' : json_encode($payload);
    }

    // Untuk update: kalau image baru tidak diunggah, kita pertahankan gambar
    // lama dari record di database.
    public function getById($id) {
        $this->query("SELECT * FROM payment_methods WHERE id = :id LIMIT 1");
        $this->bind(':id', $id);
        return $this->single();
    }

    public function addMethod($name, $type, $info, $image = null) {
        $this->query("INSERT INTO payment_methods (name, type, account_info, is_active) VALUES (:name, :type, :info, 1)");
        $this->bind(':name', $name);
        $this->bind(':type', $type);
        $this->bind(':info', $this->packPayload($info, $image));
        return $this->execute();
    }

    public function updateMethod($id, $name, $type, $info, $image = null) {
        $this->query("UPDATE payment_methods SET name = :name, type = :type, account_info = :info WHERE id = :id");
        $this->bind(':id', $id);
        $this->bind(':name', $name);
        $this->bind(':type', $type);
        $this->bind(':info', $this->packPayload($info, $image));
        return $this->execute();
    }
}