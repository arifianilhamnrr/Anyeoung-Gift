<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProductModel;

class ProductController extends Controller
{
    /**
     * Endpoint API: GET /api/products
     */
    public function index()
    {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        try {
            $productModel = new ProductModel();
            $products = $productModel->getAllProducts();

            return $this->jsonResponse([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Endpoint API: POST /api/products
    public function store()
    {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        // Tangkap JSON string dari FormData, lalu ubah jadi Array PHP
        $data = json_decode($_POST['product_data'] ?? '{}', true);

        // Tangkap File Foto (Jika ada)
        $fileGambar = $_FILES['image'] ?? null;

        if (empty($data['name']) || !isset($data['base_price'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Nama dan Harga wajib diisi!'], 400);
        }

        try {
            $productService = new \App\Services\ProductService();
            // Kirim data text dan file gambarnya ke Service
            $productId = $productService->createCustomProduct($data, $fileGambar);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Produk custom berhasil ditambahkan.'
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Endpoint API: POST /api/products/toggle-status
    public function toggleStatus()
    {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        $data = $this->getJsonInput();

        if (!isset($data['id']) || !isset($data['status'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'ID Produk dan Status wajib dikirim!'
            ], 400);
        }

        try {
            $productModel = new ProductModel();
            $productModel->updateStatus($data['id'], $data['status']);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Status produk berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Endpoint API: POST /api/products/delete
    public function delete()
    {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        $data = $this->getJsonInput();

        if (!isset($data['id'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'ID Produk tidak ditemukan!'
            ], 400);
        }

        try {
            $productModel = new ProductModel();
            
            // Panggil fungsi hapus di Model
            $productModel->deleteProduct($data['id']);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus permanen.'
            ]);

        } catch (\Exception $e) {
            // Tangkap error (misalnya jika produk tidak bisa dihapus karena sedang ada di pesanan aktif)
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    // Endpoint API: GET /api/products/details
    public function details() {
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $id = $_GET['id'] ?? null;
        if (!$id) return $this->jsonResponse(['status' => 'error', 'message' => 'ID produk diperlukan'], 400);

        try {
            $model = new ProductModel();
            $data = $model->getProductDetails($id);
            
            if (!$data) return $this->jsonResponse(['status' => 'error', 'message' => 'Produk tidak ditemukan'], 404);
            
            return $this->jsonResponse(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Endpoint API: POST /api/products/update
    public function update() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        $productData = json_decode($_POST['product_data'] ?? '{}', true);
        $productId = $_POST['product_id'] ?? null;

        if (!$productId || empty($productData['name'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
        }

        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }

        try {
            $model = new ProductModel();
            $model->updateProduct($productId, $productData, $imageName);
            return $this->jsonResponse(['status' => 'success', 'message' => 'Produk berhasil diperbarui!']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}