<?php
$cart = $_SESSION['cart'] ?? [];
?>

<div class="space-y-6 relative pb-28 lg:pb-0">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Keranjang</h1>
        <p class="text-gray-400">Pilih produk yang ingin kamu pesan sekarang.</p>
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
                class="hidden lg:inline-block bg-gold text-black px-8 py-3 rounded-full font-bold hover:bg-yellow-500 hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300">
                Belanja Sekarang
            </a>
        </div>
    <?php else: ?>

        <form action="index.php?page=checkout" method="POST" id="cartForm"
            class="grid lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <div class="lg:col-span-7 space-y-4">

                <div class="bg-white/5 border border-white/10 rounded-xl p-4 flex items-center gap-3 backdrop-blur-md">
                    <input type="checkbox" id="selectAll" checked
                        class="w-5 h-5 accent-gold rounded focus:ring-gold cursor-pointer">
                    <label for="selectAll" class="text-gray-200 font-bold cursor-pointer select-none">Pilih Semua</label>
                </div>

                <?php foreach ($cart as $index => $item): ?>
                    <div
                        class="group bg-white/5 border border-white/10 rounded-2xl p-4 md:p-5 backdrop-blur-md hover:border-gold/30 transition-all duration-300 flex items-start gap-3 md:gap-4">

                        <div class="pt-1 shrink-0 mt-0.5">
                            <input type="checkbox" name="selected_items[]" value="<?= $index; ?>"
                                data-price="<?= (int) $item['price']; ?>" checked
                                class="item-checkbox w-5 h-5 accent-gold rounded focus:ring-gold cursor-pointer">
                        </div>

                        <div class="flex-1 flex flex-col min-w-0 w-full">
                            <h2 class="text-lg md:text-xl font-bold text-white truncate mb-2">
                                <?= htmlspecialchars($item['product_name']); ?>
                            </h2>

                            <?php if (!empty($item['options']) && is_array($item['options'])): ?>
                                <div class="space-y-1">
                                    <?php foreach ($item['options'] as $optionName => $optionValue): ?>
                                        <div class="text-sm text-gray-300 flex">
                                            <span
                                                class="text-gold/80 font-medium w-24 md:w-28 shrink-0 truncate"><?= htmlspecialchars($optionName); ?></span>
                                            <span class="text-gray-400 break-words flex-1">:
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
                                <div class="bg-black/30 border border-white/5 rounded-lg p-2.5 mt-2">
                                    <p class="text-xs text-gray-400 italic flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gold shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        <span class="truncate"><?= htmlspecialchars($item['custom_input']); ?></span>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-white/5">
                                <div class="text-gold font-bold text-lg">
                                    Rp <?= number_format((int) $item['price'], 0, ',', '.'); ?>
                                </div>

                                <button type="button"
                                    class="p-2 rounded-lg text-gray-400 hover:bg-red-500/20 hover:text-red-500 transition-colors focus:outline-none"
                                    onclick="openDeleteModal(<?= (int) ($item['cart_item_id'] ?? 0); ?>, '<?= htmlspecialchars($item['product_name'], ENT_QUOTES); ?>')"
                                    title="Hapus Item">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hidden lg:block lg:col-span-5 sticky top-24">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md">
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
                        <span>Total Item Terpilih</span>
                        <span class="font-bold text-white selected-count">0</span>
                    </div>

                    <div class="border-t border-white/10 py-4 mb-2">
                        <div class="flex items-center justify-between text-lg px-1">
                            <span class="text-gray-300 font-medium">Total Pembayaran</span>
                            <span
                                class="font-bold text-gold text-2xl drop-shadow-[0_0_10px_rgba(212,175,55,0.3)] selected-total">
                                Rp 0
                            </span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                            class="checkout-btn block w-full text-center bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] transition-all duration-300">
                            Checkout Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-[80] lg:hidden bg-black/90 backdrop-blur-xl border-t border-gold/30 shadow-[0_-8px_24px_rgba(0,0,0,0.7)] px-4 py-3"
                style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
                <div class="max-w-7xl mx-auto flex items-center gap-3 justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] uppercase tracking-wider text-gray-400">Total (<span
                                class="selected-count">0</span> item)</div>
                        <div class="text-gold font-bold text-lg truncate selected-total">
                            Rp 0
                        </div>
                    </div>
                    <button type="submit"
                        class="checkout-btn shrink-0 inline-flex items-center justify-center gap-2 bg-gold text-black px-8 py-3 rounded-xl font-bold shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] hover:bg-yellow-400 transition-all duration-300">
                        Checkout
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>

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
                        <input type="hidden" name="cart_item_id" id="deleteIndex">
                        <button type="submit"
                            class="w-full py-4 text-red-400 font-bold text-sm hover:bg-white/5 transition-colors">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // --- LOGIKA CHECKBOX & KALKULASI HARGA SHOPEE STYLE ---
            const selectAllBtn = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const countDisplays = document.querySelectorAll('.selected-count');
            const totalDisplays = document.querySelectorAll('.selected-total');
            const checkoutBtns = document.querySelectorAll('.checkout-btn');

            function calculateTotal() {
                let total = 0;
                let count = 0;
                let allChecked = true;

                itemCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        total += parseInt(cb.dataset.price);
                        count++;
                    } else {
                        allChecked = false;
                    }
                });

                if (itemCheckboxes.length > 0) {
                    selectAllBtn.checked = allChecked;
                }

                const formattedTotal = total.toLocaleString('id-ID');

                countDisplays.forEach(el => el.innerText = count);
                totalDisplays.forEach(el => el.innerText = 'Rp ' + formattedTotal);

                checkoutBtns.forEach(btn => {
                    if (count === 0) {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        btn.classList.remove('hover:bg-yellow-400');
                    } else {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                        btn.classList.add('hover:bg-yellow-400');
                    }
                });
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('change', function (e) {
                    itemCheckboxes.forEach(cb => cb.checked = e.target.checked);
                    calculateTotal();
                });
            }

            itemCheckboxes.forEach(cb => {
                cb.addEventListener('change', calculateTotal);
            });

            calculateTotal();


            // --- LOGIKA MODAL HAPUS ---
            const deleteModal = document.getElementById('deleteModal');
            const deleteModalContent = document.getElementById('deleteModalContent');
            const inputDeleteIndex = document.getElementById('deleteIndex');
            const deleteProductName = document.getElementById('deleteProductName');

            function openDeleteModal(cartItemId, productName) {
                inputDeleteIndex.value = cartItemId;
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