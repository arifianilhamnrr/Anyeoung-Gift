<?php
$authError = $_SESSION['error'] ?? null;
$authSuccess = $_SESSION['success'] ?? null;
$activeAuthView = $_SESSION['active_auth_view'] ?? null;

unset($_SESSION['error'], $_SESSION['success'], $_SESSION['active_auth_view']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Anyeoung Gift - Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#A68A56',
                        goldDark: '#8c7345',
                        darkbg: '#000000'
                    },
                    fontFamily: {
                        title: ['"Playfair Display"', 'serif'],
                        sans: ['"Inter"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            overflow-x: hidden;
            touch-action: manipulation;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(166, 138, 86, 0.3);
            border-radius: 10px;
        }

        .view-section {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            transition: opacity 0.5s ease-in-out, transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #000000;
        }

        .hidden-state {
            opacity: 0;
            pointer-events: none;
            z-index: 0;
            transform: scale(0.95) translateY(10px);
        }

        .active-state {
            opacity: 1;
            pointer-events: auto;
            z-index: 10;
            transform: scale(1) translateY(0);
        }

        .custom-input {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            width: 100%;
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .custom-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .custom-input:focus {
            border-color: #A68A56;
            background-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(166, 138, 86, 0.15);
        }
    </style>
</head>

<body class="bg-darkbg text-white font-sans h-[100dvh] w-full relative overflow-hidden">

    <section id="view-welcome" class="view-section active-state flex flex-col justify-between">
        <div class="flex-1 flex items-center justify-center px-6 pb-20">
            <div class="text-right w-full max-w-md mt-10">
                <p class="font-title text-xl md:text-3xl leading-relaxed text-gray-200 italic">
                    "<span class="font-bold text-white not-italic">Komunitas</span> adalah ikatan indah yang menyatukan
                    kita, menciptakan <span class="font-bold text-gold">rasa memiliki dan saling mendukung.</span>"
                </p>
                <p class="mt-4 text-[10px] tracking-[0.25em] text-gray-500 uppercase font-bold">
                    ANYEOUNG GIFT
                </p>
            </div>
        </div>

        <div class="relative w-full">
            <div class="absolute -top-10 left-0 w-full overflow-hidden leading-[0]">
                <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"
                    preserveAspectRatio="none" class="relative block w-full h-[50px]">
                    <path d="M0,60 C400,0 800,0 1200,60 L1200,120 L0,120 Z" fill="#A68A56"></path>
                </svg>
            </div>

            <div class="bg-gold px-6 pt-4 pb-12 min-h-[40vh] flex flex-col justify-center items-center relative z-10">
                <div class="w-full max-w-md text-center">
                    <h1 class="font-title text-3xl md:text-4xl font-bold text-black mb-4 leading-tight">
                        Selamat Datang di <br><span class="text-white drop-shadow-md">Anyeoung Gift</span>
                    </h1>

                    <p class="text-black/80 text-sm mb-8 px-4 leading-relaxed font-medium">
                        Kami bangga melayani ribuan pelanggan dengan menghadirkan hadiah terbaik untuk setiap momen
                        spesial Anda.
                    </p>

                    <button onclick="goTo('view-welcome', 'view-register')"
                        class="w-full bg-[#1a1a1a] text-white py-4 rounded-full font-bold text-lg shadow-xl hover:bg-black transition transform hover:scale-[1.02] active:scale-[0.98]">
                        Mulai Sekarang
                    </button>

                    <div class="mt-6 text-black font-semibold text-sm">
                        Sudah punya akun?
                        <button onclick="goTo('view-welcome', 'view-login')"
                            class="text-[#6b1e1e] underline decoration-1 underline-offset-2 ml-1 hover:text-red-900 transition-colors">Masuk</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="view-register" class="view-section hidden-state p-6 flex flex-col items-center">
        <div class="w-full max-w-md h-full flex flex-col">
            <div class="pt-4 mb-6 relative z-10 w-full">
                <button onclick="goTo('view-register', 'view-welcome')"
                    class="text-gold text-2xl p-2 -ml-2 hover:bg-white/5 rounded-full transition-colors w-10 h-10 flex items-center justify-center">←</button>
            </div>

            <div class="flex-1 pb-8 flex flex-col relative z-10 w-full">
                <h2 class="font-title text-3xl md:text-4xl font-bold mb-2 text-white">Buat Akun</h2>
                <p class="text-gray-400 text-sm mb-8">Lengkapi data diri Anda di bawah ini.</p>

                <form action="actions/register-process.php" method="POST" class="space-y-5 flex-1 flex flex-col">
                    <div>
                        <label class="block text-xs font-medium mb-2 pl-1 text-gray-400 uppercase tracking-wider">Nama
                            Lengkap</label>
                        <input type="text" name="name" placeholder="Ketik nama Anda" class="custom-input" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2 pl-1 text-gray-400 uppercase tracking-wider">Alamat
                            Email</label>
                        <input type="email" name="email" placeholder="contoh@email.com" class="custom-input" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2 pl-1 text-gray-400 uppercase tracking-wider">Kata
                            Sandi</label>
                        <input type="password" name="password" placeholder="Buat kata sandi" class="custom-input"
                            required minlength="6">
                    </div>

                    <div class="mt-auto pt-8">
                        <button type="submit"
                            class="w-full bg-gold text-black font-bold py-4 rounded-full text-lg shadow-[0_0_15px_rgba(166,138,86,0.3)] hover:bg-goldDark hover:text-white transition transform hover:scale-[1.02] active:scale-[0.98]">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-400 mb-6">
                        Sudah punya akun?
                        <button onclick="goTo('view-register', 'view-login')"
                            class="text-gold font-bold hover:text-white underline decoration-gold underline-offset-4 transition-colors">Masuk</button>
                    </p>

                    <p class="text-[11px] text-gray-500 px-4 leading-normal">
                        Dengan mendaftar, Anda menyetujui <br>
                        <button onclick="openModal('snkModal', 'snkModalContent')"
                            class="text-gold/80 hover:text-gold transition-colors underline underline-offset-2">Syarat &
                            Ketentuan</button> dan
                        <button onclick="openModal('privacyModal', 'privacyModalContent')"
                            class="text-gold/80 hover:text-gold transition-colors underline underline-offset-2">Kebijakan
                            Privasi</button> kami.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="view-login" class="view-section hidden-state p-6 flex flex-col items-center">
        <div class="w-full max-w-md h-full flex flex-col">
            <div class="pt-4 mb-6 relative z-10 w-full">
                <button onclick="goTo('view-login', 'view-welcome')"
                    class="text-gold text-2xl p-2 -ml-2 hover:bg-white/5 rounded-full transition-colors w-10 h-10 flex items-center justify-center">←</button>
            </div>

            <div class="flex-1 pb-8 flex flex-col relative z-10 w-full">
                <h2 class="font-title text-3xl md:text-4xl font-bold mb-2 text-white">Selamat Datang</h2>
                <p class="text-gray-400 text-sm mb-8">Senang bertemu Anda kembali!</p>

                <?php if ($authSuccess && $activeAuthView === 'login'): ?>
                    <div
                        class="bg-green-500/20 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 text-sm backdrop-blur-sm">
                        <?= htmlspecialchars($authSuccess); ?>
                    </div>
                <?php endif; ?>

                <?php if ($authError && $activeAuthView === 'login'): ?>
                    <div
                        class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 text-sm backdrop-blur-sm">
                        <?= htmlspecialchars($authError); ?>
                    </div>
                <?php endif; ?>

                <form action="actions/login-process.php" method="POST" class="space-y-6 flex-1 flex flex-col">
                    <div>
                        <label class="block text-xs font-medium mb-2 pl-1 text-gray-400 uppercase tracking-wider">Alamat
                            Email</label>
                        <input type="email" name="email" placeholder="Masukkan email Anda" class="custom-input"
                            required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2 pl-1 text-gray-400 uppercase tracking-wider">Kata
                            Sandi</label>
                        <input type="password" name="password" placeholder="Masukkan kata sandi" class="custom-input"
                            required>
                        <div class="text-right mt-3">
                            <a href="#" class="text-gold/70 text-xs font-medium hover:text-gold transition-colors">Lupa
                                Kata Sandi?</a>
                        </div>
                    </div>

                    <div class="mt-auto pt-8">
                        <button type="submit"
                            class="w-full bg-gold text-black font-bold py-4 rounded-full text-lg shadow-[0_0_15px_rgba(166,138,86,0.3)] hover:bg-goldDark hover:text-white transition transform hover:scale-[1.02] active:scale-[0.98]">
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center text-sm text-gray-400 pb-2">
                    Belum punya akun?
                    <button onclick="goTo('view-login', 'view-register')"
                        class="text-gold font-bold hover:text-white underline decoration-gold underline-offset-4 transition-colors">Daftar</button>
                </div>
            </div>
        </div>
    </section>

    <div id="snkModal"
        class="fixed inset-0 z-[110] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('snkModal', 'snkModalContent')">
        </div>
        <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[80vh]"
            id="snkModalContent">
            <div class="p-6 border-b border-white/10 flex justify-between items-center shrink-0">
                <h3 class="text-xl font-title text-gold">Syarat & Ketentuan</h3>
                <button type="button" onclick="closeModal('snkModal', 'snkModalContent')"
                    class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar space-y-4 text-sm text-gray-300 flex-1 leading-relaxed">
                <p><strong class="text-white">1. Ketentuan Umum</strong><br>Dengan mendaftar dan menggunakan layanan
                    Anyeoung Gift, Anda setuju untuk mematuhi semua syarat dan ketentuan yang berlaku.</p>
                <p><strong class="text-white">2. Pemesanan</strong><br>Setiap pesanan akan diproses apabila proses
                    *checkout* dan validasi pembayaran telah berhasil dilakukan.</p>
                <p><strong class="text-white">3. Pembayaran</strong><br>Pengguna wajib mengunggah bukti pembayaran
                    sesuai nominal tagihan paling lambat 1x24 jam. Jika melewati batas waktu, pesanan dapat dibatalkan
                    secara otomatis.</p>
                <p><strong class="text-white">4. Pengambilan Barang</strong><br>Pesanan hanya dapat diambil di toko
                    fisik kami ketika status pesanan telah berubah menjadi "Siap Diambil". Pembeli wajib menunjukkan
                    nomor tagihan kepada admin toko.</p>
                <p><strong class="text-white">5. Pembatalan</strong><br>Pesanan yang sudah dibayar dan sedang diproses
                    tidak dapat dibatalkan atau di-refund secara sepihak tanpa persetujuan admin.</p>
            </div>
            <div class="p-6 border-t border-white/10 shrink-0">
                <button type="button" onclick="closeModal('snkModal', 'snkModalContent')"
                    class="w-full bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 transition-colors">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <div id="privacyModal"
        class="fixed inset-0 z-[110] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            onclick="closeModal('privacyModal', 'privacyModalContent')"></div>
        <div class="relative bg-white/10 backdrop-blur-2xl border border-white/20 shadow-2xl rounded-[2rem] w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[80vh]"
            id="privacyModalContent">
            <div class="p-6 border-b border-white/10 flex justify-between items-center shrink-0">
                <h3 class="text-xl font-title text-gold">Kebijakan Privasi</h3>
                <button type="button" onclick="closeModal('privacyModal', 'privacyModalContent')"
                    class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar space-y-4 text-sm text-gray-300 flex-1 leading-relaxed">
                <p>Kami di Anyeoung Gift sangat menghargai privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana
                    kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.</p>
                <p><strong class="text-white">Pengumpulan Data</strong><br>Kami hanya mengumpulkan informasi yang Anda
                    berikan saat mendaftar, seperti nama, email, dan nomor WhatsApp. Kami juga menyimpan riwayat pesanan
                    Anda untuk keperluan konfirmasi.</p>
                <p><strong class="text-white">Penggunaan Data</strong><br>Data pribadi Anda akan digunakan sepenuhnya
                    untuk memproses pesanan, memverifikasi identitas, dan berkomunikasi mengenai status pembelian Anda.
                </p>
                <p><strong class="text-white">Keamanan Data</strong><br>Kami berkomitmen untuk melindungi data pribadi
                    Anda menggunakan standar keamanan yang layak dan kami tidak akan menjual maupun menyewakan data Anda
                    kepada pihak ketiga.</p>
            </div>
            <div class="p-6 border-t border-white/10 shrink-0">
                <button type="button" onclick="closeModal('privacyModal', 'privacyModalContent')"
                    class="w-full bg-gold text-black py-3.5 rounded-xl font-bold hover:bg-yellow-400 transition-colors">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Swipe View Login/Register/Welcome
        function goTo(fromId, toId) {
            const fromEl = document.getElementById(fromId);
            const toEl = document.getElementById(toId);

            fromEl.classList.remove('active-state');
            fromEl.classList.add('hidden-state');

            setTimeout(() => {
                toEl.classList.remove('hidden-state');
                toEl.classList.add('active-state');
            }, 50);
        }

        // Fungsi Buka/Tutup Modal Universal
        function openModal(modalId, contentId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(contentId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function closeModal(modalId, contentId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(contentId);
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    </script>

    <?php if ($activeAuthView === 'register'): ?>
        <script>
            window.addEventListener('load', function () {
                goTo('view-welcome', 'view-register');
            });
        </script>
    <?php elseif ($activeAuthView === 'login'): ?>
        <script>
            window.addEventListener('load', function () {
                goTo('view-welcome', 'view-login');
            });
        </script>
    <?php endif; ?>
</body>

</html>