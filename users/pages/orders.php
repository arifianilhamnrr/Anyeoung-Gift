<?php
$userId = $_SESSION['user_id'];

$orderSuccess = $_SESSION['order_success'] ?? null;
$orderError = $_SESSION['order_error'] ?? null;
$checkoutSuccessOrderId = $_SESSION['checkout_success_order_id'] ?? null;

unset($_SESSION['order_success'], $_SESSION['order_error'], $_SESSION['checkout_success_order_id']);

$stmt = $pdo->prepare("
    SELECT 
        o.*,
        p.id AS payment_id,
        p.status AS payment_status,
        p.proof_image,
        pm.name AS payment_method_name,
        pm.type AS payment_method_type
    FROM orders o
    LEFT JOIN payments p ON p.order_id = o.id
    LEFT JOIN payment_methods pm ON pm.id = p.payment_method_id
    WHERE o.user_id = ?
    ORDER BY o.id DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

function getOrderStatusBadge($status)
{
    switch ($status) {
        case 'waiting_payment':
            return 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30';
        case 'paid':
            return 'bg-blue-500/20 text-blue-300 border border-blue-500/30';
        case 'ready_pickup':
            return 'bg-purple-500/20 text-purple-300 border border-purple-500/30';
        case 'completed':
            return 'bg-green-500/20 text-green-300 border border-green-500/30';
        case 'cancelled':
            return 'bg-red-500/20 text-red-300 border border-red-500/30';
        default:
            return 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
    }
}

function getOrderStatusLabel($status)
{
    switch ($status) {
        case 'waiting_payment':
            return 'Menunggu Pembayaran';
        case 'paid':
            return 'Sudah Dibayar';
        case 'ready_pickup':
            return 'Siap Diambil';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return ucfirst($status);
    }
}

function getPaymentStatusBadge($status)
{
    switch ($status) {
        case 'confirmed':
            return 'bg-green-500/20 text-green-300 border border-green-500/30';
        case 'rejected':
            return 'bg-red-500/20 text-red-300 border border-red-500/30';
        case 'pending':
        default:
            return 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30';
    }
}

function getPaymentStatusLabel($status)
{
    switch ($status) {
        case 'confirmed':
            return 'Terkonfirmasi';
        case 'rejected':
            return 'Ditolak';
        case 'pending':
        default:
            return 'Menunggu Konfirmasi';
    }
}
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Pesanan Saya</h1>
        <p class="text-gray-400">Lihat riwayat pesanan dan detail pesanan kamu di sini.</p>
    </div>

    <?php if ($checkoutSuccessOrderId): ?>
        <div class="bg-green-600 text-white p-4 rounded-xl">
            Pesanan berhasil dibuat. Nomor pesanan kamu: <strong>#<?= (int) $checkoutSuccessOrderId; ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($orderSuccess): ?>
        <div class="bg-green-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($orderSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($orderError): ?>
        <div class="bg-red-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($orderError); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="bg-black border border-gold rounded-2xl p-8 text-center">
            <p class="text-lg text-gray-300 mb-4">Kamu belum punya pesanan.</p>
            <a href="index.php?page=home"
                class="inline-block bg-gold text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>

        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
                <?php
                $stmtItems = $pdo->prepare("
                    SELECT * FROM order_items
                    WHERE order_id = ?
                    ORDER BY id ASC
                ");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll();

                $addressSnapshot = [];
                if (!empty($order['address_snapshot'])) {
                    $decodedAddress = json_decode($order['address_snapshot'], true);
                    if (is_array($decodedAddress)) {
                        $addressSnapshot = $decodedAddress;
                    }
                }
                ?>

                <details class="bg-black border border-gold rounded-2xl p-5">
                    <summary class="list-none cursor-pointer">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="text-gold font-semibold text-lg">
                                    Pesanan #<?= (int) $order['id']; ?>
                                </div>
                                <div class="text-sm text-gray-400 mt-1">
                                    Tanggal: <?= date('d M Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                                <?php if (!empty($order['payment_method_name'])): ?>
                                    <div class="text-sm text-gray-400 mt-1">
                                        Metode Pembayaran: <?= htmlspecialchars($order['payment_method_name']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="text-gold font-bold mt-2">
                                    Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 items-center">
                                <span
                                    class="px-3 py-2 rounded-full text-xs font-semibold <?= getOrderStatusBadge($order['status']); ?>">
                                    <?= getOrderStatusLabel($order['status']); ?>
                                </span>

                                <?php if (!empty($order['payment_status'])): ?>
                                    <span
                                        class="px-3 py-2 rounded-full text-xs font-semibold <?= getPaymentStatusBadge($order['payment_status']); ?>">
                                        Pembayaran: <?= getPaymentStatusLabel($order['payment_status']); ?>
                                    </span>
                                <?php endif; ?>

                                <span class="text-gray-400 text-sm">Lihat Detail</span>
                            </div>
                        </div>
                    </summary>

                    <div class="mt-6 grid lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-4">
                            <?php foreach ($items as $item): ?>
                                <?php
                                $stmtOpts = $pdo->prepare("
                                    SELECT * FROM order_item_options
                                    WHERE order_item_id = ?
                                    ORDER BY id ASC
                                ");
                                $stmtOpts->execute([$item['id']]);
                                $itemOptions = $stmtOpts->fetchAll();
                                ?>

                                <div class="border border-gold/20 rounded-xl p-4">
                                    <div class="text-white font-semibold text-lg mb-2">
                                        <?= htmlspecialchars($item['product_name_snapshot']); ?>
                                    </div>

                                    <?php if (!empty($itemOptions)): ?>
                                        <div class="space-y-1 mb-3">
                                            <?php foreach ($itemOptions as $opt): ?>
                                                <div class="text-sm text-gray-300">
                                                    <span class="text-gold font-medium">
                                                        <?= htmlspecialchars($opt['option_name_snapshot']); ?>:
                                                    </span>

                                                    <?php if (!empty($opt['custom_value'])): ?>
                                                        <?= htmlspecialchars($opt['custom_value']); ?>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($opt['option_value_snapshot']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-gold font-bold">
                                        Rp <?= number_format((int) $item['subtotal'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="space-y-4">
                            <div class="border border-gold/20 rounded-xl p-4">
                                <div class="text-gold font-semibold mb-2">Kontak Pemesan</div>
                                <div class="text-white text-sm">
                                    <?= htmlspecialchars($addressSnapshot['recipient_name'] ?? '-'); ?>
                                </div>
                                <div class="text-gray-300 text-sm mt-1">
                                    <?= htmlspecialchars($addressSnapshot['whatsapp_number'] ?? '-'); ?>
                                </div>
                                <div class="text-gray-400 text-sm mt-2 whitespace-pre-line">
                                    <?= htmlspecialchars($addressSnapshot['address_text'] ?? '-'); ?>
                                </div>
                                <?php if (!empty($addressSnapshot['notes'])): ?>
                                    <div class="text-gray-500 text-xs mt-2">
                                        Catatan: <?= htmlspecialchars($addressSnapshot['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="border border-gold/20 rounded-xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">Total</span>
                                    <span class="text-gold font-bold text-lg">
                                        Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                                    </span>
                                </div>

                                <?php if (
                                    !empty($order['payment_id']) &&
                                    $order['payment_status'] === 'pending' &&
                                    $order['payment_method_type'] !== 'onsite' &&
                                    $order['status'] !== 'cancelled'
                                ): ?>
                                    <a href="index.php?page=payment_upload&order_id=<?= (int) $order['id']; ?>"
                                        class="block w-full text-center bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                                        Upload Bukti Pembayaran
                                    </a>
                                <?php elseif (
                                    !empty($order['payment_id']) &&
                                    !empty($order['proof_image'])
                                ): ?>
                                    <div class="text-sm text-green-400">
                                        Bukti pembayaran sudah diupload.
                                    </div>
                                <?php elseif (
                                    !empty($order['payment_method_type']) &&
                                    $order['payment_method_type'] === 'onsite'
                                ): ?>
                                    <div class="text-sm text-gold">
                                        Pembayaran dilakukan saat ambil pesanan di toko.
                                    </div>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'ready_pickup'): ?>
                                    <div class="text-sm text-purple-300">
                                        Pesanan kamu sudah siap diambil di toko.
                                    </div>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'waiting_payment'): ?>
                                    <form action="actions/cancel-order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id']; ?>">
                                        <button type="submit" onclick="return confirm('Yakin mau membatalkan pesanan ini?')"
                                            class="w-full bg-red-600 text-white py-3 rounded-full font-semibold hover:bg-red-700 transition">
                                            Batalkan Pesanan
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>