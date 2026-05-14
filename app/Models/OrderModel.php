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
        // Sertakan tipe metode pembayaran (terutama untuk membedakan onsite/COD
        // dari online) agar UI admin bisa menampilkan aksi yang sesuai per
        // pesanan (mis. "Konfirmasi Pesanan" untuk COD, "Konfirmasi Pembayaran"
        // untuk online).
        $sql = "SELECT o.id, o.total_price, o.status, o.created_at,
                    u.name AS customer_name,
                    pm.type AS payment_method_type,
                    pm.name AS payment_method_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN payments p ON p.order_id = o.id
                LEFT JOIN payment_methods pm ON pm.id = p.payment_method_id
                ORDER BY o.created_at DESC";
        $this->query($sql);
        return $this->resultSet();
    }

    /**
     * Ringkasan dashboard. Kalau $month / $year diisi (1-12 dan 4 digit),
     * data dibatasi pada bulan tersebut  berguna untuk rekap bulanan di
     * dashboard admin. Kalau null, hitung total keseluruhan (perilaku lama).
     */
    public function getDashboardSummary(?int $month = null, ?int $year = null)
    {
        $hasPeriod = $month !== null && $year !== null;
        $periodClause = $hasPeriod ? " AND MONTH(created_at) = :month AND YEAR(created_at) = :year" : "";
        $bindPeriod = function () use ($hasPeriod, $month, $year) {
            if ($hasPeriod) {
                $this->bind(':month', $month);
                $this->bind(':year', $year);
            }
        };

        // Pemasukan dihitung dari pesanan yang sudah dibayar (paid, ready_pickup, completed)
        $this->query("SELECT COALESCE(SUM(total_price), 0) AS total_revenue
                      FROM orders
                      WHERE status IN ('paid', 'ready_pickup', 'completed')" . $periodClause);
        $bindPeriod();
        $revenue = $this->single();

        // Pesanan aktif adalah yang belum selesai atau dibatalkan
        $this->query("SELECT COUNT(*) AS active_orders
                      FROM orders
                      WHERE status NOT IN ('completed', 'cancelled')" . $periodClause);
        $bindPeriod();
        $active = $this->single();

        // Pending payment dihitung dari 'pending' atau 'waiting_payment'
        $this->query("SELECT COUNT(*) AS pending_payments
                      FROM orders
                      WHERE status IN ('pending', 'waiting_payment')" . $periodClause);
        $bindPeriod();
        $pending = $this->single();

        return [
            'total_revenue' => $revenue['total_revenue'] ?? 0,
            'active_orders' => $active['active_orders'] ?? 0,
            'pending_payments' => $pending['pending_payments'] ?? 0
        ];
    }

    /**
     * Daftar order yang dipakai untuk tabel "Pesanan Terbaru" di dashboard.
     * Optional difilter ke bulan tertentu untuk rekap bulanan.
     */
    public function getRecentOrdersForDashboard(?int $month = null, ?int $year = null, int $limit = 5)
    {
        $hasPeriod = $month !== null && $year !== null;
        $periodClause = $hasPeriod ? " WHERE MONTH(o.created_at) = :month AND YEAR(o.created_at) = :year" : "";
        $safeLimit = max(1, min(100, (int) $limit));
        $this->query("SELECT o.id, o.total_price, o.status, o.created_at,
                             u.name AS customer_name,
                             pm.type AS payment_method_type,
                             pm.name AS payment_method_name
                      FROM orders o
                      LEFT JOIN users u ON o.user_id = u.id
                      LEFT JOIN payments p ON p.order_id = o.id
                      LEFT JOIN payment_methods pm ON pm.id = p.payment_method_id" . $periodClause . "
                      ORDER BY o.created_at DESC
                      LIMIT " . $safeLimit);
        if ($hasPeriod) {
            $this->bind(':month', $month);
            $this->bind(':year', $year);
        }
        return $this->resultSet();
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

    public function updatePaymentStatus($orderId, $newStatus)
    {
        $sql = "UPDATE payments SET status = :status WHERE order_id = :order_id";
        $this->query($sql);
        $this->bind(':status', $newStatus);
        $this->bind(':order_id', $orderId);
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

            // Tarik opsi tambahannya beserta custom_value (mis. teks pita / tulisan kustom)
            $this->query("SELECT option_name_snapshot AS option_name,
                                 option_value_snapshot AS value_name,
                                 additional_price,
                                 custom_value
                          FROM order_item_options
                          WHERE order_item_id = :item_id
                          ORDER BY id ASC");
            $this->bind(':item_id', $item['id']);
            $item['options'] = $this->resultSet();
        }

        return $items;
    }

    // Mengambil header pesanan beserta info kontak pelanggan dari address_snapshot.
    // Digunakan oleh halaman detail pesanan admin untuk menampilkan kontak pembeli
    // dan tombol chat WhatsApp.
    public function getOrderHeader($orderId) {
        $this->query("
            SELECT o.id, o.user_id, o.total_price, o.status, o.created_at,
                   o.address_snapshot,
                   u.name AS customer_name,
                   u.email AS customer_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = :order_id
            LIMIT 1
        ");
        $this->bind(':order_id', $orderId);
        $order = $this->single();

        if (!$order) {
            return null;
        }

        $address = null;
        if (!empty($order['address_snapshot'])) {
            $decoded = json_decode($order['address_snapshot'], true);
            if (is_array($decoded)) {
                $address = $decoded;
            }
        }
        $order['address'] = $address;

        return $order;
    }

    public function getStoreAddress(): ?array
    {
        $this->query("SELECT * FROM addresses WHERE type = 'store' ORDER BY is_default DESC, id DESC LIMIT 1");
        return $this->single();
    }

    // Mengambil data pembayaran terbaru untuk satu pesanan (untuk halaman detail
    // pesanan admin). Termasuk metode pembayaran yang dipakai pembeli dan link
    // ke file bukti transfer / QRIS kalau ada.
    public function getOrderPayment($orderId) {
        $this->query("
            SELECT p.id, p.order_id, p.amount, p.status, p.proof_image, p.paid_at, p.created_at,
                   pm.id AS method_id,
                   pm.name AS method_name,
                   pm.type AS method_type,
                   pm.account_info AS method_account
            FROM payments p
            LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
            WHERE p.order_id = :order_id
            ORDER BY p.id DESC
            LIMIT 1
        ");
        $this->bind(':order_id', $orderId);
        return $this->single();
    }
}