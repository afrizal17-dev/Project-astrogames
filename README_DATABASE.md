# 🚀 ASTROGAMES - Panduan Database Laragon & Dokumentasi Skema

Dokumen ini berisi panduan lengkap **cara meng-import** file `astrogames_database.sql` ke dalam **Laragon MySQL**, penjelasan struktur 24 tabel, relasi antar tabel, akun akses default, dan algoritma perhitungan *Compatibility Checker Engine*.

---

## 📋 PERSYARATAN & ENVIRONMENT LARAGON

- **Laragon**: Version 3 / 4 / 5 / 6 (Apache / Nginx)
- **Database**: MySQL 5.7+ / 8.0+ atau MariaDB 10.3+
- **PHP**: PHP 8.0 / 8.1 / 8.2 / 8.3 dengan ekstensi `pdo_mysql` aktif.
- **Port Default**: `3306`
- **Username Default**: `root`
- **Password Default**: `""` (Kosong)

---

## 🛠️ CARA IMPORT DATABASE KE LARAGON

Pilih **salah satu** dari 3 cara mudah berikut untuk meng-import `astrogames_database.sql`:

### Cara 1: Menggunakan phpMyAdmin (Rekomendasi Pemula)
1. Buka Laragon dan klik tombol **Start All**.
2. Klik tombol **Database** di Laragon atau buka browser lalu akses `http://localhost/phpmyadmin`.
3. Klik tab **Import** pada menu utama atas.
4. Klik **Choose File** dan pilih file `astrogames_database.sql` yang berada di folder project `astrogames/astrogames_database.sql`.
5. Scroll ke bawah dan klik tombol **Import** (atau **Go**).
6. Database `astrogames_db` beserta 24 tabel dan seed data awal akan otomatis terbuat.

---

### Cara 2: Menggunakan HeidiSQL (Bawaan Laragon)
1. Buka Laragon dan klik **Start All**.
2. Klik tombol **Database** di Laragon (akan membuka HeidiSQL).
3. Klik **Open** pada koneksi Localhost (`Uncompressed MySQL`, user `root`).
4. Klik menu **File** -> **Load SQL file...** (atau tekan `Ctrl + O`).
5. Pilih file `astrogames_database.sql`.
6. Tekan tombol **Execute** / ▶️ (atau tekan `F9`).
7. Klik kanan pada daftar database dan pilih **Refresh** (`F5`). Database `astrogames_db` siap digunakan.

---

### Cara 3: Menggunakan Terminal / Command Line Laragon
1. Buka Laragon dan klik tombol **Terminal**.
2. Ketik perintah berikut dan tekan Enter:
   ```bash
   mysql -u root < "C:/Users/ThinkPad/.gemini/antigravity/scratch/astrogames/astrogames_database.sql"
   ```
3. Verifikasi dengan perintah:
   ```bash
   mysql -u root -e "SHOW DATABASES LIKE 'astrogames_db';"
   ```

---

## 🔑 AKUN AKSES DEFAULT (SEED DATA)

Database ini sudah dilengkapi dengan data awal (*seed data*) untuk pengujian langsung:

### 1. Akun Admin (Full Control)
- **Username**: `admin`
- **Email**: `admin@astrogames.com`
- **Password**: `admin123`
- **Role**: `admin`

### 2. Akun User Regular (Gamer)
- **Username**: `gamer_pro`
- **Email**: `gamer@astrogames.com`
- **Password**: `gamer123`
- **Role**: `user`
- **Laptop Spec**: Intel Core i5-12400F, RAM 16GB, NVIDIA RTX 3060 12GB, SSD 512GB, Windows 11.

---

## 🗺️ STRUKTUR & DOKUMENTASI TABEL (24 TABEL)

| No | Nama Tabel | Fungsi Utama | Kunci / Constraint |
|:---|:---|:---|:---|
| 1 | `users` | Akun Pengguna & Admin | `PRIMARY KEY(id)`, `UNIQUE(username, email)` |
| 2 | `user_settings` | Preferensi Dark/Light mode & Notifikasi | `FK -> users(id)` |
| 3 | `cpus` | Master Data Processor (Score, Cores, GHz) | `PRIMARY KEY(id)` |
| 4 | `gpus` | Master Data Kartu Grafis (Score, VRAM) | `PRIMARY KEY(id)` |
| 5 | `user_specs` | Spesifikasi Laptop Terimpan Pengguna | `FK -> users, cpus, gpus` |
| 6 | `genres` | Master 13 Genre Game | `PRIMARY KEY(id)`, `UNIQUE(slug)` |
| 7 | `games` | Data Utama Game Digital & Harga | `PRIMARY KEY(id)`, `FULLTEXT(title, dev)` |
| 8 | `game_genres` | Relasi M:N Game dan Genre | `FK -> games(id), genres(id)` |
| 9 | `game_screenshots` | Galeri Screenshot Detail Game | `FK -> games(id)` |
| 10 | `game_tags` | Tagar Pencarian Game | `FK -> games(id)` |
| 11 | `game_requirements` | Requirement Minimum & Rekomendasi Laptop Game | `FK -> games, cpus(min/rec), gpus(min/rec)` |
| 12 | `wishlist` | Daftar Keinginan Game User | `FK -> users(id), games(id)` |
| 13 | `cart` | Sesi Keranjang Belanja | `FK -> users(id)` |
| 14 | `cart_items` | Item Game dalam Keranjang | `FK -> cart(id), games(id)` |
| 15 | `vouchers` | Kode Diskon & Kupon Promo | `PRIMARY KEY(id)`, `UNIQUE(code)` |
| 16 | `orders` | Header Transaksi Pembelian (`#AG20260814...`) | `FK -> users(id), vouchers(id)` |
| 17 | `order_items` | Detail Rincian Game per Order | `FK -> orders(id), games(id)` |
| 18 | `payments` | Log Status Pembayaran & Payment Gateway | `FK -> orders(id)` |
| 19 | `game_library` | Koleksi Game Milik User yang Sudah Dibeli | `FK -> users(id), games(id), orders(id)` |
| 20 | `reviews` | Rating (1-5 ⭐) & Ulasan Resmi Pembeli | `FK -> users(id), games(id)` |
| 21 | `promotions` | Event Campaign Promo Berdurasi | `PRIMARY KEY(id)` |
| 22 | `promotion_games` | Relasi Game yang Masuk Campaign Promo | `FK -> promotions(id), games(id)` |
| 23 | `notifications` | Sistem Notifikasi User | `FK -> users(id)` |
| 24 | `recently_viewed` | History Game Terakhir Dilihat User | `FK -> users(id), games(id)` |

---

## 💻 COMPATIBILITY CHECKER ENGINE FORMULA (SKEMA DATA)

Tabel `cpus` dan `gpus` menyimpan kolom `performance_score`.
Di tabel `game_requirements`, setiap game dikaitkan dengan `min_cpu_id`, `rec_cpu_id`, `min_gpu_id`, `rec_gpu_id`, `min_ram_gb`, `rec_ram_gb`, `min_vram_gb`, `rec_vram_gb`, `min_storage_gb`, `storage_type`, dan `min_os`.

### Formula Kalkulasi Kompatibilitas (% Score):
1. **CPU Score Weight**: 30% (`(User_CPU_Score / Game_Rec_CPU_Score) * 30`)
2. **GPU Score Weight**: 40% (`(User_GPU_Score / Game_Rec_GPU_Score) * 40`)
3. **RAM Weight**: 15% (`(User_RAM / Game_Rec_RAM) * 15`)
4. **VRAM Weight**: 15% (`(User_VRAM / Game_Rec_VRAM) * 15`)

### Status Kompatibilitas:
- 🟢 **GAME COMPATIBLE** (Score >= 80% dan memenuhi min req): Perangkat mampu menjalankan game dengan lancar.
- 🟡 **PLAYABLE WITH LOWER SETTINGS** (Score 60% - 79%): Game dapat dimainkan pada grafik Low/Medium.
- 🔴 **NOT RECOMMENDED** (Score < 60% atau GPU/CPU di bawah minimum): Perangkat tidak disarankan.

---

## 🔌 KONEKSI DATABASE PHP (`config/database.php`)

Koneksi menggunakan **PDO PHP** dengan prepared statement standar industri:

```php
require_once __DIR__ . '/config/database.php';

// Contoh Query Fetch Game dengan Requirement & Hardware Score
$game = dbFetchOne("
    SELECT g.*, req.min_ram_gb, req.min_vram_gb, min_cpu.model_name AS min_cpu_name, min_gpu.model_name AS min_gpu_name
    FROM games g
    LEFT JOIN game_requirements req ON g.id = req.game_id
    LEFT JOIN cpus min_cpu ON req.min_cpu_id = min_cpu.id
    LEFT JOIN gpus min_gpu ON req.min_gpu_id = min_gpu.id
    WHERE g.slug = :slug
", ['slug' => 'cyberpunk-2077']);
```

---
*ASTROGAMES Database Schema v3.0 - Ready for Laragon MySQL Deployment.*
