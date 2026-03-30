<?php
namespace App\Models;

use App\Core\Model;

class OrderModel extends Model
{

    // ==========================================
    // READ METHODS (Untuk Dashboard & List)
    // ==========================================

    public function getAllOrders()
    {
        // Hapus u.phone as customer_phone
        $sql = "SELECT o.id, o.total_price, o.status, o.created_at, 
                    u.name as customer_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
        $this->query($sql);
        return $this->resultSet();
    }

    public function getDashboardSummary()
    {
        // Pemasukan dihitung dari pesanan yang sudah dibayar (paid, ready_pickup, completed)
        $this->query("SELECT SUM(total_price) as total_revenue FROM orders WHERE status IN ('paid', 'ready_pickup', 'completed')");
        $revenue = $this->single();

        // Pesanan aktif adalah yang belum selesai atau dibatalkan
        $this->query("SELECT COUNT(*) as active_orders FROM orders WHERE status NOT IN ('completed', 'cancelled')");
        $active = $this->single();

        // Pending payment dihitung dari 'pending' atau 'waiting_payment'
        $this->query("SELECT COUNT(*) as pending_payments FROM orders WHERE status IN ('pending', 'waiting_payment')");
        $pending = $this->single();

        return [
            'total_revenue' => $revenue['total_revenue'] ?? 0,
            'active_orders' => $active['active_orders'] ?? 0,
            'pending_payments' => $pending['pending_payments'] ?? 0
        ];
    }

    // ==========================================
    // WRITE METHODS (Untuk Sistem Snapshot)
    // ==========================================

    public function insertOrder($userId, $addressSnapshot, $totalPrice, $status = 'pending')
    {
        $sql = "INSERT INTO orders (user_id, address_snapshot, total_price, status) 
                VALUES (:user_id, :address_snapshot, :total_price, :status)";

        $this->query($sql);
        $this->bind(':user_id', $userId);
        $this->bind(':address_snapshot', $addressSnapshot); // Menyimpan format text/JSON dari alamat
        $this->bind(':total_price', $totalPrice);
        $this->bind(':status', $status);

        $this->execute();
        return $this->lastInsertId();
    }

    public function insertOrderItem($orderId, $productId, $productName, $basePrice, $subtotal)
    {
        $sql = "INSERT INTO order_items (order_id, product_id, product_name_snapshot, base_price, subtotal) 
                VALUES (:order_id, :product_id, :name, :base_price, :subtotal)";

        $this->query($sql);
        $this->bind(':order_id', $orderId);
        $this->bind(':product_id', $productId);
        $this->bind(':name', $productName);
        $this->bind(':base_price', $basePrice);
        $this->bind(':subtotal', $subtotal);

        $this->execute();
        return $this->lastInsertId();
    }

    public function insertOrderItemOption($orderItemId, $optionName, $optionValue, $additionalPrice, $customValue = null)
    {
        $sql = "INSERT INTO order_item_options 
                (order_item_id, option_name_snapshot, option_value_snapshot, additional_price, custom_value) 
                VALUES (:item_id, :opt_name, :opt_val, :price, :custom_val)";

        $this->query($sql);
        $this->bind(':item_id', $orderItemId);
        $this->bind(':opt_name', $optionName);
        $this->bind(':opt_val', $optionValue);
        $this->bind(':price', $additionalPrice);
        $this->bind(':custom_val', $customValue); // Bisa berisi teks ucapan pita

        $this->execute();
    }

    // Mengubah status pesanan (Contoh: pending -> processing)
    public function updateOrderStatus($orderId, $newStatus)
    {
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $this->query($sql);
        $this->bind(':status', $newStatus);
        $this->bind(':id', $orderId);
        return $this->execute();
    }

    public function getOrderDetails($orderId) {
        // 1. Ambil daftar produk yang dibeli di pesanan ini
        $this->query("SELECT * FROM order_items WHERE order_id = :order_id");
        $this->bind(':order_id', $orderId);
        $items = $this->resultSet();

        // 2. Ambil opsi kustomisasi (ukuran, warna, dll) untuk tiap produk
        foreach ($items as &$item) {
            // Sesuaikan nama kolom dengan yang diharapkan Javascript
            $item['product_name'] = $item['product_name_snapshot'];
            $item['price_at_time'] = $item['base_price'];
            $item['quantity'] = 1; // Karena di tabel order_items tidak ada kolom quantity, kita set 1

            // Tarik opsi tambahannya
            $this->query("SELECT option_name_snapshot as option_name, option_value_snapshot as value_name, additional_price FROM order_item_options WHERE order_item_id = :item_id");
            $this->bind(':item_id', $item['id']);
            $item['options'] = $this->resultSet();
        }

        return $items;
    }
}