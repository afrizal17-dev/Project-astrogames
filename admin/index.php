<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$msg = '';
$msgType = 'success';

// Handle Add Game POST Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_game') {
    $title = trim($_POST['title'] ?? '');
    $developer = trim($_POST['developer'] ?? 'Astro Studios');
    $price = (float)($_POST['price'] ?? 0);
    $discount = (int)($_POST['discount'] ?? 0);
    $rating = (float)($_POST['rating'] ?? 4.8);
    $minCpuId = (int)($_POST['min_cpu_id'] ?? 3);
    $minGpuId = (int)($_POST['min_gpu_id'] ?? 4);
    $minRam = (int)($_POST['min_ram'] ?? 8);
    $minVram = (float)($_POST['min_vram'] ?? 4);
    $cover = trim($_POST['cover_image'] ?? '');
    $banner = trim($_POST['banner_image'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');

    if (empty($cover)) {
        $cover = 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80';
    }
    if (empty($banner)) {
        $banner = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80';
    }

    try {
        dbBegin();

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        // 1. Insert into games table
        $sql = "INSERT INTO games (title, slug, developer, publisher, release_date, price, discount_percentage, rating, cover_image, banner_image, video_url, description, short_description) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, '', '')";
        $params = [$title, $slug, $developer, $developer, $price, $discount, $rating, $cover, $banner, $videoUrl];
        dbExecute($sql, $params);
        
        $gameId = dbFetchOne("SELECT LAST_INSERT_ID() as id")['id'];

        // 2. Insert genres
        $genresInput = $_POST['genres'] ?? [];
        $genreList = is_array($genresInput) ? $genresInput : array_map('trim', explode(',', $genresInput));
        
        foreach ($genreList as $gName) {
            $gName = trim($gName);
            if (empty($gName)) continue;
            $gRow = dbFetchOne("SELECT id FROM genres WHERE name = ?", [$gName]);
            if ($gRow) {
                dbExecute("INSERT INTO game_genres (game_id, genre_id) VALUES (?, ?)", [$gameId, $gRow['id']]);
            }
        }

        // 3. Insert requirements (using real CPU/GPU IDs)
        dbExecute("INSERT INTO game_requirements (game_id, min_cpu_id, rec_cpu_id, min_ram_gb, rec_ram_gb, min_gpu_id, rec_gpu_id, min_vram_gb, rec_vram_gb, min_storage_gb) VALUES (?, ?, 4, ?, 16, ?, 6, ?, 8, 50)", [$gameId, $minCpuId, $minRam, $minGpuId, $minVram]);

        dbCommit();
        $msg = "Game '$title' berhasil ditambahkan!";
    } catch (Exception $e) {
        dbRollback();
        $msg = "Gagal menambahkan game: " . $e->getMessage();
        $msgType = 'danger';
    }
}

// Handle Edit Game POST Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_game') {
    error_log("EDIT GAME TRIGGERED! POST DATA: " . print_r($_POST, true));
    $gameId = (int)$_POST['game_id'];
    $title = trim($_POST['title'] ?? '');
    $developer = trim($_POST['developer'] ?? 'Astro Studios');
    $price = (float)($_POST['price'] ?? 0);
    $discount = (int)($_POST['discount'] ?? 0);
    $minCpuId = (int)($_POST['min_cpu_id'] ?? 3);
    $minGpuId = (int)($_POST['min_gpu_id'] ?? 4);
    $minRam = (int)($_POST['min_ram'] ?? 8);
    $minVram = (float)($_POST['min_vram'] ?? 4);
    $cover = trim($_POST['cover_image'] ?? '');
    $banner = trim($_POST['banner_image'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');

    if (empty($cover)) $cover = 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80';
    if (empty($banner)) $banner = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80';

    try {
        dbBegin();

        // 1. Update games table
        $sql = "UPDATE games SET title=?, developer=?, publisher=?, price=?, discount_percentage=?, rating=?, cover_image=?, banner_image=?, video_url=? WHERE id=?";
        dbExecute($sql, [$title, $developer, $developer, $price, $discount, $rating, $cover, $banner, $videoUrl, $gameId]);
        
        // 2. Update genres
        dbExecute("DELETE FROM game_genres WHERE game_id=?", [$gameId]);
        
        $genresInput = $_POST['genres'] ?? [];
        $genreList = is_array($genresInput) ? $genresInput : array_map('trim', explode(',', $genresInput));
        
        foreach ($genreList as $gName) {
            $gName = trim($gName);
            if (empty($gName)) continue;
            $gRow = dbFetchOne("SELECT id FROM genres WHERE name = ?", [$gName]);
            if ($gRow) {
                dbExecute("INSERT INTO game_genres (game_id, genre_id) VALUES (?, ?)", [$gameId, $gRow['id']]);
            }
        }

        // 3. Update or Insert requirements
        $reqCheck = dbFetchOne("SELECT id FROM game_requirements WHERE game_id=?", [$gameId]);
        if ($reqCheck) {
            dbExecute("UPDATE game_requirements SET min_cpu_id=?, min_gpu_id=?, min_ram_gb=?, min_vram_gb=? WHERE game_id=?", [$minCpuId, $minGpuId, $minRam, $minVram, $gameId]);
        } else {
            dbExecute("INSERT INTO game_requirements (game_id, min_cpu_id, rec_cpu_id, min_ram_gb, rec_ram_gb, min_gpu_id, rec_gpu_id, min_vram_gb, rec_vram_gb, min_storage_gb) VALUES (?, ?, 4, ?, 16, ?, 6, ?, 8, 50)", [$gameId, $minCpuId, $minRam, $minGpuId, $minVram]);
        }

        dbCommit();
        $msg = "Game '$title' berhasil diperbarui!";
        error_log("EDIT GAME SUCCESS: $msg");
    } catch (Exception $e) {
        dbRollback();
        $msg = "Gagal memperbarui game: " . $e->getMessage();
        $msgType = 'danger';
        error_log("EDIT GAME ERROR: " . $e->getMessage() . " TRACE: " . $e->getTraceAsString());
    }
}

// Handle Delete Game GET Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    dbExecute("DELETE FROM games WHERE id = ?", [$deleteId]);
    $msg = "Game dengan ID #$deleteId berhasil dihapus dari Database!";
    $msgType = 'warning';
}

$allGames = getAllGames();
$masterGenres = getMasterGenres();
$masterCpus = dbFetchAll("SELECT id, model_name FROM cpus ORDER BY performance_score DESC, model_name ASC");
if (empty($masterCpus)) {
    $masterCpus = array_map(function($c) { return ['id' => $c['id'], 'model_name' => $c['name']]; }, getMasterCPUs());
}
$masterGpus = dbFetchAll("SELECT id, model_name FROM gpus ORDER BY performance_score DESC, model_name ASC");
if (empty($masterGpus)) {
    $masterGpus = array_map(function($g) { return ['id' => $g['id'], 'model_name' => $g['name']]; }, getMasterGPUs());
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="container py-4">

    <!-- BREADCRUMB & ADMIN TITLE -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="badge bg-purple px-3 py-2 mb-2"><i class="fas fa-shield-alt me-1"></i> Mode Administrator ASTROGAMES</div>
            <h1 class="display-6 text-white fw-bold mb-1"><i class="fas fa-user-shield text-purple me-2"></i> ADMIN DASHBOARD</h1>
            <p class="text-muted mb-0">Panel kontrol administrator untuk mengelola katalog game, spesifikasi requirement, dan pesanan.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-purple btn-sm"><i class="fas fa-external-link-alt me-1"></i> Lihat Website Frontend</a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msgType; ?> bg-card border border-purple text-white p-3 mb-4 rounded-4 text-center fw-bold shadow-lg">
            <i class="fas fa-info-circle me-2"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- STATS CARDS (DARK PURPLE PANELS - NO WHITE BOXES) -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card bg-card border border-purple p-3 text-center shadow-lg">
                <div class="display-6 text-purple fw-bold mb-1">1,248</div>
                <div class="small text-muted">Total Registered Users</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-card border border-purple p-3 text-center shadow-lg">
                <div class="display-6 text-white fw-bold mb-1"><?php echo count($allGames); ?></div>
                <div class="small text-muted">Total Games Catalog</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-card border border-purple p-3 text-center shadow-lg">
                <div class="display-6 text-warning fw-bold mb-1">482</div>
                <div class="small text-muted">Total Orders Completed</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-card border border-purple p-3 text-center shadow-lg">
                <div class="display-6 text-success fw-bold mb-1">Rp 184.2M</div>
                <div class="small text-muted">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- ADMIN MANAGEMENT TABLE CARD (DARK PURPLE - NO WHITE BOXES) -->
    <div class="card bg-card border border-purple p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white fw-bold mb-0"><i class="fas fa-list text-purple me-2"></i> Kelola Game & Requirement Spesifikasi Laptop</h5>
            <!-- WORKING BUTTON OPENING MODAL -->
            <button class="btn btn-purple btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addGameModal">
                <i class="fas fa-plus-circle me-1"></i> + Tambah Game Baru
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-purple border-bottom border-purple">
                        <th>Cover</th>
                        <th>Judul Game</th>
                        <th>Developer</th>
                        <th>Harga (Rp)</th>
                        <th>Min RAM / VRAM</th>
                        <th>Min CPU / GPU Requirement</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allGames as $g): ?>
                    <tr>
                        <td><img src="<?php echo $g['cover_image']; ?>" alt="<?php echo htmlspecialchars($g['title']); ?>" style="width: 45px; height: 55px; object-fit: cover; border-radius: 6px;" class="border border-purple"></td>
                        <td class="fw-bold text-white"><?php echo htmlspecialchars($g['title']); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($g['developer'] ?? 'Publisher'); ?></td>
                        <td class="fw-bold text-purple"><?php echo formatRupiah($g['price'] * (1 - ($g['discount_percentage'] ?? 0)/100)); ?></td>
                        <?php 
                            $vramText = ($g['min_vram_gb'] < 1) ? ($g['min_vram_gb'] * 1024) . ' MB' : $g['min_vram_gb'] . ' GB';
                        ?>
                        <td class="small text-light"><?php echo $g['min_ram_gb'] ?? 8; ?>GB RAM / <?php echo $vramText; ?> VRAM</td>
                        <td class="small text-muted"><?php echo htmlspecialchars($g['min_cpu'] ?? 'Core i5'); ?> • <?php echo htmlspecialchars($g['min_gpu'] ?? 'GTX 1650'); ?></td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=<?php echo $g['slug']; ?>" class="btn-action view" title="Lihat Game"><i class="fas fa-external-link-alt"></i></a>
                                <button class="btn-action edit btn-edit-game" 
                                    data-id="<?php echo $g['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($g['title']); ?>"
                                    data-developer="<?php echo htmlspecialchars($g['developer'] ?? 'Publisher'); ?>"
                                    data-price="<?php echo $g['price']; ?>"
                                    data-discount="<?php echo $g['discount_percentage']; ?>"
                                    data-rating="<?php echo $g['rating']; ?>"
                                    data-genres="<?php echo is_array($g['genres']) ? htmlspecialchars(implode(', ', $g['genres'])) : htmlspecialchars($g['genre_names'] ?? ''); ?>"
                                    data-cpu="<?php echo $g['min_cpu_id'] ?? ''; ?>"
                                    data-gpu="<?php echo $g['min_gpu_id'] ?? ''; ?>"
                                    data-ram="<?php echo $g['min_ram_gb'] ?? 8; ?>"
                                    data-vram="<?php echo $g['min_vram_gb'] ?? 4; ?>"
                                    data-cover="<?php echo htmlspecialchars($g['cover_image']); ?>"
                                    data-banner="<?php echo htmlspecialchars($g['banner_image']); ?>"
                                    data-video="<?php echo htmlspecialchars($g['video_url'] ?? ''); ?>"
                                    title="Edit Game" data-bs-toggle="modal" data-bs-target="#editGameModal">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php?action=delete&id=<?php echo $g['id']; ?>" class="btn-action delete" title="Hapus Game" onclick="return confirm('Apakah Anda yakin ingin menghapus game <?php echo htmlspecialchars($g['title']); ?>?');"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- MODAL FORM TAMBAH GAME BARU (REAL WORKING FORM) -->
<div class="modal fade" id="addGameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-card border border-purple text-white p-2">
            <div class="modal-header border-purple">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-plus-circle text-purple me-2"></i> Tambah Game Baru Ke Katalog</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/index.php">
                <input type="hidden" name="action" value="add_game">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="astro-label">Judul Game</label>
                            <input type="text" name="title" class="astro-input" placeholder="Contoh: Spider-Man Remastered" required>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Developer / Publisher</label>
                            <input type="text" name="developer" class="astro-input" placeholder="Contoh: Insomniac Games" required>
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Harga (Rp)</label>
                            <input type="number" name="price" class="astro-input" placeholder="799000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Diskon (%)</label>
                            <input type="number" name="discount" class="astro-input" placeholder="20" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Rating (1-5)</label>
                            <input type="text" name="rating" class="astro-input" placeholder="4.9" value="4.8">
                        </div>
                        <div class="col-md-12">
                            <label class="astro-label mb-2">Pilih Genre Game</label>
                            <div class="row g-2">
                                <?php foreach ($masterGenres as $gen): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input border-purple" type="checkbox" name="genres[]" value="<?php echo htmlspecialchars($gen['name']); ?>" id="add_genre_<?php echo $gen['id']; ?>">
                                        <label class="form-check-label text-light small" for="add_genre_<?php echo $gen['id']; ?>">
                                            <?php echo htmlspecialchars($gen['name']); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum CPU Requirement</label>
                            <select name="min_cpu_id" class="astro-select" required>
                                <option value="">-- Pilih CPU --</option>
                                <?php foreach ($masterCpus as $cpu): ?>
                                    <option value="<?php echo $cpu['id']; ?>"><?php echo htmlspecialchars($cpu['model_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum GPU Requirement</label>
                            <select name="min_gpu_id" class="astro-select" required>
                                <option value="">-- Pilih GPU --</option>
                                <?php foreach ($masterGpus as $gpu): ?>
                                    <option value="<?php echo $gpu['id']; ?>"><?php echo htmlspecialchars($gpu['model_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum RAM (GB)</label>
                            <select name="min_ram" class="astro-select">
                                <option value="4">4 GB</option>
                                <option value="8" selected>8 GB</option>
                                <option value="12">12 GB</option>
                                <option value="16">16 GB</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum VRAM</label>
                            <select name="min_vram" class="astro-select">
                                <option value="0.0625">64 MB</option>
                                <option value="0.125">128 MB</option>
                                <option value="0.25">256 MB</option>
                                <option value="0.5">512 MB</option>
                                <option value="1">1 GB</option>
                                <option value="2">2 GB</option>
                                <option value="4" selected>4 GB</option>
                                <option value="6">6 GB</option>
                                <option value="8">8 GB</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">URL Cover Image</label>
                            <input type="text" name="cover_image" class="astro-input" placeholder="https://..." value="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80">
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">URL Banner Image</label>
                            <input type="text" name="banner_image" class="astro-input" placeholder="https://..." value="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80">
                        </div>
                        <div class="col-md-12">
                            <label class="astro-label">URL Video Trailer (Opsional)</label>
                            <input type="text" name="video_url" class="astro-input" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-purple">
                    <button type="button" class="btn btn-outline-purple" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-purple px-4 fw-bold"><i class="fas fa-save me-1"></i> Simpan Game Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL FORM EDIT GAME -->
<div class="modal fade" id="editGameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-card border border-purple text-white p-2">
            <div class="modal-header border-purple">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-edit text-purple me-2"></i> Edit Data Game</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/index.php">
                <input type="hidden" name="action" value="edit_game">
                <input type="hidden" name="game_id" id="edit_game_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="astro-label">Judul Game</label>
                            <input type="text" name="title" id="edit_title" class="astro-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Developer / Publisher</label>
                            <input type="text" name="developer" id="edit_developer" class="astro-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Harga (Rp)</label>
                            <input type="number" name="price" id="edit_price" class="astro-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Diskon (%)</label>
                            <input type="number" name="discount" id="edit_discount" class="astro-input" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="astro-label">Rating (1-5)</label>
                            <input type="text" name="rating" id="edit_rating" class="astro-input" value="4.8">
                        </div>
                        <div class="col-md-12">
                            <label class="astro-label mb-2">Pilih Genre Game</label>
                            <div class="row g-2">
                                <?php foreach ($masterGenres as $gen): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input border-purple edit-genre-checkbox" type="checkbox" name="genres[]" value="<?php echo htmlspecialchars($gen['name']); ?>" id="edit_genre_<?php echo $gen['id']; ?>">
                                        <label class="form-check-label text-light small" for="edit_genre_<?php echo $gen['id']; ?>">
                                            <?php echo htmlspecialchars($gen['name']); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum CPU Requirement</label>
                            <select name="min_cpu_id" id="edit_cpu" class="astro-select" required>
                                <option value="">-- Pilih CPU --</option>
                                <?php foreach ($masterCpus as $cpu): ?>
                                    <option value="<?php echo $cpu['id']; ?>"><?php echo htmlspecialchars($cpu['model_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum GPU Requirement</label>
                            <select name="min_gpu_id" id="edit_gpu" class="astro-select" required>
                                <option value="">-- Pilih GPU --</option>
                                <?php foreach ($masterGpus as $gpu): ?>
                                    <option value="<?php echo $gpu['id']; ?>"><?php echo htmlspecialchars($gpu['model_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum RAM (GB)</label>
                            <select name="min_ram" id="edit_ram" class="astro-select">
                                <option value="4">4 GB</option>
                                <option value="8">8 GB</option>
                                <option value="12">12 GB</option>
                                <option value="16">16 GB</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">Minimum VRAM</label>
                            <select name="min_vram" id="edit_vram" class="astro-select">
                                <option value="0.0625">64 MB</option>
                                <option value="0.125">128 MB</option>
                                <option value="0.25">256 MB</option>
                                <option value="0.5">512 MB</option>
                                <option value="1">1 GB</option>
                                <option value="2">2 GB</option>
                                <option value="4">4 GB</option>
                                <option value="6">6 GB</option>
                                <option value="8">8 GB</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">URL Cover Image</label>
                            <input type="text" name="cover_image" id="edit_cover" class="astro-input" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="astro-label">URL Banner Image</label>
                            <input type="text" name="banner_image" id="edit_banner" class="astro-input" placeholder="https://...">
                        </div>
                        <div class="col-md-12">
                            <label class="astro-label">URL Video Trailer (Opsional)</label>
                            <input type="text" name="video_url" id="edit_video" class="astro-input" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-purple">
                    <button type="button" class="btn btn-outline-purple" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-purple px-4 fw-bold"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-game');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_game_id').value = this.dataset.id;
            document.getElementById('edit_title').value = this.dataset.title;
            document.getElementById('edit_developer').value = this.dataset.developer;
            document.getElementById('edit_price').value = this.dataset.price;
            document.getElementById('edit_discount').value = this.dataset.discount;
            document.getElementById('edit_rating').value = this.dataset.rating;
            document.getElementById('edit_cpu').value = this.dataset.cpu;
            document.getElementById('edit_gpu').value = this.dataset.gpu;
            document.getElementById('edit_ram').value = this.dataset.ram;
            document.getElementById('edit_vram').value = this.dataset.vram;
            document.getElementById('edit_cover').value = this.dataset.cover;
            document.getElementById('edit_banner').value = this.dataset.banner;
            document.getElementById('edit_video').value = this.dataset.video || '';
            
            // Check genres checkboxes
            const genreStr = this.dataset.genres || '';
            const genresArr = genreStr.split(',').map(g => g.trim());
            const checkboxes = document.querySelectorAll('.edit-genre-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = genresArr.includes(cb.value);
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
