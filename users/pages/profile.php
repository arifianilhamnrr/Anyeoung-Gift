<?php
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT * FROM addresses
    WHERE user_id = ? AND is_default = 1
    LIMIT 1
");
$stmt->execute([$userId]);
$defaultAddress = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM orders WHERE user_id = ?
");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetchColumn();

$profileSuccess = $_SESSION['profile_success'] ?? null;
$profileError = $_SESSION['profile_error'] ?? null;
unset($_SESSION['profile_success'], $_SESSION['profile_error']);
?>

<div class="space-y-6">

    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Profil Saya</h1>
        <p class="text-gray-400">Kelola informasi akun dan aktivitas kamu.</p>
    </div>

    <?php if ($profileSuccess): ?>
        <div class="bg-green-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($profileSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($profileError): ?>
        <div class="bg-red-600 text-white p-4 rounded-xl">
            <?= htmlspecialchars($profileError); ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">

        <div class="bg-black border border-gold rounded-2xl p-6">
            <h2 class="text-xl font-title text-gold mb-4">Informasi Akun</h2>

            <form action="actions/update-profile.php" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="update_name">

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Nama</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Email</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled
                        class="w-full bg-white/5 border border-gold/10 rounded-xl px-4 py-3 text-gray-400">
                </div>

                <button type="submit"
                    class="w-full bg-gold text-black py-3 rounded-full font-semibold hover:bg-yellow-500 transition">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <div class="bg-black border border-gold rounded-2xl p-6">
            <h2 class="text-xl font-title text-gold mb-4">Ubah Password</h2>

            <form action="actions/update-profile.php" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="update_password">

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Password Lama</label>
                    <input type="password" name="current_password" required
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Password Baru</label>
                    <input type="password" name="new_password" required minlength="6"
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <div>
                    <label class="block text-sm text-gray-300 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="w-full bg-transparent border border-gold/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold">
                </div>

                <button type="submit"
                    class="w-full border border-gold text-gold py-3 rounded-full font-semibold hover:bg-gold hover:text-black transition">
                    Update Password
                </button>
            </form>
        </div>

        <div class="bg-black border border-gold rounded-2xl p-6">
            <h2 class="text-xl font-title text-gold mb-4">Kontak Utama</h2>

            <?php if ($defaultAddress): ?>
                <div class="space-y-2 text-sm">
                    <div class="text-white font-semibold">
                        <?= htmlspecialchars($defaultAddress['recipient_name']); ?>
                    </div>

                    <div class="text-gray-300">
                        <?= htmlspecialchars($defaultAddress['whatsapp_number']); ?>
                    </div>

                    <div class="text-gray-400 whitespace-pre-line">
                        <?= htmlspecialchars($defaultAddress['address_text']); ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-400">Belum ada kontak utama.</p>
            <?php endif; ?>

            <div class="mt-4">
                <a href="index.php?page=addresses" class="text-gold hover:underline text-sm">
                    Kelola Kontak
                </a>
            </div>
        </div>
    </div>
</div>