<?php
/**
 * ASTROGAMES - Core Functions & Compatibility Engine
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Calculate Dynamic Base URL to prevent 404 errors anywhere (including /admin)
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Get root directory relative to script path
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = str_replace('\\', '/', dirname($scriptName));
    
    // Remove /admin or /api if inside those directories
    $dir = preg_replace('/\/admin.*$/', '', $dir);
    $dir = preg_replace('/\/api.*$/', '', $dir);
    $dir = rtrim($dir, '/');
    
    define('BASE_URL', $protocol . '://' . $host . $dir);
}

/**
 * Initialize Default User Session Specifications (Mock Laptop Spec)
 */
if (!isset($_SESSION['user_specs'])) {
    $_SESSION['user_specs'] = [
        'cpu_id' => 2, // Intel Core i5-12400F
        'ram_gb' => 16,
        'gpu_id' => 2, // NVIDIA GeForce RTX 3060
        'vram_gb' => 8,
        'storage_gb' => 512,
        'storage_type' => 'SSD',
        'os' => 'Windows 11'
    ];
}

/**
 * Initialize User Wishlist and Cart Sessions
 */
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['library'])) {
    $_SESSION['library'] = [1]; // Owned Cyberpunk 2077 by default
}

/**
 * Compatibility Engine Algorithm
 * Calculates compatibility score (0 - 100%) for a given laptop spec against a game's requirements.
 */
function calculateCompatibility(array $userSpec, array $game): array {
    // 1. CPU Score (30% weight)
    $cpuScore = 100;
    if (isset($game['min_cpu_tier'])) {
        $userCpuTier = getCpuTier($userSpec['cpu_id']);
        if ($userCpuTier < $game['min_cpu_tier']) {
            $cpuScore = max(40, 100 - (($game['min_cpu_tier'] - $userCpuTier) * 20));
        }
    }

    // 2. GPU & VRAM Score (40% weight)
    $gpuScore = 100;
    if (isset($game['min_gpu_tier'])) {
        $userGpuTier = getGpuTier($userSpec['gpu_id']);
        if ($userGpuTier < $game['min_gpu_tier']) {
            $gpuScore = max(35, 100 - (($game['min_gpu_tier'] - $userGpuTier) * 25));
        }
    }

    $minVram = $game['min_vram_gb'] ?? 4;
    $vramScore = ($userSpec['vram_gb'] >= $minVram) ? 100 : max(30, ($userSpec['vram_gb'] / $minVram) * 100);

    // 3. RAM Score (15% weight)
    $minRam = $game['min_ram_gb'] ?? 8;
    $ramScore = ($userSpec['ram_gb'] >= $minRam) ? 100 : max(20, ($userSpec['ram_gb'] / $minRam) * 100);

    // 4. Final Weighted Score Calculation
    $totalScore = round(($cpuScore * 0.30) + (($gpuScore * 0.7 + $vramScore * 0.3) * 0.40) + ($ramScore * 0.30));
    $totalScore = max(10, min(100, (int)$totalScore));

    // Determine Compatibility Badge & Status
    if ($totalScore >= 80) {
        $status = 'GREEN';
        $badgeText = '<i class="fas fa-check-circle me-1"></i> GAME COMPATIBLE';
        $statusDesc = 'Sangat Lancar! Spesifikasi laptop kamu sangat mumpuni untuk memainkan game ini di rata kanan / settingan tinggi.';
        $settings = ['resolution' => '1080p / 1440p', 'graphics' => 'High / Ultra', 'performance' => '60+ FPS'];
    } elseif ($totalScore >= 60) {
        $status = 'YELLOW';
        $badgeText = '<i class="fas fa-exclamation-triangle me-1"></i> PLAYABLE (LOW SETTINGS)';
        $statusDesc = 'Bisa Dimainkan! Kamu perlu menyesuaikan beberapa settingan grafis ke Medium / Low untuk performa stabil.';
        $settings = ['resolution' => '1080p / 720p', 'graphics' => 'Low / Medium', 'performance' => '30 - 50 FPS'];
    } else {
        $status = 'RED';
        $badgeText = '<i class="fas fa-times-circle me-1"></i> NOT RECOMMENDED';
        $statusDesc = 'Kurang Direkomendasikan. Perangkat kamu di bawah kebutuhan minimum, berisiko mengalami lag / patah-patah.';
        $settings = ['resolution' => '720p', 'graphics' => 'Very Low', 'performance' => '< 30 FPS'];
    }

    return [
        'score' => $totalScore,
        'status' => $status,
        'badgeText' => $badgeText,
        'statusDesc' => $statusDesc,
        'recommendedSettings' => $settings,
        'breakdown' => [
            'cpu' => ($cpuScore >= 80) ? 'GREEN' : (($cpuScore >= 50) ? 'YELLOW' : 'RED'),
            'gpu' => ($gpuScore >= 80) ? 'GREEN' : (($gpuScore >= 50) ? 'YELLOW' : 'RED'),
            'ram' => ($ramScore >= 80) ? 'GREEN' : (($ramScore >= 50) ? 'YELLOW' : 'RED'),
            'vram' => ($vramScore >= 80) ? 'GREEN' : (($vramScore >= 50) ? 'YELLOW' : 'RED'),
        ]
    ];
}

function getCpuTier(int $cpuId): int {
    $tiers = [1 => 2, 2 => 7, 3 => 9, 4 => 6, 5 => 8];
    return $tiers[$cpuId] ?? 5;
}

function getGpuTier(int $gpuId): int {
    $tiers = [1 => 2, 2 => 7, 3 => 9, 4 => 5, 5 => 6];
    return $tiers[$gpuId] ?? 5;
}

/**
 * Format Currency to Indonesian Rupiah (Rp)
 */
function formatRupiah($number): string {
    if ($number == 0) return 'GRATIS';
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

/**
 * Fallback / DB Master Games Repository
 */
function getAllGames(): array {
    $sql = "
        SELECT g.*, 
               gr.min_ram_gb, gr.min_vram_gb, gr.min_storage_gb, gr.min_cpu_id, gr.min_gpu_id,
               c.model_name AS min_cpu,
               gp.model_name AS min_gpu,
               c.performance_score AS min_cpu_tier,
               gp.performance_score AS min_gpu_tier,
               (SELECT GROUP_CONCAT(gn.name SEPARATOR ', ') 
                FROM game_genres gg 
                JOIN genres gn ON gg.genre_id = gn.id 
                WHERE gg.game_id = g.id) AS genre_names
        FROM games g 
        LEFT JOIN game_requirements gr ON g.id = gr.game_id
        LEFT JOIN cpus c ON gr.min_cpu_id = c.id
        LEFT JOIN gpus gp ON gr.min_gpu_id = gp.id
        ORDER BY g.id ASC
    ";
    $dbGames = dbFetchAll($sql);
    if (!empty($dbGames)) {
        foreach ($dbGames as &$g) {
            $g['genres'] = explode(', ', $g['genre_names'] ?? 'Action, Adventure');
            $g['cover_image'] = $g['cover_image'] ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80';
            $g['banner_image'] = $g['banner_image'] ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80';
            $g['discount_percentage'] = (int)($g['discount_percentage'] ?? 0);
        }
        return $dbGames;
    }

    return getFallbackGames();
}

function getGameByIdOrSlug($idOrSlug): ?array {
    $sql = "
        SELECT g.*, 
               gr.min_ram_gb, gr.min_vram_gb, gr.min_storage_gb, gr.min_cpu_id, gr.min_gpu_id,
               c.model_name AS min_cpu,
               gp.model_name AS min_gpu,
               c.performance_score AS min_cpu_tier,
               gp.performance_score AS min_gpu_tier,
               (SELECT GROUP_CONCAT(gn.name SEPARATOR ', ') 
                FROM game_genres gg 
                JOIN genres gn ON gg.genre_id = gn.id 
                WHERE gg.game_id = g.id) AS genre_names
        FROM games g
        LEFT JOIN game_requirements gr ON g.id = gr.game_id
        LEFT JOIN cpus c ON gr.min_cpu_id = c.id
        LEFT JOIN gpus gp ON gr.min_gpu_id = gp.id
    ";
    if (is_numeric($idOrSlug)) {
        $g = dbFetchOne("$sql WHERE g.id = ?", [(int)$idOrSlug]);
    } else {
        $g = dbFetchOne("$sql WHERE g.slug = ?", [$idOrSlug]);
    }

    if ($g) {
        $g['genres'] = explode(', ', $g['genre_names'] ?? 'Action, Adventure');
        $g['cover_image'] = $g['cover_image'] ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80';
        $g['banner_image'] = $g['banner_image'] ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80';
        $g['discount_percentage'] = (int)($g['discount_percentage'] ?? 0);
        return $g;
    }

    $all = getFallbackGames();
    foreach ($all as $item) {
        if ($item['id'] == $idOrSlug || $item['slug'] == $idOrSlug) {
            return $item;
        }
    }
    return $all[0] ?? null;
}

function getFallbackGames(): array {
    return [
        [
            'id' => 1,
            'slug' => 'cyberpunk-2077',
            'title' => 'Cyberpunk 2077',
            'developer' => 'CD PROJEKT RED',
            'publisher' => 'CD PROJEKT RED',
            'release_date' => '2020-12-10',
            'price' => 699999,
            'discount_percentage' => 40,
            'rating' => 4.6,
            'genres' => ['Action', 'RPG', 'Open World'],
            'short_description' => 'Cyberpunk 2077 adalah game action-adventure open-world berlatar di megacity Night City yang penuh akan kekuatan, glamour, dan modifikasi tubuh cybernetic.',
            'description' => "Cyberpunk 2077 adalah cerita RPG aksi berlatar di Night City, sebuah megalopolis yang terobsesi dengan kekuasaan, pesona, dan modifikasi tubuh.\n\nAnda bermain sebagai V, seorang tentara bayaran cyber-enhanced yang mengincar salah satu impian paling berharga: chip implan rahasia yang merupakan kunci keabadian.",
            'cover_image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80',
            'banner_image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80',
            'video_url' => 'https://www.youtube.com/embed/8X2kIfS6fb8',
            'min_cpu' => 'Intel Core i5-10400F / AMD Ryzen 5 3600',
            'min_ram_gb' => 12,
            'min_gpu' => 'NVIDIA GTX 1060 (6GB) / AMD Radeon RX 590',
            'min_vram_gb' => 6,
            'min_storage_gb' => 70,
            'storage_type' => 'SSD',
            'rec_cpu' => 'Intel Core i7-12700K / AMD Ryzen 7 7800X3D',
            'rec_ram_gb' => 16,
            'rec_gpu' => 'NVIDIA RTX 3070 (8GB) / AMD RX 6800 XT',
            'rec_vram_gb' => 8,
            'min_cpu_tier' => 7,
            'min_gpu_tier' => 7
        ],
        [
            'id' => 2,
            'slug' => 'gta-v',
            'title' => 'Grand Theft Auto V: Premium Edition',
            'developer' => 'Rockstar North',
            'publisher' => 'Rockstar Games',
            'release_date' => '2015-04-14',
            'price' => 401000,
            'discount_percentage' => 50,
            'rating' => 4.9,
            'genres' => ['Action', 'Open World', 'Multiplayer'],
            'short_description' => 'Jelajahi dunia Los Santos dan Blaine County dalam pengalaman open-world terbaik dan akses ke GTA Online.',
            'description' => 'Ketika seorang penipu muda, perampok bank kawakan, dan psikopat menakutkan mendapati diri mereka terjerat dalam masalah besar di dunia kriminal bawah tanah.',
            'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
            'banner_image' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=1200&q=80',
            'video_url' => 'https://www.youtube.com/embed/QkkoHAzjnUs',
            'min_cpu' => 'Intel Core i5 3470 / AMD FX-8350',
            'min_ram_gb' => 8,
            'min_gpu' => 'NVIDIA GTX 660 / AMD HD 7870',
            'min_vram_gb' => 2,
            'min_storage_gb' => 90,
            'storage_type' => 'HDD/SSD',
            'rec_cpu' => 'Intel Core i5-10400F',
            'rec_ram_gb' => 16,
            'rec_gpu' => 'NVIDIA GTX 1660 Super',
            'rec_vram_gb' => 6,
            'min_cpu_tier' => 4,
            'min_gpu_tier' => 4
        ],
        [
            'id' => 3,
            'slug' => 'valorant-points',
            'title' => 'Valorant (Points Pack - Deluxe)',
            'developer' => 'Riot Games',
            'publisher' => 'Riot Games',
            'release_date' => '2020-06-02',
            'price' => 0,
            'discount_percentage' => 0,
            'rating' => 4.8,
            'genres' => ['FPS', 'Multiplayer', 'Strategy'],
            'short_description' => 'Game tactical shooter 5v5 berbasis karakter di mana keahlian menembak tajam berpadu dengan kemampuan agen unik.',
            'description' => 'VALORANT adalah game tactical shooter 5v5 berbasis karakter di mana keahlian tembak menembak dipadukan dengan kemampuan taktis unik.',
            'cover_image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80',
            'banner_image' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1200&q=80',
            'video_url' => 'https://www.youtube.com/embed/e_E9W2vsRbA',
            'min_cpu' => 'Intel Core 2 Duo E8400',
            'min_ram_gb' => 4,
            'min_gpu' => 'Intel HD 4000',
            'min_vram_gb' => 1,
            'min_storage_gb' => 20,
            'storage_type' => 'HDD',
            'rec_cpu' => 'Intel Core i5-9400F',
            'rec_ram_gb' => 8,
            'rec_gpu' => 'NVIDIA GTX 1050 Ti',
            'rec_vram_gb' => 4,
            'min_cpu_tier' => 2,
            'min_gpu_tier' => 2
        ],
        [
            'id' => 4,
            'slug' => 'forza-horizon-5',
            'title' => 'Forza Horizon 5',
            'developer' => 'Playground Games',
            'publisher' => 'Xbox Game Studios',
            'release_date' => '2021-11-09',
            'price' => 799000,
            'discount_percentage' => 35,
            'rating' => 4.9,
            'genres' => ['Racing', 'Open World', 'Sports'],
            'short_description' => 'Petualangan Horizon Terhebat menanti! Jelajahi lanskap dunia terbuka Meksiko yang semarak dan terus berkembang.',
            'description' => 'Forza Horizon 5 menyajikan balapan dunia terbuka terbaik dengan ratusan mobil terbaik dunia di lanskap Meksiko yang menakjubkan.',
            'cover_image' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=600&q=80',
            'banner_image' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1200&q=80',
            'video_url' => 'https://www.youtube.com/embed/FYH9n37B7Yw',
            'min_cpu' => 'Intel Core i5-4460 / AMD Ryzen 3 1200',
            'min_ram_gb' => 8,
            'min_gpu' => 'NVIDIA GTX 970 / AMD RX 470',
            'min_vram_gb' => 4,
            'min_storage_gb' => 110,
            'storage_type' => 'SSD',
            'rec_cpu' => 'Intel Core i7-10700K',
            'rec_ram_gb' => 16,
            'rec_gpu' => 'NVIDIA RTX 3060 Ti',
            'rec_vram_gb' => 8,
            'min_cpu_tier' => 5,
            'min_gpu_tier' => 6
        ]
    ];
}

function getMasterCPUs(): array {
    $dbCpus = dbFetchAll("SELECT id, model_name AS name, performance_score AS tier FROM cpus ORDER BY performance_score DESC, model_name ASC");
    if (!empty($dbCpus)) {
        return $dbCpus;
    }
    
    return [
        ['id' => 1, 'name' => 'Intel Core i3-10100F / Equivalent', 'tier' => 3],
        ['id' => 2, 'name' => 'Intel Core i5-12400F / AMD Ryzen 5 5600', 'tier' => 7],
        ['id' => 3, 'name' => 'Intel Core i7-13700K / AMD Ryzen 7 7800X3D', 'tier' => 9],
        ['id' => 4, 'name' => 'Intel Core i5-8400 / AMD Ryzen 5 2600', 'tier' => 5],
        ['id' => 5, 'name' => 'Intel Core i9-13900K / AMD Ryzen 9 7950X', 'tier' => 10]
    ];
}

function getMasterGPUs(): array {
    $dbGpus = dbFetchAll("SELECT id, model_name AS name, performance_score AS tier FROM gpus ORDER BY performance_score DESC, model_name ASC");
    if (!empty($dbGpus)) {
        return $dbGpus;
    }
    
    return [
        ['id' => 1, 'name' => 'Intel UHD / Iris Xe Graphics (Integrated)', 'tier' => 2],
        ['id' => 2, 'name' => 'NVIDIA GeForce RTX 3060 (8GB/12GB)', 'tier' => 7],
        ['id' => 3, 'name' => 'NVIDIA GeForce RTX 4070 / RTX 4080', 'tier' => 9],
        ['id' => 4, 'name' => 'NVIDIA GeForce GTX 1650 / GTX 1050 Ti', 'tier' => 4],
        ['id' => 5, 'name' => 'AMD Radeon RX 6600 (8GB)', 'tier' => 6]
    ];
}

function getMasterGenres(): array {
    return [
        ['id' => 1, 'name' => 'Action', 'slug' => 'action', 'icon' => 'fa-crosshairs'],
        ['id' => 2, 'name' => 'Adventure', 'slug' => 'adventure', 'icon' => 'fa-compass'],
        ['id' => 3, 'name' => 'RPG', 'slug' => 'rpg', 'icon' => 'fa-dragon'],
        ['id' => 4, 'name' => 'FPS', 'slug' => 'fps', 'icon' => 'fa-bullseye'],
        ['id' => 5, 'name' => 'Horror', 'slug' => 'horror', 'icon' => 'fa-ghost'],
        ['id' => 6, 'name' => 'Racing', 'slug' => 'racing', 'icon' => 'fa-car'],
        ['id' => 7, 'name' => 'Sports', 'slug' => 'sports', 'icon' => 'fa-futbol'],
        ['id' => 8, 'name' => 'Strategy', 'slug' => 'strategy', 'icon' => 'fa-chess-king'],
        ['id' => 9, 'name' => 'Open World', 'slug' => 'open-world', 'icon' => 'fa-globe']
    ];
}

function getYoutubeEmbedUrl($url) {
    if (empty($url)) return "";
    $parsed = parse_url($url);
    if (isset($parsed["host"])) {
        if (strpos($parsed["host"], "youtube.com") !== false) {
            parse_str($parsed["query"] ?? "", $query);
            if (isset($query["v"])) {
                return "https://www.youtube.com/embed/" . $query["v"];
            }
        } elseif (strpos($parsed["host"], "youtu.be") !== false) {
            $path = trim($parsed["path"] ?? "", "/");
            if (!empty($path)) {
                return "https://www.youtube.com/embed/" . $path;
            }
        }
    }
    // Return original url if not a known youtube format (or if it is already an embed url)
    return $url;
}

