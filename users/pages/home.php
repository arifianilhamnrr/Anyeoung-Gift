<?php
// Ambil data produk dan gambar utamanya
$stmt = $pdo->query("
    SELECT p.*, 
           (SELECT image_path 
            FROM product_images 
            WHERE product_id = p.id 
            AND is_primary = 1 
            AND option_value_id IS NULL 
            LIMIT 1) as main_image
    FROM products p
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
");

$products = $stmt->fetchAll();

// Ambil settingan toko untuk nomor WA Admin
$stmt = $pdo->query("SELECT * FROM store_settings LIMIT 1");
$storeSetting = $stmt->fetch();
?>

<section class="mb-12 text-center px-4 pt-10">
    <h1 class="text-3xl md:text-5xl font-title text-gold mb-4 drop-shadow-lg">
        Hadiah Spesial
    </h1>
    <p class="text-gray-300 max-w-2xl mx-auto text-sm md:text-base opacity-90">
        Pilih bouquet dan hampers terbaik untuk orang tersayang.
    </p>
</section>

<section class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-8 px-2 md:px-0">

    <?php foreach ($products as $product): ?>
        <?php
        // --- 1. PERBAIKAN LOGIC WA ---
        // Pindah ke sini agar nama produk di chat WA beda-beda tiap barang
        $waLink = '#';
        if ($product['product_type'] === 'chat_only') {
            $message = str_replace('{{product_name}}', $product['name'], $storeSetting['whatsapp_message_template']);
            $waLink = 'https://wa.me/' . $storeSetting['whatsapp_admin'] . '?text=' . urlencode($message);
        }

        // --- 2. PERBAIKAN PATH GAMBAR ---
        // Fungsi basename() dipakai buat ngambil NAMA FILE-nya aja, jaga-jaga kalau di DB keinput full path
        $imgFile = basename($product['main_image'] ?? 'default.jpg');

        // Karena jalannya dari users/index.php, kita keluar dulu (../) baru masuk ke public/
        $imageSrc = "../public/uploads/products/" . $imgFile;
        ?>

        <div
            class="group flex flex-col h-full bg-black/40 backdrop-blur-md border border-gold/20 rounded-xl overflow-hidden hover:border-gold/60 hover:shadow-[0_0_20px_rgba(212,175,55,0.2)] hover:-translate-y-1 transition-all duration-300">

            <div class="h-40 md:h-60 bg-white/5 overflow-hidden relative">
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 z-10">
                </div>

                <img src="<?= htmlspecialchars($imageSrc); ?>" alt="<?= htmlspecialchars($product['name']); ?>"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-in-out">
            </div>

            <div class="p-3 md:p-6 flex flex-col flex-grow relative z-20">

                <h2
                    class="text-sm md:text-xl font-semibold mb-2 text-gray-100 group-hover:text-gold transition-colors line-clamp-2">
                    <?= htmlspecialchars($product['name']); ?>
                </h2>

                <div class="mb-4">
                    <?php if ($product['product_type'] === 'simple'): ?>
                        <p class="text-gold font-bold text-sm md:text-lg drop-shadow-sm">
                            Rp <?= number_format($product['base_price'], 0, ',', '.'); ?>
                        </p>
                    <?php else: ?>
                        <p class="text-gold font-bold text-sm md:text-lg drop-shadow-sm">
                            Custom Order
                        </p>
                    <?php endif; ?>
                </div>

                <div class="mt-auto">
                    <?php if ($product['product_type'] === 'chat_only'): ?>
                        <a href="<?= htmlspecialchars($waLink); ?>" target="_blank"
                            class="block text-center bg-gold/90 text-black text-xs md:text-base py-2 rounded-lg font-medium hover:bg-gold hover:shadow-lg hover:shadow-gold/30 transition-all duration-300">
                            Hubungi Admin
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=product&id=<?= $product['id']; ?>"
                            class="block text-center border border-gold text-gold hover:text-black hover:bg-gold text-xs md:text-base py-2 rounded-lg font-medium transition-all duration-300">
                            Lihat Detail
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    <?php endforeach; ?>

</section>