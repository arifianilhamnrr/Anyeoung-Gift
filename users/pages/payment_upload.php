<?php
$userId = $_SESSION['user_id'];
$orderId = (int) ($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    $_SESSION['order_error'] = 'Order tidak valid.';
    header('Location: index.php?page=orders');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        o.*,
        p.id AS payment_id,
        p.status AS payment_status,
        p.proof_image,
        pm.name AS payment_method_name,
        pm.type AS payment_method_type
    FROM orders o
    INNER JOIN payments p ON p.order_id = o.id
    INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['order_error'] = 'Pesanan tidak ditemukan.';
    header('Location: index.php?page=orders');
    exit;
}

if ($order['payment_method_type'] === 'onsite') {
    $_SESSION['order_error'] = 'Pesanan ini menggunakan pembayaran di toko, jadi tidak perlu upload bukti.';
    header('Location: index.php?page=orders');
    exit;
}

$uploadSuccess = $_SESSION['upload_success'] ?? null;
$uploadError = $_SESSION['upload_error'] ?? null;
unset($_SESSION['upload_success'], $_SESSION['upload_error']);
?>

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Upload Bukti Pembayaran</h1>
        <p class="text-gray-400">Upload bukti pembayaran untuk pesanan #<?= (int) $order['id']; ?>.</p>
    </div>

    <?php if ($uploadSuccess): ?>
        <div class="bg-green-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($uploadSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($uploadError): ?>
        <div class="bg-red-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($uploadError); ?>
        </div>
    <?php endif; ?>

    <div class="bg-black border border-gold rounded-2xl p-6">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <div class="text-sm text-gray-400 mb-1">Nomor Pesanan</div>
                <div class="text-white font-semibold mb-4">#<?= (int) $order['id']; ?></div>

                <div class="text-sm text-gray-400 mb-1">Metode Pembayaran</div>
                <div class="text-white font-semibold mb-4"><?= htmlspecialchars($order['payment_method_name']); ?></div>

                <div class="text-sm text-gray-400 mb-1">Total Pembayaran</div>
                <div class="text-gold font-bold text-xl">
                    Rp <?= number_format((int) $order['total_price'], 0, ',', '.'); ?>
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-400 mb-1">Status Pembayaran</div>
                <div class="text-white font-semibold mb-4">
                    <?= $order['payment_status'] === 'confirmed' ? 'Terkonfirmasi' : ($order['payment_status'] === 'rejected' ? 'Ditolak' : 'Menunggu Konfirmasi'); ?>
                </div>

                <?php if (!empty($order['proof_image'])): ?>
                    <div class="text-sm text-gray-400 mb-2">Bukti Saat Ini</div>
                    <img src="../uploads/payments/<?= htmlspecialchars($order['proof_image']); ?>" alt="Bukti Pembayaran"
                        class="w-full max-w-xs rounded-xl border border-gold/20 object-cover">
                <?php else: ?>
                    <div class="text-sm text-gray-400">
                        Belum ada bukti pembayaran yang diupload.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-black border border-gold rounded-2xl p-6">
        <h2 class="text-xl font-title text-gold mb-4">Form Upload</h2>

        <form action="actions/upload-payment.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="order_id" value="<?= (int) $order['id']; ?>">

            <div>
                <label class="block text-sm text-gray-300 mb-2">Pilih Bukti Pembayaran</label>
                <input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.webp" required
                    class="block w-full text-sm text-gray-300 file:mr-4 file:py-3 file:px-4 file:rounded-full file:border-0 file:bg-gold file:text-black file:font-semibold hover:file:bg-yellow-500">
                <p class="text-xs text-gray-500 mt-2">
                    Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2MB.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="w-full bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                    Upload Bukti
                </button>

                <a href="index.php?page=orders"
                    class="w-full text-center border border-gold text-gold py-3 rounded-full font-semibold hover:bg-gold hover:text-black transition">
                    Kembali ke Pesanan
                </a>
            </div>
        </form>
    </div>
</div>