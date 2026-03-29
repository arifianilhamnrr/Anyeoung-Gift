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
?>

<div class="mb-8">
    <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Checkout</h1>
    <p class="text-gray-400">Lengkapi data checkout untuk melanjutkan pemesanan dan pengambilan di toko.</p>
</div>

<?php if (empty($addresses)): ?>
    <div class="bg-black border border-gold rounded-2xl p-6">
        <p class="text-white mb-4">Kamu belum punya kontak pemesan.</p>
        <a href="index.php?page=addresses"
            class="inline-block bg-gold text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
            Tambah Kontak Dulu
        </a>
    </div>
<?php elseif (empty($paymentMethods)): ?>
    <div class="bg-black border border-gold rounded-2xl p-6">
        <p class="text-white">Metode pembayaran belum tersedia. Hubungi admin dulu ya.</p>
    </div>
<?php else: ?>

    <form action="actions/checkout-process.php" method="POST" class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-black border border-gold rounded-2xl p-6">
                <h2 class="text-xl font-title text-gold mb-4">Pilih Kontak Pemesan</h2>
                <p class="text-sm text-gray-400 mb-4">
                    Data ini digunakan untuk konfirmasi admin dan pengambilan pesanan di toko.
                </p>

                <div class="space-y-4">
                    <?php foreach ($addresses as $address): ?>
                        <label class="block border border-gold/30 rounded-xl p-4 cursor-pointer hover:border-gold transition">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="address_id" value="<?= $address['id']; ?>"
                                    class="mt-1 accent-yellow-500" <?= $address['is_default'] ? 'checked' : ''; ?> required>
                                <div>
                                    <div class="text-white font-semibold">
                                        <?= htmlspecialchars($address['recipient_name']); ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="ml-2 text-xs bg-gold text-black px-2 py-1 rounded-full">Utama</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-300 text-sm mt-1">
                                        <?= htmlspecialchars($address['whatsapp_number']); ?>
                                    </div>
                                    <div class="text-gray-400 text-sm mt-1">
                                        <?= nl2br(htmlspecialchars($address['address_text'])); ?>
                                    </div>
                                    <?php if (!empty($address['notes'])): ?>
                                        <div class="text-gray-500 text-xs mt-2">
                                            Catatan: <?= htmlspecialchars($address['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <a href="index.php?page=addresses" class="text-gold hover:underline text-sm">
                        Kelola kontak pemesan
                    </a>
                </div>
            </div>

            <div class="bg-black border border-gold rounded-2xl p-6">
                <h2 class="text-xl font-title text-gold mb-4">Metode Pembayaran</h2>

                <div class="space-y-4">
                    <?php foreach ($paymentMethods as $method): ?>
                        <?php
                        $accountInfo = [];
                        if (!empty($method['account_info'])) {
                            $decoded = json_decode($method['account_info'], true);
                            if (is_array($decoded)) {
                                $accountInfo = $decoded;
                            }
                        }
                        ?>
                        <label class="block border border-gold/30 rounded-xl p-4 cursor-pointer hover:border-gold transition">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="payment_method_id" value="<?= $method['id']; ?>"
                                    class="mt-1 accent-yellow-500" required>
                                <div>
                                    <div class="text-white font-semibold">
                                        <?= htmlspecialchars($method['name']); ?>
                                    </div>

                                    <?php if (!empty($accountInfo)): ?>
                                        <div class="text-gray-400 text-sm mt-2 space-y-1">
                                            <?php foreach ($accountInfo as $key => $value): ?>
                                                <div><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:
                                                    <?= htmlspecialchars($value); ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($method['type'] === 'onsite'): ?>
                                        <div class="text-gold text-xs mt-2">
                                            Bayar langsung saat ambil pesanan di toko. Tetap akan dicek admin.
                                        </div>
                                    <?php else: ?>
                                        <div class="text-gold text-xs mt-2">
                                            Bukti pembayaran bisa diupload setelah order dibuat.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-black border border-gold rounded-2xl p-6">
                <h2 class="text-xl font-title text-gold mb-4">Informasi Pengambilan</h2>

                <?php if ($storeAddress): ?>
                    <div class="text-white font-semibold mb-2">
                        Ambil Pesanan di Toko
                    </div>
                    <div class="text-gray-300 text-sm mb-2">
                        <?= htmlspecialchars($storeAddress['recipient_name']); ?>
                    </div>
                    <div class="text-gray-400 text-sm mb-2">
                        <?= nl2br(htmlspecialchars($storeAddress['address_text'])); ?>
                    </div>
                    <div class="text-gray-300 text-sm">
                        WhatsApp Toko: <?= htmlspecialchars($storeAddress['whatsapp_number']); ?>
                    </div>
                <?php else: ?>
                    <div class="text-gray-300 text-sm">
                        Alamat toko belum diatur admin.
                    </div>
                <?php endif; ?>

                <div class="mt-4 text-sm text-gold">
                    Semua pesanan diambil langsung di toko setelah admin mengubah status menjadi siap diambil.
                </div>
            </div>

        </div>

        <div>
            <div class="bg-black border border-gold rounded-2xl p-6 sticky top-24">
                <h3 class="text-xl font-title text-gold mb-4">Ringkasan Pesanan</h3>

                <div class="space-y-4 max-h-72 overflow-auto pr-1">
                    <?php foreach ($cart as $item): ?>
                        <div class="border-b border-gold/20 pb-3">
                            <div class="text-white font-medium">
                                <?= htmlspecialchars($item['product_name']); ?>
                            </div>

                            <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                <div class="mt-2 space-y-1">
                                    <?php foreach ($item['options'] as $optionName => $optionValue): ?>
                                        <div class="text-xs text-gray-400">
                                            <?= htmlspecialchars($optionName); ?>:
                                            <?php if (is_array($optionValue)): ?>
                                                <?= htmlspecialchars(implode(', ', $optionValue)); ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($optionValue); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item['custom_input'])): ?>
                                <div class="text-xs text-gray-400 mt-1">
                                    Tulisan: <?= htmlspecialchars($item['custom_input']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-gold font-semibold mt-2">
                                Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-gold/30 my-4"></div>

                <div class="flex items-center justify-between text-lg font-bold text-gold mb-6">
                    <span>Total</span>
                    <span>Rp <?= number_format($grandTotal, 0, ',', '.'); ?></span>
                </div>

                <button type="submit"
                    class="w-full bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                    Buat Pesanan
                </button>

                <a href="index.php?page=cart"
                    class="block w-full text-center border border-gold text-gold py-3 rounded-full font-semibold hover:bg-gold hover:text-black transition mt-3">
                    Kembali ke Keranjang
                </a>
            </div>
        </div>
    </form>

<?php endif; ?>