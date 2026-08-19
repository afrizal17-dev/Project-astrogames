<?php
// katalog.php — Katalog Game v2.0 dengan Filter Lengkap
$page_title = "Katalog Game PC - GameCheck";
require_once __DIR__ . '/includes/header.php';

// ── Filter Variables ──────────────────────────────────────────
$search     = isset($_GET['search'])     ? trim($_GET['search']) : '';
$genre      = isset($_GET['genre'])      ? trim($_GET['genre']) : '';
$difficulty = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : '';
$price_type = isset($_GET['price_type']) ? trim($_GET['price_type']) : '';
$ram        = isset($_GET['ram'])        ? (int)$_GET['ram'] : 0;
$vram       = isset($_GET['vram'])       ? (int)$_GET['vram'] : 0;
$sort       = isset($_GET['sort'])       ? trim($_GET['sort']) : 'newest';

// ── Build SQL ─────────────────────────────────────────────────
$sql    = "SELECT * FROM games WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE :search OR description LIKE :search OR genre LIKE :search OR developer LIKE :search OR publisher LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($genre !== '') {
    $sql .= " AND genre LIKE :genre";
    $params[':genre'] = "%$genre%";
}
if ($difficulty !== '') {
    $sql .= " AND difficulty = :difficulty";
    $params[':difficulty'] = $difficulty;
}
if ($price_type === 'gratis') {
    $sql .= " AND price = 0";
} elseif ($price_type === 'berbayar') {
    $sql .= " AND price > 0";
} elseif ($price_type === 'lt100') {
    $sql .= " AND price > 0 AND price < 100000";
} elseif ($price_type === '100to300') {
    $sql .= " AND price >= 100000 AND price <= 300000";
} elseif ($price_type === '300to500') {
    $sql .= " AND price > 300000 AND price <= 500000";
} elseif ($price_type === 'gt500') {
    $sql .= " AND price > 500000";
}
if ($ram > 0) {
    $sql .= " AND minimum_ram <= :ram";
    $params[':ram'] = $ram;
}
if ($vram > 0) {
    $sql .= " AND minimum_vram <= :vram";
    $params[':vram'] = $vram;
}

switch ($sort) {
    case 'name_asc':   $sql .= " ORDER BY name ASC"; break;
    case 'name_desc':  $sql .= " ORDER BY name DESC"; break;
    case 'ram_asc':    $sql .= " ORDER BY minimum_ram ASC"; break;
    case 'price_asc':  $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'newest':
    default:           $sql .= " ORDER BY id DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll();

$genres_stmt = $pdo->query("SELECT DISTINCT genre FROM games ORDER BY genre ASC");
$all_genres  = $genres_stmt->fetchAll(PDO::FETCH_COLUMN);

// Has active filters?
$has_filters = $search || $genre || $difficulty || $price_type || $ram || $vram;
?>

<div class="container-xxl px-3 px-lg-4 py-5" style="max-width:1400px;">

  <!-- Page Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
      <h1 class="display-6 fw-800 text-gradient mb-1"><i class="bi bi-controller me-2"></i>Katalog Game PC</h1>
      <p class="text-secondary mb-0">Jelajahi <?= count($games); ?> game &mdash; filter berdasarkan spesifikasi laptopmu</p>
    </div>
    <a href="<?= BASE_URL; ?>/cek-spek.php" class="btn-purple mt-3 mt-md-0" style="padding:0.6rem 1.4rem;">
      <i class="bi bi-laptop me-2"></i>Cek Spek Laptop
    </a>
  </div>

  <!-- ── FILTER PANEL ────────────────────────────────────────── -->
  <div class="card-custom p-4 mb-5">
    <form method="GET" action="<?= BASE_URL; ?>/katalog.php" id="filter-form">
      <div class="row g-3">

        <!-- Search -->
        <div class="col-lg-4">
          <label class="form-label">Cari Game</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control"
                   placeholder="Nama game, genre, developer..."
                   value="<?= htmlspecialchars($search); ?>">
          </div>
        </div>

        <!-- Genre -->
        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Genre</label>
          <select name="genre" class="form-select">
            <option value="">Semua Genre</option>
            <?php foreach ($all_genres as $g): ?>
              <option value="<?= htmlspecialchars($g); ?>" <?= ($genre === $g) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($g); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Difficulty -->
        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Tingkat Spek</label>
          <select name="difficulty" class="form-select">
            <option value="">Semua</option>
            <option value="Ringan"   <?= ($difficulty === 'Ringan')   ? 'selected' : ''; ?>> Ringan</option>
            <option value="Menengah" <?= ($difficulty === 'Menengah') ? 'selected' : ''; ?>> Menengah</option>
            <option value="Berat"    <?= ($difficulty === 'Berat')    ? 'selected' : ''; ?>> Berat</option>
          </select>
        </div>

        <!-- Price -->
        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Harga</label>
          <select name="price_type" class="form-select">
            <option value="">Semua Harga</option>
            <option value="gratis"  <?= ($price_type === 'gratis')  ? 'selected' : ''; ?>>Gratis</option>
            <option value="lt100"   <?= ($price_type === 'lt100')   ? 'selected' : ''; ?>>&lt; Rp 100.000</option>
            <option value="100to300" <?= ($price_type === '100to300') ? 'selected' : ''; ?>>Rp 100K–300K</option>
            <option value="300to500" <?= ($price_type === '300to500') ? 'selected' : ''; ?>>Rp 300K–500K</option>
            <option value="gt500"   <?= ($price_type === 'gt500')   ? 'selected' : ''; ?>>&gt; Rp 500.000</option>
            <option value="berbayar" <?= ($price_type === 'berbayar') ? 'selected' : ''; ?>>Berbayar</option>
          </select>
        </div>

        <!-- Sort -->
        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Urutkan</label>
          <select name="sort" class="form-select">
            <option value="newest"     <?= ($sort === 'newest')     ? 'selected' : ''; ?>>Terbaru</option>
            <option value="name_asc"   <?= ($sort === 'name_asc')   ? 'selected' : ''; ?>>Nama A-Z</option>
            <option value="name_desc"  <?= ($sort === 'name_desc')  ? 'selected' : ''; ?>>Nama Z-A</option>
            <option value="price_asc"  <?= ($sort === 'price_asc')  ? 'selected' : ''; ?>>Harga ↑</option>
            <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : ''; ?>>Harga ↓</option>
            <option value="ram_asc"    <?= ($sort === 'ram_asc')    ? 'selected' : ''; ?>>RAM Terendah</option>
          </select>
        </div>

        <!-- RAM / VRAM -->
        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Max Min RAM</label>
          <select name="ram" class="form-select">
            <option value="0">Semua RAM</option>
            <?php foreach ([4,8,12,16,32] as $r): ?>
              <option value="<?= $r; ?>" <?= ($ram === $r) ? 'selected' : ''; ?>>≤ <?= $r; ?> GB</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-sm-6 col-lg-2">
          <label class="form-label">Max Min VRAM</label>
          <select name="vram" class="form-select">
            <option value="0">Semua VRAM</option>
            <?php foreach ([1,2,4,6,8] as $v): ?>
              <option value="<?= $v; ?>" <?= ($vram === $v) ? 'selected' : ''; ?>>≤ <?= $v; ?> GB</option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Actions -->
        <div class="col-12 d-flex justify-content-end gap-2 mt-1">
          <?php if ($has_filters): ?>
            <a href="<?= BASE_URL; ?>/katalog.php" class="btn-outline-purple btn-sm" style="padding:0.5rem 1.2rem;font-size:0.85rem;">
              <i class="bi bi-x-lg me-1"></i>Reset Filter
            </a>
          <?php endif; ?>
          <button type="submit" class="btn-purple" style="padding:0.5rem 1.5rem;font-size:0.875rem;">
            <i class="bi bi-filter me-1"></i>Terapkan Filter
          </button>
        </div>
      </div>
    </form>

    <!-- Active filter pills -->
    <?php if ($has_filters): ?>
    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top" style="border-color:var(--border-color)!important;">
      <span class="text-muted small">Filter aktif:</span>
      <?php if ($search): ?><span class="badge-genre">Cari: <?= htmlspecialchars($search); ?></span><?php endif; ?>
      <?php if ($genre): ?><span class="badge-genre">Genre: <?= htmlspecialchars($genre); ?></span><?php endif; ?>
      <?php if ($difficulty): ?><span class="badge-genre">Spek: <?= htmlspecialchars($difficulty); ?></span><?php endif; ?>
      <?php if ($price_type): ?><span class="badge-genre">Harga: <?= htmlspecialchars($price_type); ?></span><?php endif; ?>
      <?php if ($ram): ?><span class="badge-genre">RAM ≤ <?= $ram; ?>GB</span><?php endif; ?>
      <?php if ($vram): ?><span class="badge-genre">VRAM ≤ <?= $vram; ?>GB</span><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── GAMES GRID ─────────────────────────────────────────── -->
  <?php if (count($games) > 0): ?>
    <div class="row g-4">
      <?php foreach ($games as $game): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="game-card">
            <div class="game-cover-wrapper">
              <img src="<?= BASE_URL; ?>/assets/images/games/<?= htmlspecialchars($game['cover']); ?>"
                   onerror="this.src='https://placehold.co/600x340/151c2c/a855f7?text=<?= urlencode($game['name']); ?>'"
                   alt="<?= htmlspecialchars($game['name']); ?>"
                   class="game-cover-img" loading="lazy">
              <span class="badge-difficulty badge-<?= strtolower($game['difficulty']); ?>">
                <?= htmlspecialchars($game['difficulty']); ?>
              </span>
              <!-- Hover overlay -->
              <div class="card-actions-overlay">
                <button class="btn-card-action wishlist-btn"
                        data-wishlist-id="<?= $game['id']; ?>"
                        data-wishlist-name="<?= htmlspecialchars($game['name']); ?>"
                        data-wishlist-price="<?= $game['price']; ?>"
                        data-wishlist-slug="<?= $game['slug']; ?>"
                        data-wishlist-cover="<?= $game['cover']; ?>"
                        title="Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="btn-card-action"
                        onclick="CartManager.add({id:<?= $game['id']; ?>,name:'<?= addslashes($game['name']); ?>',price:<?= $game['price']; ?>,slug:'<?= $game['slug']; ?>',cover:'<?= $game['cover']; ?>'})"
                        title="Tambah ke Keranjang">
                  <i class="bi bi-cart-plus me-1"></i>
                  <?= $game['price'] == 0 ? 'Gratis' : 'Keranjang'; ?>
                </button>
              </div>
            </div>

            <div class="p-3 d-flex flex-column flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge-genre"><?= htmlspecialchars($game['genre']); ?></span>
                <span class="price-badge <?= $game['price'] == 0 ? 'price-free' : ''; ?>">
                  <?= $game['price'] == 0 ? 'Gratis' : 'Rp ' . number_format($game['price'], 0, ',', '.'); ?>
                </span>
              </div>

              <h5 class="fw-700 mb-1" style="font-size:0.9rem;"><?= htmlspecialchars($game['name']); ?></h5>
              <div class="text-muted mb-2" style="font-size:0.75rem;"><?= htmlspecialchars($game['developer']); ?></div>
              <div class="text-secondary mb-3" style="font-size:0.77rem;">
                <i class="bi bi-cpu me-1 text-purple"></i><?= htmlspecialchars(substr($game['minimum_cpu'], 0, 22)) . (strlen($game['minimum_cpu']) > 22 ? '...' : ''); ?><br>
                <i class="bi bi-memory me-1 text-purple"></i>Min RAM: <?= $game['minimum_ram']; ?> GB &bull;
                <i class="bi bi-collection-play me-1 text-purple"></i>VRAM: <?= $game['minimum_vram']; ?> GB
              </div>

              <div class="mt-auto d-flex gap-2">
                <a href="<?= BASE_URL; ?>/detail-game.php?slug=<?= $game['slug']; ?>" class="btn-purple flex-grow-1 d-flex justify-content-center" style="padding:0.5rem;font-size:0.82rem;">
                  <i class="bi bi-eye me-1"></i>Detail
                </a>
                <a href="<?= BASE_URL; ?>/cek-spek.php?game_id=<?= $game['id']; ?>" class="btn-outline-purple" style="padding:0.5rem 0.7rem;font-size:0.82rem;" title="Cek Kompatibilitas">
                  <i class="bi bi-laptop"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5 my-4 card-custom p-5">
      <i class="bi bi-emoji-frown display-1 mb-4" style="color:var(--accent-light)"></i>
      <h3 class="fw-700 mb-2">Game Tidak Ditemukan</h3>
      <p class="text-secondary mb-4">Tidak ada game yang sesuai dengan filter pencarian Anda.<br>Coba ubah kombinasi filter atau hapus beberapa filter.</p>
      <a href="<?= BASE_URL; ?>/katalog.php" class="btn-purple">
        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Semua Filter
      </a>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
