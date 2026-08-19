-- Database GameCheck Schema & Seed Data
-- Import file ini ke MySQL (XAMPP phpMyAdmin)

CREATE DATABASE IF NOT EXISTS `gamecheck` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gamecheck`;

-- 1. Tabel users
DROP TABLE IF EXISTS `user_products`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `user_specs`;
DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password admin: admin123
-- Password user: user123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrator GameCheck', 'admin@gamecheck.com', '$2y$10$4.aCmslA088Zf9V33.L1u.3c.t8/3/6wJj1Ww982O4M9S/cQp1e8u', 'admin'),
(2, 'Gamer Indonesia', 'user@gamecheck.com', '$2y$10$4.aCmslA088Zf9V33.L1u.3c.t8/3/6wJj1Ww982O4M9S/cQp1e8u', 'user');

-- 2. Tabel games
CREATE TABLE `games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `genre` VARCHAR(100) NOT NULL,
  `developer` VARCHAR(100) NOT NULL,
  `publisher` VARCHAR(100) NOT NULL,
  `release_date` DATE NOT NULL,
  `platform` VARCHAR(50) NOT NULL DEFAULT 'PC (Windows)',
  `cover` VARCHAR(255) NOT NULL DEFAULT 'default_game.jpg',
  `minimum_cpu` VARCHAR(150) NOT NULL,
  `minimum_ram` INT NOT NULL, -- GB
  `minimum_gpu` VARCHAR(150) NOT NULL,
  `minimum_vram` INT NOT NULL, -- GB
  `minimum_os` VARCHAR(50) NOT NULL DEFAULT 'Windows 10',
  `recommended_cpu` VARCHAR(150) NOT NULL,
  `recommended_ram` INT NOT NULL, -- GB
  `recommended_gpu` VARCHAR(150) NOT NULL,
  `recommended_vram` INT NOT NULL, -- GB
  `recommended_os` VARCHAR(50) NOT NULL DEFAULT 'Windows 10 / 11',
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `difficulty` ENUM('Ringan','Menengah','Berat') NOT NULL DEFAULT 'Menengah',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `games` (`id`, `name`, `slug`, `description`, `genre`, `developer`, `publisher`, `release_date`, `platform`, `cover`, `minimum_cpu`, `minimum_ram`, `minimum_gpu`, `minimum_vram`, `minimum_os`, `recommended_cpu`, `recommended_ram`, `recommended_gpu`, `recommended_vram`, `recommended_os`, `price`, `difficulty`) VALUES
(1, 'Valorant', 'valorant', 'Valorant adalah game FPS taktikal 5v5 berbasis karakter yang mengandalkan keakuratan menembak dan kemampuan unik setiap Agen.', 'FPS / Shooter', 'Riot Games', 'Riot Games', '2020-06-02', 'PC (Windows)', 'valorant.jpg', 'Intel Core i3-370M / AMD A6-3620', 4, 'Intel HD 3000 / AMD Radeon R5', 1, 'Windows 10 64-bit', 'Intel Core i5-4460 / AMD Ryzen 3 1200', 8, 'NVIDIA GeForce GTX 1050 Ti / AMD Radeon R7 370', 2, 'Windows 10/11 64-bit', 0.00, 'Ringan'),

(2, 'Minecraft', 'minecraft', 'Minecraft adalah game sandbox tempat pemain dapat menjelajahi dunia piksel 3D tanpa batas, mengumpulkan bahan, dan membangun apa saja.', 'Sandbox / Adventure', 'Mojang Studios', 'Xbox Game Studios', '2011-11-18', 'PC (Windows)', 'minecraft.jpg', 'Intel Core i3-3210 / AMD A8-7600', 4, 'Intel HD Graphics 4000 / AMD Radeon R5', 1, 'Windows 10', 'Intel Core i5-4690 / AMD FX-8350', 8, 'NVIDIA GeForce 700 Series / AMD Radeon R9 200', 2, 'Windows 10/11', 400000.00, 'Ringan'),

(3, 'Grand Theft Auto V (GTA V)', 'gta-v', 'Jelajahi Los Santos dan Blaine County dalam pengalaman dunia terbuka GTA V yang penuh dengan misi perampokan dan aksi seru.', 'Action / Open World', 'Rockstar North', 'Rockstar Games', '2015-04-14', 'PC (Windows)', 'gtav.jpg', 'Intel Core 2 Quad Q6600 / AMD Phenom 9850', 8, 'NVIDIA GeForce 9800 GT 1GB / AMD HD 4870 1GB', 2, 'Windows 10 64-bit', 'Intel Core i5 3470 / AMD X8 FX-8350', 16, 'NVIDIA GeForce GTX 660 2GB / AMD HD 7870 2GB', 4, 'Windows 10/11 64-bit', 400000.00, 'Menengah'),

(4, 'Roblox', 'roblox', 'Platform alam semesta virtual terbesar yang memungkinkan pengguna membuat game mereka sendiri dan bermain dengan jutaan teman secara online.', 'Sandbox / Multiplayer', 'Roblox Corporation', 'Roblox Corporation', '2006-09-01', 'PC (Windows)', 'roblox.jpg', 'Intel Core i3-2100 / AMD Athlon II X4', 4, 'Intel HD Graphics 4000 / AMD Radeon HD 6450', 1, 'Windows 10', 'Intel Core i5-4460 / AMD Ryzen 3 2200G', 8, 'NVIDIA GeForce GTX 750 Ti / AMD Radeon RX 550', 2, 'Windows 10/11', 0.00, 'Ringan'),

(5, 'Counter-Strike 2 (CS2)', 'counter-strike-2', 'CS2 adalah lompatan teknis terbesar dalam sejarah Counter-Strike yang dibangun di atas engine Source 2 dengan grafis dan visual canggih.', 'FPS / Competitive', 'Valve', 'Valve', '2023-09-27', 'PC (Windows)', 'cs2.jpg', 'Intel Core i5-750 / AMD FX-6300', 8, 'NVIDIA GeForce GTX 660 / AMD Radeon HD 7850', 2, 'Windows 10 64-bit', 'Intel Core i7-9700K / AMD Ryzen 7 3700X', 16, 'NVIDIA GeForce RTX 2060 / AMD Radeon RX 5700 XT', 6, 'Windows 10/11 64-bit', 0.00, 'Menengah'),

(6, 'Dota 2', 'dota-2', 'Setiap hari, jutaan pemain di seluruh dunia memasuki pertempuran sebagai salah satu dari lebih dari seratus pahlawan Dota dalam game MOBA legendaris ini.', 'MOBA / Strategy', 'Valve', 'Valve', '2013-07-09', 'PC (Windows)', 'dota2.jpg', 'Intel Dual Core 2.8 GHz / AMD Athlon 64 X2', 4, 'NVIDIA GeForce 8600GT / AMD Radeon HD 2600', 1, 'Windows 10', 'Intel Core i5-4460 / AMD Ryzen 3 1300X', 8, 'NVIDIA GeForce GTX 960 / AMD Radeon R9 280', 2, 'Windows 10/11', 0.00, 'Ringan'),

(7, 'League of Legends', 'league-of-legends', 'LoL adalah game arena pertarungan online berbasis tim di mana dua tim beranggotakan lima juara kuat berhadapan untuk menghancurkan Nexus musuh.', 'MOBA', 'Riot Games', 'Riot Games', '2009-10-27', 'PC (Windows)', 'lol.jpg', 'Intel Core i3-530 / AMD A6-3650', 4, 'Intel HD Graphics 4600 / AMD Radeon HD 6570', 1, 'Windows 10', 'Intel Core i5-3300 / AMD Ryzen 3 1200', 8, 'NVIDIA GeForce GTX 560 / AMD Radeon HD 6950', 2, 'Windows 10/11', 0.00, 'Ringan'),

(8, 'Fortnite', 'fortnite', 'Ciptakan, bermain, dan bertempur dengan teman secara gratis di Fortnite Battle Royale yang selalu dinamis.', 'Battle Royale / Action', 'Epic Games', 'Epic Games', '2017-07-25', 'PC (Windows)', 'fortnite.jpg', 'Intel Core i3-3225 / AMD FX-4300', 8, 'Intel HD 4000 / AMD Radeon Vega 8', 2, 'Windows 10 64-bit', 'Intel Core i5-7300U / AMD Ryzen 3 3300X', 16, 'NVIDIA GeForce GTX 960 / AMD Radeon R9 280', 4, 'Windows 10/11 64-bit', 0.00, 'Menengah'),

(9, 'The Sims 4', 'the-sims-4', 'Kendalikan kehidupan para Sim, bangun rumah impian mereka, dan jelajahi dunia cerah yang penuh dengan kemungkinan dalam game simulasi ini.', 'Simulation', 'Maxis', 'Electronic Arts', '2014-09-02', 'PC (Windows)', 'sims4.jpg', 'Intel Core 2 Duo 1.8 GHz / AMD Athlon 64 X2', 4, 'NVIDIA GeForce 6600 / AMD Radeon X1300', 1, 'Windows 10', 'Intel Core i5 2.5 GHz / AMD FX-6300', 8, 'NVIDIA GeForce GTX 650 / AMD Radeon HD 7750', 2, 'Windows 10/11', 0.00, 'Ringan'),

(10, 'Euro Truck Simulator 2', 'ets2', 'Jadilah raja jalanan di Eropa! Kemudikan truk pengangkut kargo melintasi puluhan kota ikonik di Inggris, Belgia, Jerman, Italia, dan lainnya.', 'Simulation / Driving', 'SCS Software', 'SCS Software', '2012-10-19', 'PC (Windows)', 'ets2.jpg', 'Intel Core i5-6400 / AMD Ryzen 3 1200', 8, 'NVIDIA GeForce GTX 660 / AMD Radeon RX 460', 2, 'Windows 10 64-bit', 'Intel Core i5-9600K / AMD Ryzen 5 3600', 16, 'NVIDIA GeForce GTX 1660 / AMD Radeon RX 590', 4, 'Windows 10/11 64-bit', 200000.00, 'Ringan'),

(11, 'Forza Horizon 4', 'forza-horizon-4', 'Rasakan keindahan musim yang dinamis di Britania Raya dalam festival balap dunia terbuka paling megah di dunia.', 'Racing / Open World', 'Playground Games', 'Xbox Game Studios', '2018-10-02', 'PC (Windows)', 'fh4.jpg', 'Intel Core i5-750 / AMD FX-6300', 8, 'NVIDIA GeForce GTX 650 Ti / AMD Radeon R7 250X', 2, 'Windows 10 64-bit', 'Intel Core i7-3820 / AMD Ryzen 5 1400', 16, 'NVIDIA GeForce GTX 1060 3GB / AMD Radeon RX 470', 4, 'Windows 10/11 64-bit', 500000.00, 'Menengah'),

(12, 'Forza Horizon 5', 'forza-horizon-5', 'Petualangan Horizon terbaik telah menanti! Jelajahi pemandangan dunia terbuka Meksiko yang hidup dan selalu berubah dengan aksi berkendara tak terbatas.', 'Racing / Open World', 'Playground Games', 'Xbox Game Studios', '2021-11-09', 'PC (Windows)', 'fh5.jpg', 'Intel Core i5-4460 / AMD Ryzen 3 1200', 8, 'NVIDIA GeForce GTX 970 / AMD Radeon RX 470', 4, 'Windows 10 64-bit', 'Intel Core i7-10700K / AMD Ryzen 7 3800XT', 16, 'NVIDIA GeForce RTX 3060 / AMD Radeon RX 6700 XT', 8, 'Windows 10/11 64-bit', 700000.00, 'Berat'),

(13, 'Elden Ring', 'elden-ring', 'Game Action RPG pemenang Game of the Year yang berlatar di dunia Lands Between garapan Hidetaka Miyazaki dan George R.R. Martin.', 'RPG / Action', 'FromSoftware', 'Bandai Namco', '2022-02-25', 'PC (Windows)', 'eldenring.jpg', 'Intel Core i5-8400 / AMD Ryzen 3 3300X', 12, 'NVIDIA GeForce GTX 1060 3GB / AMD Radeon RX 580', 3, 'Windows 10 64-bit', 'Intel Core i7-8700K / AMD Ryzen 5 3600X', 16, 'NVIDIA GeForce GTX 1070 8GB / AMD Radeon RX Vega 56', 6, 'Windows 10/11 64-bit', 799000.00, 'Berat'),

(14, 'Cyberpunk 2077', 'cyberpunk-2077', 'Cyberpunk 2077 adalah RPG aksi dunia terbuka yang berlatar di megalopolis Night City yang terobsesi dengan kekuasaan, kemewahan, dan modifikasi tubuh.', 'RPG / Open World', 'CD PROJEKT RED', 'CD PROJEKT RED', '2020-12-10', 'PC (Windows)', 'cyberpunk.jpg', 'Intel Core i7-6700 / AMD Ryzen 5 1600', 12, 'NVIDIA GeForce GTX 1060 6GB / AMD Radeon RX 580', 6, 'Windows 10 64-bit', 'Intel Core i7-12700 / AMD Ryzen 7 7800X3D', 16, 'NVIDIA GeForce RTX 2060 SUPER / AMD Radeon RX 5700 XT', 8, 'Windows 10/11 64-bit', 699000.00, 'Berat'),

(15, 'Red Dead Redemption 2', 'rdr2', 'Arthur Morgan dan geng Van der Linde adalah buronan yang berusaha bertahan hidup di pedalaman Amerika yang kejam pada akhir era Wild West.', 'Action / Adventure', 'Rockstar Games', 'Rockstar Games', '2019-12-05', 'PC (Windows)', 'rdr2.jpg', 'Intel Core i5-2500K / AMD FX-6300', 8, 'NVIDIA GeForce GTX 770 2GB / AMD Radeon R9 280 3GB', 2, 'Windows 10 64-bit', 'Intel Core i7-4770K / AMD Ryzen 5 1500X', 12, 'NVIDIA GeForce GTX 1060 6GB / AMD Radeon RX 480 4GB', 6, 'Windows 10/11 64-bit', 640000.00, 'Berat');

-- 3. Tabel user_specs
CREATE TABLE `user_specs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `cpu` VARCHAR(150) NOT NULL,
  `ram` INT NOT NULL,
  `gpu` VARCHAR(150) NOT NULL,
  `vram` INT NOT NULL,
  `os` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_specs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_specs` (`user_id`, `cpu`, `ram`, `gpu`, `vram`, `os`) VALUES
(2, 'Intel Core i5-11400H', 16, 'NVIDIA GeForce RTX 3050 Laptop GPU', 4, 'Windows 11');

-- 4. Tabel products
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) NOT NULL DEFAULT 'default_product.jpg',
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 999,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `image`, `price`, `stock`, `status`) VALUES
(1, 'GameCheck VIP Optimization Guidebook', 'gamecheck-vip-guidebook', 'Panduan eksklusif tingkat lanjut untuk memaksimalkan performa Windows 10/11, menurunkan suhu CPU/GPU, dan mendongkrak FPS game hingga 40% di laptop gaming standar.', 'guidebook.jpg', 19000.00, 999, 'active'),
(2, 'GameCheck Database Game Compatibility List PDF', 'gamecheck-game-list-pdf', 'File PDF komprehensif berisi daftar 500+ game populer dengan perincian spesifikasi minimum & rekomendasi resmi beserta tips setting grafis terbaik.', 'gamelist.jpg', 10000.00, 999, 'active'),
(3, 'GameCheck Ultimate Performance Tweak Script', 'gamecheck-performance-script', 'Script otomatis untuk menonaktifkan telemetry Windows yang tidak perlu, membersihkan cache RAM secara otomatis, dan mengoptimalkan power plan laptop.', 'tweakscript.jpg', 29000.00, 999, 'active');

-- 5. Tabel orders
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_status` ENUM('pending','paid','failed','expired') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel payments
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'QRIS',
  `transaction_id` VARCHAR(100) NULL,
  `status` VARCHAR(50) NOT NULL,
  `paid_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel user_products
CREATE TABLE `user_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  `activated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_products_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_products_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
