<?php
$userId = $_SESSION['user_id'];
$orderId = (int) ($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    $_SESSION['order_error'] = 'Order tidak valid.';
    header('Location: index.php?page=orders');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        o.*,
        p.id AS payment_id,
        p.status AS payment_status,
        p.proof_image,
        pm.name AS payment_method_name,
        pm.type AS payment_method_type,
        pm.account_info AS payment_account_info
    FROM orders o
    INNER JOIN payments p ON p.order_id = o.id
    INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['order_error'] = 'Pesanan tidak ditemukan.';
    header('Location: index.php?page=orders');
    exit;
}

if ($order['payment_method_type'] === 'onsite') {
    $_SESSION['order_error'] = 'Pesanan ini menggunakan pembayaran di toko, jadi tidak perlu upload bukti.';
    header('Location: index.php?page=orders');
    exit;
}

// Proses Data Rekening
$accountDetails = [];
$qrisImage = null;
if (!empty($order['payment_account_info'])) {
    $decoded = json_decode($order['payment_account_info'], true);
    if (is_array($decoded)) {
        if (isset($decoded['info']) || isset($decoded['image'])) {
            if (!empty($decoded['info'])) {
                $accountDetails['No. Rekening'] = $decoded['info'];
            }
            if (!empty($decoded['image'])) {
                $qrisImage = $decoded['image'];
            }
        } else {
            $accountDetails = $decoded;
        }
    } else {
        $accountDetails['No. Rekening'] = trim($order['payment_account_info'], '"');
    }
}
$isQrisMethod = strtolower($order['payment_method_type'] ?? '') === 'qris';

// Ambil nomor WA admin
$stmtStore = $pdo->query("SELECT whatsapp_admin FROM store_settings LIMIT 1");
$storeSettings = $stmtStore->fetch();
$waNumber = $storeSettings ? $storeSettings['whatsapp_admin'] : '';

// Format Nomor Pesanan (ORD-XXXXX)
$formattedOrderNumber = "ORD-" . str_pad($order['id'], 5, '0', STR_PAD_LEFT);

if (!empty($order['proof_image'])) {
    $waText = "Halo admin Anyeoung Gift, saya sudah melakukan pembayaran dan mengunggah bukti transfer untuk pesanan *" . $formattedOrderNumber . "*. Mohon segera dicek dan diproses ya. Terima kasih.";
} else {
    $waText = "Halo admin Anyeoung Gift, saya ingin konfirmasi mengenai pesanan saya dengan nomor *" . $formattedOrderNumber . "*.";
}
$waLink = "https://wa.me/" . preg_replace('/\D+/', '', $waNumber) . "?text=" . urlencode($waText);

$uploadSuccess = $_SESSION['upload_success'] ?? null;
$uploadError = $_SESSION['upload_error'] ?? null;
unset($_SESSION['upload_success'], $_SESSION['upload_error']);
?>

<div class="max-w-4xl mx-auto space-y-6 relative">

    <div class="mb-6 md:mb-8">
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-1 md:mb-2">Upload Pembayaran</h1>
    </div>

    <div class="grid lg:grid-cols-2 gap-5 lg:gap-8 items-start">

        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 md:p-8 backdrop-blur-md">
            <h2 class="text-[17px] md:text-xl font-title text-gold mb-4 md:mb-6 border-b border-white/10 pb-3 md:pb-4">
                Detail Tagihan</h2>

            <div class="space-y-3 md:space-y-4">
                <div
                    class="flex justify-between items-center bg-black/30 p-3.5 md:p-4 rounded-xl border border-white/5">
                    <span class="text-[12px] md:text-sm text-gray-400">Nomor Pesanan</span>
                    <span
                        class="text-white text-[13px] md:text-base font-bold tracking-wider"><?= $formattedOrderNumber; ?></span>
                </div>

                <div class="bg-black/30 p-3.5 md:p-4 rounded-xl border border-white/5">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[12px] md:text-sm text-gray-400">Metode Pembayaran</span>
                        <span
                            class="text-white text-[13px] md:text-base font-semibold"><?= htmlspecialchars($order['payment_method_name']); ?></span>
                    </div>

                    <?php if (!empty($accountDetails)): ?>
                        <div class="mt-3 p-3 bg-white/5 rounded-lg border border-white/10 space-y-2">
                            <?php foreach ($accountDetails as $label => $value): ?>
                                <div class="text-[11px] md:text-xs flex justify-between items-center gap-2">
                                    <span class="text-gray-500 uppercase"><?= htmlspecialchars($label); ?></span>
                                    <div onclick="copyToClipboard('<?= htmlspecialchars($value); ?>')"
                                        class="flex items-center gap-1.5 cursor-pointer group bg-black/40 px-2 py-1 rounded border border-white/5 hover:border-gold/30 transition-colors"
                                        title="Salin nomor rekening">
                                        <span
                                            class="text-gold font-mono font-bold tracking-widest"><?= htmlspecialchars($value); ?></span>
                                        <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-gold transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isQrisMethod && !empty($qrisImage)): ?>
                        <div class="mt-4 bg-white/95 rounded-xl p-3 flex flex-col items-center gap-2">
                            <img src="../public/uploads/payment_methods/<?= htmlspecialchars($qrisImage); ?>"
                                alt="QRIS <?= htmlspecialchars(storeNameRaw($pdo ?? null)); ?>" class="w-full max-w-[200px] aspect-square object-contain">
                            <p class="text-[10px] uppercase tracking-wider text-gray-600 font-bold">Scan QRIS di atas</p>
                        </div>
                    <?php elseif ($isQrisMethod): ?>
                        <div
                            class="mt-4 text-[11px] md:text-xs text-yellow-300 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3 text-center">
                            Gambar QRIS belum diunggah oleh admin. Mohon hubungi admin untuk panduan pembayaran.
                        </div>
                    <?php endif; ?>
                </div>

                <div
                    class="flex justify-between items-center bg-black/30 p-3.5 md:p-4 rounded-xl border border-white/5 flex-nowrap overflow-hidden gap-2">
                    <span class="text-[12px] md:text-sm text-gray-400 whitespace-nowrap shrink-0">Status
                        Pembayaran</span>
                    <?php if ($order['payment_status'] === 'confirmed'): ?>
                        <span
                            class="text-green-400 font-bold bg-green-500/10 px-2.5 py-1 rounded-full text-[10px] md:text-sm border border-green-500/20 whitespace-nowrap truncate">Terkonfirmasi</span>
                    <?php elseif ($order['payment_status'] === 'rejected'): ?>
                        <span
                            class="text-red-400 font-bold bg-red-500/10 px-2.5 py-1 rounded-full text-[10px] md:text-sm border border-red-500/20 whitespace-nowrap truncate">Ditolak</span>
                    <?php else: ?>
                        <span
                            class="text-yellow-400 font-bold bg-yellow-500/10 px-2.5 py-1 rounded-full text-[10px] md:text-sm border border-yellow-500/20 whitespace-nowrap truncate">Menunggu
                            Verifikasi</span>
                    <?php endif; ?>
                </div>

                <div onclick="copyToClipboard('<?= (int) $order['total_price']; ?>')"
                    class="flex justify-between items-center bg-gold/10 p-3.5 md:p-4 rounded-xl border border-gold/20 cursor-pointer group hover:bg-gold/20 transition-colors"
                    title="Salin nominal">
                    <span class="text-[13px] md:text-sm text-gray-300 font-medium">Total Pembayaran</span>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-gold font-bold text-[17px] md:text-2xl drop-shadow-[0_0_10px_rgba(212,175,55,0.3)]">
                            Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                        </span>
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gold/50 group-hover:text-gold transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 md:p-8 backdrop-blur-md">
            <h2 class="text-[17px] md:text-xl font-title text-gold mb-4 md:mb-6 border-b border-white/10 pb-3 md:pb-4">
                Bukti Transfer</h2>

            <?php if (!empty($order['proof_image'])): ?>
                <div class="mb-5 md:mb-6">
                    <div class="text-[12px] md:text-sm text-green-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Bukti pembayaran sudah diunggah.
                    </div>
                    <div
                        class="relative group rounded-2xl overflow-hidden border border-white/10 bg-black/50 aspect-[4/3] flex items-center justify-center">
                        <img src="../public/uploads/payments/<?= htmlspecialchars($order['proof_image']); ?>"
                            alt="Bukti Pembayaran"
                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                    </div>
                </div>
            <?php else: ?>
                <div
                    class="text-[12px] md:text-sm text-yellow-400 mb-5 md:mb-6 flex items-start gap-2 bg-yellow-500/10 p-3 rounded-xl border border-yellow-500/20">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <span>Silakan transfer dengan nominal dan rekening sesuai rincian, lalu upload buktinya di sini.</span>
                </div>
            <?php endif; ?>

            <form id="paymentUploadForm" action="actions/upload-payment.php" method="POST" enctype="multipart/form-data"
                class="space-y-4 md:space-y-5">
                <input type="hidden" name="order_id" value="<?= (int) $order['id']; ?>">

                <div class="bg-black/30 p-3.5 md:p-4 rounded-xl border border-white/5 border-dashed">
                    <label class="block text-[13px] md:text-sm font-medium text-gray-300 mb-3">Pilih File Bukti
                        (JPG/PNG)</label>
                    <input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.webp" required
                        class="block w-full text-[12px] md:text-sm text-gray-400 
                        file:mr-3 file:md:mr-4 file:py-2 file:md:py-2.5 file:px-4 file:md:px-6 file:rounded-lg file:md:rounded-xl file:border-0 
                        file:bg-white/10 file:text-gold file:font-bold hover:file:bg-white/20 hover:file:text-yellow-400 file:transition-colors file:cursor-pointer cursor-pointer">
                </div>

                <div class="flex gap-2 sm:gap-3 pt-2 items-stretch">
                    <button type="submit"
                        class="flex-1 bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] transition-all duration-300 text-[13px] md:text-[15px]">
                        <?= !empty($order['proof_image']) ? 'Upload Ulang Bukti' : 'Upload Bukti' ?>
                    </button>

                    <a href="<?= htmlspecialchars($waLink); ?>" target="_blank"
                        class="bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/30 px-4 sm:px-6 py-3.5 rounded-xl font-bold hover:bg-[#25D366] hover:text-white shadow-[0_0_15px_rgba(37,211,102,0.2)] transition-all duration-300 flex items-center justify-center gap-2 shrink-0"
                        title="Hubungi Admin">
                        <svg class="w-6 h-6 sm:w-5 sm:h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <span class="hidden sm:inline text-[13px] md:text-[15px]">Hubungi Admin</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$loaderId = 'paymentUploadLoadingModal';
$loaderTitle = 'Mengunggah Bukti...';
$loaderText = 'Mohon tunggu sebentar, bukti pembayaranmu sedang diproses.<br>Jangan tutup atau refresh halaman ini.';
include __DIR__ . '/../components/loader-modal.php';
?>

<div id="copyToast"
    class="fixed top-24 left-1/2 -translate-x-1/2 z-[150] bg-green-500/90 text-black px-4 py-2 rounded-full font-bold text-sm shadow-[0_5px_20px_rgba(34,197,94,0.4)] flex items-center gap-2 transform -translate-y-10 opacity-0 transition-all duration-300 pointer-events-none">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
    </svg>
    <span>Berhasil disalin!</span>
</div>

<script>
    // --- LOGIKA COPY TO CLIPBOARD ---
    function copyToClipboard(text) {
        // Fallback untuk browser lawas/mobile tertentu kalau writeText gagal
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToast();
        } catch (err) {
            console.error('Gagal menyalin', err);
            // Coba modern API
            navigator.clipboard.writeText(text).then(() => {
                showToast();
            });
        }
        document.body.removeChild(textArea);
    }

    // --- ANIMASI TOAST COPY ---
    let toastTimeout;
    function showToast() {
        const toast = document.getElementById('copyToast');

        // Reset animasi kalau diklik berkali-kali
        clearTimeout(toastTimeout);
        toast.classList.remove('opacity-0', '-translate-y-10');
        toast.classList.add('opacity-100', 'translate-y-0');

        toastTimeout = setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', '-translate-y-10');
        }, 2000);
    }

    // Modal loading saat upload bukti pembayaran.
    if (typeof window.attachTruckLoaderToForm === 'function') {
        window.attachTruckLoaderToForm('paymentUploadForm', 'paymentUploadLoadingModal');
    }
</script>