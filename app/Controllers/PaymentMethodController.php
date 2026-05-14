<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\PaymentMethodModel;

class PaymentMethodController extends Controller {
    public function index() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);

        try {
            $model = new PaymentMethodModel();
            return $this->jsonResponse(['status' => 'success', 'data' => $model->getAll()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);

        // Form sekarang multipart agar bisa membawa file gambar QRIS, jadi data
        // utamanya datang lewat $_POST.
        $data = $this->extractPayload();
        if (empty($data['name']) || empty($data['type'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Nama dan Tipe wajib diisi!'], 400);
        }

        try {
            $imageFile = $this->handleQrisUpload($data['type']);
            $model = new PaymentMethodModel();
            $model->addMethod($data['name'], $data['type'], $data['account_info'] ?? '', $imageFile);
            return $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Endpoint API: POST /api/payment-methods/update
    public function update() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);

        $data = $this->extractPayload();

        if (empty($data['id']) || empty($data['name']) || empty($data['type'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'ID, Nama, dan Tipe wajib diisi!'], 400);
        }

        try {
            $model = new PaymentMethodModel();
            $newImage = $this->handleQrisUpload($data['type']);
            // Kalau tidak ada upload baru, pertahankan gambar lama dari DB.
            if ($newImage === null) {
                $existing = $model->getById((int) $data['id']);
                if ($existing && !empty($existing['account_info'])) {
                    $decoded = json_decode($existing['account_info'], true);
                    if (is_array($decoded) && !empty($decoded['image'])) {
                        $newImage = $decoded['image'];
                    }
                }
            }
            $model->updateMethod($data['id'], $data['name'], $data['type'], $data['account_info'] ?? '', $newImage);

            return $this->jsonResponse(['status' => 'success', 'message' => 'Metode Pembayaran berhasil diperbarui!']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Dukung dua format: multipart (form admin baru) atau JSON body (kompatibilitas
    // lama). Ini memudahkan migrasi tanpa memecah klien.
    private function extractPayload() {
        if (!empty($_POST)) {
            return $_POST;
        }
        return $this->getJsonInput();
    }

    // Pindahkan file gambar QRIS ke folder uploads kalau ada di request.
    // Mengembalikan nama file unik, atau null kalau tidak ada upload / tipe
    // pembayaran bukan QRIS.
    private function handleQrisUpload($type) {
        if (strtolower((string) $type) !== 'qris') {
            return null;
        }
        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload gambar QRIS gagal (kode: ' . $file['error'] . ').');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            throw new \Exception('Format gambar QRIS tidak didukung. Gunakan JPG/PNG/WEBP.');
        }
        $name = uniqid('qris_') . '.' . $ext;
        $dest = __DIR__ . '/../../public/uploads/payment_methods/' . $name;
        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0775, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \Exception('Gagal memindahkan file gambar QRIS.');
        }
        return $name;
    }
}
