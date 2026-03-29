<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;

class OrderController extends Controller {
    
    // Endpoint: GET /api/orders
    public function index() {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        try {
            $orderModel = new OrderModel();
            $orders = $orderModel->getAllOrders();
            
            return $this->jsonResponse([
                'status' => 'success',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Endpoint: POST /api/orders/update-status
    public function updateStatus() {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        $data = $this->getJsonInput();
        
        if (!isset($data['order_id']) || !isset($data['status'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Data tidak lengkap!'], 400);
        }

        try {
            $orderModel = new OrderModel();
            $orderModel->updateOrderStatus($data['order_id'], $data['status']);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Endpoint Khusus Developer: GET /api/dev/generate-order
    public function generateDummy() {
        // 🔒 GEMBOK API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Silakan login.'], 401);
        }

        try {
            $orderModel = new OrderModel();
            
            $orderModel->query("SELECT id FROM users WHERE id = 1");
            $userExists = $orderModel->single();
            
            if (!$userExists) {
                $orderModel->query("INSERT INTO users (id, name, email, password, role) VALUES (1, 'Joko Simulasi', 'joko@dummy.com', 'rahasia', 'customer')");
                $orderModel->execute();
            }

            $userId = 1; 
            $alamatSnapshot = json_encode([
                'penerima' => 'Joko Simulasi',
                'telepon' => '08123456789',
                'alamat_lengkap' => 'Jl. Kembang Kenangan No. 99, Bekasi'
            ]);
            
            $totalHarga = rand(150000, 350000);

            $orderId = $orderModel->insertOrder($userId, $alamatSnapshot, $totalHarga, 'pending');
            $itemId = $orderModel->insertOrderItem($orderId, 1, 'Buket Mawar Premium (Dummy)', 150000, $totalHarga);
            $orderModel->insertOrderItemOption($itemId, 'Tambahan', 'Kartu Ucapan', 0, 'Selamat Ulang Tahun!');

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Tring! 1 Pesanan baru berhasil disimulasikan.'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}