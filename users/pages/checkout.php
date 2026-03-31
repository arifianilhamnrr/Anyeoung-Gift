<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$cart = $_SESSION['cart'] ?? [];

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

// Ambil item default untuk ditampilkan di preview (index 0)
$selectedAddressId = !empty($addresses) ? $addresses[0]['id'] : null;
$selectedPaymentId = !empty($paymentMethods) ? $paymentMethods[0]['id'] : null;
?>

<div class="space-y-6 relative">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Checkout</h1>
        <p class="text-gray-400 text-sm md:text-base">Lengkapi data di bawah ini untuk memproses pesanan kamu.</p>
    </div>

    <?php if (empty($addresses)): ?>
        <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-10 text-center">
            <div class="w-16 h-16 bg-white/5 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <p class="text-lg text-gray-300 mb-6">Kamu belum punya kontak pemesan.</p>
            <a href="index.php?page=addresses"
                class="inline-block bg-gold text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300">
                Tambah Kontak Dulu
            </a>
        </div>
    <?php elseif (empty($paymentMethods)): ?>
        <div class="bg-red-500/10 border border-red-500/20 backdrop-blur-md rounded-2xl p-6 text-center">
            <p class="text-red-400 font-medium">Metode pembayaran belum tersedia. Hubungi admin terlebih dahulu.</p>
        </div>
    <?php else: ?>

        <form action="actions/checkout-process.php" method="POST" class="grid lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <div class="lg:col-span-7 space-y-6">

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-title text-gold">Kontak Pemesan</h2>
                        </div>
                        <button type="button" onclick="openAddressModal()"
                            class="text-sm font-bold text-gold bg-gold/10 hover:bg-gold/20 px-4 py-2 rounded-full transition-colors">
                            Ubah
                        </button>
                    </div>

                    <div id="addressPreviewContainer">
                        <?php foreach ($addresses as $index => $address): ?>
                            <div id="preview-address-<?= $address['id']; ?>"
                                class="address-preview-item <?= $index === 0 ? 'block' : 'hidden'; ?>">
                                <div class="bg-black/40 border border-white/5 rounded-2xl p-5">
                                    <div class="text-white font-bold text-lg flex items-center gap-2 mb-1">
                                        <?= htmlspecialchars($address['recipient_name']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span
                                                class="text-[10px] bg-gold text-black px-2 py-0.5 rounded-full uppercase tracking-wider">Utama</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gold text-sm font-medium mb-2">
                                        <?= htmlspecialchars($address['whatsapp_number']); ?>
                                    </div>
                                    <div class="text-gray-400 text-sm leading-relaxed whitespace-pre-line">
                                        <?= htmlspecialchars($address['address_text']); ?>
                                    </div>
                                    <?php if (!empty($address['notes'])): ?>
                                        <div
                                            class="text-gray-500 text-xs mt-3 bg-white/5 p-2 rounded-lg italic border border-white/5">
                                            Catatan: <?= htmlspecialchars($address['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                    </path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-title text-gold">Metode Pembayaran</h2>
                        </div>
                        <button type="button" onclick="openPaymentModal()"
                            class="text-sm font-bold text-gold bg-gold/10 hover:bg-gold/20 px-4 py-2 rounded-full transition-colors">
                            Ubah
                        </button>
                    </div>

                    <div id="paymentPreviewContainer">
                        <?php foreach ($paymentMethods as $index => $method): ?>
                            <?php
                            $accountInfoArray = [];
                            $accountInfoString = '';
                            if (!empty($method['account_info'])) {
                                $decoded = json_decode($method['account_info'], true);
                                if (is_array($decoded)) {
                                    $accountInfoArray = $decoded;
                                } else {
                                    $accountInfoString = trim($method['account_info'], '"');
                                }
                            }
                            ?>
                            <div id="preview-payment-<?= $method['id']; ?>"
                                class="payment-preview-item <?= $index === 0 ? 'block' : 'hidden'; ?>">
                                <div
                                    class="bg-black/40 border border-white/5 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <div class="text-white font-bold mb-2 text-lg">
                                            <?= htmlspecialchars($method['name']); ?>
                                        </div>
                                        <?php if ($method['type'] === 'onsite'): ?>
                                            <div class="text-gray-500 text-xs italic flex items-center gap-1">
                                                <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Bayar tunai saat ambil pesanan di toko.
                                            </div>
                                        <?php else: ?>
                                            <div class="text-gray-500 text-xs italic flex items-center gap-1">
                                                <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                </svg>
                                                Upload bukti transfer setelah checkout.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($accountInfoArray)): ?>
                                        <div
                                            class="text-gray-400 text-xs space-y-1.5 p-3 bg-white/5 rounded-xl border border-white/5 md:min-w-[200px]">
                                            <?php foreach ($accountInfoArray as $key => $value): ?>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-gray-500 uppercase tracking-wider text-[10px] mb-0.5"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></span>
                                                    <span
                                                        class="text-gold font-mono tracking-wide text-sm"><?= htmlspecialchars($value); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($accountInfoString !== '' && $accountInfoString !== 'null'): ?>
                                        <div
                                            class="text-gray-400 text-xs p-3 bg-white/5 rounded-xl border border-white/5 md:min-w-[200px]">
                                            <span class="text-gray-500 uppercase tracking-wider text-[10px] block mb-1">No. Rekening
                                                / Info</span>
                                            <span
                                                class="text-gold font-mono font-bold text-sm tracking-wide"><?= htmlspecialchars($accountInfoString); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-title text-gold">Lokasi Pengambilan</h2>
                    </div>

                    <div class="bg-black/40 border border-white/10 rounded-2xl p-5">
                        <?php if ($storeAddress): ?>
                            <div class="text-white font-bold text-lg mb-1">
                                <?= htmlspecialchars($storeAddress['recipient_name']); ?>
                            </div>
                            <div class="text-gold text-sm font-medium mb-3">
                                WA: <?= htmlspecialchars($storeAddress['whatsapp_number']); ?>
                            </div>
                            <div class="text-gray-400 text-sm leading-relaxed whitespace-pre-line mb-4">
                                <?= htmlspecialchars($storeAddress['address_text']); ?>
                            </div>
                            <div
                                class="bg-gold/10 border border-gold/20 text-gold text-xs p-3 rounded-lg flex gap-2 items-start">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Pesanan diambil langsung di toko setelah admin mengubah status menjadi <strong>"Siap
                                        Diambil"</strong>.</span>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-400 text-sm italic">
                                Alamat toko belum diatur admin.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md sticky top-24">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-title text-gold">Ringkasan Pesanan</h3>
                    </div>

                    <div class="space-y-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach ($cart as $item): ?>
                            <div class="bg-black/30 border border-white/5 rounded-xl p-4">
                                <div class="text-white font-bold mb-1">
                                    <?= htmlspecialchars($item['product_name']); ?>
                                </div>
                                <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                    <div class="space-y-1 my-2">
                                        <?php foreach ($item['options'] as $optionName => $optionValue): ?>
                                            <div class="text-xs text-gray-400 flex">
                                                <span class="w-24 shrink-0"><?= htmlspecialchars($optionName); ?></span>
                                                <span class="text-gray-300">:
                                                    <?php if (is_array($optionValue)): ?>
                                                        <?= htmlspecialchars(implode(', ', $optionValue)); ?>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($optionValue); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($item['custom_input'])): ?>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Tulisan: <span
                                            class="text-gold italic">"<?= htmlspecialchars($item['custom_input']); ?>"</span>
                                    </div>
                                <?php endif; ?>
                                <div class="text-gold font-bold mt-3">
                                    Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-white/10 my-5"></div>

                    <div class="flex items-center justify-between text-lg mb-6 px-1">
                        <span class="text-gray-300 font-medium">Total Pembayaran</span>
                        <span class="font-bold text-gold text-2xl drop-shadow-[0_0_10px_rgba(212,175,55,0.3)]">
                            Rp <?= number_format($grandTotal, 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="space-y-3">
                        <button type="submit"
                            class="w-full bg-gold text-black py-4 rounded-xl font-bold text-lg hover:bg-yellow-400 shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] transition-all duration-300">
                            Buat Pesanan
                        </button>

                        <a href="index.php?page=cart"
                            class="block w-full text-center border border-white/20 text-white bg-white/5 py-3.5 rounded-xl font-semibold hover:bg-white/10 transition-colors">
                            Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>

            <div id="addressSelectModal"
                class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddressModal()"></div>
                <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[85vh]"
                    id="addressModalContent">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center shrink-0">
                        <h3 class="text-xl font-title text-gold">Pilih Kontak Pemesan</h3>
                        <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar space-y-3 flex-1">
                        <?php foreach ($addresses as $address): ?>
                            <label
                                class="flex items-start gap-4 bg-black/40 border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-gold/50 transition-all duration-300">
                                <input type="radio" name="address_id" value="<?= $address['id']; ?>"
                                    onchange="updateAddressPreview(<?= $address['id']; ?>)"
                                    class="mt-1 w-4 h-4 accent-gold cursor-pointer" <?= $address['id'] === $selectedAddressId ? 'checked' : ''; ?> required>
                                <div class="flex-1">
                                    <div class="text-white font-bold mb-1">
                                        <?= htmlspecialchars($address['recipient_name']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span
                                                class="ml-2 text-[10px] bg-gold text-black px-2 py-0.5 rounded-full uppercase tracking-wider">Utama</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-400 text-xs leading-relaxed">
                                        <?= htmlspecialchars($address['whatsapp_number']); ?> <br>
                                        <span
                                            class="truncate block max-w-[250px]"><?= htmlspecialchars($address['address_text']); ?></span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-6 border-t border-white/10 shrink-0">
                        <button type="button" onclick="closeAddressModal()"
                            class="w-full bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 transition-colors">
                            Gunakan Kontak Ini
                        </button>
                    </div>
                </div>
            </div>

            <div id="paymentSelectModal"
                class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePaymentModal()"></div>
                <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[85vh]"
                    id="paymentModalContent">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center shrink-0">
                        <h3 class="text-xl font-title text-gold">Pilih Metode Pembayaran</h3>
                        <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar space-y-3 flex-1">
                        <?php foreach ($paymentMethods as $method): ?>
                            <label
                                class="flex items-start gap-4 bg-black/40 border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-gold/50 transition-all duration-300">
                                <input type="radio" name="payment_method_id" value="<?= $method['id']; ?>"
                                    onchange="updatePaymentPreview(<?= $method['id']; ?>)"
                                    class="mt-1 w-4 h-4 accent-gold cursor-pointer" <?= $method['id'] === $selectedPaymentId ? 'checked' : ''; ?> required>
                                <div class="flex-1">
                                    <div class="text-white font-bold mb-1">
                                        <?= htmlspecialchars($method['name']); ?>
                                    </div>
                                    <div class="text-gray-400 text-xs">
                                        <?= $method['type'] === 'onsite' ? 'Bayar di tempat' : 'Transfer Bank / E-Wallet' ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-6 border-t border-white/10 shrink-0">
                        <button type="button" onclick="closePaymentModal()"
                            class="w-full bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 transition-colors">
                            Gunakan Metode Ini
                        </button>
                    </div>
                </div>
            </div>

        </form>

    <?php endif; ?>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(212, 175, 55, 0.3);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(212, 175, 55, 0.6);
    }
</style>

<script>
    // --- LOGIC GANTI PREVIEW KONTAK ---
    function updateAddressPreview(id) {
        // Sembunyikan semua preview kontak
        document.querySelectorAll('.address-preview-item').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });
        // Munculkan preview kontak yang di-klik
        const activePreview = document.getElementById('preview-address-' + id);
        if (activePreview) {
            activePreview.classList.remove('hidden');
            activePreview.classList.add('block');
        }
    }

    // --- LOGIC GANTI PREVIEW PEMBAYARAN ---
    function updatePaymentPreview(id) {
        // Sembunyikan semua preview pembayaran
        document.querySelectorAll('.payment-preview-item').forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });
        // Munculkan preview pembayaran yang di-klik
        const activePreview = document.getElementById('preview-payment-' + id);
        if (activePreview) {
            activePreview.classList.remove('hidden');
            activePreview.classList.add('block');
        }
    }

    // --- LOGIC MODAL KONTAK ---
    const addressModal = document.getElementById('addressSelectModal');
    const addressModalContent = document.getElementById('addressModalContent');

    function openAddressModal() {
        addressModal.classList.remove('hidden');
        addressModal.classList.add('flex');
        setTimeout(() => {
            addressModal.classList.remove('opacity-0');
            addressModalContent.classList.remove('scale-95');
            addressModalContent.classList.add('scale-100');
        }, 10);
    }

    function closeAddressModal() {
        addressModal.classList.add('opacity-0');
        addressModalContent.classList.remove('scale-100');
        addressModalContent.classList.add('scale-95');
        setTimeout(() => {
            addressModal.classList.add('hidden');
            addressModal.classList.remove('flex');
        }, 300);
    }

    // --- LOGIC MODAL PEMBAYARAN ---
    const paymentModal = document.getElementById('paymentSelectModal');
    const paymentModalContent = document.getElementById('paymentModalContent');

    function openPaymentModal() {
        paymentModal.classList.remove('hidden');
        paymentModal.classList.add('flex');
        setTimeout(() => {
            paymentModal.classList.remove('opacity-0');
            paymentModalContent.classList.remove('scale-95');
            paymentModalContent.classList.add('scale-100');
        }, 10);
    }

    function closePaymentModal() {
        paymentModal.classList.add('opacity-0');
        paymentModalContent.classList.remove('scale-100');
        paymentModalContent.classList.add('scale-95');
        setTimeout(() => {
            paymentModal.classList.add('hidden');
            paymentModal.classList.remove('flex');
        }, 300);
    }
</script>