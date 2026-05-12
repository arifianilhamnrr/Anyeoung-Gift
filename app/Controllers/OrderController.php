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

    // Endpoint API: GET /api/orders/details
    public function details() {
        // 🔒 Keamanan: Pastikan Admin sudah login
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $orderId = $_GET['id'] ?? null;
        
        if (!$orderId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'ID pesanan tidak ditemukan.'], 400);
        }

        try {
            // Panggil fungsi di Model untuk mengambil isi keranjang
            $orderModel = new \App\Models\OrderModel();
            $items = $orderModel->getOrderDetails($orderId);
            $order = $orderModel->getOrderHeader($orderId);
            $payment = $orderModel->getOrderPayment($orderId);

            return $this->jsonResponse([
                'status' => 'success',
                'data' => $items,
                'order' => $order,
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}