<?php
/**
 * Helper keranjang persisten.
 *
 * Cart utama disimpan di tabel `cart_items` (per user_id) supaya tidak hilang
 * saat user logout / session habis. Session `$_SESSION['cart']` tetap dipakai
 * sebagai cache supaya halaman cart, navbar, dan checkout tidak perlu banyak
 * berubah -- isinya disinkronkan dari DB di awal request.
 *
 * Setiap item di session memiliki struktur:
 *   [
 *     'cart_item_id' => int,        // id baris di cart_items (untuk hapus)
 *     'product_id'   => int,
 *     'product_name' => string,
 *     'price'        => int,
 *     'options'      => array,
 *     'custom_input' => string|null,
 *   ]
 */

if (!function_exists('loadCartFromDb')) {
    /**
     * Ambil seluruh cart user dari DB dan kembalikan sebagai array siap pakai
     * untuk session.
     */
    function loadCartFromDb(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare("
            SELECT id, product_id, product_name, price, options, custom_input
            FROM cart_items
            WHERE user_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $cart = [];
        foreach ($rows as $row) {
            $options = [];
            if (!empty($row['options'])) {
                $decoded = json_decode($row['options'], true);
                if (is_array($decoded)) {
                    $options = $decoded;
                }
            }

            $cart[] = [
                'cart_item_id' => (int) $row['id'],
                'product_id'   => (int) $row['product_id'],
                'product_name' => $row['product_name'],
                'price'        => (int) $row['price'],
                'options'      => $options,
                'custom_input' => $row['custom_input'],
            ];
        }

        return $cart;
    }
}

if (!function_exists('syncCartSession')) {
    /**
     * Sinkronkan $_SESSION['cart'] dengan isi DB untuk user yang sedang login.
     * Aman dipanggil di setiap request -- kalau user belum login, session cart
     * dikosongkan agar tidak ada sisa cart user lain.
     */
    function syncCartSession(PDO $pdo): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['cart'] = [];
            return;
        }

        $_SESSION['cart'] = loadCartFromDb($pdo, (int) $_SESSION['user_id']);
    }
}

if (!function_exists('addCartItem')) {
    /**
     * Simpan satu item baru ke DB dan kembalikan id baris-nya.
     */
    function addCartItem(PDO $pdo, int $userId, array $item): int
    {
        $stmt = $pdo->prepare("
            INSERT INTO cart_items
                (user_id, product_id, product_name, price, options, custom_input)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            (int) $item['product_id'],
            (string) $item['product_name'],
            (int) $item['price'],
            !empty($item['options']) ? json_encode($item['options'], JSON_UNESCAPED_UNICODE) : null,
            $item['custom_input'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('removeCartItem')) {
    /**
     * Hapus satu item milik user (proteksi by user_id supaya user lain tidak
     * bisa menghapus item orang).
     */
    function removeCartItem(PDO $pdo, int $userId, int $cartItemId): void
    {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartItemId, $userId]);
    }
}

if (!function_exists('clearCart')) {
    /**
     * Kosongkan seluruh cart user (dipakai setelah checkout sukses).
     */
    function clearCart(PDO $pdo, int $userId): void
    {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}
