<?php
/**
 * Modal konfirmasi reusable untuk sisi user.
 *
 * Dipakai sebagai pengganti window.confirm() bawaan browser supaya tampilannya
 * konsisten dengan modal-modal lain (Batalkan Pesanan, Hapus Item Keranjang,
 * Hapus Alamat). Include partial ini sekali di halaman yang membutuhkan
 * konfirmasi (atau di layout global), lalu panggil:
 *
 *   const ok = await showUserConfirmModal({
 *       title: 'Keluar dari Akun?',
 *       message: 'Sesi kamu akan diakhiri.',
 *       tone: 'danger',           // 'danger' (merah, default) | 'warning' | 'info'
 *       okText: 'Ya, Keluar',
 *       cancelText: 'Batal'
 *   });
 *   if (ok) { ... lanjut aksi ... }
 *
 * Bisa juga dipasang ke form lewat data-attribute:
 *   <form data-confirm
 *         data-confirm-title="Selesaikan pesanan?"
 *         data-confirm-message="Pesanan akan dikunci sebagai selesai."
 *         data-confirm-tone="warning"
 *         data-confirm-ok="Ya, Selesaikan">
 *     ...
 *   </form>
 */
?>
<div id="userConfirmModal"
    class="fixed inset-0 z-[150] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-confirm-cancel></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
        id="userConfirmModalContent">
        <div class="p-8 text-center">
            <div id="userConfirmIconWrap"
                class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-red-500/20 text-red-400 border-red-500/30">
                <svg id="userConfirmIcon" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="userConfirmTitle" class="text-xl font-bold text-white mb-2">Konfirmasi</h3>
            <p id="userConfirmMessage" class="text-gray-300 text-sm leading-relaxed">
                Yakin ingin melanjutkan?
            </p>
        </div>

        <div class="flex border-t border-white/10">
            <button type="button" id="userConfirmCancelBtn" data-confirm-cancel
                class="flex-1 py-4 text-gray-300 font-medium text-sm hover:bg-white/5 transition-colors border-r border-white/10">
                Batal
            </button>
            <button type="button" id="userConfirmOkBtn"
                class="flex-1 py-4 text-red-400 font-bold text-sm hover:bg-white/5 transition-colors">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__userConfirmInstalled) return;
        window.__userConfirmInstalled = true;

        const TONES = {
            danger: { wrap: 'bg-red-500/20 text-red-400 border-red-500/30', ok: 'text-red-400' },
            warning: { wrap: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30', ok: 'text-yellow-300' },
            info: { wrap: 'bg-blue-500/20 text-blue-300 border-blue-500/30', ok: 'text-blue-300' }
        };

        function openModal(modal, content) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(function () {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function closeModal(modal, content) {
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        /**
         * Tampilkan modal konfirmasi. Mengembalikan Promise<boolean>: true kalau
         * user klik OK, false kalau klik Batal / backdrop / Escape.
         */
        window.showUserConfirmModal = function (opts) {
            opts = opts || {};
            return new Promise(function (resolve) {
                const modal = document.getElementById('userConfirmModal');
                const content = document.getElementById('userConfirmModalContent');
                if (!modal || !content) {
                    resolve(window.confirm(opts.message || 'Yakin?'));
                    return;
                }
                const titleEl = document.getElementById('userConfirmTitle');
                const msgEl = document.getElementById('userConfirmMessage');
                const okBtn = document.getElementById('userConfirmOkBtn');
                const cancelBtn = document.getElementById('userConfirmCancelBtn');
                const iconWrap = document.getElementById('userConfirmIconWrap');
                const tone = TONES[opts.tone] || TONES.danger;

                if (titleEl) titleEl.textContent = opts.title || 'Konfirmasi';
                if (msgEl) msgEl.textContent = opts.message || 'Yakin ingin melanjutkan?';
                if (iconWrap) iconWrap.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border ' + tone.wrap;
                if (okBtn) {
                    okBtn.textContent = opts.okText || 'Ya, Lanjutkan';
                    okBtn.className = 'flex-1 py-4 font-bold text-sm hover:bg-white/5 transition-colors ' + tone.ok;
                }
                if (cancelBtn) cancelBtn.textContent = opts.cancelText || 'Batal';

                function finish(result) {
                    okBtn.onclick = null;
                    modal.querySelectorAll('[data-confirm-cancel]').forEach(function (el) { el.onclick = null; });
                    document.removeEventListener('keydown', onKey);
                    closeModal(modal, content);
                    resolve(result);
                }
                function onKey(e) { if (e.key === 'Escape') finish(false); }

                okBtn.onclick = function () { finish(true); };
                modal.querySelectorAll('[data-confirm-cancel]').forEach(function (el) {
                    el.onclick = function () { finish(false); };
                });
                document.addEventListener('keydown', onKey);
                openModal(modal, content);
            });
        };

        // Sugar: form[data-confirm] otomatis dicegat dan submit hanya kalau user OK.
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === '1') return; // sudah dikonfirmasi, lanjutkan submit
            e.preventDefault();
            window.showUserConfirmModal({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirmMessage,
                tone: form.dataset.confirmTone,
                okText: form.dataset.confirmOk,
                cancelText: form.dataset.confirmCancel
            }).then(function (ok) {
                if (!ok) return;
                form.dataset.confirmed = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        }, true);

        // Sugar: <a data-confirm href="..."> akan minta konfirmasi sebelum navigasi.
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-confirm]');
            if (!link) return;
            if (link.dataset.confirmed === '1') return;
            e.preventDefault();
            window.showUserConfirmModal({
                title: link.dataset.confirmTitle,
                message: link.dataset.confirmMessage,
                tone: link.dataset.confirmTone,
                okText: link.dataset.confirmOk,
                cancelText: link.dataset.confirmCancel
            }).then(function (ok) {
                if (!ok) return;
                link.dataset.confirmed = '1';
                window.location.href = link.href;
            });
        });
    })();
</script>
