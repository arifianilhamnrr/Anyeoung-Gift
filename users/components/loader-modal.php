<?php
/**
 * Modal loading dengan 3-dot bouncing loader (Uiverse.io by Javierrocadev).
 * Dipakai di halaman yang submit form ke server yang butuh waktu (checkout,
 * upload bukti pembayaran). Modal akan otomatis hilang ketika halaman
 * navigasi ke halaman berikutnya setelah server selesai memproses.
 *
 * Parameter opsional:
 *   $loaderId    - id wrapper modal (default: "globalLoadingModal")
 *   $loaderTitle - judul modal (default: "Memproses...")
 *   $loaderText  - teks penjelas (default: "Mohon tunggu sebentar.")
 */
$loaderId    = $loaderId    ?? 'globalLoadingModal';
$loaderTitle = $loaderTitle ?? 'Memproses...';
$loaderText  = $loaderText  ?? 'Mohon tunggu sebentar.<br>Jangan tutup atau refresh halaman ini.';
?>
<div id="<?= htmlspecialchars($loaderId); ?>"
    class="fixed inset-0 z-[120] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <!-- Backdrop redup supaya loader-nya kelihatan jelas -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
        id="<?= htmlspecialchars($loaderId); ?>Content">
        <div class="p-8 text-center flex flex-col items-center">
            <div class="dot-loader mb-5" role="status" aria-label="Memuat">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($loaderTitle); ?></h3>
            <p class="text-gray-300 text-sm leading-relaxed">
                <?= $loaderText; ?>
            </p>
        </div>
    </div>
</div>

<style>
    /* 3-dot bouncing loader - by Javierrocadev (Uiverse.io) */
    .dot-loader {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
    }
    .dot-loader .dot {
        width: 1rem;
        height: 1rem;
        border-radius: 9999px;
        background-color: #1d4ed8; /* tailwind blue-700 */
        animation: dotBounce 1s infinite;
    }
    .dot-loader .dot:nth-child(1) { animation-delay: 0.7s; }
    .dot-loader .dot:nth-child(2) { animation-delay: 0.3s; }
    .dot-loader .dot:nth-child(3) { animation-delay: 0.7s; }
    @keyframes dotBounce {
        0%, 100% {
            transform: translateY(-25%);
            animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
        }
        50% {
            transform: none;
            animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
        }
    }
</style>

<script>
    (function () {
        if (window.__loaderHelpersInstalled) {
            return;
        }
        window.__loaderHelpersInstalled = true;

        function openLoader(modal, content) {
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(function () {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            }, 10);
        }

        function closeLoader(modal, content) {
            if (!modal) return;
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        window.openTruckLoader = function (loaderId) {
            const modal = document.getElementById(loaderId);
            const content = document.getElementById(loaderId + 'Content');
            openLoader(modal, content);
        };
        window.closeTruckLoader = function (loaderId) {
            const modal = document.getElementById(loaderId);
            const content = document.getElementById(loaderId + 'Content');
            closeLoader(modal, content);
        };

        /**
         * Pasang loader ke sebuah form. Loader akan otomatis muncul saat
         * submit dan ditutup kalau user kembali via tombol back (bfcache).
         */
        window.attachTruckLoaderToForm = function (formId, loaderId) {
            const form = document.getElementById(formId);
            const modal = document.getElementById(loaderId);
            const content = document.getElementById(loaderId + 'Content');
            if (!form || !modal) return;

            form.addEventListener('submit', function () {
                const submitButtons = form.querySelectorAll(
                    'button[type="submit"], [form="' + formId + '"]'
                );
                submitButtons.forEach(function (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                });
                openLoader(modal, content);
            });

            window.addEventListener('pageshow', function () {
                if (!modal.classList.contains('hidden')) {
                    closeLoader(modal, content);
                }
            });
        };
    })();
</script>
