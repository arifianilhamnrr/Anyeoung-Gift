<?php
// Halaman verifikasi OTP setelah user submit form pendaftaran. User harus
// memasukkan kode 6 digit yang dikirim ke email-nya untuk menyelesaikan
// pembuatan akun.

$pendingEmail = $_SESSION['pending_register_email'] ?? null;

// Kalau user buka halaman ini tapi tidak ada pendaftaran tertunda, kembalikan
// ke halaman login.
if (!$pendingEmail) {
    if (!headers_sent()) {
        header('Location: index.php?page=login');
        exit;
    }
}

$otpError = $_SESSION['otp_error'] ?? null;
$otpSuccess = $_SESSION['otp_success'] ?? null;
unset($_SESSION['otp_error'], $_SESSION['otp_success']);

$maskedEmail = $pendingEmail;
if ($pendingEmail && strpos($pendingEmail, '@') !== false) {
    [$local, $domain] = explode('@', $pendingEmail, 2);
    $localMasked = strlen($local) <= 2
        ? $local[0] . '***'
        : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));
    $maskedEmail = $localMasked . '@' . $domain;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Anyeong Gift</title>
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

        .otp-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            background-color: #121212;
            border: 1px solid #333333;
            color: #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            caret-color: #F59E0B;
        }

        .otp-input:focus {
            border-color: #F59E0B;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }
    </style>
</head>

<body class="bg-darkbg text-white min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-dark-surface border border-dark-border rounded-2xl p-6 md:p-8 shadow-2xl">
        <h1 class="font-title text-3xl font-bold mb-2">Verifikasi Akun</h1>
        <p class="text-gray-400 text-sm mb-6">
            Kami sudah mengirim kode OTP 6 digit ke
            <span class="text-gold-400 font-semibold"><?= htmlspecialchars($maskedEmail ?? ''); ?></span>.
            Masukkan kode di bawah untuk menyelesaikan pendaftaran.
        </p>

        <?php if ($otpSuccess): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-400 p-4 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($otpSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if ($otpError): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-xl mb-5 text-sm">
                <?= htmlspecialchars($otpError); ?>
            </div>
        <?php endif; ?>

        <form id="otpForm" action="actions/verify-otp.php" method="POST" class="space-y-5">
            <div class="flex justify-between gap-2" id="otpFields">
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="0" required>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="1" required>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="2" required>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="3" required>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="4" required>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-otp-index="5" required>
            </div>
            <input type="hidden" name="otp" id="otpValue">

            <button type="submit"
                class="w-full bg-gold-500 text-gray-900 font-bold py-3 rounded-xl hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(245,158,11,0.3)] transition">
                Verifikasi
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-400">
            Belum dapat email?
            <form action="actions/resend-otp.php" method="POST" class="inline">
                <button type="submit"
                    class="text-gold-400 font-semibold hover:text-gold-500 underline underline-offset-4">
                    Kirim ulang kode
                </button>
            </form>
        </div>

        <div class="mt-4 text-center text-xs text-gray-500">
            Salah email?
            <a href="index.php?page=login"
                class="text-gold-500 font-semibold hover:text-gold-400 underline underline-offset-4">
                Kembali ke pendaftaran
            </a>
        </div>
    </div>

    <script>
        // Input OTP: pindah fokus otomatis, paste 6 digit langsung sebar ke
        // tiap field, backspace mundur ke field sebelumnya.
        const fields = document.querySelectorAll('.otp-input');
        const hidden = document.getElementById('otpValue');
        const form = document.getElementById('otpForm');

        function syncHidden() {
            hidden.value = Array.from(fields).map(f => f.value).join('');
        }

        fields.forEach((field, idx) => {
            field.addEventListener('input', (e) => {
                const v = e.target.value.replace(/\D/g, '');
                e.target.value = v.slice(-1);
                if (e.target.value && idx < fields.length - 1) {
                    fields[idx + 1].focus();
                }
                syncHidden();
            });

            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                    fields[idx - 1].focus();
                }
            });

            field.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const digits = text.replace(/\D/g, '').slice(0, fields.length);
                digits.split('').forEach((d, i) => {
                    if (fields[i]) fields[i].value = d;
                });
                syncHidden();
                if (fields[digits.length]) fields[digits.length].focus();
                else fields[fields.length - 1].blur();
            });
        });

        // Auto-focus ke input pertama saat halaman dibuka.
        if (fields[0]) fields[0].focus();

        form.addEventListener('submit', (e) => {
            syncHidden();
            if ((hidden.value || '').length !== fields.length) {
                e.preventDefault();
                alert('Masukkan 6 digit kode OTP.');
            }
        });
    </script>
</body>

</html>
