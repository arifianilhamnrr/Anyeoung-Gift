<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Anyeong Gift</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' },
                        dark: { base: '#121212', surface: '#1E1E1E', border: '#333333' }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="bg-dark-base text-gray-200 flex justify-center items-center h-screen antialiased selection:bg-gold-500 selection:text-gray-900 px-4">

    <div
        class="bg-dark-surface w-full max-w-sm p-8 md:p-10 rounded-2xl border border-dark-border shadow-2xl text-center relative overflow-hidden">

        <div
            class="absolute top-0 right-0 -mr-16 -mt-16 w-32 h-32 rounded-full bg-gold-500/10 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 rounded-full bg-gold-500/10 blur-3xl pointer-events-none">
        </div>

        <div class="relative z-10">
            <div class="text-4xl mb-2">🎁</div>
            <h2 class="text-2xl font-bold text-gray-100 mb-1">Anyeong Admin</h2>
            <p class="text-gray-400 text-sm mb-8">Silakan masuk untuk mengelola toko.</p>

            <form id="loginForm" onsubmit="handleLogin(event)" class="text-left space-y-4">

                <div id="error-msg" style="display: none;"
                    class="text-red-500 text-sm font-medium text-center bg-red-500/10 py-2.5 rounded-lg border border-red-500/20">
                </div>

                <div>
                    <input type="email" id="email" required placeholder="Email Admin"
                        class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition duration-300 placeholder-gray-500">
                </div>

                <div>
                    <input type="password" id="password" required placeholder="Password"
                        class="w-full p-3.5 bg-dark-base border border-dark-border text-gray-200 rounded-xl text-sm focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition duration-300 placeholder-gray-500">
                </div>

                <button type="submit" id="btn-submit"
                    class="w-full bg-gold-500 text-gray-900 font-bold text-base py-3.5 rounded-xl hover:bg-gold-400 hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition-all duration-300 mt-2">
                    Masuk Dashboard
                </button>
            </form>
        </div>
    </div>

    <script>
        async function handleLogin(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit');
            const errorMsg = document.getElementById('error-msg');

            btn.innerText = 'Memeriksa...';
            btn.disabled = true;
            errorMsg.style.display = 'none';

            try {
                // Tembak API Login
                const response = await fetch('/anyeong-gift/public/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Jika sukses, lempar ke dashboard admin
                    window.location.href = '/anyeong-gift/public/admin';
                } else {
                    errorMsg.innerText = result.message;
                    errorMsg.style.display = 'block';
                }
            } catch (error) {
                errorMsg.innerText = "Terjadi kesalahan jaringan.";
                errorMsg.style.display = 'block';
            } finally {
                btn.innerText = 'Masuk Dashboard';
                btn.disabled = false;
            }
        }
    </script>
</body>

</html>