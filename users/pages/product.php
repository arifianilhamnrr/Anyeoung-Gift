<?php
$id = $_GET['id'] ?? null;
$justAdded = isset($_GET['added']) && $_GET['added'] === '1';

if (!$id) {
    echo "<p>Produk tidak ditemukan.</p>";
    return;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<p>Produk tidak ditemukan.</p>";
    return;
}

// Mengambil gambar utama
$stmt = $pdo->prepare("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    AND is_primary = 1 
    AND option_value_id IS NULL
    LIMIT 1
");
$stmt->execute([$id]);
$image = $stmt->fetch();

// Mengambil opsi produk
$stmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ?");
$stmt->execute([$id]);
$options = $stmt->fetchAll();

// Jika produk chat_only, siapkan link WhatsApp
$waLink = '#';
if ($product['product_type'] === 'chat_only') {
    // Pastikan $storeSetting sudah ada. Jika belum, query di sini
    $stmtStore = $pdo->query("SELECT * FROM store_settings LIMIT 1");
    $storeSetting = $stmtStore->fetch();

    if ($storeSetting) {
        $message = str_replace('{{product_name}}', $product['name'], $storeSetting['whatsapp_message_template']);
        $waLink = 'https://wa.me/' . $storeSetting['whatsapp_admin'] . '?text=' . urlencode($message);
    }
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&display=swap');

    .font-elegant {
        font-family: 'Playfair Display', serif;
    }
</style>

<div class="grid md:grid-cols-2 gap-8 lg:gap-16 pb-40 sm:pb-10 max-w-7xl mx-auto items-start">

    <div class="md:sticky md:top-24">
        <?php
        $imgFile = basename($image['image_path'] ?? 'default.jpg');
        $imageSrc = "../public/uploads/products/" . $imgFile;
        ?>
        <img src="<?= htmlspecialchars($imageSrc); ?>" alt="<?= htmlspecialchars($product['name']); ?>"
            class="w-full h-[26rem] md:h-auto md:max-h-[75vh] object-cover rounded-b-[2.5rem] md:rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
    </div>

    <div class="px-5 md:px-0 md:pr-4">
        <?php
        $rawName = htmlspecialchars($product['name']);
        $words = explode(' ', $rawName);

        if (count($words) > 1) {
            $firstWord = array_shift($words);
            $restOfWords = implode(' ', $words);
            $displayName = $firstWord . '<br>' . $restOfWords;
        } else {
            $displayName = $rawName;
        }
        ?>

        <h1 class="font-elegant text-gray-200 mb-10 mt-6 text-center md:text-left uppercase tracking-widest"
            style="font-size: 3.2rem; line-height: 0.68;">
            <?= $displayName; ?>
        </h1>

        <?php if ($product['product_type'] === 'chat_only'): ?>

            <a href="<?= htmlspecialchars($waLink); ?>" target="_blank"
                class="block text-center text-white py-3 rounded-lg font-semibold transition-all duration-300 mt-6 hover:opacity-80"
                style="background-color: #A3804C;">
                Hubungi Admin
            </a>

        <?php else: ?>

            <form action="actions/add-to-cart.php" method="POST" id="productForm" class="space-y-6 mt-4 md:mt-10">

                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                <input type="hidden" name="total_price" id="finalPrice">
                <input type="hidden" id="productType" value="<?= htmlspecialchars($product['product_type']); ?>">
                <input type="hidden" id="basePrice" value="<?= htmlspecialchars($product['base_price'] ?? 0); ?>">

                <?php foreach ($options as $opt): ?>

                    <?php
                    $stmt2 = $pdo->prepare("SELECT * FROM product_option_values WHERE option_id = ? AND is_active = 1");
                    $stmt2->execute([$opt['id']]);
                    $values = $stmt2->fetchAll();
                    ?>

                    <div class="bg-[#181818] p-5 rounded-xl border border-white/5 shadow-sm">
                        <label class="block mb-4 font-medium border-b border-white/10 pb-3" style="color: #A3804C;">
                            <?= htmlspecialchars($opt['option_name']); ?>
                            <?php if ($opt['is_required']): ?>
                                <span class="text-red-500 ml-1">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($opt['option_type'] === 'single'): ?>
                            <div class="space-y-3 pl-2">
                                <?php foreach ($values as $val): ?>
                                    <label
                                        class="flex items-start gap-3 cursor-pointer text-gray-300 hover:text-white transition-colors">
                                        <input type="radio" name="options[<?= htmlspecialchars($opt['option_name']); ?>]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= htmlspecialchars($val['additional_price']); ?>"
                                            data-extra='<?= htmlspecialchars($val['extra_data'] ?? '{}'); ?>'
                                            class="optionInput w-4 h-4 mt-0.5 shrink-0" <?= $opt['is_required'] ? 'required' : ''; ?>>
                                        <span class="text-[15px] leading-snug">
                                            <?= htmlspecialchars($val['value_name']); ?>
                                            <?php if ($val['additional_price'] > 0): ?>
                                                <span class="text-sm ml-1 inline-block whitespace-nowrap"
                                                    style="color: rgba(163, 128, 76, 0.8);">
                                                    (+Rp <?= number_format($val['additional_price'], 0, ',', '.'); ?>)
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'multiple'): ?>
                            <div class="space-y-3 pl-2">
                                <?php foreach ($values as $val): ?>
                                    <label
                                        class="flex items-start gap-3 cursor-pointer text-gray-300 hover:text-white transition-colors">
                                        <input type="checkbox" name="options[<?= htmlspecialchars($opt['option_name']); ?>][]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= htmlspecialchars($val['additional_price']); ?>"
                                            data-extra='<?= htmlspecialchars($val['extra_data'] ?? '{}'); ?>'
                                            class="optionInput w-4 h-4 mt-0.5 shrink-0 rounded">
                                        <span class="text-[15px] leading-snug">
                                            <?= htmlspecialchars($val['value_name']); ?>
                                            <?php if ($val['additional_price'] > 0): ?>
                                                <span class="text-sm ml-1 inline-block whitespace-nowrap"
                                                    style="color: rgba(163, 128, 76, 0.8);">
                                                    (+Rp <?= number_format($val['additional_price'], 0, ',', '.'); ?>)
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'custom_input'): ?>
                            <textarea name="custom_input"
                                class="w-full p-4 bg-black/40 border rounded-lg text-white focus:outline-none transition-all"
                                style="border-color: rgba(163, 128, 76, 0.5);" placeholder="Masukkan tulisan di sini..."
                                <?= $opt['is_required'] ? 'required' : ''; ?>></textarea>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

                <div class="hidden sm:flex border-t border-white/10 pt-8 mt-10 justify-between items-center">
                    <span class="text-gray-300 font-medium text-lg">Total Harga</span>
                    <p class="text-4xl font-bold" style="color: #A3804C;">
                        Rp <span id="totalPrice"><?= number_format($product['base_price'] ?? 0, 0, ',', '.'); ?></span>
                    </p>
                </div>

                <input type="hidden" name="buy_now" id="buyNowFlag" value="0">

                <div class="hidden sm:grid sm:grid-cols-2 gap-4 items-stretch mt-6">
                    <button type="submit" onclick="document.getElementById('buyNowFlag').value='0'"
                        class="inline-flex items-center justify-center gap-2 bg-transparent border rounded-xl font-bold text-base hover:bg-white/5 transition-all duration-300 uppercase tracking-wider w-full py-4 px-6"
                        style="border-color: #A3804C; color: #A3804C;">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7A1 1 0 006.5 17h11M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z">
                            </path>
                        </svg>
                        <span>Keranjang</span>
                    </button>

                    <button type="submit" onclick="document.getElementById('buyNowFlag').value='1'"
                        class="inline-flex items-center justify-center gap-2 text-white py-4 rounded-xl font-bold text-base hover:opacity-80 transition-all duration-300 uppercase tracking-wider shadow-lg"
                        style="background-color: #A3804C; box-shadow: 0 4px 20px rgba(163, 128, 76, 0.3);">
                        Bayar Sekarang
                    </button>
                </div>

                <div style="height: 140px;" class="block sm:hidden w-full"></div>

            </form>

            <div class="fixed inset-x-0 bottom-0 z-50 sm:hidden bg-black border-t border-white/10 shadow-2xl px-5 py-4"
                style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));">

                <div class="max-w-7xl mx-auto flex flex-col gap-4">

                    <div class="flex justify-between items-center">
                        <span class="text-white font-semibold text-[15px] tracking-wide">Total :</span>
                        <span class="text-gray-100 font-semibold text-[15px] tracking-wide">
                            Rp. <span
                                id="stickyTotalPrice"><?= number_format($product['base_price'] ?? 0, 0, ',', '.'); ?></span>
                        </span>
                    </div>

                    <div class="flex items-stretch gap-4">
                        <button type="submit" form="productForm" onclick="document.getElementById('buyNowFlag').value='0'"
                            aria-label="Tambah ke Keranjang"
                            class="shrink-0 flex items-center justify-center text-white px-2 hover:text-gray-300 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7A1 1 0 006.5 17h11M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z">
                                </path>
                            </svg>
                        </button>

                        <button type="submit" form="productForm" onclick="document.getElementById('buyNowFlag').value='1'"
                            class="flex-1 text-white py-3 rounded text-sm font-medium hover:opacity-80 transition-colors tracking-wide"
                            style="background-color: #A3804C;">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php if ($justAdded): ?>
    <div id="cartToast" role="status" aria-live="polite"
        class="fixed left-1/2 -translate-x-1/2 top-24 z-[120] flex items-center gap-3 bg-green-500/95 text-black px-5 py-3 rounded-full shadow-[0_10px_30px_rgba(34,197,94,0.45)] font-semibold cartToastEnter">
        <span class="cartToastIcon w-8 h-8 bg-black/15 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </span>
        <span>Berhasil ditambahkan ke keranjang!</span>
    </div>

    <style>
        @keyframes cartToastIn {
            0% {
                opacity: 0;
                transform: translate(-50%, -16px) scale(0.92);
            }

            60% {
                opacity: 1;
                transform: translate(-50%, 4px) scale(1.04);
            }

            100% {
                opacity: 1;
                transform: translate(-50%, 0) scale(1);
            }
        }

        @keyframes cartToastOut {
            0% {
                opacity: 1;
                transform: translate(-50%, 0) scale(1);
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -12px) scale(0.96);
            }
        }

        @keyframes cartIconPop {
            0% {
                transform: scale(0.6) rotate(-15deg);
            }

            60% {
                transform: scale(1.2) rotate(8deg);
            }

            100% {
                transform: scale(1) rotate(0);
            }
        }

        .cartToastEnter {
            animation: cartToastIn 360ms cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        .cartToastLeave {
            animation: cartToastOut 280ms ease forwards;
        }

        .cartToastIcon svg {
            animation: cartIconPop 500ms ease 80ms both;
        }

        @media (prefers-reduced-motion: reduce) {

            .cartToastEnter,
            .cartToastLeave,
            .cartToastIcon svg {
                animation: none;
            }
        }
    </style>

    <script>
        (function () {
            const toast = document.getElementById('cartToast');
            if (!toast) return;
            setTimeout(function () {
                toast.classList.remove('cartToastEnter');
                toast.classList.add('cartToastLeave');
                toast.addEventListener('animationend', function () {
                    toast.remove();
                });
            }, 2400);

            if (window.history && history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('added');
                history.replaceState({}, '', url.toString());
            }
        })();
    </script>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        calculateTotal();

        document.addEventListener("change", function (e) {
            if (e.target && e.target.classList.contains('optionInput')) {
                calculateTotal();
            }
        });

        function calculateTotal() {
            let productType = document.getElementById('productType').value;
            let basePrice = parseInt(document.getElementById('basePrice').value) || 0;

            let total = 0;
            let modelPrice = 0;
            let nominal = 0;
            let qty = 0;

            document.querySelectorAll('.optionInput:checked').forEach(el => {
                let price = parseInt(el.dataset.price || 0);
                modelPrice += price;

                if (el.dataset.extra && el.dataset.extra !== '{}') {
                    try {
                        let extra = JSON.parse(el.dataset.extra);
                        if (extra.nominal) nominal = parseInt(extra.nominal);
                        if (extra.qty) qty = parseInt(extra.qty);
                    } catch (e) {
                        console.error("Error parsing JSON:", e);
                    }
                }
            });

            if (productType === 'custom_money') {
                if (nominal > 0 && qty > 0) {
                    total = (nominal * qty) + modelPrice;
                } else {
                    total = modelPrice;
                }
            } else {
                total = basePrice + modelPrice;
            }

            let formattedTotal = total.toLocaleString('id-ID');
            document.getElementById('totalPrice').innerText = formattedTotal;

            let stickyTotalEl = document.getElementById('stickyTotalPrice');
            if (stickyTotalEl) {
                stickyTotalEl.innerText = formattedTotal;
            }
        }

        const form = document.getElementById('productForm');
        if (form) {
            form.addEventListener("submit", function () {
                let totalText = document.getElementById('totalPrice').innerText;
                let totalNumber = totalText.replace(/\./g, '');
                document.getElementById('finalPrice').value = totalNumber;
            });
        }
    });
</script>