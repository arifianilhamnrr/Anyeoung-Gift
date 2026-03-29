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

$stmt = $pdo->prepare("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    AND is_primary = 1 
    AND option_value_id IS NULL
    LIMIT 1
");
$stmt->execute([$id]);
$image = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ?");
$stmt->execute([$id]);
$options = $stmt->fetchAll();
?>

<div class="grid md:grid-cols-2 gap-10">

    <!-- IMAGE -->
    <div>
        <img src="../uploads/products/<?= $image['image_path'] ?? 'default.jpg'; ?>"
            class="rounded-xl w-full h-80 md:h-96 object-cover">
    </div>

    <!-- DETAIL -->
    <div>
        <h1 class="text-3xl font-title text-gold mb-4">
            <?= htmlspecialchars($product['name']); ?>
        </h1>

        <?php if ($product['product_type'] === 'simple'): ?>
            <p class="text-2xl font-bold text-gold mb-6">
                Rp <?= number_format($product['base_price'], 0, ',', '.'); ?>
            </p>
        <?php endif; ?>

        <?php if ($product['product_type'] === 'chat_only'): ?>

            <a href="#"
                class="block text-center bg-gold text-black py-3 rounded-lg font-semibold hover:bg-yellow-500 transition">
                Hubungi Admin
            </a>

        <?php else: ?>

            <form action="actions/add-to-cart.php" method="POST" id="productForm" class="space-y-6">

                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                <input type="hidden" name="total_price" id="finalPrice">

                <input type="hidden" id="productType" value="<?= $product['product_type']; ?>">
                <input type="hidden" id="basePrice" value="<?= $product['base_price'] ?? 0; ?>">

                <?php foreach ($options as $opt): ?>

                    <?php
                    $stmt2 = $pdo->prepare("SELECT * FROM product_option_values WHERE option_id = ? AND is_active = 1");
                    $stmt2->execute([$opt['id']]);
                    $values = $stmt2->fetchAll();
                    ?>

                    <div>
                        <label class="block text-gold mb-2 font-medium">
                            <?= $opt['option_name']; ?>
                            <?= $opt['is_required'] ? '*' : ''; ?>
                        </label>

                        <?php if ($opt['option_type'] === 'single'): ?>
                            <div class="space-y-2">
                                <?php foreach ($values as $val): ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="options[<?= htmlspecialchars($opt['option_name']); ?>]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= $val['additional_price']; ?>" data-extra='<?= $val['extra_data']; ?>'
                                            class="optionInput accent-yellow-500" <?= $opt['is_required'] ? 'required' : ''; ?>>
                                        <?= $val['value_name']; ?>
                                        <?php if ($val['additional_price'] > 0): ?>
                                            (+Rp <?= number_format($val['additional_price'], 0, ',', '.'); ?>)
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'multiple'): ?>
                            <div class="space-y-2">
                                <?php foreach ($values as $val): ?>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="options[<?= htmlspecialchars($opt['option_name']); ?>][]"
                                            value="<?= htmlspecialchars($val['value_name']); ?>"
                                            data-price="<?= $val['additional_price']; ?>" data-extra='<?= $val['extra_data']; ?>'
                                            class="optionInput accent-yellow-500">
                                        <?= $val['value_name']; ?>
                                        (+Rp <?= number_format($val['additional_price'], 0, ',', '.'); ?>)
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($opt['option_type'] === 'custom_input'): ?>
                            <textarea name="custom_input" class="w-full p-3 bg-black border border-gold rounded-lg"
                                placeholder="Masukkan tulisan..."></textarea>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

                <div class="border-t border-gold pt-6">
                    <p class="text-xl font-bold text-gold">
                        Total: Rp <span id="totalPrice">
                            <?= number_format($product['base_price'] ?? 0, 0, ',', '.'); ?>
                        </span>
                    </p>
                </div>

                <button type="submit"
                    class="w-full bg-gold text-black py-3 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Tambah ke Keranjang
                </button>

            </form>

        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("change", function () {

        let productType = document.getElementById('productType').value;
        let basePrice = parseInt(document.getElementById('basePrice').value) || 0;

        let total = 0;
        let modelPrice = 0;
        let nominal = 0;
        let qty = 0;

        document.querySelectorAll('.optionInput:checked').forEach(el => {

            let price = parseInt(el.dataset.price || 0);
            modelPrice += price;

            if (el.dataset.extra) {
                try {
                    let extra = JSON.parse(el.dataset.extra);

                    if (extra.nominal) {
                        nominal = parseInt(extra.nominal);
                    }

                    if (extra.qty) {
                        qty = parseInt(extra.qty);
                    }
                } catch (e) { }
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

        document.getElementById('totalPrice').innerText =
            total.toLocaleString('id-ID');
    });


    // 🔥 TAMBAHAN INI PENTING BANGET
    document.getElementById('productForm').addEventListener("submit", function () {

        let totalText = document.getElementById('totalPrice').innerText;

        // hapus titik ribuan
        let totalNumber = totalText.replace(/\./g, '');

        document.getElementById('finalPrice').value = totalNumber;
    });
</script>