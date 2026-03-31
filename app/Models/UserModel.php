<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model {
    // Mencari user khusus yang memiliki role 'admin'
    public function getAdminByEmail($email) {
        $this->query("SELECT * FROM users WHERE email = :email AND role = 'admin'");
        $this->bind(':email', $email);
        return $this->single();
    }
}