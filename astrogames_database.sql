-- ==============================================================================
-- ASTROGAMES - DIGITAL GAME MARKETPLACE & LAPTOP COMPATIBILITY CHECKER
-- Database Schema & Seed Data Script for Laragon / MySQL / MariaDB
-- Version: 3.0
-- Encoding: UTF-8 (utf8mb4_unicode_ci)
-- ==============================================================================

-- CREATE DATABASE IF NOT EXISTS `astrogames_db` 
--   DEFAULT CHARACTER SET utf8mb4 
--   COLLATE utf8mb4_unicode_ci;

-- USE `astrogames_db`;

-- Set SQL Mode & Disable Foreign Key Checks during setup
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET TIME_ZONE = "+07:00";

-- ==============================================================================
-- 1. DROP EXISTING TABLES IF THEY EXIST (FOR CLEAN RE-CREATION)
-- ==============================================================================
DROP TABLE IF EXISTS `recently_viewed`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `promotion_games`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `game_library`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `vouchers`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `game_requirements`;
DROP TABLE IF EXISTS `game_tags`;
DROP TABLE IF EXISTS `game_screenshots`;
DROP TABLE IF EXISTS `game_genres`;
DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `user_specs`;
DROP TABLE IF EXISTS `gpus`;
DROP TABLE IF EXISTS `cpus`;
DROP TABLE IF EXISTS `user_settings`;
DROP TABLE IF EXISTS `users`;

-- ==============================================================================
-- 2. CREATE TABLES
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- Table 1: users
-- Data akun pengguna & administrator
-- ------------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 2: user_settings
-- Pengaturan preferensi tampilan dan notifikasi pengguna
-- ------------------------------------------------------------------------------
CREATE TABLE `user_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `theme_mode` ENUM('dark', 'light') NOT NULL DEFAULT 'dark',
  `language` VARCHAR(10) NOT NULL DEFAULT 'id',
  `notify_promo` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_wishlist` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_price_drop` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_payment` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_order` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 3: cpus
-- Master data CPU (Intel & AMD) untuk Compatibility Checker Engine
-- ------------------------------------------------------------------------------
CREATE TABLE `cpus` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `brand` ENUM('Intel', 'AMD') NOT NULL,
  `model_name` VARCHAR(100) NOT NULL,
  `series` VARCHAR(50) NOT NULL,
  `cores` INT NOT NULL DEFAULT 4,
  `threads` INT NOT NULL DEFAULT 8,
  `base_clock_ghz` DECIMAL(3,2) NOT NULL DEFAULT 2.50,
  `performance_score` INT NOT NULL DEFAULT 1000 COMMENT 'Skor relatif performa CPU',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cpus_brand_series` (`brand`, `series`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 4: gpus
-- Master data GPU (NVIDIA, AMD, Intel) untuk Compatibility Checker Engine
-- ------------------------------------------------------------------------------
CREATE TABLE `gpus` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `brand` ENUM('NVIDIA', 'AMD', 'Intel') NOT NULL,
  `model_name` VARCHAR(100) NOT NULL,
  `default_vram_gb` INT NOT NULL DEFAULT 4,
  `performance_score` INT NOT NULL DEFAULT 1000 COMMENT 'Skor relatif performa GPU',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_gpus_brand` (`brand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 5: user_specs
-- Data spesifikasi laptop terimpan milik user
-- ------------------------------------------------------------------------------
CREATE TABLE `user_specs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `cpu_id` INT UNSIGNED NOT NULL,
  `ram_gb` INT NOT NULL DEFAULT 8,
  `gpu_id` INT UNSIGNED NOT NULL,
  `vram_gb` INT NOT NULL DEFAULT 4,
  `storage_type` ENUM('HDD', 'SSD', 'NVMe') NOT NULL DEFAULT 'SSD',
  `storage_gb` INT NOT NULL DEFAULT 512,
  `os` ENUM('Windows 7', 'Windows 8', 'Windows 8.1', 'Windows 10', 'Windows 11') NOT NULL DEFAULT 'Windows 10',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_specs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_specs_cpu` FOREIGN KEY (`cpu_id`) REFERENCES `cpus` (`id`),
  CONSTRAINT `fk_user_specs_gpu` FOREIGN KEY (`gpu_id`) REFERENCES `gpus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 6: genres
-- Master Genre Game
-- ------------------------------------------------------------------------------
CREATE TABLE `genres` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 7: games
-- Master Data Game Digital
-- ------------------------------------------------------------------------------
CREATE TABLE `games` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `cover_image` VARCHAR(255) NOT NULL DEFAULT 'default-game-cover.jpg',
  `banner_image` VARCHAR(255) NOT NULL DEFAULT 'default-game-banner.jpg',
  `video_url` VARCHAR(255) NULL,
  `description` TEXT NOT NULL,
  `short_description` VARCHAR(255) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_percentage` INT NOT NULL DEFAULT 0,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `is_new_release` TINYINT(1) NOT NULL DEFAULT 0,
  `release_date` DATE NOT NULL,
  `developer` VARCHAR(100) NOT NULL,
  `publisher` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_games_slug` (`slug`),
  INDEX `idx_games_featured` (`is_featured`),
  INDEX `idx_games_popular` (`is_popular`),
  FULLTEXT KEY `ft_games_search` (`title`, `developer`, `publisher`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 8: game_genres
-- Relasi Many-to-Many Game dan Genre
-- ------------------------------------------------------------------------------
CREATE TABLE `game_genres` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL,
  `genre_id` INT UNSIGNED NOT NULL,
  UNIQUE KEY `uk_game_genre` (`game_id`, `genre_id`),
  CONSTRAINT `fk_game_genres_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_game_genres_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 9: game_screenshots
-- Galeri Screenshot Detail Game
-- ------------------------------------------------------------------------------
CREATE TABLE `game_screenshots` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(150) NULL,
  CONSTRAINT `fk_game_screenshots_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 10: game_tags
-- Tagar pencarian game
-- ------------------------------------------------------------------------------
CREATE TABLE `game_tags` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL,
  `tag_name` VARCHAR(50) NOT NULL,
  INDEX `idx_game_tags_name` (`tag_name`),
  CONSTRAINT `fk_game_tags_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 11: game_requirements
-- Spesifikasi Minimum & Rekomendasi Laptop/PC per Game
-- ------------------------------------------------------------------------------
CREATE TABLE `game_requirements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL UNIQUE,
  `min_cpu_id` INT UNSIGNED NOT NULL,
  `rec_cpu_id` INT UNSIGNED NOT NULL,
  `min_ram_gb` INT NOT NULL DEFAULT 8,
  `rec_ram_gb` INT NOT NULL DEFAULT 16,
  `min_gpu_id` INT UNSIGNED NOT NULL,
  `rec_gpu_id` INT UNSIGNED NOT NULL,
  `min_vram_gb` INT NOT NULL DEFAULT 4,
  `rec_vram_gb` INT NOT NULL DEFAULT 8,
  `min_storage_gb` INT NOT NULL DEFAULT 50,
  `storage_type` ENUM('HDD', 'SSD', 'NVMe') NOT NULL DEFAULT 'SSD',
  `min_os` ENUM('Windows 7', 'Windows 8', 'Windows 8.1', 'Windows 10', 'Windows 11') NOT NULL DEFAULT 'Windows 10',
  `rec_os` ENUM('Windows 7', 'Windows 8', 'Windows 8.1', 'Windows 10', 'Windows 11') NOT NULL DEFAULT 'Windows 11',
  CONSTRAINT `fk_req_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_req_min_cpu` FOREIGN KEY (`min_cpu_id`) REFERENCES `cpus` (`id`),
  CONSTRAINT `fk_req_rec_cpu` FOREIGN KEY (`rec_cpu_id`) REFERENCES `cpus` (`id`),
  CONSTRAINT `fk_req_min_gpu` FOREIGN KEY (`min_gpu_id`) REFERENCES `gpus` (`id`),
  CONSTRAINT `fk_req_rec_gpu` FOREIGN KEY (`rec_gpu_id`) REFERENCES `gpus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 12: wishlist
-- Daftar Wishlist Pengguna
-- ------------------------------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_wishlist` (`user_id`, `game_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 13: cart
-- Keranjang Pengguna (Session/User Based)
-- ------------------------------------------------------------------------------
CREATE TABLE `cart` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(100) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_cart_user` (`user_id`),
  INDEX `idx_cart_session` (`session_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 14: cart_items
-- Item di Keranjang Belanja
-- ------------------------------------------------------------------------------
CREATE TABLE `cart_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `price_at_addition` DECIMAL(12,2) NOT NULL,
  UNIQUE KEY `uk_cart_game` (`cart_id`, `game_id`),
  CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_items_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 15: vouchers
-- Master Kode Voucher / Kupon Diskon
-- ------------------------------------------------------------------------------
CREATE TABLE `vouchers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('fixed', 'percentage') NOT NULL DEFAULT 'percentage',
  `discount_value` DECIMAL(12,2) NOT NULL,
  `min_purchase` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `max_discount` DECIMAL(12,2) NULL,
  `usage_limit` INT NOT NULL DEFAULT 100,
  `times_used` INT NOT NULL DEFAULT 0,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_vouchers_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 16: orders
-- Data Transaksi Pemesanan
-- ------------------------------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `total_original_price` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `voucher_id` INT UNSIGNED NULL,
  `final_price` DECIMAL(12,2) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
  `payment_method` ENUM('qris', 'va', 'ewallet', 'bank_transfer') NOT NULL DEFAULT 'qris',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_orders_user` (`user_id`),
  INDEX `idx_orders_status` (`payment_status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_orders_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 17: order_items
-- Rincian Game yang Dibeli per Order
-- ------------------------------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `original_price` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 18: payments
-- Data Pembayaran & Transaksi Payment Gateway
-- ------------------------------------------------------------------------------
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL UNIQUE,
  `payment_gateway_ref` VARCHAR(100) NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 19: game_library
-- Library Game Digital Milik Pengguna (Game yang berhasil dibeli)
-- ------------------------------------------------------------------------------
CREATE TABLE `game_library` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NOT NULL,
  `purchased_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_game_library` (`user_id`, `game_id`),
  CONSTRAINT `fk_library_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_library_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `fk_library_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 20: reviews
-- Rating & Ulasan Game dari Pembeli Resmi
-- ------------------------------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_text` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_game_review` (`user_id`, `game_id`),
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 21: promotions
-- Banner Campaign & Event Promo Diskon
-- ------------------------------------------------------------------------------
CREATE TABLE `promotions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `banner_image` VARCHAR(255) NOT NULL,
  `discount_percentage` INT NOT NULL DEFAULT 0,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 22: promotion_games
-- Relasi Game yang Termasuk dalam Event Promo
-- ------------------------------------------------------------------------------
CREATE TABLE `promotion_games` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  UNIQUE KEY `uk_promo_game` (`promotion_id`, `game_id`),
  CONSTRAINT `fk_promo_games_promo` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_promo_games_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 23: notifications
-- Sistem Notifikasi Pengguna
-- ------------------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('promo', 'wishlist', 'price_drop', 'payment', 'order') NOT NULL DEFAULT 'promo',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notifications_user_read` (`user_id`, `is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 24: recently_viewed
-- Riwayat Game Terakhir yang Dilihat Pengguna
-- ------------------------------------------------------------------------------
CREATE TABLE `recently_viewed` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `session_id` VARCHAR(100) NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_recent_user` (`user_id`),
  INDEX `idx_recent_session` (`session_id`),
  CONSTRAINT `fk_recent_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recent_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 3. SEED INITIAL DATA
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- Seed: Users (Password default: 'admin123' untuk admin, 'gamer123' untuk user)
-- Password di-hash dengan PASSWORD_BCRYPT php standard
-- ------------------------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `avatar`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@astrogames.com', '$2y$10$e.wS9tK1v.Lg2Zf9bM8bVeB2tH5qZ7r9k7X6d.5c.3A.1b.2c.3d4', 'ASTROGAMES Administrator', 'admin-avatar.png', 'admin', NOW()),
(2, 'gamer_pro', 'gamer@astrogames.com', '$2y$10$e.wS9tK1v.Lg2Zf9bM8bVeB2tH5qZ7r9k7X6d.5c.3A.1b.2c.3d4', 'Reza Gunawan', 'user-avatar.png', 'user', NOW());

-- Seed User Settings
INSERT INTO `user_settings` (`user_id`, `theme_mode`, `language`, `notify_promo`, `notify_wishlist`, `notify_price_drop`, `notify_payment`, `notify_order`) VALUES
(1, 'dark', 'id', 1, 1, 1, 1, 1),
(2, 'dark', 'id', 1, 1, 1, 1, 1);

-- ------------------------------------------------------------------------------
-- Seed: CPUs (Master hardware processor)
-- ------------------------------------------------------------------------------
INSERT INTO `cpus` (`id`, `brand`, `model_name`, `series`, `cores`, `threads`, `base_clock_ghz`, `performance_score`) VALUES
(1, 'Intel', 'Core i3-10100F', 'Core i3', 4, 8, 3.60, 2200),
(2, 'Intel', 'Core i3-12100F', 'Core i3', 4, 8, 3.30, 3100),
(3, 'Intel', 'Core i5-10400F', 'Core i5', 6, 12, 2.90, 3200),
(4, 'Intel', 'Core i5-12400F', 'Core i5', 6, 12, 2.50, 4500),
(5, 'Intel', 'Core i7-11700K', 'Core i7', 8, 16, 3.60, 5800),
(6, 'Intel', 'Core i7-13700K', 'Core i7', 16, 24, 3.40, 8500),
(7, 'Intel', 'Core i9-13900K', 'Core i9', 24, 32, 3.00, 10000),
(8, 'AMD', 'Ryzen 3 3200G', 'Ryzen 3', 4, 4, 3.60, 1800),
(9, 'AMD', 'Ryzen 3 4100', 'Ryzen 3', 4, 8, 3.80, 2400),
(10, 'AMD', 'Ryzen 5 3600', 'Ryzen 5', 6, 12, 3.60, 3500),
(11, 'AMD', 'Ryzen 5 5600X', 'Ryzen 5', 6, 12, 3.70, 4800),
(12, 'AMD', 'Ryzen 7 5700X', 'Ryzen 7', 8, 16, 3.40, 6100),
(13, 'AMD', 'Ryzen 7 7800X3D', 'Ryzen 7', 8, 16, 4.20, 9200),
(14, 'AMD', 'Ryzen 9 7950X', 'Ryzen 9', 16, 32, 4.50, 9900);

-- ------------------------------------------------------------------------------
-- Seed: GPUs (Master hardware graphics card)
-- ------------------------------------------------------------------------------
INSERT INTO `gpus` (`id`, `brand`, `model_name`, `default_vram_gb`, `performance_score`) VALUES
(1, 'Intel', 'Intel UHD Graphics 630', 2, 800),
(2, 'Intel', 'Intel Iris Xe Graphics', 2, 1400),
(3, 'NVIDIA', 'GeForce GTX 750 Ti', 2, 1200),
(4, 'NVIDIA', 'GeForce GTX 1050 Ti', 4, 2100),
(5, 'NVIDIA', 'GeForce GTX 1650', 4, 2800),
(6, 'NVIDIA', 'GeForce GTX 1660 Super', 6, 4200),
(7, 'NVIDIA', 'GeForce RTX 2060', 6, 5400),
(8, 'NVIDIA', 'GeForce RTX 3050', 8, 4800),
(9, 'NVIDIA', 'GeForce RTX 3060', 12, 6800),
(10, 'NVIDIA', 'GeForce RTX 4060', 8, 8200),
(11, 'NVIDIA', 'GeForce RTX 4070 Ti', 12, 11500),
(12, 'NVIDIA', 'GeForce RTX 4090', 24, 16000),
(13, 'AMD', 'Radeon RX 570', 4, 2900),
(14, 'AMD', 'Radeon RX 580', 8, 3400),
(15, 'AMD', 'Radeon RX 6600', 8, 6500),
(16, 'AMD', 'Radeon RX 7700 XT', 12, 9800);

-- Seed User Laptop Specs (Gamer Pro Laptop)
INSERT INTO `user_specs` (`user_id`, `cpu_id`, `ram_gb`, `gpu_id`, `vram_gb`, `storage_type`, `storage_gb`, `os`) VALUES
(2, 4, 16, 9, 12, 'SSD', 512, 'Windows 11');

-- ------------------------------------------------------------------------------
-- Seed: Genres (13 Genres sesuai spesifikasi prompt)
-- ------------------------------------------------------------------------------
INSERT INTO `genres` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Action', 'action', 'Game aksi penuh adrenalin, pertempuran cepat, dan petualangan seru.'),
(2, 'Adventure', 'adventure', 'Jelajahi dunia luas dan nikmati alur cerita mendalam.'),
(3, 'RPG', 'rpg', 'Role-playing game dengan kustomisasi karakter dan peningkatan level.'),
(4, 'FPS', 'fps', 'First-person shooter dengan aksi menembak presisi tinggi.'),
(5, 'Horror', 'horror', 'Pengalaman mencekam penuh teka-teki dan kejutan menakutkan.'),
(6, 'Racing', 'racing', 'Balapan mobil dan kendaraan super cepat di berbagai lintasan.'),
(7, 'Sports', 'sports', 'Simulasi olahraga sepak bola, basket, dan perbandingan atlet profesional.'),
(8, 'Strategy', 'strategy', 'Asah otak dan taktik perang untuk memenangkan pertempuran.'),
(9, 'Simulation', 'simulation', 'Simulasi kehidupan nyata, penerbangan, manajemen kota, dan bisnis.'),
(10, 'Survival', 'survival', 'Bertahan hidup dari ancaman alam, zombie, dan musuh bahaya.'),
(11, 'Open World', 'open-world', 'Dunia bebas tanpa batas untuk dijelajahi kapan saja.'),
(12, 'Multiplayer', 'multiplayer', 'Main bareng teman secara online dan berkompetisi secara global.'),
(13, 'Indie', 'indie', 'Game kreatif buatan developer independen berpengalaman.');

-- ------------------------------------------------------------------------------
-- Seed: Games (Sampel Game Populer)
-- ------------------------------------------------------------------------------
INSERT INTO `games` (`id`, `title`, `slug`, `cover_image`, `banner_image`, `video_url`, `description`, `short_description`, `price`, `discount_percentage`, `rating`, `is_featured`, `is_popular`, `is_new_release`, `release_date`, `developer`, `publisher`) VALUES
(1, 'Cyberpunk 2077', 'cyberpunk-2077', 'cyberpunk2077-cover.jpg', 'cyberpunk2077-banner.jpg', 'https://www.youtube.com/embed/8X2kIfS6fb8', 'Cyberpunk 2077 adalah RPG aksi dunia terbuka di megalopolis Night City, tempat Anda bermain sebagai tentara bayaran cyberpunk yang terlibat dalam pertarungan hidup atau mati.', 'Petualangan aksi open-world di masa depan Night City yang futuristik.', 699999.00, 40, 4.70, 1, 1, 0, '2020-12-10', 'CD PROJEKT RED', 'CD PROJEKT RED'),

(2, 'Grand Theft Auto V: Premium Edition', 'gta-v', 'gtav-cover.jpg', 'gtav-banner.jpg', 'https://www.youtube.com/embed/QkkoHAzjinY', 'Ketika seorang penipu jalanan muda, perampok bank yang pensiun, dan psikopat yang menakutkan mendapati diri mereka terjerat dalam dunia kriminal.', 'Nikmati kebebasan dunia open-world Los Santos yang legendaris.', 401000.00, 50, 4.85, 1, 1, 0, '2015-04-14', 'Rockstar North', 'Rockstar Games'),

(3, 'Valorant (Points Pack - Deluxe)', 'valorant', 'valorant-cover.jpg', 'valorant-banner.jpg', 'https://www.youtube.com/embed/e_E9W2vsRbA', 'VALORANT adalah penembak taktis berbasis karakter 5v5 yang berlatar di panggung dunia. Akurasi tembakan dan keahlian taktis adalah kunci kemenangan.', 'FPS Taktis 5v5 gratis berbasis karakter dengan pertempuran intens.', 150000.00, 0, 4.90, 1, 1, 1, '2020-06-02', 'Riot Games', 'Riot Games'),

(4, 'Forza Horizon 5', 'forza-horizon-5', 'fh5-cover.jpg', 'fh5-banner.jpg', 'https://www.youtube.com/embed/FYH9n37B7Yw', 'Petualangan Horizon Terhebat menanti Anda! Jelajahi pemandangan dunia terbuka Meksiko yang bersemangat dan terus berkembang.', 'Balapan open world spektakuler di jalanan dan alam terbuka Meksiko.', 799000.00, 35, 4.80, 0, 1, 0, '2021-11-09', 'Playground Games', 'Xbox Game Studios'),

(5, 'Elden Ring', 'elden-ring', 'eldenring-cover.jpg', 'eldenring-banner.jpg', 'https://www.youtube.com/embed/AKXlBOjfUbc', 'GAME OF THE YEAR. Bangkitlah, Tarnished, dan dibimbing oleh rahmat untuk menguasai kekuatan Elden Ring dan menjadi Elden Lord di Lands Between.', 'RPG Aksi mahakarya dari Hidetaka Miyazaki dan George R.R. Martin.', 599000.00, 20, 4.95, 1, 1, 0, '2022-02-25', 'FromSoftware Inc.', 'Bandai Namco Entertainment'),

(6, 'Red Dead Redemption 2', 'red-dead-redemption-2', 'rdr2-cover.jpg', 'rdr2-banner.jpg', 'https://www.youtube.com/embed/eaW0tYxi5rg', 'Amerika, 1899. Arthur Morgan dan geng Van der Linde adalah buronan yang melarikan diri dari agen federal dan pemburu hadiah terbaik.', 'Kisah epik kehidupan koboi di era akhir wilayah barat Amerika.', 640000.00, 60, 4.92, 0, 1, 0, '2019-12-05', 'Rockstar Games', 'Rockstar Games'),

(7, 'The Witcher 3: Wild Hunt - Complete Edition', 'the-witcher-3', 'witcher3-cover.jpg', 'witcher3-banner.jpg', 'https://www.youtube.com/embed/c0i88t0Kacs', 'Anda adalah Geralt dari Rivia, pemburu monster bayaran. Di depan Anda berdiri benua yang dilanda perang dan dipenuhi monster.', 'RPG open world legendaris dengan jalan cerita petualangan terbaik.', 449999.00, 75, 4.98, 0, 1, 0, '2015-05-18', 'CD PROJEKT RED', 'CD PROJEKT RED'),

(8, 'Hogwarts Legacy', 'hogwarts-legacy', 'hogwarts-cover.jpg', 'hogwarts-banner.jpg', 'https://www.youtube.com/embed/1O6Qstncpnc', 'Hogwarts Legacy adalah RPG aksi dunia terbuka yang berlatar di dunia yang pertama kali diperkenalkan dalam buku-buku Harry Potter.', 'Jadilah penyihir hebat di sekolah Hogwarts abad ke-19.', 799000.00, 50, 4.65, 0, 1, 1, '2023-02-10', 'Avalanche Software', 'Warner Bros. Games');

-- Seed Game Genres
INSERT INTO `game_genres` (`game_id`, `genre_id`) VALUES
(1, 1), (1, 3), (1, 11), -- Cyberpunk: Action, RPG, Open World
(2, 1), (2, 2), (2, 11), (2, 12), -- GTA V: Action, Adventure, Open World, Multiplayer
(3, 4), (3, 12), -- Valorant: FPS, Multiplayer
(4, 6), (4, 9), (4, 11), -- Forza: Racing, Simulation, Open World
(5, 1), (5, 3), (5, 11), -- Elden Ring: Action, RPG, Open World
(6, 1), (6, 2), (6, 11), -- RDR2: Action, Adventure, Open World
(7, 2), (7, 3), (7, 11), -- Witcher 3: Adventure, RPG, Open World
(8, 2), (8, 3), (8, 11); -- Hogwarts Legacy: Adventure, RPG, Open World

-- Seed Game Tags
INSERT INTO `game_tags` (`game_id`, `tag_name`) VALUES
(1, 'Cyberpunk'), (1, 'Sci-Fi'), (1, 'Ray Tracing'), (1, 'Singleplayer'),
(2, 'Open World'), (2, 'Crime'), (2, 'Multiplayer'), (2, 'Sandbox'),
(3, 'Competitive'), (3, 'Esports'), (3, 'Hero Shooter'), (3, 'Tactical'),
(4, 'Cars'), (4, 'Graphics'), (4, 'Mexico'), (4, 'Driving'),
(5, 'Souls-like'), (5, 'Dark Fantasy'), (5, 'Difficult'), (5, 'Masterpiece'),
(6, 'Story Rich'), (6, 'Western'), (6, 'Atmospheric'), (6, 'Realistic'),
(7, 'Magic'), (7, 'Story Rich'), (7, 'Great Soundtrack'), (7, 'Choices Matter'),
(8, 'Harry Potter'), (8, 'Magic'), (8, 'Fantasy'), (8, 'Exploration');

-- ------------------------------------------------------------------------------
-- Seed: Game Requirements (Data Spek Laptop untuk Compatibility Checker)
-- ------------------------------------------------------------------------------
INSERT INTO `game_requirements` 
(`game_id`, `min_cpu_id`, `rec_cpu_id`, `min_ram_gb`, `rec_ram_gb`, `min_gpu_id`, `rec_gpu_id`, `min_vram_gb`, `rec_vram_gb`, `min_storage_gb`, `storage_type`, `min_os`, `rec_os`) VALUES
-- Cyberpunk 2077
(1, 3, 4, 12, 16, 6, 9, 6, 8, 70, 'SSD', 'Windows 10', 'Windows 11'),
-- GTA V
(2, 1, 3, 8, 16, 4, 6, 4, 6, 110, 'HDD', 'Windows 10', 'Windows 11'),
-- Valorant
(3, 1, 3, 4, 8, 1, 4, 2, 4, 30, 'SSD', 'Windows 10', 'Windows 11'),
-- Forza Horizon 5
(4, 3, 4, 8, 16, 5, 9, 4, 8, 110, 'SSD', 'Windows 10', 'Windows 11'),
-- Elden Ring
(5, 3, 4, 12, 16, 5, 9, 4, 8, 60, 'SSD', 'Windows 10', 'Windows 11'),
-- Red Dead Redemption 2
(6, 3, 4, 12, 16, 5, 7, 4, 6, 120, 'SSD', 'Windows 10', 'Windows 11'),
-- Witcher 3
(7, 1, 3, 8, 16, 4, 6, 4, 6, 50, 'HDD', 'Windows 10', 'Windows 11'),
-- Hogwarts Legacy
(8, 3, 5, 16, 16, 6, 10, 6, 8, 85, 'SSD', 'Windows 10', 'Windows 11');

-- ------------------------------------------------------------------------------
-- Seed: Vouchers (Kupon promo checkout)
-- ------------------------------------------------------------------------------
INSERT INTO `vouchers` (`id`, `code`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `usage_limit`, `times_used`, `start_date`, `end_date`, `is_active`) VALUES
(1, 'ASTROLAUNCH', 'percentage', 20.00, 100000.00, 150000.00, 500, 12, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
(2, 'GAMERID', 'fixed', 50000.00, 300000.00, 50000.00, 200, 45, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1),
(3, 'PROMO50', 'percentage', 50.00, 200000.00, 250000.00, 100, 98, '2026-08-01 00:00:00', '2026-08-31 23:59:59', 1);

-- ------------------------------------------------------------------------------
-- Seed: Promotions (Event Promo Berdurasi)
-- ------------------------------------------------------------------------------
INSERT INTO `promotions` (`id`, `title`, `description`, `banner_image`, `discount_percentage`, `start_date`, `end_date`, `is_active`) VALUES
(1, '🔥 PROMO MEGA SUMMER SALE 2026', 'Diskon besar-besaran hingga 75% untuk game AAA pilihan!', 'promo-summer-sale.jpg', 50, '2026-08-01 00:00:00', '2026-08-31 23:59:59', 1),
(2, '⚡ DISKON MINGGUAN GAME ACTION', 'Hemat lebih banyak untuk koleksi game petualangan & aksi.', 'promo-weekly-action.jpg', 30, '2026-08-10 00:00:00', '2026-08-20 23:59:59', 1);

-- Seed Promotion Games
INSERT INTO `promotion_games` (`promotion_id`, `game_id`) VALUES
(1, 1), (1, 2), (1, 6), (1, 7),
(2, 4), (2, 5), (2, 8);

-- ------------------------------------------------------------------------------
-- Seed: Reviews (Rating & Ulasan dari User)
-- ------------------------------------------------------------------------------
INSERT INTO `reviews` (`user_id`, `game_id`, `rating`, `review_text`, `created_at`) VALUES
(2, 1, 5, 'Performa di RTX 3060 lancar jaya! Night City sangat memukau setelah update versi terbaru.', '2026-08-10 14:20:00'),
(2, 2, 5, 'Game wajib punya sepanjang masa. Online mode makin seru!', '2026-08-11 16:45:00'),
(2, 5, 5, 'Grafik memukau dan gameplay sangat menantang. Game RPG terbaik!', '2026-08-12 09:15:00');

-- ------------------------------------------------------------------------------
-- Seed: Orders & Library (Contoh Transaksi Selesai)
-- ------------------------------------------------------------------------------
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total_original_price`, `discount_amount`, `voucher_id`, `final_price`, `payment_status`, `payment_method`, `created_at`) VALUES
(1, 'AG20260814001', 2, 699999.00, 279999.60, 1, 419999.40, 'paid', 'qris', '2026-08-14 10:00:00');

INSERT INTO `order_items` (`order_id`, `game_id`, `price`, `original_price`) VALUES
(1, 1, 419999.40, 699999.00);

INSERT INTO `payments` (`order_id`, `payment_gateway_ref`, `payment_method`, `amount`, `status`, `paid_at`) VALUES
(1, 'QRIS-AG-8891230192', 'QRIS Dynamic', 419999.40, 'paid', '2026-08-14 10:02:15');

INSERT INTO `game_library` (`user_id`, `game_id`, `order_id`, `purchased_at`) VALUES
(2, 1, 1, '2026-08-14 10:02:15');

-- Re-enable Foreign Key Checks
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- END OF ASTROGAMES DATABASE SCRIPT
-- ==============================================================================
