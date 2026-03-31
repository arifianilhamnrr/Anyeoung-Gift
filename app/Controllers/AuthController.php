<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{

    /**
     * 1. Menampilkan Halaman Login
     */
    public function loginView()
    {
        // Jika session sudah ada, langsung arahkan ke Dashboard Admin
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: /anyeong-gift/public/admin');
            exit;
        }

        require_once __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * 2. Memproses Data Login (API JSON)
     */
    public function processLogin()
    {
        // Ambil data input JSON (email & password)
        $data = $this->getJsonInput();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Email dan Password wajib diisi!'
            ], 400);
        }

        $userModel = new UserModel();
        $admin = $userModel->getAdminByEmail($email);

        // --- LOGIKA ENKRIPSI SHA-256 ---
        // Kita hash input password user dengan sha256
        $inputHash = hash('sha256', $password);

        // Bandingkan hash input dengan password (hash) yang ada di database
        if ($admin && $admin['password'] === $inputHash) {

            // Set Session sebagai "Tiket Masuk"
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Login berhasil, mengalihkan...'
            ]);
        }

        // Jika gagal
        return $this->jsonResponse([
            'status' => 'error',
            'message' => 'Email atau Password salah!'
        ], 401);
    }

    /**
     * 3. Memproses Logout
     */
    public function logout()
    {
        // Hapus semua data session
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        return $this->jsonResponse([
            'status' => 'success',
            'message' => 'Berhasil logout'
        ]);
    }
}