-- Migration: Tambah dukungan driver email berbasis API (Brevo, MailerSend, SendPulse).
-- SMTP PHPMailer tetap dipertahankan sebagai default. Driver API dipakai
-- sebagai alternatif jika port SMTP diblokir oleh penyedia hosting.
--
-- Jalankan migration ini di database yang sudah ada SEKALI saja:
--
--     mysql -u <user> -p <database> < database/migrations/2026_05_14_add_email_api_drivers.sql
--

ALTER TABLE `store_settings`
  ADD COLUMN `email_driver` VARCHAR(20) NOT NULL DEFAULT 'smtp'
      COMMENT 'smtp | brevo | mailersend | sendpulse' AFTER `email_enabled`,
  ADD COLUMN `email_brevo_api_key` VARCHAR(255) DEFAULT NULL AFTER `email_smtp_encryption`,
  ADD COLUMN `email_mailersend_api_key` VARCHAR(500) DEFAULT NULL AFTER `email_brevo_api_key`,
  ADD COLUMN `email_sendpulse_client_id` VARCHAR(255) DEFAULT NULL AFTER `email_mailersend_api_key`,
  ADD COLUMN `email_sendpulse_client_secret` VARCHAR(255) DEFAULT NULL AFTER `email_sendpulse_client_id`;

-- Idempotensi: kalau migration sudah pernah dijalankan, perintah di atas akan
-- error. Kalau ingin aman, pakai versi conditional di bawah (uncomment):
--
-- SET @sql := IF(
--     (SELECT COUNT(*) FROM information_schema.COLUMNS
--      WHERE TABLE_SCHEMA = DATABASE()
--        AND TABLE_NAME = 'store_settings'
--        AND COLUMN_NAME = 'email_driver') = 0,
--     'ALTER TABLE `store_settings` ADD COLUMN `email_driver` VARCHAR(20) NOT NULL DEFAULT ''smtp'' AFTER `email_enabled`',
--     'SELECT 1');
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
