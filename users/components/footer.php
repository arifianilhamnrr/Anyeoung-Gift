<?php
// ambil store setting
$stmt = $pdo->query("SELECT * FROM store_settings LIMIT 1");
$store = $stmt->fetch();

// ambil alamat toko
$stmt = $pdo->query("
    SELECT * FROM addresses
    WHERE type = 'store'
    ORDER BY is_default DESC, id DESC
    LIMIT 1
");
$storeAddress = $stmt->fetch();
?>

<footer class="bg-black border-t border-gold mt-20">
    <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 text-sm">

        <!-- BRAND -->
        <div>
            <h3 class="font-title text-gold text-lg mb-3">
                <?= htmlspecialchars($store['store_name'] ?? 'Anyeoung Gift'); ?>
            </h3>
            <p class="text-gray-400">
                Hadiah spesial untuk momen spesial Anda.
            </p>
        </div>

        <!-- NAVIGASI -->
        <div>
            <h3 class="font-title text-gold text-lg mb-3">Navigasi</h3>
            <ul class="space-y-2 text-gray-400">
                <li>
                    <a href="index.php?page=home" class="hover:text-gold transition">
                        Beranda
                    </a>
                </li>
                <li>
                    <a href="index.php?page=products" class="hover:text-gold transition">
                        Produk
                    </a>
                </li>
                <li>
                    <a href="index.php?page=orders" class="hover:text-gold transition">
                        Pesanan
                    </a>
                </li>
                <li>
                    <a href="index.php?page=addresses" class="hover:text-gold transition">
                        Kontak
                    </a>
                </li>
            </ul>
        </div>

        <!-- KONTAK -->
        <div>
            <h3 class="font-title text-gold text-lg mb-3">Kontak</h3>

            <?php if (!empty($store['whatsapp_admin'])): ?>
                <p class="text-gray-400 mb-2">
                    WhatsApp:
                    <a href="https://wa.me/<?= $store['whatsapp_admin']; ?>" target="_blank"
                        class="text-gold hover:underline">
                        <?= $store['whatsapp_admin']; ?>
                    </a>
                </p>
            <?php endif; ?>

            <?php if ($storeAddress): ?>
                <p class="text-gray-400 text-sm mb-2 whitespace-pre-line">
                    <?= htmlspecialchars($storeAddress['address_text']); ?>
                </p>
            <?php endif; ?>

            <p class="text-gold text-sm">
                Ambil pesanan langsung di toko
            </p>
        </div>

    </div>

    <div class="text-center text-xs py-4 border-t border-gold text-gray-500">
        © <?= date('Y'); ?> <?= htmlspecialchars($store['store_name'] ?? 'Anyeoung Gift'); ?>. All rights reserved.
    </div>
</footer>

</body>

</html>