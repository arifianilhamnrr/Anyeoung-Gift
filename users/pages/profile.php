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

<div class="space-y-6 relative">

    <div>
        <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Profil Saya</h1>
        <p class="text-gray-400">Kelola informasi akun dan aktivitas kamu.</p>
    </div>

    <?php if ($profileSuccess): ?>
        <div class="bg-green-600/20 border border-green-500/50 text-green-300 p-4 rounded-xl backdrop-blur-sm">
            <?= htmlspecialchars($profileSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($profileError): ?>
        <div class="bg-red-600/20 border border-red-500/50 text-red-300 p-4 rounded-xl backdrop-blur-sm">
            <?= htmlspecialchars($profileError); ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-title text-gold">Informasi Akun</h2>
                    <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <div class="space-y-3 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Nama</p>
                        <p class="text-white font-semibold text-lg"><?= htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Email</p>
                        <p class="text-gray-300"><?= htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Pesanan</p>
                        <p class="text-gold font-bold"><?= (int) $totalOrders; ?> Pesanan</p>
                    </div>
                </div>
            </div>

            <button type="button" onclick="openProfileModal()"
                class="w-full border border-gold/50 text-gold py-2.5 rounded-xl font-semibold hover:bg-gold hover:text-black transition-colors duration-300">
                Ubah Profil
            </button>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-title text-gold">Keamanan Akun</h2>
                    <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="space-y-3 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Password</p>
                        <p class="text-gray-300 tracking-[0.2em] font-mono text-lg mt-1">••••••••</p>
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Pastikan untuk mengganti password secara berkala agar akun kamu tetap aman.
                    </p>
                </div>
            </div>

            <button type="button" onclick="openPasswordModal()"
                class="w-full border border-gold/50 text-gold py-2.5 rounded-xl font-semibold hover:bg-gold hover:text-black transition-colors duration-300">
                Ubah Password
            </button>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-title text-gold">Kontak Utama</h2>
                    <div class="w-10 h-10 bg-gold/20 rounded-full flex items-center justify-center text-gold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>

                <?php if ($defaultAddress): ?>
                    <div class="space-y-2 text-sm mb-6">
                        <div class="text-white font-semibold text-base">
                            <?= htmlspecialchars($defaultAddress['recipient_name']); ?>
                        </div>
                        <div class="text-gold">
                            <?= htmlspecialchars($defaultAddress['whatsapp_number']); ?>
                        </div>
                        <div class="text-gray-400 whitespace-pre-line leading-relaxed">
                            <?= htmlspecialchars($defaultAddress['address_text']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400 mb-6">Belum ada kontak utama.</p>
                <?php endif; ?>
            </div>

            <a href="index.php?page=addresses"
                class="block w-full text-center bg-white/5 border border-white/10 text-white py-2.5 rounded-xl font-semibold hover:bg-white/10 transition-colors duration-300">
                Kelola Kontak
            </a>
        </div>
    </div>
</div>

<div id="profileModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeProfileModal()"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300"
        id="profileModalContent">

        <div class="p-6 md:p-8">
            <h3 class="text-2xl font-title text-gold mb-1 text-center">Ubah Profil</h3>
            <p class="text-gray-400 text-sm text-center mb-6">Perbarui informasi akun kamu di bawah ini.</p>

            <form action="actions/update-profile.php" method="POST" class="space-y-5">
                <input type="hidden" name="form_type" value="update_name">

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-300 ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-300 ml-1">Email <span
                            class="text-gray-500 text-xs">(Tidak dapat diubah)</span></label>
                    <input type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled
                        class="w-full bg-white/5 border border-white/5 rounded-xl px-4 py-3 text-gray-500 cursor-not-allowed">
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeProfileModal()"
                        class="flex-1 bg-white/5 text-gray-300 py-3 rounded-xl font-semibold hover:bg-white/10 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-gold text-black py-3 rounded-xl font-bold hover:bg-yellow-500 shadow-[0_0_15px_rgba(212,175,55,0.3)] transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="passwordModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePasswordModal()"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300"
        id="passwordModalContent">

        <div class="p-6 md:p-8">
            <h3 class="text-2xl font-title text-gold mb-1 text-center">Ubah Password</h3>
            <p class="text-gray-400 text-sm text-center mb-6">Gunakan kombinasi minimal 6 karakter.</p>

            <form action="actions/update-profile.php" method="POST" class="space-y-4">
                <input type="hidden" name="form_type" value="update_password">

                <div class="space-y-1">
                    <input type="password" name="current_password" required placeholder="Password Lama"
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="space-y-1">
                    <input type="password" name="new_password" required minlength="6" placeholder="Password Baru"
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="space-y-1">
                    <input type="password" name="confirm_password" required minlength="6"
                        placeholder="Konfirmasi Password Baru"
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closePasswordModal()"
                        class="flex-1 bg-white/5 text-gray-300 py-3 rounded-xl font-semibold hover:bg-white/10 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-gold text-black py-3 rounded-xl font-bold hover:bg-yellow-500 shadow-[0_0_15px_rgba(212,175,55,0.3)] transition">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- LOGIC UNTUK MODAL PROFIL ---
    const profileModal = document.getElementById('profileModal');
    const profileModalContent = document.getElementById('profileModalContent');

    function openProfileModal() {
        profileModal.classList.remove('hidden');
        profileModal.classList.add('flex');
        setTimeout(() => {
            profileModal.classList.remove('opacity-0');
            profileModalContent.classList.remove('scale-95');
            profileModalContent.classList.add('scale-100');
        }, 10);
    }

    function closeProfileModal() {
        profileModal.classList.add('opacity-0');
        profileModalContent.classList.remove('scale-100');
        profileModalContent.classList.add('scale-95');
        setTimeout(() => {
            profileModal.classList.add('hidden');
            profileModal.classList.remove('flex');
        }, 300);
    }

    // --- LOGIC UNTUK MODAL PASSWORD ---
    const passwordModal = document.getElementById('passwordModal');
    const passwordModalContent = document.getElementById('passwordModalContent');

    function openPasswordModal() {
        passwordModal.classList.remove('hidden');
        passwordModal.classList.add('flex');
        setTimeout(() => {
            passwordModal.classList.remove('opacity-0');
            passwordModalContent.classList.remove('scale-95');
            passwordModalContent.classList.add('scale-100');
        }, 10);
    }

    function closePasswordModal() {
        passwordModal.classList.add('opacity-0');
        passwordModalContent.classList.remove('scale-100');
        passwordModalContent.classList.add('scale-95');
        setTimeout(() => {
            passwordModal.classList.add('hidden');
            passwordModal.classList.remove('flex');
        }, 300);
    }
</script>