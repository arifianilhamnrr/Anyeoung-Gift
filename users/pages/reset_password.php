<?php
$resetError = $_SESSION['reset_password_error'] ?? null;
unset($_SESSION['reset_password_error']);

$token = $_GET['token'] ?? '';
$tokenValid = false;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$tokenHash]);
    $tokenValid = (bool) $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Anyeong Gift</title>
    <link rel="icon" type="image/svg+xml" href="../assets/images/anyeong-logo.svg">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-title {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body class="bg-darkbg text-white min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-dark-surface border border-dark-border rounded-2xl p-6 md:p-8 shadow-2xl">
        <h1 class="font-title text-3xl font-bold mb-2">Reset Password</h1>
        <p class="text-gray-400 text-sm mb-6">Buat password baru untuk akun kamu.</p>

        <?php if ($resetError): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($resetError); ?>
            </div>
        <?php endif; ?>

        <?php if (!$tokenValid): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-xl mb-5 text-sm">
                Link reset tidak valid atau sudah kadaluarsa. Silakan minta link baru.
            </div>
            <a href="index.php?page=forgot_password"
                class="w-full inline-flex justify-center bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                Minta Link Baru
            </a>
        <?php else: ?>
            <form action="actions/reset-password.php" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <div>
                    <label class="block text-xs font-medium mb-2 text-gray-400 uppercase tracking-wider">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="new_password" required minlength="6"
                            class="w-full p-3 pr-12 bg-dark-base border border-dark-border rounded-xl text-sm text-gray-200 focus:border-gold-500 focus:ring-1 outline-none transition">
                        <button type="button" onclick="toggleResetPwd('new_password', this)"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:text-gold-500 hover:bg-gold-500/10 transition"
                            aria-label="Tampilkan password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-2 text-gray-400 uppercase tracking-wider">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                            class="w-full p-3 pr-12 bg-dark-base border border-dark-border rounded-xl text-sm text-gray-200 focus:border-gold-500 focus:ring-1 outline-none transition">
                        <button type="button" onclick="toggleResetPwd('confirm_password', this)"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:text-gold-500 hover:bg-gold-500/10 transition"
                            aria-label="Tampilkan password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                    Simpan Password Baru
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-6 text-center text-xs text-gray-400">
            <a href="index.php?page=login" class="text-gold-500 font-semibold hover:text-gold-400 underline underline-offset-4">Kembali ke Login</a>
        </div>
    </div>

    <script>
        // Toggle show/hide password untuk form reset password.
        function toggleResetPwd(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
            btn.innerHTML = showing
                ? '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" /></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a3 3 0 104.24 4.24M9.88 5.09A10.94 10.94 0 0112 5c6.5 0 10.5 7 10.5 7a17.43 17.43 0 01-3.32 4.16M6.1 6.1A17.55 17.55 0 001.5 12s4 7 10.5 7c1.6 0 3.07-.32 4.39-.85" /></svg>';
        }
    </script>
</body>

</html>
