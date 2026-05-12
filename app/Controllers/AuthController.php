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
            header('Location: ' . rtrim(BASE_URL, '/') . '/admin');
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

        if (!$admin) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Email atau Password salah!'
            ], 401);
        }

        $storedHash = $admin['password'];
        $isValid = false;
        $needsRehash = false;

        if ($this->isBcryptHash($storedHash)) {
            $isValid = password_verify($password, $storedHash);
            if ($isValid && password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
                $needsRehash = true;
            }
        } elseif ($this->isLegacySha256Hash($storedHash)) {
            // Akun admin lama masih memakai hash SHA-256. Jika cocok, kita
            // izinkan login lalu lakukan migrasi otomatis ke password_hash().
            $isValid = hash_equals($storedHash, hash('sha256', $password));
            if ($isValid) {
                $needsRehash = true;
            }
        }

        if (!$isValid) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Email atau Password salah!'
            ], 401);
        }

        if ($needsRehash) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $userModel->updatePassword($admin['id'], $newHash);
        }

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

    private function isBcryptHash($hash)
    {
        return is_string($hash) && preg_match('/^\$2[aby]\$/', $hash) === 1;
    }

    private function isLegacySha256Hash($hash)
    {
        return is_string($hash) && preg_match('/^[a-f0-9]{64}$/i', $hash) === 1;
    }

    private function verifyPassword($password, $storedHash)
    {
        if ($this->isBcryptHash($storedHash)) {
            return password_verify($password, $storedHash);
        }

        if ($this->isLegacySha256Hash($storedHash)) {
            return hash_equals($storedHash, hash('sha256', $password));
        }

        return false;
    }

    public function updatePassword()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $data = $this->getJsonInput();
        $current = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';

        if ($current === '' || $newPassword === '' || $confirm === '') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Semua field password wajib diisi.'], 400);
        }

        if (strlen($newPassword) < 6) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Password baru minimal 6 karakter.'], 400);
        }

        if ($newPassword !== $confirm) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.'], 400);
        }

        $userModel = new UserModel();
        $admin = $userModel->getAdminById($_SESSION['admin_id']);

        if (!$admin) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Admin tidak ditemukan.'], 404);
        }

        $storedHash = $admin['password'];
        if (!$this->verifyPassword($current, $storedHash)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Password lama tidak sesuai.'], 400);
        }

        if ($this->verifyPassword($newPassword, $storedHash)) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Password baru tidak boleh sama dengan password lama.'], 400);
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel->updatePassword($admin['id'], $newHash);

        return $this->jsonResponse(['status' => 'success', 'message' => 'Password admin berhasil diperbarui.']);
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
