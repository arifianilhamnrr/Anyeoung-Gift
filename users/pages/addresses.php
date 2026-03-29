<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
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

$addressSuccess = $_SESSION['address_success'] ?? null;
$addressError = $_SESSION['address_error'] ?? null;
unset($_SESSION['address_success'], $_SESSION['address_error']);
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Kontak Pemesan</h1>
        <p class="text-gray-400">Simpan data kontak yang akan dipakai saat checkout dan pengambilan pesanan.</p>
    </div>

    <?php if ($addressSuccess): ?>
        <div class="bg-green-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($addressSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($addressError): ?>
        <div class="bg-red-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($addressError); ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-black border border-gold rounded-2xl p-6">
            <h2 class="text-xl font-title text-gold mb-4">Tambah Kontak Baru</h2>

            <form action="actions/save-address.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Nama Pemesan / Penerima</label>
                    <input type="text" name="recipient_name" required
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp_number" required
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Alamat / Domisili</label>
                    <textarea name="address_text" rows="4" required
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold"></textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        Data ini untuk identitas pemesan, bukan untuk pengiriman barang.
                    </p>
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Catatan Tambahan</label>
                    <textarea name="notes" rows="2"
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold"></textarea>
                </div>

                <label class="flex items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="is_default" value="1" class="accent-yellow-500">
                    Jadikan kontak utama
                </label>

                <button type="submit"
                    class="w-full bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                    Simpan Kontak
                </button>
            </form>
        </div>

        <div class="bg-black border border-gold rounded-2xl p-6">
            <h2 class="text-xl font-title text-gold mb-4">Daftar Kontak</h2>

            <?php if (empty($addresses)): ?>
                <p class="text-gray-400">Belum ada kontak tersimpan.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($addresses as $address): ?>
                        <div class="border border-gold/20 rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
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

                                    <div class="text-gray-400 text-sm mt-2 whitespace-pre-line">
                                        <?= htmlspecialchars($address['address_text']); ?>
                                    </div>

                                    <?php if (!empty($address['notes'])): ?>
                                        <div class="text-gray-500 text-xs mt-2">
                                            Catatan: <?= htmlspecialchars($address['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <?php if (!$address['is_default']): ?>
                                        <form action="actions/save-address.php" method="POST">
                                            <input type="hidden" name="set_default_id" value="<?= $address['id']; ?>">
                                            <button type="submit"
                                                class="px-3 py-2 text-xs rounded-full border border-gold text-gold hover:bg-gold hover:text-black transition">
                                                Jadikan Utama
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="actions/delete-address.php" method="POST"
                                        onsubmit="return confirm('Hapus kontak ini?')">
                                        <input type="hidden" name="address_id" value="<?= $address['id']; ?>">
                                        <button type="submit"
                                            class="px-3 py-2 text-xs rounded-full bg-red-600 text-white hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>