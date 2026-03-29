<?php
$cart = $_SESSION['cart'] ?? [];
$grandTotal = 0;
?>

<div class="mb-8">
    <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Keranjang</h1>
    <p class="text-gray-400">Cek kembali produk yang akan kamu pesan.</p>
</div>

<?php if (empty($cart)): ?>
    <div class="bg-black border border-gold rounded-2xl p-8 text-center">
        <p class="text-lg text-gray-300 mb-4">Keranjang kamu masih kosong.</p>
        <a href="index.php?page=home"
            class="inline-block bg-gold text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
            Belanja Sekarang
        </a>
    </div>
<?php else: ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cart as $index => $item): ?>
                <?php $grandTotal += (int) $item['price']; ?>

                <div class="bg-black border border-gold rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-white mb-2">
                                <?= htmlspecialchars($item['product_name']); ?>
                            </h2>

                            <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                <div class="space-y-2 mb-3">
                                    <?php foreach ($item['options'] as $optionName => $optionValue): ?>
                                        <div class="text-sm text-gray-300">
                                            <span class="text-gold font-medium"><?= htmlspecialchars($optionName); ?>:</span>
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
                                <div class="text-sm text-gray-300 mb-3">
                                    <span class="text-gold font-medium">Tulisan Custom:</span>
                                    <?= htmlspecialchars($item['custom_input']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-gold font-bold text-lg">
                                Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                            </div>
                        </div>

                        <div>
                            <button type="button"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition"
                                onclick="openDeleteModal(<?= $index; ?>, '<?= htmlspecialchars(addslashes($item['product_name'])); ?>')">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div>
            <div class="bg-black border border-gold rounded-2xl p-6 sticky top-24">
                <h3 class="text-xl font-title text-gold mb-4">Ringkasan Pesanan</h3>

                <div class="flex items-center justify-between text-sm text-gray-300 mb-3">
                    <span>Jumlah Item</span>
                    <span><?= count($cart); ?></span>
                </div>

                <div class="border-t border-gold/40 my-4"></div>

                <div class="flex items-center justify-between text-lg font-bold text-gold mb-6">
                    <span>Total</span>
                    <span>Rp <?= number_format($grandTotal, 0, ',', '.'); ?></span>
                </div>

                <a href="index.php?page=checkout"
                    class="block w-full text-center bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition mb-3">
                    Lanjut Checkout
                </a>

                <a href="index.php?page=home"
                    class="block w-full text-center border border-gold text-gold py-3 rounded-full font-semibold hover:bg-gold hover:text-black transition">
                    Tambah Produk Lagi
                </a>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden">

        <!-- OVERLAY -->
        <div id="menuOverlay"
            class="absolute inset-0 bg-black/20 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        </div>

        <!-- MODAL CONTENT -->
        <div id="menuContent" class="absolute inset-0 flex items-center justify-center px-4">

            <div
                class="w-full max-w-md bg-black/50 backdrop-blur-xl border border-gold/20 rounded-2xl p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300">

                <h3 class="text-2xl font-title text-gold mb-3">Hapus Item</h3>
                <p class="text-gray-300 mb-2">Yakin mau hapus item ini?</p>
                <p id="deleteProductName" class="text-white font-medium mb-6"></p>

                <form action="actions/remove-cart-item.php" method="POST" class="flex gap-3">
                    <input type="hidden" name="index" id="deleteIndex">

                    <button type="button" onclick="closeDeleteModal()"
                        class="w-full border border-gold text-gold py-3 rounded-full font-semibold hover:bg-gold hover:text-black transition">
                        Batal
                    </button>

                    <button type="submit"
                        class="w-full bg-red-600 text-white py-3 rounded-full font-semibold hover:bg-red-700 transition">
                        Hapus
                    </button>
                </form>

            </div>

        </div>
    </div>

    <script>
        function openDeleteModal(index, productName) {
            const modal = document.getElementById('deleteModal');
            const overlay = modal.querySelector('#menuOverlay');
            const content = modal.querySelector('.max-w-md');

            document.getElementById('deleteIndex').value = index;
            document.getElementById('deleteProductName').textContent = productName;

            modal.classList.remove('hidden');

            setTimeout(() => {
                overlay.classList.remove('opacity-0');

                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const overlay = modal.querySelector('#menuOverlay');
            const content = modal.querySelector('.max-w-md');

            overlay.classList.add('opacity-0');

            content.classList.remove('opacity-100', 'scale-100');
            content.classList.add('opacity-0', 'scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // klik luar
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('deleteModal');
            if (!modal.classList.contains('hidden') && e.target.id === 'menuOverlay') {
                closeDeleteModal();
            }
        });

        // ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
<?php endif; ?>