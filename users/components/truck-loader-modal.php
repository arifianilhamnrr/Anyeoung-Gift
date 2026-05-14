<?php
/**
 * Modal loading dengan truck loader (Uiverse.io by vinodjangid07).
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
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300"
        id="<?= htmlspecialchars($loaderId); ?>Content">
        <div class="p-8 text-center flex flex-col items-center">
            <div class="loader mb-4">
                <div class="truckWrapper">
                    <div class="truckBody">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 198 93" class="trucksvg">
                            <path stroke-width="3" stroke="#282828" fill="#F83D3D" d="M135 22.5H177.264C178.295 22.5 179.22 23.133 179.594 24.0939L192.33 56.8443C192.442 57.1332 192.5 57.4404 192.5 57.7504V89C192.5 90.3807 191.381 91.5 190 91.5H135C133.619 91.5 132.5 90.3807 132.5 89V25C132.5 23.6193 133.619 22.5 135 22.5Z"></path>
                            <path stroke-width="3" stroke="#282828" fill="#7D7C7C" d="M146 33.5H181.741C182.779 33.5 183.709 34.1415 184.078 35.112L190.538 52.112C191.16 53.748 189.951 55.5 188.201 55.5H146C144.619 55.5 143.5 54.3807 143.5 53V36C143.5 34.6193 144.619 33.5 146 33.5Z"></path>
                            <path stroke-width="2" stroke="#282828" fill="#282828" d="M150 65C150 65.39 149.763 65.8656 149.127 66.2893C148.499 66.7083 147.573 67 146.5 67C145.427 67 144.501 66.7083 143.873 66.2893C143.237 65.8656 143 65.39 143 65C143 64.61 143.237 64.1344 143.873 63.7107C144.501 63.2917 145.427 63 146.5 63C147.573 63 148.499 63.2917 149.127 63.7107C149.763 64.1344 150 64.61 150 65Z"></path>
                            <rect stroke-width="2" stroke="#282828" fill="#FFFCAB" rx="1" height="7" width="5" y="63" x="187"></rect>
                            <rect stroke-width="2" stroke="#282828" fill="#282828" rx="1" height="11" width="4" y="81" x="193"></rect>
                            <rect stroke-width="3" stroke="#282828" fill="#DFDFDF" rx="2.5" height="90" width="121" y="1.5" x="6.5"></rect>
                            <rect stroke-width="2" stroke="#282828" fill="#DFDFDF" rx="2" height="4" width="6" y="84" x="1"></rect>
                        </svg>
                    </div>
                    <div class="truckTires">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="tiresvg">
                            <circle stroke-width="3" stroke="#282828" fill="#282828" r="13.5" cy="15" cx="15"></circle>
                            <circle fill="#DFDFDF" r="7" cy="15" cx="15"></circle>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30" class="tiresvg">
                            <circle stroke-width="3" stroke="#282828" fill="#282828" r="13.5" cy="15" cx="15"></circle>
                            <circle fill="#DFDFDF" r="7" cy="15" cx="15"></circle>
                        </svg>
                    </div>
                    <div class="road"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 453.459 453.459" xml:space="preserve" class="lampPost">
                        <path d="M252.882,0c-37.781,0-68.686,29.953-70.245,67.358h-6.917v8.954c-26.109,2.163-45.463,10.011-45.463,19.366h9.993 c-1.65,5.146-2.507,10.54-2.507,16.017c0,28.956,23.558,52.514,52.514,52.514c28.956,0,52.514-23.558,52.514-52.514 c0-5.478-0.856-10.872-2.506-16.017h9.992c0-9.354-19.354-17.203-45.463-19.366v-8.954h-6.149C200.189,38.779,223.924,16,252.882,16 c29.952,0,54.32,24.368,54.32,54.32c0,28.774-11.078,37.009-25.105,47.437c-17.444,12.968-37.216,27.667-37.216,78.884v113.914 h-7.764c-19.823,0-35.954,16.131-35.954,35.954v29.318c0,5.654,3.392,10.531,8.246,12.728v50.864 c0,8.227,6.671,14.898,14.898,14.898h35.806c8.227,0,14.898-6.671,14.898-14.898v-50.864c4.854-2.197,8.246-7.074,8.246-12.728 v-29.318c0-19.823-16.131-35.954-35.954-35.954h-7.764V196.641c0-43.224,15.071-54.432,30.802-66.124 c14.689-10.919,29.879-22.21,29.879-60.197C300.022,31.519,278.864,0,252.882,0z M232.94,254.589v50.776h-15.86 c-1.39,0-2.612-0.567-3.566-1.434l24.058-50.343C238.815,254.045,236.247,254.589,232.94,254.589z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($loaderTitle); ?></h3>
            <p class="text-gray-300 text-sm leading-relaxed">
                <?= $loaderText; ?>
            </p>
        </div>
    </div>
</div>

<style>
    /* Truck loader - by vinodjangid07 (Uiverse.io) */
    .loader {
        width: fit-content;
        height: fit-content;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .truckWrapper {
        width: 200px;
        height: 100px;
        display: flex;
        flex-direction: column;
        position: relative;
        align-items: center;
        justify-content: flex-end;
        overflow-x: hidden;
    }
    .truckBody {
        width: 130px;
        height: fit-content;
        margin-bottom: 6px;
        animation: motion 1s linear infinite;
    }
    @keyframes motion {
        0% { transform: translateY(0px); }
        50% { transform: translateY(3px); }
        100% { transform: translateY(0px); }
    }
    .truckTires {
        width: 130px;
        height: fit-content;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0px 10px 0px 15px;
        position: absolute;
        bottom: 0;
    }
    .truckTires svg { width: 24px; }
    .road {
        width: 100%;
        height: 1.5px;
        background-color: #282828;
        position: relative;
        bottom: 0;
        align-self: flex-end;
        border-radius: 3px;
    }
    .road::before {
        content: "";
        position: absolute;
        width: 20px;
        height: 100%;
        background-color: #282828;
        right: -50%;
        border-radius: 3px;
        animation: roadAnimation 1.4s linear infinite;
        border-left: 10px solid white;
    }
    .road::after {
        content: "";
        position: absolute;
        width: 10px;
        height: 100%;
        background-color: #282828;
        right: -65%;
        border-radius: 3px;
        animation: roadAnimation 1.4s linear infinite;
        border-left: 4px solid white;
    }
    .lampPost {
        position: absolute;
        bottom: 0;
        right: -90%;
        height: 90px;
        animation: roadAnimation 1.4s linear infinite;
    }
    @keyframes roadAnimation {
        0% { transform: translateX(0px); }
        100% { transform: translateX(-350px); }
    }
</style>

<script>
    (function () {
        if (window.__truckLoaderHelpersInstalled) {
            return;
        }
        window.__truckLoaderHelpersInstalled = true;

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
