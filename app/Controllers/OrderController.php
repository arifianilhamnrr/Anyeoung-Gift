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

            // Jika pesanan dikonfirmasi (paid), perbarui juga status pembayarannya agar tampil "Diproses" di user
            if ($data['status'] === 'paid') {
                $orderModel->updatePaymentStatus($data['order_id'], 'confirmed');
            }

            // Balas browser dulu sebelum mengirim email, biar AJAX di admin
            // tidak menunggu SMTP / API email. Tanpa ini, kalau koneksi SMTP
            // lambat, fetch admin sering timeout dan menampilkan
            // "Kesalahan jaringan" padahal status pesanan sudah berubah.
            $this->respondAndContinue([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diperbarui.'
            ]);

            $this->sendOrderStatusEmail($orderModel, (int) $data['order_id']);
            exit;
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

            $orderNumber = 'ORD-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);
            $storeName = $settings['store_name'] ?? 'Anyeong Gift';
            $statusLabel = $this->formatStatusLabel($order['status'] ?? '');
            $customerName = $order['customer_name'] ?? 'Pelanggan';
            $total = isset($order['total_price']) ? number_format((int) $order['total_price'], 0, ',', '.') : '0';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $logoUrl = $scheme . '://' . $host . '/assets/images/anyeong-logo.svg';

            $items = $orderModel->getOrderDetails($orderId) ?: [];
            $payment = $orderModel->getOrderPayment($orderId) ?: [];
            $storeAddress = $orderModel->getStoreAddress() ?: [];
            $storeAddressText = $storeAddress['address_text'] ?? '';
            $storeWhatsapp = $settings['whatsapp_admin'] ?? '';
            $orderDate = !empty($order['created_at'])
                ? date('d M Y H:i', strtotime($order['created_at']))
                : date('d M Y H:i');

            $paymentTypeRaw = strtolower((string) ($payment['method_type'] ?? ''));
            if ($paymentTypeRaw === 'onsite') {
                $paymentMethod = 'Cash on Pick Up';
            } else {
                $paymentMethod = $payment['method_name'] ?? 'Pembayaran Online';
            }
            $paymentStatus = strtolower((string) ($payment['status'] ?? ''));
            $paymentStatusLabel = $paymentStatus === 'confirmed'
                ? 'Terverifikasi'
                : ($paymentStatus === 'rejected' ? 'Ditolak' : 'Menunggu Verifikasi');

            $itemRows = '';
            $itemLines = [];
            foreach ($items as $item) {
                $name = htmlspecialchars((string) ($item['product_name'] ?? '-'));
                $subtotal = (int) ($item['subtotal'] ?? 0);

                $optionLines = [];
                if (!empty($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as $opt) {
                        $optName = trim((string) ($opt['option_name'] ?? ''));
                        $value = trim((string) ($opt['custom_value'] ?? ''));
                        if ($value === '') {
                            $value = trim((string) ($opt['value_name'] ?? '-'));
                        }
                        $extra = (int) ($opt['additional_price'] ?? 0);
                        $extraLabel = $extra > 0 ? ' (+' . number_format($extra, 0, ',', '.') . ')' : '';
                        if ($optName !== '') {
                            $optionLines[] = htmlspecialchars($optName) . ': ' . htmlspecialchars($value) . $extraLabel;
                        }
                    }
                }

                $optionHtml = '';
                if (!empty($optionLines)) {
                    $optionHtml = '<div style="margin-top:4px;color:#555;font-size:12px;">' . implode('<br>', $optionLines) . '</div>';
                }

                $itemRows .= "<tr><td style=\"padding:8px 10px;border-bottom:1px solid #eee;\"><strong>{$name}</strong>{$optionHtml}</td><td style=\"padding:8px 10px;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;\">Rp " . number_format($subtotal, 0, ',', '.') . "</td></tr>";
                $itemLines[] = '- ' . ($item['product_name'] ?? '-') . ' : Rp ' . number_format($subtotal, 0, ',', '.');
            }
            if ($itemRows === '') {
                $itemRows = '<tr><td style="padding:10px;color:#666;" colspan="2">Tidak ada item.</td></tr>';
            }

            $subject = "Update Pesanan {$orderNumber} - {$storeName}";
            $body = "
                <div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #111;\">
                    <div style=\"margin-bottom:12px;\"><img src=\"{$logoUrl}\" alt=\"Logo {$storeName}\" style=\"height:48px;\" /></div>
                    <h2 style=\"margin: 0 0 8px;\">Pemberitahuan Status Pesanan</h2>
                    <p>Halo <strong>" . htmlspecialchars($customerName) . "</strong>,</p>
                    <p>Status pesanan <strong>{$orderNumber}</strong> telah diperbarui menjadi <strong>{$statusLabel}</strong>.</p>
                    <table style=\"width:100%;border-collapse:collapse;margin:12px 0 8px;\">
                        <tr><td style=\"padding:6px 0;\">Tanggal Pesanan</td><td style=\"padding:6px 0;text-align:right;\">{$orderDate}</td></tr>
                        <tr><td style=\"padding:6px 0;\">Metode Pembayaran</td><td style=\"padding:6px 0;text-align:right;\">" . htmlspecialchars($paymentMethod) . "</td></tr>
                        <tr><td style=\"padding:6px 0;\">Status Pembayaran</td><td style=\"padding:6px 0;text-align:right;\">{$paymentStatusLabel}</td></tr>
                        <tr><td style=\"padding:6px 0;font-weight:bold;\">Total</td><td style=\"padding:6px 0;text-align:right;font-weight:bold;\">Rp {$total}</td></tr>
                    </table>
                    <h4 style=\"margin:16px 0 8px;\">Detail Pesanan</h4>
                    <table style=\"width:100%;border-collapse:collapse;\">
                        <thead>
                            <tr>
                                <th style=\"text-align:left;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Item</th>
                                <th style=\"text-align:right;background:#111;color:#fff;padding:8px 10px;font-size:12px;\">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemRows}
                        </tbody>
                    </table>
                    <h4 style=\"margin:16px 0 8px;\">Alamat Toko</h4>
                    <p style=\"margin:0;white-space:pre-line;\">" . htmlspecialchars($storeAddressText) . "</p>
                    <p style=\"margin:8px 0 0;\">WhatsApp Toko: " . htmlspecialchars($storeWhatsapp) . "</p>
                    <p style=\"margin-top:16px;\">Terima kasih telah berbelanja di {$storeName}.</p>
                </div>
            ";

            $textBody = "Halo {$customerName}, status pesanan {$orderNumber} sekarang {$statusLabel}.\n";
            $textBody .= "Tanggal pesanan: {$orderDate}.\n";
            $textBody .= "Metode pembayaran: {$paymentMethod}. Status pembayaran: {$paymentStatusLabel}.\n";
            $textBody .= "Total: Rp {$total}.\n\n";
            $textBody .= "Detail pesanan:\n" . implode("\n", $itemLines) . "\n\n";
            if ($storeAddressText !== '') {
                $textBody .= "Alamat toko:\n{$storeAddressText}\n";
            }
            if ($storeWhatsapp !== '') {
                $textBody .= "WhatsApp toko: {$storeWhatsapp}\n";
            }
            $textBody .= "\nTerima kasih telah berbelanja di {$storeName}.";

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
