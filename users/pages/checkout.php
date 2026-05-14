<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Mode "Bayar Sekarang" dari halaman produk pakai bucket session terpisah
$isBuyNow = !empty($_SESSION['buy_now']);

// --- LOGIKA CHECKOUT SHOPEE STYLE ---
$fullCart = $_SESSION['cart'] ?? [];
$checkoutItems = [];

if ($isBuyNow) {
    $checkoutItems = $_SESSION['buy_now'];
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {
        foreach ($_POST['selected_items'] as $idx) {
            if (isset($fullCart[$idx])) {
                $checkoutItems[$idx] = $fullCart[$idx];
            }
        }
        $_SESSION['checkout_items'] = $checkoutItems;
    } elseif (isset($_SESSION['checkout_items'])) {
        $checkoutItems = $_SESSION['checkout_items'];
    } else {
        header('Location: index.php?page=cart');
        exit;
    }
}

$cart = $checkoutItems;

if (empty($cart)) {
    header('Location: index.php?page=cart');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT * FROM addresses
    WHERE user_id = ? AND type = 'user'
    ORDER BY is_default DESC, id DESC
");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT * FROM payment_methods
    WHERE is_active = 1
    ORDER BY id ASC
");
$paymentMethods = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT * FROM addresses
    WHERE type = 'store'
    ORDER BY is_default DESC, id DESC
    LIMIT 1
");
$storeAddress = $stmt->fetch();

$grandTotal = 0;
foreach ($cart as $item) {
    $grandTotal += (int) $item['price'];
}

$selectedAddressId = !empty($addresses) ? $addresses[0]['id'] : null;
$selectedPaymentId = !empty($paymentMethods) ? $paymentMethods[0]['id'] : null;
?>

<div class="relative">
    <div class="max-w-3xl mx-auto mb-6 px-4 md:px-0">
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Checkout</h1>
        <p class="text-gray-400 text-sm md:text-base">Pastikan alamat dan rincian pesanan sudah benar.</p>
    </div>

    <?php if (empty($addresses)): ?>
        <div class="max-w-3xl mx-auto bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-8 md:p-10 text-center shadow-lg mx-4 md:mx-0">
            <div class="w-16 h-16 bg-white/5 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <p class="text-base md:text-lg text-gray-300 mb-6">Kamu belum punya kontak pemesan.</p>
            <a href="index.php?page=addresses"
                class="inline-block bg-gold text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300">
                Tambah Kontak
            </a>
        </div>
    <?php elseif (empty($paymentMethods)): ?>
        <div class="max-w-3xl mx-auto bg-red-500/10 border border-red-500/20 backdrop-blur-md rounded-2xl p-6 text-center shadow-lg mx-4 md:mx-0">
            <p class="text-red-400 text-sm md:text-base font-medium">Metode pembayaran belum tersedia. Hubungi admin terlebih dahulu.</p>
        </div>
    <?php else: ?>

        <form id="checkoutForm" action="actions/checkout-process.php" method="POST"
            class="max-w-3xl mx-auto space-y-4 pb-40 md:pb-48 px-4 md:px-0">
            
            <?php foreach (array_keys($cart) as $cartKey): ?>
                <input type="hidden" name="checkout_keys[]" value="<?= $cartKey; ?>">
            <?php endforeach; ?>

            <div id="addressPreviewContainer">
                <?php foreach ($addresses as $index => $address): ?>
                    <div id="preview-address-<?= $address['id']; ?>" class="address-preview-item <?= $index === 0 ? 'block' : 'hidden'; ?>">
                        <div class="bg-black/40 border border-white/5 rounded-2xl overflow-hidden hover:border-gold/30 transition-colors cursor-pointer" onclick="openAddressModal()">
                            <div class="h-1 w-full bg-gradient-to-r from-gold via-yellow-200 to-gold opacity-80"></div>
                            <div class="p-4 sm:p-5 flex items-center gap-3 sm:gap-4">
                                <div class="text-gold shrink-0">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-white text-[14px] sm:text-[15px] font-bold mb-1 truncate">
                                        Alamat Pengiriman
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1.5">
                                        <span class="text-gray-200 text-[13px] sm:text-sm font-medium"><?= htmlspecialchars($address['recipient_name']); ?></span>
                                        <span class="text-gray-400 text-[12px] sm:text-[13px]">(<?= htmlspecialchars($address['whatsapp_number']); ?>)</span>
                                        <?php if ($address['is_default']): ?>
                                            <span class="hidden sm:inline-block text-[9px] bg-gold/20 text-gold px-1.5 py-0.5 rounded border border-gold/30 uppercase tracking-wider font-bold">Utama</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-400 text-[12px] sm:text-[13px] leading-relaxed line-clamp-2">
                                        <?= htmlspecialchars($address['address_text']); ?>
                                    </div>
                                </div>
                                <div class="text-gray-500 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-black/40 border border-white/5 rounded-2xl p-4 sm:p-5">
                <div class="flex items-center gap-2 border-b border-white/10 pb-3 mb-4">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-white font-bold text-[14px] sm:text-[15px]">Anyeong Gift</span>
                </div>

                <div class="space-y-4">
                    <?php foreach ($cart as $item): ?>
                        <?php 
                            // Query untuk mengambil gambar produk
                            $productId = $item['product_id'] ?? 0;
                            $stmtImg = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                            $stmtImg->execute([$productId]);
                            $imgRow = $stmtImg->fetch();
                            
                            $imgSrc = '';
                            if ($imgRow && !empty($imgRow['image_path'])) {
                                $imgFile = basename($imgRow['image_path']);
                                $imgSrc = "../public/uploads/products/" . $imgFile;
                            }
                        ?>
                        <div class="flex gap-3 sm:gap-4">
                            <?php if ($imgSrc): ?>
                                <img src="<?= htmlspecialchars($imgSrc); ?>" alt="<?= htmlspecialchars($item['product_name']); ?>" 
                                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl shrink-0 border border-white/10">
                            <?php else: ?>
                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/5 border border-white/10 rounded-xl shrink-0 flex items-center justify-center text-gray-500">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    <div class="text-white text-[13px] sm:text-sm font-medium line-clamp-2 leading-snug">
                                        <?= htmlspecialchars($item['product_name']); ?>
                                    </div>
                                    <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                        <?php 
                                            $opsiText = [];
                                            foreach($item['options'] as $v) {
                                                $opsiText[] = is_array($v) ? implode(', ', $v) : $v;
                                            }
                                        ?>
                                        <div class="text-gray-400 text-[10px] sm:text-[11px] bg-white/5 inline-block px-1.5 py-0.5 rounded mt-1.5 truncate max-w-full border border-white/5">
                                            Variasi: <?= htmlspecialchars(implode(', ', $opsiText)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['custom_input'])): ?>
                                        <div class="text-[10px] sm:text-[11px] text-gold/80 italic mt-1 truncate">
                                            "<?= htmlspecialchars($item['custom_input']); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <div class="text-gold font-bold text-[13px] sm:text-sm">
                                        Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                                    </div>
                                    <div class="text-gray-400 text-[11px] sm:text-xs">x1</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="paymentPreviewContainer">
                <?php foreach ($paymentMethods as $index => $method): ?>
                    <div id="preview-payment-<?= $method['id']; ?>" class="payment-preview-item <?= $index === 0 ? 'block' : 'hidden'; ?>">
                        <div class="bg-black/40 border border-white/5 rounded-2xl p-4 sm:p-5 flex items-center justify-between cursor-pointer hover:border-gold/30 transition-colors" onclick="openPaymentModal()">
                            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <span class="text-white text-[13px] sm:text-[15px] font-bold">Metode Pembayaran</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-2 min-w-0 justify-end pl-2">
                                <span class="text-gold text-[12px] sm:text-[13px] font-medium truncate text-right"><?= htmlspecialchars($method['name']); ?></span>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-black/40 border border-white/5 rounded-2xl p-4 sm:p-5">
                <div class="flex items-center gap-2 text-white text-[14px] sm:text-[15px] font-bold mb-3 border-b border-white/10 pb-3">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Rincian Pembayaran
                </div>
                <div class="space-y-2.5 text-[12px] sm:text-[13px]">
                    <div class="flex justify-between text-gray-400">
                        <span>Subtotal Produk</span>
                        <span class="text-gray-300">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between text-white font-bold pt-3 border-t border-white/5 mt-3 items-center">
                        <span class="text-[13px] sm:text-sm">Total Pembayaran</span>
                        <span class="text-gold text-base sm:text-lg">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($storeAddress): ?>
            <div class="bg-gold/10 border border-gold/20 rounded-2xl p-3.5 sm:p-4 flex gap-2 sm:gap-3 items-start">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-gold/90 text-[11px] sm:text-xs leading-relaxed">
                    Pesanan diambil langsung di toko setelah admin mengubah status menjadi <strong>"Siap Diambil"</strong>. Hubungi admin melalui WhatsApp untuk konfirmasi detail pengambilan.
                </div>
            </div>
            <?php endif; ?>

            <div class="h-6 w-full clear-both"></div>

            <div id="addressSelectModal"
                class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddressModal()"></div>
                
                <div class="relative bg-[#1a1a1a] border border-white/10 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform transition-transform duration-300 flex flex-col max-h-[85vh] scale-95"
                    id="addressModalContent">
                    
                    <div class="px-5 pb-4 pt-5 border-b border-white/10 flex justify-between items-center shrink-0 bg-[#222]">
                        <h3 class="text-lg font-bold text-white">Pilih Alamat</h3>
                        <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5 overflow-y-auto custom-scrollbar space-y-3 flex-1">
                        <?php foreach ($addresses as $address): ?>
                            <label class="flex items-start gap-3 sm:gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 cursor-pointer hover:border-gold/50 transition-colors">
                                <input type="radio" name="address_id" value="<?= $address['id']; ?>"
                                    onchange="updateAddressPreview(<?= $address['id']; ?>); closeAddressModal();"
                                    class="mt-0.5 sm:mt-1 w-4 h-4 accent-gold cursor-pointer shrink-0" <?= $address['id'] === $selectedAddressId ? 'checked' : ''; ?> required>
                                <div class="flex-1 min-w-0">
                                    <div class="text-white font-bold text-[13px] sm:text-sm mb-1 flex items-center flex-wrap gap-1.5">
                                        <?= htmlspecialchars($address['recipient_name']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="hidden sm:inline-block text-[9px] bg-gold/20 text-gold border border-gold/30 px-1.5 py-0.5 rounded uppercase tracking-wider font-bold">Utama</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-400 text-[11px] sm:text-xs leading-relaxed">
                                        <?= htmlspecialchars($address['whatsapp_number']); ?> <br>
                                        <span class="truncate block w-full mt-0.5"><?= htmlspecialchars($address['address_text']); ?></span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div id="paymentSelectModal"
                class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePaymentModal()"></div>
                
                <div class="relative bg-[#1a1a1a] border border-white/10 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform transition-transform duration-300 flex flex-col max-h-[85vh] scale-95"
                    id="paymentModalContent">
                    
                    <div class="px-5 pb-4 pt-5 border-b border-white/10 flex justify-between items-center shrink-0 bg-[#222]">
                        <h3 class="text-lg font-bold text-white">Metode Pembayaran</h3>
                        <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 sm:p-5 overflow-y-auto custom-scrollbar space-y-3 flex-1">
                        <?php foreach ($paymentMethods as $method): ?>
                            <label class="flex items-center gap-3 sm:gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 cursor-pointer hover:border-gold/50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="text-white font-bold text-[13px] sm:text-sm truncate">
                                        <?= htmlspecialchars($method['name']); ?>
                                    </div>
                                    <div class="text-gray-400 text-[11px] sm:text-xs mt-0.5 truncate">
                                        <?= $method['type'] === 'onsite' ? 'Bayar tunai di toko' : 'Transfer Bank / E-Wallet' ?>
                                    </div>
                                </div>
                                <input type="radio" name="payment_method_id" value="<?= $method['id']; ?>"
                                    onchange="updatePaymentPreview(<?= $method['id']; ?>); closePaymentModal();"
                                    class="w-4 h-4 sm:w-5 sm:h-5 accent-gold cursor-pointer shrink-0" <?= $method['id'] === $selectedPaymentId ? 'checked' : ''; ?> required>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-[80] bg-[#1a1a1a] border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.8)] px-4 py-3 md:py-4"
                style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));">
                <div class="max-w-3xl mx-auto flex items-center justify-between gap-4">
                    <div class="text-left flex-1 min-w-0">
                        <div class="text-[11px] md:text-xs uppercase tracking-wider text-gray-400 mb-0.5">Total Pembayaran</div>
                        <div class="text-gold font-bold text-xl md:text-2xl truncate drop-shadow-[0_0_10px_rgba(212,175,55,0.3)]">
                            Rp <?= number_format($grandTotal, 0, ',', '.'); ?>
                        </div>
                    </div>
                    <button type="submit" form="checkoutForm"
                        class="shrink-0 inline-flex items-center justify-center bg-gold text-black px-6 sm:px-8 md:px-10 py-3 sm:py-3.5 rounded-xl font-bold shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] hover:bg-yellow-400 transition-all duration-300 text-[13px] sm:text-[15px]">
                        Buat Pesanan
                    </button>
                </div>
            </div>

        </form>

    <?php endif; ?>
</div>

<?php
$loaderId = 'checkoutLoadingModal';
$loaderTitle = 'Memproses Pesanan...';
$loaderText = 'Mohon tunggu sebentar, pesananmu sedang dibuat.<br>Jangan tutup atau refresh halaman ini.';
include __DIR__ . '/../components/loader-modal.php';
?>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
</style>

<script>
    // --- LOGIC GANTI PREVIEW KONTAK & PEMBAYARAN ---
    function updateAddressPreview(id) {
        document.querySelectorAll('.address-preview-item').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });
        const activePreview = document.getElementById('preview-address-' + id);
        if (activePreview) {
            activePreview.classList.remove('hidden');
            activePreview.classList.add('block');
        }
    }

    function updatePaymentPreview(id) {
        document.querySelectorAll('.payment-preview-item').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });
        const activePreview = document.getElementById('preview-payment-' + id);
        if (activePreview) {
            activePreview.classList.remove('hidden');
            activePreview.classList.add('block');
        }
    }

    // --- LOGIC MODAL CENTERED ---
    function animateOpen(modal, content) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }

    function animateClose(modal, content) {
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    const addressModal = document.getElementById('addressSelectModal');
    const addressModalContent = document.getElementById('addressModalContent');
    function openAddressModal() { animateOpen(addressModal, addressModalContent); }
    function closeAddressModal() { animateClose(addressModal, addressModalContent); }

    const paymentModal = document.getElementById('paymentSelectModal');
    const paymentModalContent = document.getElementById('paymentModalContent');
    function openPaymentModal() { animateOpen(paymentModal, paymentModalContent); }
    function closePaymentModal() { animateClose(paymentModal, paymentModalContent); }

    // Modal loading saat submit checkout. Modal otomatis tertutup ketika
    // halaman navigasi ke halaman berikutnya (payment_upload atau orders)
    // setelah server selesai memproses pesanan.
    if (typeof window.attachTruckLoaderToForm === 'function') {
        window.attachTruckLoaderToForm('checkoutForm', 'checkoutLoadingModal');
    }
</script>