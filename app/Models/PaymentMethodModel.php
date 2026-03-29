<?php
namespace App\Models;

use App\Core\Model;

class PaymentMethodModel extends Model {
    
    public function getAll() {
        $this->query("SELECT * FROM payment_methods ORDER BY id DESC");
        $results = $this->resultSet();
        
        // BUKA BUNGKUSAN JSON untuk ditampilkan di HTML
        foreach ($results as &$row) {
            if (!empty($row['account_info'])) {
                $decoded = json_decode($row['account_info'], true);
                
                if (is_array($decoded) && isset($decoded['info'])) {
                    $row['account_info'] = $decoded['info'];
                }
            }
        }
        
        return $results;
    }

    public function addMethod($name, $type, $info) {
        $this->query("INSERT INTO payment_methods (name, type, account_info, is_active) VALUES (:name, :type, :info, 1)");
        $this->bind(':name', $name);
        $this->bind(':type', $type);
        
        // 🛡️ PERTAHANAN MUTLAK: Paksa menjadi string JSON yang sah!
        // Jika form info kosong, kita kirim objek JSON kosong "{}" agar MariaDB diam.
        // Jika ada isinya, kita kirim dalam format {"info":"123456"}
        if ($info === null || trim($info) === '') {
            $jsonInfo = '{}'; 
        } else {
            // json_encode memastikan karakter aneh seperti spasi/kutip ter-escape dengan benar
            $jsonInfo = json_encode(['info' => trim($info)]); 
        }
        
        $this->bind(':info', $jsonInfo);
        
        return $this->execute();
    }
}