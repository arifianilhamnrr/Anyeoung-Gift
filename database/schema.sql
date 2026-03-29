-- Buat Database
CREATE DATABASE IF NOT EXISTS anyeong_gift;

USE anyeong_gift;

-- Tabel Users (Untuk Akun Admin & Pelanggan)
CREATE TABLE
    `users` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `role` enum ('admin', 'customer') DEFAULT 'customer',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    );

-- Masukkan 1 Akun Admin Default
INSERT INTO
    `users` (`name`, `email`, `password`, `role`)
VALUES
    (
        'Administrator',
        'admin@anyeong.com',
        'admin123',
        'admin'
    );

-- Tabel Orders (Pesanan Utama)
CREATE TABLE
    `orders` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `user_id` int (11) NOT NULL,
        `address_snapshot` text NOT NULL,
        `total_price` decimal(10, 2) NOT NULL,
        `status` enum (
            'pending_payment',
            'processing',
            'completed',
            'cancelled'
        ) DEFAULT 'pending_payment',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    );

-- Tabel Order Items (Detail Produk dalam Pesanan)
CREATE TABLE
    `order_items` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `order_id` int (11) NOT NULL,
        `product_id` int (11) DEFAULT NULL,
        `product_name_snapshot` varchar(255) NOT NULL,
        `base_price` decimal(10, 2) NOT NULL,
        `subtotal` decimal(10, 2) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
    );

-- Tabel Order Item Options (Detail Kustomisasi Pita/Warna)
CREATE TABLE
    `order_item_options` (
        `id` int (11) NOT NULL AUTO_INCREMENT,
        `order_item_id` int (11) NOT NULL,
        `option_name_snapshot` varchar(100) NOT NULL,
        `option_value_snapshot` varchar(100) NOT NULL,
        `additional_price` decimal(10, 2) DEFAULT '0.00',
        `custom_value` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
    );