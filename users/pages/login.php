<?php
$authError = $_SESSION['error'] ?? null;
$authSuccess = $_SESSION['success'] ?? null;
$activeAuthView = $_SESSION['active_auth_view'] ?? null;
$rememberEmail = $_SESSION['remember_email'] ?? '';

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
                        inputBg: '#E8EAEB',
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
        /* Mencegah scroll horizontal & zoom di HP */
        body {
            overflow-x: hidden;
            touch-action: manipulation;
        }

        /* Utility Transisi Halaman */
        .view-section {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            /* Scrollable jika keyboard muncul */
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
            background-color: #000;
        }

        /* State Sembunyi */
        .hidden-state {
            opacity: 0;
            pointer-events: none;
            z-index: 0;
            transform: scale(0.95);
        }

        /* State Aktif */
        .active-state {
            opacity: 1;
            pointer-events: auto;
            z-index: 10;
            transform: scale(1);
        }

        /* Styling Input */
        .custom-input {
            background-color: #E8EAEB;
            border-radius: 0.75rem;
            /* rounded-xl */
            padding: 1rem 1.25rem;
            width: 100%;
            color: #000;
            font-size: 0.95rem;
            outline: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .custom-input:focus {
            border-color: #A68A56;
            box-shadow: 0 0 0 2px rgba(166, 138, 86, 0.2);
        }
    </style>
</head>

<body class="bg-black text-white font-sans h-[100dvh] w-full relative">

    <section id="view-welcome" class="view-section active-state flex flex-col justify-between">

        <div class="flex-1 flex items-center justify-end px-8 pb-20">
            <div class="text-right max-w-[90%]">
                <p class="font-title text-xl leading-relaxed text-gray-200 italic">
                    "<span class="font-bold text-white not-italic">Komunitas</span> adalah ikatan indah yang menyatukan
                    kita, menciptakan <span class="font-bold text-white">rasa memiliki dan saling mendukung.</span>"
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

            <div class="bg-gold px-6 pt-4 pb-12 text-center min-h-[40vh] flex flex-col justify-center">
                <h1 class="font-title text-3xl font-bold text-black mb-4 leading-tight">
                    Selamat Datang di <br><span class="text-white">Anyeoung Gift</span>
                </h1>

                <p class="text-black/80 text-sm mb-8 px-4 leading-relaxed font-medium">
                    Kami bangga melayani ribuan pelanggan dengan menghadirkan hadiah terbaik untuk setiap momen spesial
                    Anda.
                </p>

                <button onclick="goTo('view-welcome', 'view-register')"
                    class="w-full bg-[#1a1a1a] text-white py-4 rounded-full font-bold text-lg shadow-lg hover:bg-black transition transform active:scale-[0.98]">
                    Mulai Sekarang
                </button>

                <div class="mt-6 text-black font-semibold text-sm">
                    Sudah punya akun?
                    <button onclick="goTo('view-welcome', 'view-login')"
                        class="text-[#8c2a2a] underline decoration-1 underline-offset-2 ml-1 hover:text-red-700">Masuk</button>
                </div>
            </div>
        </div>
    </section>


    <section id="view-register" class="view-section hidden-state bg-black p-6 flex flex-col h-full">
        <div class="pt-4 mb-6">
            <button onclick="goTo('view-register', 'view-welcome')" class="text-gold text-2xl p-2 -ml-2">←</button>
        </div>

        <div class="flex-1 overflow-y-auto pb-8">
            <h2 class="font-title text-3xl font-bold mb-2 text-white">Buat Akun</h2>
            <p class="text-gray-400 text-sm mb-8">Lengkapi data diri Anda di bawah ini.</p>

            <form action="actions/register-process.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-medium mb-2 pl-1 text-gray-300">Nama Lengkap</label>
                    <input type="text" name="name" placeholder="Ketik nama Anda" class="custom-input" required>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-2 pl-1 text-gray-300">Alamat Email</label>
                    <input type="email" name="email" placeholder="contoh@email.com" class="custom-input" required>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-2 pl-1 text-gray-300">Kata Sandi</label>
                    <input type="password" name="password" placeholder="Buat kata sandi" class="custom-input" required
                        minlength="6">
                </div>

                <div class="flex items-center space-x-3 pt-1">
                    <input type="checkbox" id="keep-login" name="remember"
                        class="w-5 h-5 accent-gold rounded border-gray-600 bg-gray-800">
                    <label for="keep-login" class="text-sm text-gray-300 font-medium">Ingat saya</label>
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="w-full bg-gold text-white font-bold py-4 rounded-full text-lg shadow-lg hover:bg-goldDark transition transform active:scale-[0.98]">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-400 mb-6">
                    Sudah punya akun?
                    <button onclick="goTo('view-register', 'view-login')"
                        class="text-gold font-bold hover:text-white underline decoration-gold underline-offset-4">Masuk</button>
                </p>

                <p class="text-[10px] text-gray-600 px-4 leading-normal">
                    Dengan mendaftar, Anda menyetujui <br>
                    <a href="#" class="text-gold/60 hover:text-gold">Syarat & Ketentuan</a> dan <a href="#"
                        class="text-gold/60 hover:text-gold">Kebijakan Privasi</a> kami.
                </p>
            </div>
        </div>
    </section>


    <section id="view-login" class="view-section hidden-state bg-black p-6 flex flex-col h-full">
        <div class="pt-4 mb-6">
            <button onclick="goTo('view-login', 'view-welcome')" class="text-gold text-2xl p-2 -ml-2">←</button>
        </div>

        <div class="flex-1 overflow-y-auto pb-8">
            <h2 class="font-title text-3xl font-bold mb-2 text-white">Selamat Datang</h2>
            <p class="text-gray-400 text-sm mb-8">Senang bertemu Anda kembali!</p>

            <?php if ($authSuccess && $activeAuthView === 'login'): ?>
                <div class="bg-green-600 text-white p-3 rounded-lg mb-4 text-sm">
                    <?= htmlspecialchars($authSuccess); ?>
                </div>
            <?php endif; ?>

            <?php if ($authError && $activeAuthView === 'login'): ?>
                <div class="bg-red-600 text-white p-3 rounded-lg mb-4 text-sm">
                    <?= htmlspecialchars($authError); ?>
                </div>
            <?php endif; ?>

            <form action="actions/login-process.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-medium mb-2 pl-1 text-gray-300">Alamat Email</label>
                    <input type="email" name="email" placeholder="Masukkan email Anda" class="custom-input" required>
                </div>

                <div>
                    <label class="block text-xs font-medium mb-2 pl-1 text-gray-300">Kata Sandi</label>
                    <input type="password" name="password" placeholder="Masukkan kata sandi" class="custom-input" required>
                </div>

                <div class="text-right -mt-2">
                    <a href="#" class="text-red-900/80 text-xs font-medium hover:text-red-500 transition">Lupa Kata
                        Sandi?</a>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-gold text-white font-bold py-4 rounded-full text-lg shadow-lg hover:bg-goldDark transition transform active:scale-[0.98]">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-auto pt-10 text-center text-sm text-gray-400 pb-4">
                Belum punya akun?
                <button onclick="goTo('view-login', 'view-register')"
                    class="text-gold font-bold hover:text-white underline decoration-gold underline-offset-4">Daftar</button>
            </div>
        </div>
    </section>

    <script>
        function goTo(fromId, toId) {
            const fromEl = document.getElementById(fromId);
            const toEl = document.getElementById(toId);

            // Matikan view lama
            fromEl.classList.remove('active-state');
            fromEl.classList.add('hidden-state');

            // Nyalakan view baru dengan jeda sedikit agar smooth
            setTimeout(() => {
                toEl.classList.remove('hidden-state');
                toEl.classList.add('active-state');
            }, 50);
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