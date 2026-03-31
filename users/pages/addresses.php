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

<div class="space-y-6 relative">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-title text-gold mb-2">Kontak Pemesan</h1>
            <p class="text-gray-400 text-sm md:text-base">Simpan data kontak untuk mempercepat proses checkout.</p>
        </div>
        <button type="button" onclick="openAddressModal()"
            class="bg-gold text-black px-6 py-3 rounded-xl font-bold hover:bg-yellow-400 shadow-[0_4px_14px_0_rgba(212,175,55,0.39)] transition-all duration-300 flex items-center justify-center gap-2 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kontak
        </button>
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 backdrop-blur-md">
        <?php if (empty($addresses)): ?>
            <div class="text-center py-12 bg-black/30 border border-white/5 rounded-2xl">
                <div class="w-16 h-16 bg-white/5 text-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
                <p class="text-gray-400">Belum ada kontak tersimpan.</p>
                <button type="button" onclick="openAddressModal()"
                    class="mt-4 text-gold hover:text-yellow-400 font-semibold text-sm underline underline-offset-4">
                    Tambah sekarang
                </button>
            </div>
        <?php else: ?>
            <div class="grid lg:grid-cols-2 gap-5">
                <?php foreach ($addresses as $address): ?>
                    <div
                        class="group relative bg-black/40 border border-white/10 rounded-2xl p-5 hover:border-gold/30 transition-all duration-300 flex flex-col h-full">

                        <?php if ($address['is_default']): ?>
                            <div
                                class="absolute top-0 right-0 -mt-3 -mr-2 px-3 py-1 bg-gold text-black text-xs font-bold rounded-full shadow-[0_0_10px_rgba(212,175,55,0.4)] z-10">
                                Utama
                            </div>
                        <?php endif; ?>

                        <div class="flex-1 space-y-3">
                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                <?= htmlspecialchars($address['recipient_name']); ?>
                            </h3>

                            <div class="flex items-center gap-2 text-gold font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <?= htmlspecialchars($address['whatsapp_number']); ?>
                            </div>

                            <div class="text-gray-400 text-sm whitespace-pre-line leading-relaxed flex items-start gap-2">
                                <svg class="w-4 h-4 mt-1 shrink-0 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($address['address_text']); ?></span>
                            </div>

                            <?php if (!empty($address['notes'])): ?>
                                <div class="bg-white/5 border border-white/5 rounded-lg p-3 mt-2">
                                    <p class="text-xs text-gray-400 italic flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        <?= htmlspecialchars($address['notes']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex gap-2 border-t border-white/10 pt-4 mt-4">
                            <?php if (!$address['is_default']): ?>
                                <form action="actions/save-address.php" method="POST" class="flex-1">
                                    <input type="hidden" name="set_default_id" value="<?= $address['id']; ?>">
                                    <button type="submit"
                                        class="w-full py-2.5 px-3 text-xs rounded-xl font-bold bg-white/5 text-gold hover:bg-gold hover:text-black transition-colors border border-gold/30 hover:border-gold">
                                        Jadikan Utama
                                    </button>
                                </form>
                            <?php endif; ?>

                            <button type="button" onclick="openDeleteModal(<?= $address['id']; ?>)"
                                class="<?= $address['is_default'] ? 'w-full' : 'flex-1' ?> py-2.5 px-3 text-xs rounded-xl font-bold bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition-colors border border-red-500/30 hover:border-red-500">
                                Hapus
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($addressSuccess || $addressError): ?>
    <div id="notifModal"
        class="fixed inset-0 z-[110] flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeNotifModal()"></div>

        <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
            id="notifModalContent">
            <div class="p-8 text-center">
                <?php if ($addressSuccess): ?>
                    <div
                        class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-500/30">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Berhasil!</h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?= htmlspecialchars($addressSuccess); ?></p>
                <?php else: ?>
                    <div
                        class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Terjadi Kesalahan!</h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?= htmlspecialchars($addressError); ?></p>
                <?php endif; ?>
            </div>

            <div class="flex border-t border-white/10">
                <button type="button" onclick="closeNotifModal()"
                    class="w-full py-4 text-gold font-bold text-sm hover:bg-white/5 transition-colors">
                    Oke, Mengerti
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

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
            <h3 class="text-xl font-bold text-white mb-2">Hapus Kontak?</h3>
            <p class="text-gray-300 text-sm leading-relaxed">
                Kontak yang sudah dihapus tidak dapat dikembalikan.
            </p>
        </div>

        <div class="flex border-t border-white/10">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 py-4 text-gray-300 font-medium text-sm hover:bg-white/5 transition-colors border-r border-white/10">
                Batal
            </button>
            <form action="actions/delete-address.php" method="POST" class="flex-1 flex">
                <input type="hidden" name="address_id" id="modalDeleteId" value="">
                <button type="submit"
                    class="w-full py-4 text-red-400 font-bold text-sm hover:bg-white/5 transition-colors">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<div id="addressModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAddressModal()"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300"
        id="addressModalContent">
        <div class="p-6 md:p-8">
            <h3 class="text-2xl font-title text-gold mb-1 text-center">Tambah Kontak</h3>
            <p class="text-gray-400 text-sm text-center mb-6">Isi data pemesan dengan lengkap.</p>

            <form action="actions/save-address.php" method="POST" class="space-y-4">
                <div class="space-y-1">
                    <input type="text" name="recipient_name" required placeholder="Nama Pemesan / Penerima"
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="space-y-1">
                    <input type="number" name="whatsapp_number" required
                        placeholder="Nomor WhatsApp (Contoh: 08123456...)"
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors">
                </div>

                <div class="space-y-1">
                    <textarea name="address_text" rows="3" required placeholder="Alamat / Domisili lengkap..."
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors"></textarea>
                    <p class="text-[11px] text-gray-500 mt-1 ml-1 italic">*Hanya untuk identitas, bukan kurir
                        pengiriman.</p>
                </div>

                <div class="space-y-1">
                    <textarea name="notes" rows="2" placeholder="Catatan Tambahan (Opsional)..."
                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-colors"></textarea>
                </div>

                <label
                    class="flex items-center gap-3 text-sm text-gray-300 cursor-pointer p-4 bg-white/5 rounded-xl border border-white/5 hover:border-gold/30 transition-colors mt-2">
                    <input type="checkbox" name="is_default" value="1"
                        class="w-4 h-4 accent-gold rounded focus:ring-gold cursor-pointer">
                    <span class="select-none font-medium text-white">Jadikan kontak utama</span>
                </label>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeAddressModal()"
                        class="flex-1 bg-white/5 text-gray-300 py-3.5 rounded-xl font-semibold hover:bg-white/10 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-500 shadow-[0_0_15px_rgba(212,175,55,0.3)] transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI ANIMASI MODAL UMUM
    function animateOpen(modal, content) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }

    function animateClose(modal, content) {
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    // MODAL TAMBAH KONTAK
    const addressModal = document.getElementById('addressModal');
    const addressModalContent = document.getElementById('addressModalContent');
    function openAddressModal() { animateOpen(addressModal, addressModalContent); }
    function closeAddressModal() { animateClose(addressModal, addressModalContent); }

    // MODAL HAPUS KONTAK
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const modalDeleteId = document.getElementById('modalDeleteId');
    function openDeleteModal(id) {
        modalDeleteId.value = id;
        animateOpen(deleteModal, deleteModalContent);
    }
    function closeDeleteModal() { animateClose(deleteModal, deleteModalContent); }

    // MODAL NOTIFIKASI SUKSES/ERROR (Auto-Open)
    <?php if ($addressSuccess || $addressError): ?>
        document.addEventListener("DOMContentLoaded", function () {
            const notifModal = document.getElementById('notifModal');
            const notifModalContent = document.getElementById('notifModalContent');
            setTimeout(() => {
                notifModal.classList.remove('opacity-0');
                notifModalContent.classList.remove('scale-95');
                notifModalContent.classList.add('scale-100');
            }, 50); // Delay dikit biar transisinya jalan waktu page refresh
        });

        function closeNotifModal() {
            const notifModal = document.getElementById('notifModal');
            const notifModalContent = document.getElementById('notifModalContent');
            notifModal.classList.add('opacity-0');
            notifModalContent.classList.remove('scale-100');
            notifModalContent.classList.add('scale-95');
            setTimeout(() => {
                notifModal.style.display = 'none'; // Pakai display none karena ngga pakai class hidden di awal
            }, 300);
        }
    <?php endif; ?>
</script>