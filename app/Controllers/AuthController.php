<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller {
    
    // 1. Menampilkan Halaman Login
    public function loginView() {
        // Jika sudah login, langsung tendang ke halaman Admin
        if (isset($_SESSION['admin_logged_in'])) {
            header('Location: /anyeong-gift/public/admin');
            exit;
        }
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    // 2. Memproses Data Login dari Form
    public function processLogin() {
        $data = $this->getJsonInput();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $userModel = new UserModel();
        $admin = $userModel->getAdminByEmail($email);

        // Cek apakah admin ada dan password cocok (Kita pakai plain text dulu agar mudah)
        if ($admin && $admin['password'] === $password) {
            // Beri "Tiket Masuk" (Session)
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $admin['name'];
            
            return $this->jsonResponse(['status' => 'success']);
        }

        return $this->jsonResponse(['status' => 'error', 'message' => 'Email atau Password salah!'], 401);
    }

    // 3. Memproses Logout (VERSI API JSON)
    public function logout() {
        // Hancurkan tiket masuk
        session_unset();
        session_destroy(); 
        
        // Balas dengan JSON agar Javascript bisa memproses perpindahan halamannya dengan mulus
        return $this->jsonResponse([
            'status' => 'success',
            'message' => 'Berhasil logout'
        ]);
    }
}