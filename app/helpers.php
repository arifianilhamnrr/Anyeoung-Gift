<?php
/**
 * Helper global yang bisa dipanggil dari mana saja (admin / user / email
 * templates). Diload otomatis lewat config.php.
 *
 * Fungsi utama di sini:
 *   storeSettings($pdo = null)  -> array row store_settings (cached per-request).
 *   storeName($pdo = null, $fallback = 'Anyeong Gift') -> nama toko.
 *
 * Dipakai untuk menghilangkan hardcoded "Anyeong Gift" di seluruh tampilan
 * dan menampilkan nama dari halaman pengaturan toko.
 */

// Pastikan seluruh aplikasi (admin & user) memakai zona waktu Asia/Jakarta.
// Admin sudah men-set ini lewat config.php, tapi sisi user hanya memuat
// config/database.php sehingga PHP date() bisa jatuh ke zona default server
// (biasanya UTC). Pakai konstanta TIMEZONE bila sudah didefinisikan supaya
// tetap satu sumber kebenaran.
if (function_exists('date_default_timezone_set')) {
    $appTimezone = defined('TIMEZONE') ? TIMEZONE : 'Asia/Jakarta';
    date_default_timezone_set($appTimezone);
}

if (!function_exists('storeSettings')) {
    /**
     * Ambil baris store_settings (di-cache di static var). Mengembalikan
     * array atau [] kalau tabel/kolom belum ada.
     *
     * @param \PDO|null $pdo  PDO instance opsional. Kalau null, fungsi
     *                        mencoba pakai $GLOBALS['pdo'] atau membuat
     *                        instance baru dari konstanta DB_*.
     */
    function storeSettings(?\PDO $pdo = null): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        try {
            if ($pdo === null) {
                if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof \PDO) {
                    $pdo = $GLOBALS['pdo'];
                } elseif (defined('DB_HOST') && defined('DB_NAME')) {
                    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
                    $user = defined('DB_USER') ? DB_USER : 'root';
                    $pass = defined('DB_PASS') ? DB_PASS : '';
                    $pdo = new \PDO($dsn, $user, $pass, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    ]);
                }
            }
            if ($pdo) {
                $stmt = $pdo->query('SELECT * FROM store_settings LIMIT 1');
                $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
                $cache = $row ?: [];
                return $cache;
            }
        } catch (\Throwable $e) {
            // Diam: jangan ganggu render halaman kalau DB lagi rusak.
        }
        $cache = [];
        return $cache;
    }
}

if (!function_exists('storeName')) {
    /**
     * Nama toko yang aman untuk di-render di HTML. Sudah di-html-escape.
     * Pakai storeNameRaw() kalau perlu nilai mentahnya (mis. judul WA).
     */
    function storeName(?\PDO $pdo = null, string $fallback = 'Anyeong Gift'): string
    {
        return htmlspecialchars(storeNameRaw($pdo, $fallback), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('storeNameRaw')) {
    function storeNameRaw(?\PDO $pdo = null, string $fallback = 'Anyeong Gift'): string
    {
        $settings = storeSettings($pdo);
        $name = trim((string) ($settings['store_name'] ?? ''));
        return $name !== '' ? $name : $fallback;
    }
}
