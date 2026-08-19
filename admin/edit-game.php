<?php
// admin/edit-game.php - Edit Game & System Requirements
$page_title = "Edit Game - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $game_id]);
$game = $stmt->fetch();

if (!$game) {
    $_SESSION['flash_error'] = "Game tidak ditemukan.";
    header("Location: " . BASE_URL . "/admin/games.php");
    exit();
}

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

    $stmt_upd = $pdo->prepare("UPDATE games SET name=:name, slug=:slug, description=:desc, genre=:genre, developer=:dev, publisher=:pub, 
                              release_date=:rdate, platform=:plat, cover=:cover, price=:price, difficulty=:diff, 
                              minimum_cpu=:min_cpu, minimum_ram=:min_ram, minimum_gpu=:min_gpu, minimum_vram=:min_vram, minimum_os=:min_os, 
                              recommended_cpu=:rec_cpu, recommended_ram=:rec_ram, recommended_gpu=:rec_gpu, recommended_vram=:rec_vram, recommended_os=:rec_os 
                              WHERE id = :id");
    $stmt_upd->execute([
        ':name' => $name, ':slug' => $slug, ':desc' => $description, ':genre' => $genre,
        ':dev' => $developer, ':pub' => $publisher, ':rdate' => $release_date, ':plat' => $platform,
        ':cover' => $cover, ':price' => $price, ':diff' => $difficulty,
        ':min_cpu' => $minimum_cpu, ':min_ram' => $minimum_ram, ':min_gpu' => $minimum_gpu, ':min_vram' => $minimum_vram, ':min_os' => $minimum_os,
        ':rec_cpu' => $recommended_cpu, ':rec_ram' => $recommended_ram, ':rec_gpu' => $recommended_gpu, ':rec_vram' => $recommended_vram, ':rec_os' => $recommended_os,
        ':id' => $game_id
    ]);

    $_SESSION['flash_success'] = "Data game " . htmlspecialchars($name) . " berhasil diperbarui!";
    header("Location: " . BASE_URL . "/admin/games.php");
    exit();
}
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-pencil-square text-cyan me-2"></i> Edit Game: <?= htmlspecialchars($game['name']); ?></h1>
    <a href="<?= BASE_URL; ?>/admin/games.php" class="btn btn-outline-secondary btn-sm">Batal</a>
  </div>

  <div class="card-custom p-4 p-md-5">
    <form method="POST" action="<?= BASE_URL; ?>/admin/edit-game.php?id=<?= $game_id; ?>">
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Nama Game *</label>
          <input type="text" name="name" class="form-control form-control-custom" value="<?= htmlspecialchars($game['name']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Slug URL *</label>
          <input type="text" name="slug" class="form-control form-control-custom" value="<?= htmlspecialchars($game['slug']); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Genre *</label>
          <input type="text" name="genre" class="form-control form-control-custom" value="<?= htmlspecialchars($game['genre']); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Tingkat Spek (Difficulty) *</label>
          <select name="difficulty" class="form-select form-select-custom" required>
            <option value="Ringan" <?= ($game['difficulty'] === 'Ringan') ? 'selected' : ''; ?>>Ringan</option>
            <option value="Menengah" <?= ($game['difficulty'] === 'Menengah') ? 'selected' : ''; ?>>Menengah</option>
            <option value="Berat" <?= ($game['difficulty'] === 'Berat') ? 'selected' : ''; ?>>Berat</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Harga (Rp) *</label>
          <input type="number" name="price" class="form-control form-control-custom" value="<?= $game['price']; ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Developer</label>
          <input type="text" name="developer" class="form-control form-control-custom" value="<?= htmlspecialchars($game['developer']); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Publisher</label>
          <input type="text" name="publisher" class="form-control form-control-custom" value="<?= htmlspecialchars($game['publisher']); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white fw-bold">Tanggal Rilis</label>
          <input type="date" name="release_date" class="form-control form-control-custom" value="<?= $game['release_date']; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Platform</label>
          <input type="text" name="platform" class="form-control form-control-custom" value="<?= htmlspecialchars($game['platform']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Nama File Gambar Cover</label>
          <input type="text" name="cover" class="form-control form-control-custom" value="<?= htmlspecialchars($game['cover']); ?>">
        </div>
        <div class="col-12">
          <label class="form-label text-white fw-bold">Deskripsi Game *</label>
          <textarea name="description" class="form-control form-control-custom" rows="3" required><?= htmlspecialchars($game['description']); ?></textarea>
        </div>
      </div>

      <!-- Minimum Requirements -->
      <h4 class="text-warning fw-bold mb-3"><i class="bi bi-shield-exclamation me-1"></i> Spesifikasi Minimum</h4>
      <div class="row g-3 mb-4 p-3 bg-dark rounded-3 border border-secondary">
        <div class="col-md-6">
          <label class="form-label text-white small">Minimum CPU *</label>
          <input type="text" name="minimum_cpu" class="form-control form-control-custom" value="<?= htmlspecialchars($game['minimum_cpu']); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Minimum RAM (GB) *</label>
          <input type="number" name="minimum_ram" class="form-control form-control-custom" value="<?= $game['minimum_ram']; ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Minimum VRAM (GB) *</label>
          <input type="number" name="minimum_vram" class="form-control form-control-custom" value="<?= $game['minimum_vram']; ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label text-white small">Minimum GPU *</label>
          <input type="text" name="minimum_gpu" class="form-control form-control-custom" value="<?= htmlspecialchars($game['minimum_gpu']); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white small">Minimum OS *</label>
          <input type="text" name="minimum_os" class="form-control form-control-custom" value="<?= htmlspecialchars($game['minimum_os']); ?>" required>
        </div>
      </div>

      <!-- Recommended Requirements -->
      <h4 class="text-success fw-bold mb-3"><i class="bi bi-check-circle-fill me-1"></i> Spesifikasi Rekomendasi</h4>
      <div class="row g-3 mb-4 p-3 bg-dark rounded-3 border border-secondary">
        <div class="col-md-6">
          <label class="form-label text-white small">Recommended CPU *</label>
          <input type="text" name="recommended_cpu" class="form-control form-control-custom" value="<?= htmlspecialchars($game['recommended_cpu']); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Recommended RAM (GB) *</label>
          <input type="number" name="recommended_ram" class="form-control form-control-custom" value="<?= $game['recommended_ram']; ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white small">Recommended VRAM (GB) *</label>
          <input type="number" name="recommended_vram" class="form-control form-control-custom" value="<?= $game['recommended_vram']; ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label text-white small">Recommended GPU *</label>
          <input type="text" name="recommended_gpu" class="form-control form-control-custom" value="<?= htmlspecialchars($game['recommended_gpu']); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label text-white small">Recommended OS *</label>
          <input type="text" name="recommended_os" class="form-control form-control-custom" value="<?= htmlspecialchars($game['recommended_os']); ?>" required>
        </div>
      </div>

      <button type="submit" class="btn btn-cyan btn-lg px-5 fw-bold">Simpan Perubahan Game</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
