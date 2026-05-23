-- Migration: Tambah tabel registration_otps untuk verifikasi OTP saat
-- pendaftaran akun. Data calon user (name, email, password_hash) ditampung
-- sementara di tabel ini sampai OTP berhasil diverifikasi. Setelah verifikasi
-- sukses, baru row di-promote ke tabel `users` dan row di registration_otps
-- dihapus.
--
-- Jalankan migration ini SEKALI saja:
--
--     mysql -u <user> -p <database> < database/migrations/2026_05_14_add_registration_otps.sql
--

CREATE TABLE IF NOT EXISTS `registration_otps` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `otp_hash` VARCHAR(64) NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_registration_otps_email` (`email`),
  KEY `idx_registration_otps_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
