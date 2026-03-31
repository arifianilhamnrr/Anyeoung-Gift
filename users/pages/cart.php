<?php
$cart = $_SESSION['cart'] ?? [];
$grandTotal = 0;
?>

<div class="space-y-6 relative">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Keranjang</h1>
        <p class="text-gray-400">Cek kembali produk yang akan kamu pesan.</p>
    </div>

    <?php if (empty($cart)): ?>
        <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-10 text-center">
            <div class="w-16 h-16 bg-white/5 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <p class="text-lg text-gray-300 mb-6">Keranjang kamu masih kosong.</p>
            <a href="index.php?page=home"
                class="inline-block bg-gold text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300">
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>

        <div class="grid lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <div class="lg:col-span-7 space-y-4">
                <?php foreach ($cart as $index => $item): ?>
                    <?php $grandTotal += (int) $item['price']; ?>

                    <div
                        class="group bg-white/5 border border-white/10 rounded-2xl p-5 md:p-6 backdrop-blur-md hover:border-gold/30 transition-all duration-300 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                        <div class="flex-1 space-y-3">
                            <h2 class="text-xl font-bold text-white">
                                <?= htmlspecialchars($item['product_name']); ?>
                            </h2>

                            <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                <div class="space-y-1 mt-2">
                                    <?php foreach ($item['options'] as $optionName => $optionValue): ?>
                                        <div class="text-sm text-gray-300 flex">
                                            <span
                                                class="text-gold/80 font-medium w-28 shrink-0"><?= htmlspecialchars($optionName); ?></span>
                                            <span class="text-gray-400">:
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
                                <div class="bg-black/30 border border-white/5 rounded-lg p-3 mt-2">
                                    <p class="text-xs text-gray-400 italic flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gold shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        <?= htmlspecialchars($item['custom_input']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="text-gold font-bold text-xl pt-2">
                                Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                            </div>
                        </div>

                        <button type="button"
                            class="w-full sm:w-auto shrink-0 px-4 py-2.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/30 text-sm font-bold hover:bg-red-500 hover:text-white transition-colors"
                            onclick="openDeleteModal(<?= $index; ?>, '<?= htmlspecialchars($item['product_name'], ENT_QUOTES); ?>')">
                            Hapus
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md sticky top-24">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-title text-gold">Ringkasan Pesanan</h3>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-300 mb-4 px-1">
                        <span>Jumlah Item</span>
                        <span class="font-bold text-white"><?= count($cart); ?></span>
                    </div>

                    <div class="border-t border-white/10 py-4 mb-2">
                        <div class="flex items-center justify-between text-lg px-1">
                            <span class="text-gray-300 font-medium">Total Pembayaran</span>
                            <span class="font-bold text-gold text-2xl drop-shadow-[0_0_10px_rgba(212,175,55,0.3)]">
                                Rp <?= number_format($grandTotal, 0, ',', '.'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4">
                        <a href="index.php?page=checkout"
                            class="block w-full text-center bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] transition-all duration-300">
                            Lanjut Checkout
                        </a>

                        <a href="index.php?page=home"
                            class="block w-full text-center border border-white/20 text-white bg-white/5 py-3.5 rounded-xl font-semibold hover:bg-white/10 transition-colors">
                            Tambah Produk Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="deleteModal"
            class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>

            <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
                id="deleteModalContent">
                <div class="p-8 text-center">
                    <div
                        class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Hapus Item?</h3>
                    <p class="text-gray-300 text-sm leading-relaxed mb-1">
                        Yakin mau hapus item ini dari keranjang?
                    </p>
                    <p id="deleteProductName" class="text-gold font-medium text-sm mt-2"></p>
                </div>

                <div class="flex border-t border-white/10">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-4 text-gray-300 font-medium text-sm hover:bg-white/5 transition-colors border-r border-white/10">
                        Batal
                    </button>
                    <form action="actions/remove-cart-item.php" method="POST" class="flex-1 flex">
                        <input type="hidden" name="index" id="deleteIndex">
                        <button type="submit"
                            class="w-full py-4 text-red-400 font-bold text-sm hover:bg-white/5 transition-colors">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const deleteModal = document.getElementById('deleteModal');
            const deleteModalContent = document.getElementById('deleteModalContent');
            const inputDeleteIndex = document.getElementById('deleteIndex');
            const deleteProductName = document.getElementById('deleteProductName');

            function openDeleteModal(index, productName) {
                inputDeleteIndex.value = index;
                deleteProductName.textContent = productName;

                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');

                setTimeout(() => {
                    deleteModal.classList.remove('opacity-0');
                    deleteModalContent.classList.remove('scale-95');
                    deleteModalContent.classList.add('scale-100');
                }, 10);
            }

            function closeDeleteModal() {
                deleteModal.classList.add('opacity-0');
                deleteModalContent.classList.remove('scale-100');
                deleteModalContent.classList.add('scale-95');

                setTimeout(() => {
                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                    inputDeleteIndex.value = '';
                    deleteProductName.textContent = '';
                }, 300);
            }
        </script>
    <?php endif; ?>
</div>