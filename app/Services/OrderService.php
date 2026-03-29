<?php
namespace App\Services;

use App\Models\OrderModel;
use Exception;

class OrderService {
    
    private $orderModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
    }

    public function getAllOrders() {
        return $this->orderModel->getAllOrders();
    }

    public function getSummary() {
        return $this->orderModel->getDashboardSummary();
    }

    /**
     * Core Logic untuk membuat Order dengan sistem Snapshot
     */
    public function createOrderWithSnapshot($data) {
        try {
            // 1. Mulai Transaksi (Mencegah data corrupt jika proses gagal di tengah jalan)
            $this->orderModel->beginTransaction();

            // 2. Hitung Grand Total dari request
            $grandTotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['base_price'];
                
                // Tambahkan harga opsi jika ada
                if (isset($item['options'])) {
                    foreach ($item['options'] as $option) {
                        $subtotal += $option['additional_price'];
                    }
                }
                $grandTotal += $subtotal;
            }

            // 3. Simpan ke tabel `orders`
            // Format address diubah menjadi JSON string agar mudah dibaca sebagai teks panjang
            $addressSnapshot = json_encode($data['address']); 
            
            $orderId = $this->orderModel->insertOrder(
                $data['user_id'], 
                $addressSnapshot, 
                $grandTotal, 
                'waiting_payment' // Default status
            );

            // 4. Simpan ke tabel `order_items`
            foreach ($data['items'] as $item) {
                // Hitung subtotal per item
                $itemSubtotal = $item['base_price'];
                if (isset($item['options'])) {
                    foreach ($item['options'] as $option) {
                        $itemSubtotal += $option['additional_price'];
                    }
                }

                $orderItemId = $this->orderModel->insertOrderItem(
                    $orderId,
                    $item['product_id'],
                    $item['product_name'],
                    $item['base_price'],
                    $itemSubtotal
                );

                // 5. Simpan ke tabel `order_item_options`
                if (isset($item['options'])) {
                    foreach ($item['options'] as $option) {
                        $this->orderModel->insertOrderItemOption(
                            $orderItemId,
                            $option['option_name'],
                            $option['option_value'],
                            $option['additional_price'],
                            $option['custom_value'] ?? null // Tangkap input pita/ucapan jika ada
                        );
                    }
                }
            }

            // 6. Jika semua berhasil, permanenkan data di database
            $this->orderModel->commit();
            return $orderId;

        } catch (Exception $e) {
            // Jika ada yang gagal (misal server down di tengah jalan), batalkan semua insert
            $this->orderModel->rollBack();
            throw new Exception("Gagal membuat pesanan: " . $e->getMessage());
        }
    }
}