<?php
// admin/tambah-game.php - Form Tambah Game Baru & System Requirements
$page_title = "Tambah Game Baru - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name']);
    $slug             = trim($_POST['slug']);
    $description      = trim($_POST['description']);
    $genre            = trim($_POST['genre']);
    $developer        = trim($_POST['developer']);
    $publisher        = trim($_POST['publisher']);
    $release_date     = trim($_POST['release_date']);
    $platform         = trim($_POST['platform']);
    $cover            = trim($_POST['cover']);
    $price            = (float)$_POST['price'];
    $difficulty       = trim($_POST['difficulty']);

    $minimum_cpu      = trim($_POST['minimum_cpu']);
    $minimum_ram      = (int)$_POST['minimum_ram'];
    $minimum_gpu      = trim($_POST['minimum_gpu']);
    $minimum_vram     = (int)$_POST['minimum_vram'];
    $minimum_os       = trim($_POST['minimum_os']);

    $recommended_cpu  = trim($_POST['recommended_cpu']);
    $recommended_ram  = (int)$_POST['recommended_ram'];
    $recommended_gpu  = trim($_POST['recommended_gpu']);
    $recommended_vram = (int)$_POST['recommended_vram'];
    $recommended_os   = trim($_POST['recommended_os']);

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    if (empty($cover)) {
        $cover = 'default_game.jpg';
    }

    $stmt = $pdo->prepare("INSERT INTO games (name, slug, description, genre, developer, publisher, release_date, platform, cover, price, difficulty, 
                                             minimum_cpu, minimum_ram, minimum_gpu, minimum_vram, minimum_os, 
                                             recommended_cpu, recommended_ram, recommended_gpu, recommended_vram, recommended_os) 
                           VALUES (:name, :slug, :desc, :genre, :dev, :pub, :rdate, :plat, :cover, :price, :diff, 
                                   :min_cpu, :min_ram, :min_gpu, :min_vram, :min_os, 
                                   :rec_cpu, :rec_ram, :rec_gpu, :rec_vram, :rec_os)");
    $stmt->execute([
        ':name' => $name, ':slug' => $slug, ':desc' => $description, ':genre' => $genre,
        ':dev' => $developer, ':pub' => $publisher, ':rdate' => $release_date, ':plat' => $platform,
        ':cover' => $cover, ':price' => $price, ':diff' => $difficulty,
        ':min_cpu' => $minimum_cpu, ':min_ram' => $minimum_ram, ':min_gpu' => $minimum_gpu, ':min_vram' => $minimum_vram, ':min_os' => $minimum_os,
        ':rec_cpu' => $recommended_cpu, ':rec_ram' => $recommended_ram, ':rec_gpu' => $recommended_gpu, ':rec_vram' => $recommended_vram, ':rec_os' => $recommended_os
    ]);

    $_SESSION['flash_success'] = "Game " . htmlspecialchars($name) . " berhasil ditambahkan!";
    header("Location: " . BASE_URL . "/admin/games.php");
    exit();
}
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-plus-circle text-cyan me-2"></i> Tambah Game Baru</h1>
    <a href="<?= BASE_URL; ?>/admin/games.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
  </div>

  <div class="card-custom p-4 p-md-5">
    <form method="POST" action="<?= BASE_URL; ?>/admin/tambah-game.php">
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Nama Game *</label>
          <input type="text" name="name" class="form-control form-control-custom" required>
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Slug URL (Opsional)</label>
          <input type="text" name="slug" class="form-control form-control-custom" placeholder="gta-v">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Genre *</label>
          <input type="text" name="genre" class="form-control form-control-custom" placeholder="FPS / Action" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Tingkat Spek (Difficulty) *</label>
          <select name="difficulty" class="form-select form-select-custom" required>
            <option value="Ringan">Ringan</option>
            <option value="Menengah" selected>Menengah</option>
            <option value="Berat">Berat</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Harga (Rp) *</label>
          <input type="number" name="price" class="form-control form-control-custom" value="0" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Developer</label>
          <input type="text" name="developer" class="form-control form-control-custom" value="Riot Games">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Publisher</label>
          <input type="text" name="publisher" class="form-control form-control-custom" value="Riot Games">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Tanggal Rilis</label>
          <input type="date" name="release_date" class="form-control form-control-custom" value="<?= date('Y-m-d'); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Platform</label>
          <input type="text" name="platform" class="form-control form-control-custom" value="PC (Windows)">
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Nama File Gambar Cover</label>
          <input type="text" name="cover" class="form-control form-control-custom" placeholder="valorant.jpg">
        </div>
        <div class="col-12">
          <label class="form-label text-white fw-bold">Deskripsi Game *</label>
          <textarea name="description" class="form-control form-control-custom" rows="3" required></textarea>
        </div>
      </div>

      <!-- Minimum Requirements -->
      <h4 class="text-warning fw-bold mb-3"><i class="bi bi-shield-exclamation me-1"></i> Spesifikasi Minimum (Minimum Requirements)</h4>
      <div class="row g-3 mb-4 p-3 bg-dark rounded-3 border border-secondary">
        <div class="col-md-6">
          <label class="form-label text-white small">Minimum CPU *</label>
          <input type="text" name="minimum_cpu" class="form-control form-control-custom" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Minimum RAM (GB) *</label>
          <input type="number" name="minimum_ram" class="form-control form-control-custom" value="4" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Minimum VRAM (GB) *</label>
          <input type="number" name="minimum_vram" class="form-control form-control-custom" value="1" required>
        </div>
        <div class="col-md-8">
          <label class="form-label text-white small">Minimum GPU *</label>
          <input type="text" name="minimum_gpu" class="form-control form-control-custom" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white small">Minimum OS *</label>
          <input type="text" name="minimum_os" class="form-control form-control-custom" value="Windows 10" required>
        </div>
      </div>

      <!-- Recommended Requirements -->
      <h4 class="text-success fw-bold mb-3"><i class="bi bi-check-circle-fill me-1"></i> Spesifikasi Rekomendasi (Recommended Requirements)</h4>
      <div class="row g-3 mb-4 p-3 bg-dark rounded-3 border border-secondary">
        <div class="col-md-6">
          <label class="form-label text-white small">Recommended CPU *</label>
          <input type="text" name="recommended_cpu" class="form-control form-control-custom" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Recommended RAM (GB) *</label>
          <input type="number" name="recommended_ram" class="form-control form-control-custom" value="8" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Recommended VRAM (GB) *</label>
          <input type="number" name="recommended_vram" class="form-control form-control-custom" value="2" required>
        </div>
        <div class="col-md-8">
          <label class="form-label text-white small">Recommended GPU *</label>
          <input type="text" name="recommended_gpu" class="form-control form-control-custom" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white small">Recommended OS *</label>
          <input type="text" name="recommended_os" class="form-control form-control-custom" value="Windows 10 / 11" required>
        </div>
      </div>

      <button type="submit" class="btn btn-cyan btn-lg px-5 fw-bold">Simpan Game Baru</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
