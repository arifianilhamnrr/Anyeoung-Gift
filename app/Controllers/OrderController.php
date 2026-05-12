<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;
use App\Models\StoreSettingModel;
use App\Services\MailerService;

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
            $this->sendOrderStatusEmail($orderModel, (int) $data['order_id']);

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

    private function sendOrderStatusEmail(OrderModel $orderModel, int $orderId): void
    {
        try {
            $order = $orderModel->getOrderHeader($orderId);
            if (!$order || empty($order['customer_email'])) {
                return;
            }

            $settingModel = new StoreSettingModel();
            $settings = $settingModel->getSettings() ?: [];
            $mailer = new MailerService($settings);
            if (!$mailer->canSend()) {
                return;
            }

            $orderNumber = 'AG-' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
            $storeName = $settings['store_name'] ?? 'Anyeong Gift';
            $statusLabel = $this->formatStatusLabel($order['status'] ?? '');
            $customerName = $order['customer_name'] ?? 'Pelanggan';
            $total = isset($order['total_price']) ? number_format((int) $order['total_price'], 0, ',', '.') : '0';

            $subject = "Update Pesanan {$orderNumber} - {$storeName}";
            $body = "
                <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
                    <h2 style=\"margin: 0 0 8px;\">Status Pesanan Diperbarui</h2>
                    <p>Halo <strong>" . htmlspecialchars($customerName) . "</strong>,</p>
                    <p>Status pesanan kamu (<strong>{$orderNumber}</strong>) sekarang <strong>{$statusLabel}</strong>.</p>
                    <p>Total transaksi: <strong>Rp {$total}</strong>.</p>
                    <p>Terima kasih sudah berbelanja di {$storeName}.</p>
                </div>
            ";

            $textBody = "Halo {$customerName}, status pesanan {$orderNumber} sekarang {$statusLabel}. Total transaksi: Rp {$total}. Terima kasih sudah berbelanja di {$storeName}.";

            $mailer->send($order['customer_email'], $customerName, $subject, $body, $textBody);
        } catch (\Exception $e) {
            return;
        }
    }

    private function formatStatusLabel(string $status): string
    {
        $map = [
            'waiting_payment' => 'Menunggu Pembayaran',
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Diterima',
            'ready_pickup' => 'Pesanan Siap',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        return $map[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
