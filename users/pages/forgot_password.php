<?php
$resetError = $_SESSION['password_reset_error'] ?? null;
$resetSuccess = $_SESSION['password_reset_success'] ?? null;
unset($_SESSION['password_reset_error'], $_SESSION['password_reset_success']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Anyeong Gift</title>
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
        <h1 class="font-title text-3xl font-bold mb-2">Lupa Password</h1>
        <p class="text-gray-400 text-sm mb-6">Masukkan email akun kamu untuk menerima link reset password.</p>

        <?php if ($resetSuccess): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-400 p-4 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($resetSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if ($resetError): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($resetError); ?>
            </div>
        <?php endif; ?>

        <form action="actions/request-password-reset.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-medium mb-2 text-gray-400 uppercase tracking-wider">Alamat Email</label>
                <input type="email" name="email" required placeholder="email@gmail.com"
                    class="w-full p-3 bg-dark-base border border-dark-border rounded-xl text-sm text-gray-200 focus:border-gold-500 focus:ring-1 outline-none transition">
            </div>
            <button type="submit"
                class="w-full bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                Kirim Link Reset
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-400">
            Sudah ingat password?
            <a href="index.php?page=login" class="text-gold-500 font-semibold hover:text-gold-400 underline underline-offset-4">Masuk</a>
        </div>
    </div>
</body>

</html>
