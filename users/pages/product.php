<?php
$id = $_GET['id'] ?? null;

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

<div class="grid md:grid-cols-2 gap-10">

    <div>
        <?php
        $imgFile = basename($image['image_path'] ?? 'default.jpg');
        $imageSrc = "../public/uploads/products/" . $imgFile;
        ?>
        <img src="<?= htmlspecialchars($imageSrc); ?>" alt="<?= htmlspecialchars($product['name']); ?>"
            class="rounded-xl w-full h-80 md:h-96 object-cover border border-gold/20 shadow-lg">
    </div>

    <div>
        <h1 class="text-3xl font-title text-gold mb-4 drop-shadow-sm">
            <?= htmlspecialchars($product['name']); ?>
        </h1>

        <?php if ($product['product_type'] === 'simple'): ?>
            <p
                class="text-2xl font-bold text-gray-100 mb-6 bg-gold/10 inline-block px-4 py-2 rounded-lg border border-gold/30">
                Rp <?= number_format($product['base_price'], 0, ',', '.'); ?>
            </p>
        <?php endif; ?>

        <?php if ($product['product_type'] === 'chat_only'): ?>

            <a href="<?= htmlspecialchars($waLink); ?>" target="_blank"
                class="block text-center bg-gold text-black py-3 rounded-lg font-semibold hover:bg-yellow-500 hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all duration-300 mt-6">
                Hubungi Admin
            </a>

        <?php else: ?>

            <form action="actions/add-to-cart.php" method="POST" id="productForm" class="space-y-6 mt-4">

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

                    <div class="bg-white/5 p-4 rounded-lg border border-white/10">
                        <label class="block text-gold mb-3 font-medium border-b border-gold/30 pb-2">
                            <?= htmlspecialchars($opt['option_name']); ?>
                            <?php if ($opt['is_required']): ?>
                                <span class="text-red-500 ml-1">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($opt['option_type'] === 'single'): ?>
                            <div class="space-y-2 pl-2">
                                <?php foreach ($values as $val): ?>
                                    <label
                                        class="flex items-center gap-3 cursor-pointer text-gray-300 hover:text-white transition-colors">
                                        <input type="radio" name="options[<?= htmlspecialchars($opt['option_name']); ?>]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= htmlspecialchars($val['additional_price']); ?>"
                                            data-extra='<?= htmlspecialchars($val['extra_data'] ?? '{}'); ?>'
                                            class="optionInput w-4 h-4 accent-gold" <?= $opt['is_required'] ? 'required' : ''; ?>>
                                        <span>
                                            <?= htmlspecialchars($val['value_name']); ?>
                                            <?php if ($val['additional_price'] > 0): ?>
                                                <span class="text-gold/80 text-sm ml-1">(+Rp
                                                    <?= number_format($val['additional_price'], 0, ',', '.'); ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'multiple'): ?>
                            <div class="space-y-2 pl-2">
                                <?php foreach ($values as $val): ?>
                                    <label
                                        class="flex items-center gap-3 cursor-pointer text-gray-300 hover:text-white transition-colors">
                                        <input type="checkbox" name="options[<?= htmlspecialchars($opt['option_name']); ?>][]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= htmlspecialchars($val['additional_price']); ?>"
                                            data-extra='<?= htmlspecialchars($val['extra_data'] ?? '{}'); ?>'
                                            class="optionInput w-4 h-4 accent-gold rounded">
                                        <span>
                                            <?= htmlspecialchars($val['value_name']); ?>
                                            <?php if ($val['additional_price'] > 0): ?>
                                                <span class="text-gold/80 text-sm ml-1">(+Rp
                                                    <?= number_format($val['additional_price'], 0, ',', '.'); ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'custom_input'): ?>
                            <textarea name="custom_input"
                                class="w-full p-3 bg-black/50 border border-gold/50 rounded-lg text-white focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all"
                                placeholder="Masukkan tulisan di sini..." <?= $opt['is_required'] ? 'required' : ''; ?>></textarea>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

                <div class="border-t border-gold/50 pt-6 mt-8 flex justify-between items-center">
                    <span class="text-gray-300 font-medium">Total Harga</span>
                    <p class="text-2xl font-bold text-gold">
                        Rp <span id="totalPrice"><?= number_format($product['base_price'] ?? 0, 0, ',', '.'); ?></span>
                    </p>
                </div>

                <button type="submit"
                    class="w-full bg-gold text-black py-4 rounded-lg font-bold text-lg hover:bg-yellow-500 hover:shadow-[0_0_20px_rgba(212,175,55,0.4)] transition-all duration-300 uppercase tracking-wider">
                    Tambah ke Keranjang
                </button>

            </form>

        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Panggil fungsi hitung saat halaman pertama kali dimuat
        calculateTotal();

        // Panggil fungsi hitung setiap kali ada input yang berubah (radio/checkbox)
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

            // Logic khusus untuk produk custom uang
            if (productType === 'custom_money') {
                if (nominal > 0 && qty > 0) {
                    total = (nominal * qty) + modelPrice;
                } else {
                    total = modelPrice;
                }
            } else {
                total = basePrice + modelPrice;
            }

            document.getElementById('totalPrice').innerText = total.toLocaleString('id-ID');
        }

        // Event listener untuk form submit
        const form = document.getElementById('productForm');
        if (form) {
            form.addEventListener("submit", function () {
                let totalText = document.getElementById('totalPrice').innerText;
                let totalNumber = totalText.replace(/\./g, ''); // Hapus titik ribuan
                document.getElementById('finalPrice').value = totalNumber;
            });
        }
    });
</script>