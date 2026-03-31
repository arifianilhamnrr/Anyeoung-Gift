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

<div class="space-y-6 relative">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Pesanan Saya</h1>
        <p class="text-gray-400">Lihat riwayat pesanan dan detail pesanan kamu di sini.</p>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white/5 border border-gold/20 backdrop-blur-md rounded-3xl p-10 text-center">
            <div class="w-16 h-16 bg-white/5 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <p class="text-lg text-gray-300 mb-6">Kamu belum punya pesanan.</p>
            <a href="index.php?page=home"
                class="inline-block bg-gold text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300">
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>

        <div class="space-y-5">
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

                <details
                    class="group bg-black/40 border border-gold/20 rounded-2xl backdrop-blur-md overflow-hidden transition-all duration-300 hover:border-gold/50">
                    <summary class="list-none cursor-pointer p-5 lg:p-6 [&::-webkit-details-marker]:hidden">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-gold font-bold text-lg md:text-xl">
                                        Pesanan #<?= (int) $order['id']; ?>
                                    </h3>
                                    <span class="text-gray-500 text-sm hidden md:inline-block">•</span>
                                    <span
                                        class="text-sm text-gray-400"><?= date('d M Y H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="text-white font-bold text-xl mt-1">
                                    Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                                </div>
                                <?php if (!empty($order['payment_method_name'])): ?>
                                    <div class="text-sm text-gray-400 mt-1 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                            </path>
                                        </svg>
                                        <?= htmlspecialchars($order['payment_method_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap lg:flex-nowrap items-center gap-2 lg:gap-3">
                                <span
                                    class="px-4 py-1.5 rounded-full text-xs font-bold tracking-wide <?= getOrderStatusBadge($order['status']); ?>">
                                    <?= getOrderStatusLabel($order['status']); ?>
                                </span>

                                <?php if (!empty($order['payment_status'])): ?>
                                    <span
                                        class="px-4 py-1.5 rounded-full text-xs font-bold tracking-wide <?= getPaymentStatusBadge($order['payment_status']); ?>">
                                        <?= getPaymentStatusLabel($order['payment_status']); ?>
                                    </span>
                                <?php endif; ?>

                                <div
                                    class="ml-2 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gold group-open:rotate-180 transition-transform duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </summary>

                    <div class="p-5 lg:p-6 border-t border-white/5 bg-black/60">
                        <div class="space-y-6 lg:space-y-8">

                            <div class="space-y-4">
                                <h4 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-3">Detail Produk</h4>

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
                                    <div
                                        class="bg-white/5 border border-white/10 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="text-white font-semibold text-lg mb-1">
                                                <?= htmlspecialchars($item['product_name_snapshot']); ?>
                                            </div>

                                            <?php if (!empty($itemOptions)): ?>
                                                <div class="space-y-1 mt-2">
                                                    <?php
                                                    $seenCustomValues = [];
                                                    foreach ($itemOptions as $opt):
                                                        $displayValue = !empty($opt['custom_value']) ? $opt['custom_value'] : $opt['option_value_snapshot'];

                                                        if (!empty($opt['custom_value'])) {
                                                            if (in_array($displayValue, $seenCustomValues)) {
                                                                continue;
                                                            }
                                                            $seenCustomValues[] = $displayValue;
                                                            $opt['option_name_snapshot'] = 'Tulisan Custom';
                                                        }
                                                        ?>
                                                        <div class="text-sm text-gray-300 flex">
                                                            <span class="text-gold/80 font-medium w-32 shrink-0">
                                                                <?= htmlspecialchars($opt['option_name_snapshot']); ?>
                                                            </span>
                                                            <span class="text-gray-400">
                                                                : <?= htmlspecialchars($displayValue); ?>
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-gold font-bold text-lg md:text-right whitespace-nowrap">
                                            Rp <?= number_format((int) $item['subtotal'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">

                                <div class="bg-white/5 border border-white/10 rounded-xl p-5 h-full">
                                    <h4 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-3">Kontak Pemesan
                                    </h4>
                                    <div class="text-white font-medium">
                                        <?= htmlspecialchars($addressSnapshot['recipient_name'] ?? '-'); ?>
                                    </div>
                                    <div class="text-gold text-sm mt-1">
                                        <?= htmlspecialchars($addressSnapshot['whatsapp_number'] ?? '-'); ?>
                                    </div>
                                    <div class="text-gray-400 text-sm mt-3 leading-relaxed">
                                        <?= htmlspecialchars($addressSnapshot['address_text'] ?? '-'); ?>
                                    </div>
                                    <?php if (!empty($addressSnapshot['notes'])): ?>
                                        <div
                                            class="bg-black/50 text-gray-400 text-xs mt-3 p-3 rounded-lg border border-white/5 italic">
                                            Catatan: <?= htmlspecialchars($addressSnapshot['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="bg-white/5 border border-white/10 rounded-xl p-5 h-full flex flex-col">
                                    <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                                        <span class="text-gray-300">Total Pembayaran</span>
                                        <span class="text-gold font-bold text-xl">
                                            Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                                        </span>
                                    </div>

                                    <div class="space-y-4 flex-1">
                                        <?php if (
                                            !empty($order['payment_id']) &&
                                            $order['payment_status'] === 'pending' &&
                                            $order['payment_method_type'] !== 'onsite' &&
                                            $order['status'] !== 'cancelled'
                                        ): ?>
                                            <a href="index.php?page=payment_upload&order_id=<?= (int) $order['id']; ?>"
                                                class="block w-full text-center bg-gold text-black py-3 rounded-xl font-bold hover:bg-yellow-400 transition-colors shadow-[0_4px_14px_0_rgba(212,175,55,0.39)]">
                                                Upload Bukti Pembayaran
                                            </a>
                                        <?php elseif (!empty($order['payment_id']) && !empty($order['proof_image'])): ?>
                                            <div
                                                class="text-sm text-center bg-green-500/10 text-green-400 p-3 rounded-lg border border-green-500/20">
                                                Bukti pembayaran sudah diupload.
                                            </div>
                                        <?php elseif (!empty($order['payment_method_type']) && $order['payment_method_type'] === 'onsite'): ?>
                                            <div
                                                class="text-sm text-center bg-gold/10 text-gold p-3 rounded-lg border border-gold/20">
                                                Bayar saat ambil di toko.
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($order['status'] === 'ready_pickup'): ?>
                                            <div
                                                class="text-sm text-center bg-purple-500/10 text-purple-300 p-3 rounded-lg border border-purple-500/20">
                                                🎉 Pesanan siap diambil!
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($order['status'] === 'waiting_payment'): ?>
                                        <div class="mt-4 pt-4 border-t border-white/5">
                                            <button type="button" onclick="openCancelModal(<?= (int) $order['id']; ?>)"
                                                class="w-full bg-transparent border border-red-500/50 text-red-500 py-3 rounded-xl font-bold hover:bg-red-500/10 transition-colors">
                                                Batalkan Pesanan
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php if ($checkoutSuccessOrderId || $orderSuccess || $orderError): ?>
    <div id="notifModal"
        class="fixed inset-0 z-[110] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeNotifModal()"></div>

        <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
            id="notifModalContent">
            <div class="p-8 text-center">
                <?php if ($checkoutSuccessOrderId || $orderSuccess): ?>
                    <div
                        class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-500/30">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Berhasil!</h3>
                    <?php if ($checkoutSuccessOrderId): ?>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Pesanan berhasil dibuat. Nomor pesanan kamu: <strong
                                class="text-white">#<?= (int) $checkoutSuccessOrderId; ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="text-gray-300 text-sm leading-relaxed"><?= htmlspecialchars($orderSuccess); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div
                        class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Terjadi Kesalahan!</h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?= htmlspecialchars($orderError); ?></p>
                <?php endif; ?>
            </div>

            <div class="flex border-t border-white/10">
                <button type="button" onclick="closeNotifModal()"
                    class="w-full py-4 text-gold font-bold text-sm hover:bg-white/5 transition-colors">
                    Oke, Mengerti
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<div id="cancelModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
        id="modalContent">
        <div class="p-8 text-center">
            <div
                class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Batalkan Pesanan?</h3>
            <p class="text-gray-300 text-sm leading-relaxed">
                Tindakan ini tidak dapat diurungkan. Pesanan yang dibatalkan tidak bisa dilanjutkan kembali.
            </p>
        </div>

        <div class="flex border-t border-white/10">
            <button type="button" onclick="closeCancelModal()"
                class="flex-1 py-4 text-gray-300 font-medium text-sm hover:bg-white/5 transition-colors border-r border-white/10">
                Kembali
            </button>
            <form action="actions/cancel-order.php" method="POST" class="flex-1 flex">
                <input type="hidden" name="order_id" id="modalOrderId" value="">
                <button type="submit"
                    class="w-full py-4 text-red-400 font-bold text-sm hover:bg-white/5 transition-colors">
                    Ya, Batalkan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // --- LOGIC UNTUK MODAL BATAL PESANAN ---
    const cancelModal = document.getElementById('cancelModal');
    const modalContent = document.getElementById('modalContent');
    const inputOrderId = document.getElementById('modalOrderId');

    function openCancelModal(orderId) {
        inputOrderId.value = orderId;
        cancelModal.classList.remove('hidden');
        cancelModal.classList.add('flex');

        setTimeout(() => {
            cancelModal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }

    function closeCancelModal() {
        cancelModal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            cancelModal.classList.add('hidden');
            cancelModal.classList.remove('flex');
            inputOrderId.value = '';
        }, 300);
    }

    // --- LOGIC UNTUK MODAL NOTIFIKASI SUKSES/ERROR ---
    <?php if ($checkoutSuccessOrderId || $orderSuccess || $orderError): ?>
        document.addEventListener("DOMContentLoaded", function () {
            const notifModal = document.getElementById('notifModal');
            const notifModalContent = document.getElementById('notifModalContent');
            setTimeout(() => {
                notifModal.classList.remove('opacity-0');
                notifModalContent.classList.remove('scale-95');
                notifModalContent.classList.add('scale-100');
            }, 50);
        });

        function closeNotifModal() {
            const notifModal = document.getElementById('notifModal');
            const notifModalContent = document.getElementById('notifModalContent');
            notifModal.classList.add('opacity-0');
            notifModalContent.classList.remove('scale-100');
            notifModalContent.classList.add('scale-95');
            setTimeout(() => {
                notifModal.style.display = 'none';
            }, 300);
        }
    <?php endif; ?>
</script>