<?php
$userId = $_SESSION['user_id'];

$orderSuccess = $_SESSION['order_success'] ?? null;
$orderError = $_SESSION['order_error'] ?? null;
$checkoutSuccessOrderId = $_SESSION['checkout_success_order_id'] ?? null;

unset($_SESSION['order_success'], $_SESSION['order_error'], $_SESSION['checkout_success_order_id']);

// Pagination: 5 pesanan per halaman.
$perPage = 5;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmtCount->execute([$userId]);
$totalOrders = (int) $stmtCount->fetchColumn();
$totalPages = max(1, (int) ceil($totalOrders / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;

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
    LIMIT $perPage OFFSET $offset
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Nomor WhatsApp admin (untuk tombol "Hubungi Admin")
$stmtStore = $pdo->query("SELECT whatsapp_admin FROM store_settings LIMIT 1");
$storeSetting = $stmtStore->fetch();
$waAdminNumber = $storeSetting['whatsapp_admin'] ?? '';

// Status pesanan
function getCombinedOrderStatus($order)
{
    $orderStatus = $order['status'] ?? '';
    $paymentStatus = $order['payment_status'] ?? null;

    switch ($orderStatus) {
        case 'cancelled':
            return [
                'label' => 'Dibatalkan',
                'class' => 'bg-red-500/20 text-red-300 border border-red-500/30',
            ];
        case 'completed':
            return [
                'label' => 'Selesai',
                'class' => 'bg-green-500/20 text-green-300 border border-green-500/30',
            ];
        case 'ready_pickup':
            return [
                'label' => 'Pesanan Siap',
                'class' => 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
            ];
        case 'paid':
            if ($paymentStatus === 'pending') {
                return [
                    'label' => 'Menunggu Konfirmasi',
                    'class' => 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
                ];
            }
            return [
                'label' => 'Diproses',
                'class' => 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30',
            ];
        case 'waiting_payment':
        default:
            return [
                'label' => 'Belum Bayar',
                'class' => 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
            ];
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
                $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll();

                $addressSnapshot = [];
                if (!empty($order['address_snapshot'])) {
                    $decodedAddress = json_decode($order['address_snapshot'], true);
                    if (is_array($decodedAddress))
                        $addressSnapshot = $decodedAddress;
                }
                ?>

                <details
                    class="group bg-black/40 border border-gold/20 rounded-2xl backdrop-blur-md overflow-hidden transition-all duration-300 hover:border-gold/50">
                    <summary class="list-none cursor-pointer p-5 lg:p-6 [&::-webkit-details-marker]:hidden">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-gold font-bold text-lg md:text-xl">
                                        Pesanan #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
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
                                <?php $statusBadge = getCombinedOrderStatus($order); ?>
                                <span
                                    class="px-4 py-1.5 rounded-full text-xs font-bold tracking-wide <?= $statusBadge['class']; ?>">
                                    <?= htmlspecialchars($statusBadge['label']); ?>
                                </span>

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
                                    $stmtOpts = $pdo->prepare("SELECT * FROM order_item_options WHERE order_item_id = ? ORDER BY id ASC");
                                    $stmtOpts->execute([$item['id']]);
                                    $itemOptions = $stmtOpts->fetchAll();
                                    ?>
                                    <div
                                        class="bg-white/5 border border-white/10 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="text-white font-semibold text-lg mb-1 flex items-center gap-2">
                                                <span
                                                    class="text-xs font-mono bg-black/50 text-gold/80 px-2 py-0.5 rounded border border-gold/20">PRD-<?= str_pad($item['product_id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                <?= htmlspecialchars($item['product_name_snapshot']); ?>
                                            </div>

                                            <?php if (!empty($itemOptions)): ?>
                                                <div class="space-y-1 mt-2">
                                                    <?php
                                                    $seenCustomValues = [];
                                                    foreach ($itemOptions as $opt):
                                                        $displayValue = !empty($opt['custom_value']) ? $opt['custom_value'] : $opt['option_value_snapshot'];

                                                        if (!empty($opt['custom_value'])) {
                                                            if (in_array($displayValue, $seenCustomValues))
                                                                continue;
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

                                <div
                                    class="bg-white/5 border border-white/10 rounded-xl p-5 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                                            <span class="text-gray-300">Total Pembayaran</span>
                                            <span class="text-gold font-bold text-xl">
                                                Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($order['payment_method_type']) && $order['payment_method_type'] === 'onsite'): ?>
                                            <p class="text-gray-400 text-sm italic mb-4">Cash on Pick Up  bayar tunai saat ambil pesanan di toko.</p>
                                        <?php elseif ($order['status'] === 'paid' || $order['payment_status'] === 'verified'): ?>
                                            <p class="text-green-400 text-sm font-medium italic mb-4">✅ Pembayaran terkonfirmasi.
                                            </p>
                                        <?php elseif (!empty($order['proof_image'])): ?>
                                            <p class="text-blue-400 text-sm italic mb-4">⏳ Bukti sedang ditinjau admin.</p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-auto pt-2">
                                        <?php
                                        // LOGIC PENENTUAN TOMBOL
                                        $showUpload = ($order['status'] === 'waiting_payment' && empty($order['proof_image']) && $order['payment_method_type'] !== 'onsite');
                                        $showComplete = ($order['status'] === 'ready_pickup');

                                        // Siapkan Link WA
                                        if ($order['status'] === 'waiting_payment') {
                                            $waOrderText = "Halo admin Anyeong Gift, saya ingin bertanya soal pembayaran pesanan saya *#ORD-" . str_pad($order['id'], 5, '0', STR_PAD_LEFT) . "*.";
                                        } elseif ($order['status'] === 'paid' || $order['status'] === 'ready_pickup') {
                                            $waOrderText = "Halo admin Anyeong Gift, saya ingin menanyakan progres pesanan saya *#ORD-" . str_pad($order['id'], 5, '0', STR_PAD_LEFT) . "*.";
                                        } else {
                                            $waOrderText = "Halo admin Anyeong Gift, saya ingin bertanya soal pesanan saya *#ORD-" . str_pad($order['id'], 5, '0', STR_PAD_LEFT) . "*.";
                                        }
                                        $waOrderLink = 'https://wa.me/' . preg_replace('/\D+/', '', $waAdminNumber) . '?text=' . urlencode($waOrderText);
                                        ?>

                                        <div class="flex items-stretch gap-3 w-full">

                                            <?php if ($showUpload): ?>
                                                <a href="index.php?page=payment_upload&order_id=<?= (int) $order['id']; ?>"
                                                    class="flex-1 inline-flex items-center justify-center text-center bg-gold text-black px-3 py-3 rounded-xl font-bold text-sm hover:bg-yellow-400 transition-colors shadow-lg">
                                                    Upload Bukti
                                                </a>
                                            <?php elseif ($showComplete): ?>
                                                <form action="actions/complete-order.php" method="POST" class="flex-1 flex"
                                                    onsubmit="return confirm('Tandai pesanan ini sebagai selesai?');">
                                                    <input type="hidden" name="order_id" value="<?= (int) $order['id']; ?>">
                                                    <button type="submit"
                                                        class="w-full inline-flex items-center justify-center bg-green-500 text-black px-3 py-3 rounded-xl font-bold text-sm hover:bg-green-400 transition-colors shadow-lg">
                                                        Selesaikan Pesanan
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($order['status'] !== 'cancelled' && !empty($waAdminNumber)): ?>
                                                <?php if ($showUpload || $showComplete): ?>
                                                    <a href="<?= htmlspecialchars($waOrderLink); ?>" target="_blank" rel="noopener"
                                                        class="flex items-center justify-center shrink-0 w-[3.25rem] md:w-auto md:flex-1 bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/30 hover:bg-[#25D366] hover:text-white rounded-xl transition-colors">
                                                        <svg class="w-5 h-5 md:mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                                        </svg>
                                                        <span class="hidden md:inline font-bold text-sm">Hubungi Admin</span>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= htmlspecialchars($waOrderLink); ?>" target="_blank" rel="noopener"
                                                        class="w-full flex items-center justify-center py-3 bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/30 hover:bg-[#25D366] hover:text-white rounded-xl font-bold text-sm transition-colors">
                                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                                        </svg>
                                                        Hubungi Admin
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($order['status'] === 'waiting_payment'): ?>
                                            <button type="button" onclick="openCancelModal(<?= (int) $order['id']; ?>)"
                                                class="w-full mt-3 bg-transparent border border-red-500/50 text-red-500 py-2.5 rounded-xl font-bold text-sm hover:bg-red-500/10 transition-colors">
                                                Batalkan Pesanan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-8 flex items-center justify-center gap-2 flex-wrap">
                <?php
                // FIX 2: Format Pagination Hide Text di Mobile
                $pagerBase = function ($targetPage, $label, $disabled = false, $active = false) {
                    $cls = 'min-w-[2.5rem] h-10 px-3 rounded-full text-sm font-bold inline-flex items-center justify-center transition border';
                    if ($disabled) {
                        $cls .= ' border-white/10 text-gray-600 bg-white/5 cursor-not-allowed';
                        return '<span class="' . $cls . '">' . $label . '</span>';
                    }
                    if ($active) {
                        $cls .= ' border-gold bg-gold text-black';
                        return '<span class="' . $cls . '">' . $label . '</span>';
                    }
                    $cls .= ' border-white/10 text-gray-200 bg-white/5 hover:border-gold hover:text-gold';
                    return '<a href="index.php?page=orders&p=' . (int) $targetPage . '" class="' . $cls . '">' . $label . '</a>';
                };

                $prevIcon = '<svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg><span class="hidden sm:inline">Prev</span>';
                $nextIcon = '<span class="hidden sm:inline">Next</span><svg class="w-4 h-4 sm:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
                ?>
                <?= $pagerBase($currentPage - 1, $prevIcon, $currentPage <= 1); ?>
                <?php for ($pIdx = 1; $pIdx <= $totalPages; $pIdx++): ?>
                    <?= $pagerBase($pIdx, (string) $pIdx, false, $pIdx === $currentPage); ?>
                <?php endfor; ?>
                <?= $pagerBase($currentPage + 1, $nextIcon, $currentPage >= $totalPages); ?>
            </nav>
            <div class="text-center text-xs text-gray-500 mt-2">
                Halaman <?= $currentPage; ?> dari <?= $totalPages; ?> &middot; Total <?= $totalOrders; ?> pesanan
            </div>
        <?php endif; ?>

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
                                class="text-white">#ORD-<?= str_pad($checkoutSuccessOrderId, 5, '0', STR_PAD_LEFT); ?></strong>
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