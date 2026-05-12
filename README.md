# Anyeong Gift

Aplikasi web toko hadiah / bouquet / hampers dengan dua sisi: **storefront pelanggan** dan **dashboard admin**. Dibangun dengan PHP murni (pola MVC ringan), MySQL/MariaDB, dan TailwindCSS v4.

> **Demo lokal:** `http://localhost/anyeong-gift/public` (admin) dan `http://localhost/anyeong-gift/users` (storefront)

---

## Daftar Isi

- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Struktur Folder](#struktur-folder)
- [Setup Lokal (XAMPP / Laragon)](#setup-lokal-xampp--laragon)
- [Login Default](#login-default)
- [Cara Pemakaian](#cara-pemakaian)
- [Setup untuk Hosting (Production)](#setup-untuk-hosting-production)
- [Konfigurasi Penting](#konfigurasi-penting)
- [FAQ / Troubleshooting](#faq--troubleshooting)

---

## Fitur

### Sisi Pelanggan (`/users`)

- **Katalog produk** dengan kategori (bouquet, hampers, dsb.) dan filter pencarian real-time.
- **Tipe produk fleksibel**: harga tetap (`simple`), bisa dikustomisasi (`semi_custom` / `custom_full`), khusus uang (`custom_money`), atau konsultasi dulu (`chat_only` → langsung WhatsApp admin).
- **Opsi produk** (warna, model, tulisan pita, dll.) termasuk input custom dari pembeli (mis. teks pita).
- **Keranjang & Bayar Sekarang**: tombol "Bayar Sekarang" di product detail melakukan checkout cuma untuk produk tersebut tanpa mengganggu isi keranjang utama.
- **Sticky checkout bar** di mobile untuk halaman keranjang, checkout, dan product detail.
- **Login & Register** dengan password bcrypt (`password_hash` / `password_verify`).
- **Manajemen alamat** pengiriman (multiple address).
- **Checkout** dengan pemilihan alamat + metode pembayaran (transfer, QRIS, e-wallet, atau bayar di toko / COD).
- **Auto-redirect** ke halaman upload pembayaran setelah checkout (untuk metode non-COD).
- **Upload bukti pembayaran**:
  - Transfer / e-wallet: tampil detail rekening + form upload bukti transfer.
  - **QRIS**: tampil gambar QRIS yang sudah diunggah admin → tinggal di-scan.
  - COD: tidak perlu upload bukti.
- **Status pesanan** yang mudah dibaca: *Belum Bayar / Konfirmasi / Diproses / Pesanan Siap / Selesai / Dibatalkan*.
- **Selesaikan pesanan** sendiri dari user side saat status `Pesanan Siap`.
- **Pagination** di riwayat pesanan (5 pesanan/halaman).
- **Hubungi Admin** lewat tombol WhatsApp di tiap pesanan + di halaman upload pembayaran.

### Sisi Admin (`/public`)

- **Login admin** terpisah (akun user dengan `role='admin'`). Mendukung legacy SHA-256 dengan auto-rehash ke bcrypt saat login berhasil.
- **Dashboard** dengan ringkasan pesanan, produk, omzet.
- **Manajemen produk**: CRUD + opsi/variasi + multiple images + toggle aktif/nonaktif. Filter kategori + pagination 10/halaman.
- **Manajemen pesanan**: detail item (termasuk add-on & teks custom pita), kontak pelanggan, tombol **Chat WhatsApp** dengan template pesan otomatis per status. Filter status + pagination 10/halaman.
- **Aksi cepat pesanan** (adaptif per status):
  - COD pending → **Konfirmasi Pesanan** (jadi `paid`).
  - Online pending → **Konfirmasi Pembayaran** (verifikasi bukti).
  - `paid` → **Tandai Pesanan Siap** (`ready_pickup`).
  - `ready_pickup` → **Tandai Selesai** (`completed`).
  - Selain `completed`/`cancelled` → **Batalkan Pesanan**.
- **Cetak invoice** pesanan langsung dari modal detail (auto-print, siap di-print sebagai PDF juga).
- **Manajemen metode pembayaran**: tambah/edit metode (transfer, QRIS, e-wallet, onsite) + **upload gambar QRIS** dengan preview. Gambar lama dipertahankan saat update tanpa upload baru.
- **Pengaturan toko**: nama toko, nomor WhatsApp admin, template pesan chat untuk produk `chat_only`.
- **Sticky modal headers** + kebab dropdown action di tabel agar UI lebih rapi.

### Template WhatsApp Otomatis

Saat admin klik tombol "Chat WhatsApp" di detail pesanan, teks pesan yang di-prefill di WhatsApp menyesuaikan status pesanan:

| Status | Template Pesan |
|---|---|
| `waiting_payment` / `pending` | Pengingat untuk segera melakukan pembayaran. |
| `paid` | Konfirmasi pembayaran diterima + sedang disiapkan. |
| `ready_pickup` | Pesanan siap diambil di toko. |
| `completed` | Ucapan terima kasih. |
| `cancelled` | Pemberitahuan pembatalan. |

Placeholder `{nama}` dan `{kode}` akan diganti otomatis (mis. `{kode}` jadi `#AG-0042`).

---

## Tech Stack

- **Backend:** PHP 8.x (vanilla, MVC ringan buatan sendiri di `app/Core/`).
- **Database:** MySQL 8 / MariaDB 10.x.
- **Frontend:** HTML + TailwindCSS v4 (via `@tailwindcss/cli`) + Vanilla JS (fetch API).
- **Target deployment:** XAMPP / Laragon (Windows/Linux) lokal, atau shared hosting / VPS dengan Apache + PHP + MySQL.

---

## Struktur Folder

```
Anyeoung-Gift/
├── app/                       # Backend MVC (dipakai sisi admin)
│   ├── Core/                  # Router, Controller, Model base classes
│   ├── Controllers/           # AuthController, DashboardController, OrderController, dst.
│   ├── Models/                # UserModel, ProductModel, OrderModel, PaymentMethodModel, ...
│   └── Services/              # Layer service tambahan (kalau ada)
├── assets/
│   ├── css/                   # input.css (sumber Tailwind) + main.css (hasil build)
│   └── images/                # Logo, dsb.
├── config.php                 # Kredensial DB + BASE_URL (sisi admin)
├── config/database.php        # Kredensial DB (sisi user, pakai PDO)
├── database/
│   └── anyeoung_gift_backup.sql   # Dump schema + data contoh
├── public/                    # Entry point ADMIN (http://localhost/anyeong-gift/public)
│   ├── index.php              # Front controller admin (router)
│   ├── assets/                # CSS/JS hasil compile
│   └── uploads/
│       ├── products/          # Foto produk
│       ├── payments/          # Bukti pembayaran user
│       └── payment_methods/   # Gambar QRIS dari admin
├── users/                     # Entry point USER (http://localhost/anyeong-gift/users)
│   ├── index.php              # Front controller user (?page=...)
│   ├── actions/               # Handler form (login, register, add-to-cart, checkout, ...)
│   ├── components/            # navbar, header, footer
│   └── pages/                 # home, product, cart, checkout, orders, payment_upload, ...
├── views/
│   ├── admin/                 # Layout + partial dashboard admin
│   └── auth/                  # Halaman login admin
├── node_modules/              # Dependencies Tailwind (jangan di-commit)
└── package.json
```

---

## Setup Lokal (XAMPP / Laragon)

### 1. Persiapan

Pastikan sudah terpasang:

- **XAMPP** (Windows/Linux/macOS) atau **Laragon** (Windows). Yang penting tersedia: Apache, PHP ≥ 8.0, MySQL/MariaDB.
- **Node.js 18+** (cuma kalau mau mengubah tampilan & rebuild Tailwind).
- **Git**.

### 2. Clone Repo ke Folder Web Server

```bash
# XAMPP
cd C:\xampp\htdocs              # Windows
cd /opt/lampp/htdocs            # Linux

# Laragon
cd C:\laragon\www

git clone https://github.com/arifianilhamnrr/Anyeoung-Gift.git anyeong-gift
cd anyeong-gift
```

> **Penting:** nama folder harus persis `anyeong-gift` karena routing admin & beberapa URL JS-nya di-hardcode ke path itu (`/anyeong-gift/public`). Kalau ingin pakai nama lain, lihat bagian [Konfigurasi Penting](#konfigurasi-penting).

### 3. Import Database

1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Buat database baru dengan nama **`anyeoung_gift`** (collation: `utf8mb4_general_ci`).
3. Pilih database tersebut → tab **Import** → upload file `database/anyeoung_gift_backup.sql` → klik **Go**.

Alternatif via CLI:

```bash
mysql -u root anyeoung_gift < database/anyeoung_gift_backup.sql
```

### 4. (Opsional) Install & Build Tailwind

CSS hasil build sudah ikut di-commit di `assets/css/main.css`, jadi tidak perlu Node.js untuk sekadar menjalankan aplikasi.

Kalau ingin mengubah style, install dependency dan rebuild:

```bash
npm install
npx tailwindcss -i ./assets/css/input.css -o ./assets/css/main.css --minify
```

Untuk mode watch saat development:

```bash
npx tailwindcss -i ./assets/css/input.css -o ./assets/css/main.css --watch
```

### 5. Jalankan

- Start Apache + MySQL via panel XAMPP/Laragon.
- Buka admin: <http://localhost/anyeong-gift/public>
- Buka storefront: <http://localhost/anyeong-gift/users>

---

## Login Default

Setelah import dump database, tersedia akun:

| Role | Email | Password |
|---|---|---|
| Admin | `super@anyeong.com` | `admin123` *(lihat catatan di bawah)* |
| User | `arifianilhamnurriandana@gmail.com` | *(unknown, bcrypt hash)* |

> Password admin di dump masih SHA-256 lama. Sistem akan otomatis meng-upgrade ke bcrypt setelah login pertama berhasil. Jika kamu lupa, set ulang dengan SQL:
>
> ```sql
> -- Ganti dengan password bcrypt yang kamu generate sendiri
> UPDATE users
> SET password = '$2y$10$/PUT_BCRYPT_HASH_HERE/'
> WHERE email = 'super@anyeong.com';
> ```
>
> Buat hash bcrypt dengan PHP:
>
> ```bash
> php -r "echo password_hash('passwordbaru', PASSWORD_DEFAULT);"
> ```

---

## Cara Pemakaian

### Untuk Pelanggan

1. Buka `/users/index.php?page=register` atau `?page=login` → buat akun / login.
2. Browse produk di home, pakai kolom search untuk filter cepat.
3. Klik produk → pilih opsi (warna, pita, dll.) → **Tambah ke Keranjang** atau **Bayar Sekarang**.
4. Di halaman checkout, pilih alamat + metode pembayaran → submit.
5. Untuk metode non-COD: kamu otomatis dibawa ke halaman pembayaran:
   - **Transfer/e-wallet**: lihat info rekening → transfer → upload bukti.
   - **QRIS**: scan gambar QRIS di layar → upload bukti.
   - **COD**: pesanan masuk ke daftar dengan status *Belum Bayar* — bayar saat pengambilan.
6. Saat status berubah jadi *Pesanan Siap*, tombol **Selesaikan Pesanan** muncul.
7. Tombol **Hubungi Admin** di tiap pesanan kalau perlu kontak langsung.

### Untuk Admin

1. Login di `/public/login` dengan akun admin.
2. **Dashboard** → ringkasan transaksi terbaru.
3. **Produk** → tambah produk + foto + opsi. Filter per kategori, pagination 10/halaman.
4. **Pesanan** → filter per status, klik detail untuk lihat item (termasuk teks pita), kontak pelanggan, dan bukti pembayaran.
5. Pakai tombol aksi cepat di modal detail untuk lifecycle pesanan (Konfirmasi → Tandai Siap → Tandai Selesai).
6. **Metode Pembayaran** → tambah metode QRIS dengan upload gambar (jpg/png/webp). Gambar tampil otomatis di halaman pembayaran user.
7. **Cetak Invoice** dari modal detail pesanan (tab baru, otomatis print).
8. **Pengaturan** → nama toko, nomor WA admin, template pesan untuk produk konsultasi.

---

## Setup untuk Hosting (Production)

### Skenario A: Shared Hosting (cPanel)

1. **Upload semua file** ke `public_html` (atau subfolder, mis. `public_html/anyeong-gift`).
2. **Buat database MySQL** dari menu *MySQL Databases* di cPanel.
3. **Import** `database/anyeoung_gift_backup.sql` lewat phpMyAdmin.
4. **Edit `config.php`**:
   ```php
   define('BASE_URL', 'https://domainkamu.com/anyeong-gift/public');
   define('DB_HOST', 'localhost');
   define('DB_USER', 'cpaneluser_dbuser');
   define('DB_PASS', 'PASSWORD_DARI_CPANEL');
   define('DB_NAME', 'cpaneluser_anyeoung_gift');
   ```
5. **Edit `config/database.php`** dengan kredensial yang sama (perhatikan: ada **dua** file koneksi DB karena dua sisi sistem).
6. **Sesuaikan hardcoded path `/anyeong-gift`** kalau folder di hosting berbeda. Lihat [Konfigurasi Penting](#konfigurasi-penting).
7. **Permission folder upload** harus writeable oleh web server:
   ```bash
   chmod -R 775 public/uploads
   chown -R www-data:www-data public/uploads     # Linux
   ```
8. **Matikan display_errors** di production (`public/index.php`):
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```
9. **Pastikan HTTPS aktif**. Update semua URL `wa.me/0xxx` agar nomor admin pakai format internasional (`62xxx`).

### Skenario B: VPS (Ubuntu + LAMP)

```bash
# 1. Install LAMP
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-gd unzip -y

# 2. Clone
sudo git clone https://github.com/arifianilhamnrr/Anyeoung-Gift.git /var/www/html/anyeong-gift
sudo chown -R www-data:www-data /var/www/html/anyeong-gift

# 3. DB
sudo mysql -e "CREATE DATABASE anyeoung_gift CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
sudo mysql -e "CREATE USER 'anyeong'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';"
sudo mysql -e "GRANT ALL ON anyeoung_gift.* TO 'anyeong'@'localhost'; FLUSH PRIVILEGES;"
sudo mysql anyeoung_gift < /var/www/html/anyeong-gift/database/anyeoung_gift_backup.sql

# 4. Update config.php & config/database.php (sesuaikan kredensial + BASE_URL)
sudo nano /var/www/html/anyeong-gift/config.php

# 5. Permission upload folder
sudo chmod -R 775 /var/www/html/anyeong-gift/public/uploads

# 6. Pasang SSL via certbot
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d domainkamu.com
```

### Optimasi Production

- Aktifkan opcode cache (`opcache`) PHP.
- Set Apache `mod_expires` untuk static assets di `public/assets/`.
- Backup database berkala (`mysqldump anyeoung_gift > backup-$(date +%F).sql`).
- Pertimbangkan offload upload (`public/uploads/`) ke object storage / CDN kalau traffic tinggi.

---

## Konfigurasi Penting

### Hardcoded Path `/anyeong-gift`

Ada beberapa tempat yang hardcode path folder. Kalau kamu deploy di folder dengan nama lain (mis. `gift-shop`), ganti semua:

| File | Yang Perlu Diubah |
|---|---|
| `config.php` | `BASE_URL` |
| `app/Core/Router.php` | `str_replace('/anyeong-gift/public', ...)` & `str_replace('/anyeong-gift', ...)` |
| `app/Controllers/AuthController.php` | `header('Location: /anyeong-gift/public/admin')` |
| `app/Controllers/DashboardController.php` | `header('Location: /anyeong-gift/public/login')` |
| `views/auth/login.php` | `fetch('/anyeong-gift/public/api/login', ...)` & redirect |

Untuk deploy di root domain (`https://domainkamu.com` langsung ke admin), strip semua path itu jadi string kosong.

### Dua File Konfigurasi DB

Sisi admin pakai `config.php` (konstanta), sisi user pakai `config/database.php` (PDO `$pdo` global). Pastikan **keduanya** punya kredensial yang sama.

### Folder Upload

Pastikan writeable oleh web server:

- `public/uploads/products/` — foto produk
- `public/uploads/payments/` — bukti transfer user
- `public/uploads/payment_methods/` — gambar QRIS admin

---

## FAQ / Troubleshooting

**Tampilan tanpa warna emas / dark theme tidak muncul.**
Pastikan `assets/css/main.css` sudah ada dan ter-load. Kalau pull dari repo bersih, build ulang Tailwind: `npx tailwindcss -i ./assets/css/input.css -o ./assets/css/main.css --minify`.

**Login admin "Email atau password salah" padahal dari dump database.**
Akun lama di dump pakai SHA-256. Reset manual ke bcrypt seperti instruksi di [Login Default](#login-default).

**Tombol "Hubungi Admin" / "Chat WhatsApp" tidak buka WhatsApp.**
Cek `store_settings.whatsapp_admin` di database — harus format internasional tanpa `+` (mis. `6281234567890`). Update di menu admin **Pengaturan**.

**Upload gambar QRIS error "Format gambar QRIS tidak didukung".**
Hanya menerima `jpg`, `jpeg`, `png`, `webp`. Konversi dulu kalau pakai format lain.

**Redirect ke `/anyeong-gift/public/login` jadi 404 di hosting.**
Folder deploy beda nama. Lihat [Hardcoded Path `/anyeong-gift`](#hardcoded-path-anyeong-gift) di atas.

**Pesanan tidak muncul di tabel admin setelah checkout user.**
Cek koneksi DB di `config.php` vs `config/database.php` — kalau dua-duanya berbeda nama database, sisi user akan menulis ke DB yang salah.

---

## Lisensi

Project ini untuk keperluan internal **Anyeong Gift**. Hubungi pemilik repo untuk penggunaan ulang.
