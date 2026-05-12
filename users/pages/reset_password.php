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
                    <input type="password" name="new_password" required minlength="6"
                        class="w-full p-3 bg-dark-base border border-dark-border rounded-xl text-sm text-gray-200 focus:border-gold-500 focus:ring-1 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-2 text-gray-400 uppercase tracking-wider">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="w-full p-3 bg-dark-base border border-dark-border rounded-xl text-sm text-gray-200 focus:border-gold-500 focus:ring-1 outline-none transition">
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
</body>

</html>
