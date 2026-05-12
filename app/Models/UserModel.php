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

    // Memperbarui hash password user (digunakan untuk migrasi otomatis dari
    // hash legacy ke password_hash()).
    public function updatePassword($userId, $newHash) {
        $this->query("UPDATE users SET password = :password WHERE id = :id");
        $this->bind(':password', $newHash);
        $this->bind(':id', $userId);
        return $this->execute();
    }
}