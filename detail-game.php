<?php
// detail-game.php — Halaman Detail Game v2.0
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->prepare("SELECT * FROM games WHERE slug = :slug");
$stmt->execute([':slug' => $slug]);
$game = $stmt->fetch();

if (!$game) {
    $page_title = "Game Tidak Ditemukan - GameCheck";
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center">
            <div class="p-5 card-custom d-inline-block" style="max-width:500px;">
              <i class="bi bi-exclamation-triangle display-1 mb-3" style="color:var(--danger)"></i>
              <h2 class="fw-700 mb-3">Game Tidak Ditemukan</h2>
              <p class="text-secondary mb-4">Game yang Anda cari tidak ada di database GameCheck.</p>
              <a href="' . BASE_URL . '/katalog.php" class="btn-purple"><i class="bi bi-arrow-left me-2"></i>Kembali ke Katalog</a>
            </div>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$page_title = htmlspecialchars($game['name']) . " — Spesifikasi & Beli Game | GameCheck";

// Related games (same genre, exclude this)
$rel_stmt = $pdo->prepare("SELECT * FROM games WHERE genre LIKE :g AND id != :id ORDER BY RAND() LIMIT 4");
$rel_stmt->execute([':g' => '%' . explode('/', $game['genre'])[0] . '%', ':id' => $game['id']]);
$related_games = $rel_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-xxl px-3 px-lg-4 py-5" style="max-width:1400px;">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/katalog.php">Katalog Game</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($game['name']); ?></li>
    </ol>
  </nav>

  <!-- ═══ MAIN GAME INFO ═══ -->
  <div class="card-custom p-4 mb-5">
    <div class="row g-4 align-items-start">

      <!-- Cover -->
      <div class="col-md-5 col-lg-4">
        <div class="game-cover-wrapper rounded-xl" style="box-shadow:0 8px 32px var(--accent-glow);">
          <img src="<?= BASE_URL; ?>/assets/images/games/<?= htmlspecialchars($game['cover']); ?>"
               onerror="this.src='https://placehold.co/600x340/151c2c/a855f7?text=<?= urlencode($game['name']); ?>'"
               alt="<?= htmlspecialchars($game['name']); ?>"
               class="game-cover-img">
        </div>

        <!-- Action buttons under cover -->
        <div class="d-flex gap-2 mt-3">
          <?php if ($game['price'] == 0): ?>
            <button class="btn-purple flex-grow-1"
                    onclick="CartManager.add({id:<?= $game['id']; ?>,name:'<?= addslashes($game['name']); ?>',price:0,slug:'<?= $game['slug']; ?>',cover:'<?= $game['cover']; ?>'})">
              <i class="bi bi-cart-plus me-2"></i>Ambil Gratis
            </button>
          <?php else: ?>
            <button class="btn-purple flex-grow-1"
                    onclick="CartManager.add({id:<?= $game['id']; ?>,name:'<?= addslashes($game['name']); ?>',price:<?= $game['price']; ?>,slug:'<?= $game['slug']; ?>',cover:'<?= $game['cover']; ?>'})">
              <i class="bi bi-cart-plus me-2"></i>Beli
            </button>
          <?php endif; ?>
          <button class="wishlist-btn-standalone"
                  data-wishlist-id="<?= $game['id']; ?>"
                  data-wishlist-name="<?= htmlspecialchars($game['name']); ?>"
                  data-wishlist-price="<?= $game['price']; ?>"
                  data-wishlist-slug="<?= $game['slug']; ?>"
                  data-wishlist-cover="<?= $game['cover']; ?>"
                  title="Tambah ke Wishlist"
                  style="width:48px;height:48px;">
            <i class="bi bi-heart"></i>
          </button>
        </div>
      </div>

      <!-- Details -->
      <div class="col-md-7 col-lg-8">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge-genre px-3 py-1"><?= htmlspecialchars($game['genre']); ?></span>
          <span class="badge-difficulty badge-<?= strtolower($game['difficulty']); ?> position-static">
            <?= htmlspecialchars($game['difficulty']); ?> Spec
          </span>
          <span class="px-2 py-1 rounded-pill small" style="background:var(--bg-surface);color:var(--text-muted);border:1px solid var(--border-color);">
            <i class="bi bi-windows me-1"></i><?= htmlspecialchars($game['platform']); ?>
          </span>
        </div>

        <h1 class="display-5 fw-800 mb-3" style="letter-spacing:-0.02em;"><?= htmlspecialchars($game['name']); ?></h1>

        <!-- Meta info grid -->
        <div class="row g-2 mb-4" style="font-size:0.875rem;">
          <div class="col-6 col-sm-3">
            <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Developer</div>
            <div class="fw-600"><?= htmlspecialchars($game['developer']); ?></div>
          </div>
          <div class="col-6 col-sm-3">
            <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Publisher</div>
            <div class="fw-600"><?= htmlspecialchars($game['publisher']); ?></div>
          </div>
          <div class="col-6 col-sm-3">
            <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Rilis</div>
            <div class="fw-600"><?= date('d M Y', strtotime($game['release_date'])); ?></div>
          </div>
          <div class="col-6 col-sm-3">
            <div style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">Harga</div>
            <div class="fw-700 fs-5" style="color:<?= $game['price'] == 0 ? 'var(--success)' : 'var(--accent-light)'; ?>">
              <?= $game['price'] == 0 ? 'Gratis' : 'Rp ' . number_format($game['price'], 0, ',', '.'); ?>
            </div>
          </div>
        </div>

        <p class="text-secondary mb-4" style="line-height:1.7;"><?= nl2br(htmlspecialchars($game['description'])); ?></p>

        <!-- CTA Buttons -->
        <div class="d-flex flex-wrap gap-3">
          <?php if ($game['price'] == 0): ?>
            <button class="btn-purple btn-lg"
                    onclick="CartManager.add({id:<?= $game['id']; ?>,name:'<?= addslashes($game['name']); ?>',price:0,slug:'<?= $game['slug']; ?>',cover:'<?= $game['cover']; ?>'})">
              <i class="bi bi-cart-plus me-2"></i>Ambil Gratis
            </button>
          <?php else: ?>
            <button class="btn-purple btn-lg"
                    onclick="CartManager.add({id:<?= $game['id']; ?>,name:'<?= addslashes($game['name']); ?>',price:<?= $game['price']; ?>,slug:'<?= $game['slug']; ?>',cover:'<?= $game['cover']; ?>'})">
              <i class="bi bi-cart-plus me-2"></i> Tambah ke Keranjang
            </button>
            <a href="<?= BASE_URL; ?>/checkout/?direct=<?= $game['id']; ?>" class="btn-outline-purple btn-lg">
              <i class="bi bi-lightning-fill me-2"></i>Beli Sekarang
            </a>
          <?php endif; ?>
          <button class="wishlist-btn-standalone"
                  data-wishlist-id="<?= $game['id']; ?>"
                  data-wishlist-name="<?= htmlspecialchars($game['name']); ?>"
                  data-wishlist-price="<?= $game['price']; ?>"
                  data-wishlist-slug="<?= $game['slug']; ?>"
                  data-wishlist-cover="<?= $game['cover']; ?>"
                  title="Wishlist"
                  style="width:52px;height:52px;font-size:1.2rem;">
            <i class="bi bi-heart"></i>
          </button>
        </div>

        <!-- Cek Spek Link CTA -->
        <div class="mt-4 p-3 rounded-lg d-flex align-items-center gap-3"
             style="background:var(--accent-muted);border:1px solid rgba(124,58,237,0.3);">
          <i class="bi bi-laptop fs-2" style="color:var(--accent-light);"></i>
          <div class="flex-grow-1">
            <div class="fw-700" style="font-size:0.9rem;">Apakah laptopmu bisa memainkan game ini?</div>
            <div class="text-secondary" style="font-size:0.8rem;">Cek kompatibilitas langsung di bawah ini</div>
          </div>
          <a href="#spec-checker-section" class="btn-purple btn-sm" style="padding:0.5rem 1rem;font-size:0.82rem;white-space:nowrap;">
            <i class="bi bi-arrow-down me-1"></i>Cek Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══ SYSTEM REQUIREMENTS ═══ -->
  <h3 class="fw-800 mb-4" style="font-family:var(--font-heading);">
    <i class="bi bi-cpu me-2" style="color:var(--accent-light);"></i>System Requirements
  </h3>
  <div class="row g-4 mb-5">
    <!-- Minimum -->
    <div class="col-md-6">
      <div class="card-custom p-4 h-100" style="border-left:4px solid var(--warning);">
        <h4 class="fw-700 mb-1 d-flex align-items-center gap-2" style="color:var(--warning);">
          <i class="bi bi-shield-exclamation"></i> Minimum Requirements
        </h4>
        <p class="text-secondary small mb-4">Spesifikasi paling dasar agar game dapat berjalan.</p>
        <?php
        $min_rows = [
            ['bi-windows', 'Sistem Operasi', $game['minimum_os']],
            ['bi-cpu', 'CPU / Prosesor', $game['minimum_cpu']],
            ['bi-memory', 'RAM', $game['minimum_ram'] . ' GB'],
            ['bi-gpu-card', 'GPU / VGA', $game['minimum_gpu']],
            ['bi-collection-play', 'VRAM', $game['minimum_vram'] . ' GB'],
        ];
        foreach ($min_rows as [$ico, $label, $val]): ?>
          <div class="d-flex justify-content-between align-items-start py-2 border-bottom" style="border-color:var(--border-color)!important;">
            <span class="text-secondary d-flex align-items-center gap-2" style="font-size:0.875rem;">
              <i class="bi <?= $ico; ?>" style="color:var(--accent-light);width:16px;text-align:center;"></i><?= $label; ?>
            </span>
            <strong class="text-end ms-2" style="font-size:0.875rem;max-width:55%;word-break:break-word;"><?= htmlspecialchars($val); ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recommended -->
    <div class="col-md-6">
      <div class="card-custom p-4 h-100" style="border-left:4px solid var(--success);">
        <h4 class="fw-700 mb-1 d-flex align-items-center gap-2" style="color:var(--success);">
          <i class="bi bi-check-circle-fill"></i> Recommended Requirements
        </h4>
        <p class="text-secondary small mb-4">Spesifikasi agar game berjalan mulus di setting grafis tinggi.</p>
        <?php
        $rec_rows = [
            ['bi-windows', 'Sistem Operasi', $game['recommended_os']],
            ['bi-cpu', 'CPU / Prosesor', $game['recommended_cpu']],
            ['bi-memory', 'RAM', $game['recommended_ram'] . ' GB'],
            ['bi-gpu-card', 'GPU / VGA', $game['recommended_gpu']],
            ['bi-collection-play', 'VRAM', $game['recommended_vram'] . ' GB'],
        ];
        foreach ($rec_rows as [$ico, $label, $val]): ?>
          <div class="d-flex justify-content-between align-items-start py-2 border-bottom" style="border-color:var(--border-color)!important;">
            <span class="text-secondary d-flex align-items-center gap-2" style="font-size:0.875rem;">
              <i class="bi <?= $ico; ?>" style="color:var(--accent-light);width:16px;text-align:center;"></i><?= $label; ?>
            </span>
            <strong class="text-end ms-2" style="font-size:0.875rem;max-width:55%;word-break:break-word;"><?= htmlspecialchars($val); ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ═══ INLINE SPEC CHECKER ═══ -->
  <div id="spec-checker-section" class="spec-checker-card mb-5">
    <div class="d-flex align-items-center gap-3 mb-4">
      <div style="width:52px;height:52px;border-radius:14px;background:var(--gradient-main);display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-laptop fs-4 text-white"></i>
      </div>
      <div>
        <h3 class="fw-800 mb-0" style="font-size:1.4rem;">Cek Apakah Laptopmu Bisa Memainkan Game Ini</h3>
        <p class="text-secondary mb-0" style="font-size:0.875rem;">Masukkan spesifikasi laptopmu untuk mendapatkan hasil analisis instan</p>
      </div>
    </div>

    <?php
    // Pre-fill from session
    $sp_cpu  = '';
    $sp_ram  = 8;
    $sp_gpu  = '';
    $sp_vram = 4;
    $sp_os   = 'Windows 11';
    if (isset($_SESSION['user_id'])) {
        $sp_stmt = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
        $sp_stmt->execute([':uid' => $_SESSION['user_id']]);
        $sp = $sp_stmt->fetch();
        if ($sp) { $sp_cpu = $sp['cpu']; $sp_ram = $sp['ram']; $sp_gpu = $sp['gpu']; $sp_vram = $sp['vram']; $sp_os = $sp['os']; }
    } elseif (isset($_SESSION['guest_spec'])) {
        $gs = $_SESSION['guest_spec'];
        $sp_cpu = $gs['cpu']; $sp_ram = $gs['ram']; $sp_gpu = $gs['gpu']; $sp_vram = $gs['vram']; $sp_os = $gs['os'];
    }
    ?>

    <form id="inline-spec-form">
      <input type="hidden" id="inline-game-id" value="<?= $game['id']; ?>">
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label"><i class="bi bi-cpu me-1" style="color:var(--accent-light)"></i>CPU / Prosesor</label>
          <input type="text" id="inline-cpu" class="form-control" placeholder="Contoh: Intel Core i5-11400H"
                 value="<?= htmlspecialchars($sp_cpu); ?>" required>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label"><i class="bi bi-memory me-1" style="color:var(--accent-light)"></i>RAM</label>
          <select id="inline-ram" class="form-select">
            <?php foreach ([4,8,12,16,32,64] as $r): ?>
              <option value="<?= $r; ?>" <?= ($sp_ram == $r) ? 'selected' : ''; ?>><?= $r; ?> GB</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 col-6">
          <label class="form-label"><i class="bi bi-collection-play me-1" style="color:var(--accent-light)"></i>VRAM</label>
          <select id="inline-vram" class="form-select">
            <?php foreach ([1,2,4,6,8,12] as $v): ?>
              <option value="<?= $v; ?>" <?= ($sp_vram == $v) ? 'selected' : ''; ?>><?= $v; ?> GB</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label"><i class="bi bi-gpu-card me-1" style="color:var(--accent-light)"></i>GPU / VGA</label>
          <input type="text" id="inline-gpu" class="form-control" placeholder="Contoh: NVIDIA GeForce RTX 3050"
                 value="<?= htmlspecialchars($sp_gpu); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label"><i class="bi bi-windows me-1" style="color:var(--accent-light)"></i>OS</label>
          <select id="inline-os" class="form-select">
            <option value="Windows 10" <?= ($sp_os === 'Windows 10') ? 'selected' : ''; ?>>Windows 10</option>
            <option value="Windows 11" <?= ($sp_os === 'Windows 11') ? 'selected' : ''; ?>>Windows 11</option>
          </select>
        </div>
      </div>

      <!-- Quick presets -->
      <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="text-muted small align-self-center me-1">Preset:</span>
        <button type="button" class="btn btn-sm border-secondary text-secondary" style="background:var(--bg-input);border-radius:var(--radius-sm);font-size:0.78rem;"
                onclick="fillInlinePreset('Intel Core i3-10100', 8, 'Intel UHD 630', 2, 'Windows 10')">
          <i class="fas fa-laptop text-info"></i> Standard
        </button>
        <button type="button" class="btn btn-sm border-secondary text-secondary" style="background:var(--bg-input);border-radius:var(--radius-sm);font-size:0.78rem;"
                onclick="fillInlinePreset('Intel Core i5-11400H', 16, 'NVIDIA GeForce RTX 3050', 4, 'Windows 11')">
          <i class="fas fa-gamepad"></i> Gaming Entry
        </button>
        <button type="button" class="btn btn-sm border-secondary text-secondary" style="background:var(--bg-input);border-radius:var(--radius-sm);font-size:0.78rem;"
                onclick="fillInlinePreset('AMD Ryzen 7 5800H', 16, 'NVIDIA GeForce RTX 3060', 6, 'Windows 11')">
          <i class="fas fa-rocket text-primary"></i> High End
        </button>
      </div>

      <button type="submit" class="btn-purple pulse-glow" style="padding:0.75rem 2.5rem;font-size:1rem;">
        <i class="bi bi-play-circle-fill me-2"></i>Analisis Sekarang
      </button>
    </form>

    <!-- Result area -->
    <div id="compat-result" class="mt-4"></div>
  </div>

  <!-- ═══ RELATED GAMES ═══ -->
  <?php if (!empty($related_games)): ?>
  <div class="mt-2">
    <div class="section-header mb-4">
      <div>
        <h3 class="section-title" style="font-size:1.4rem;"><i class="fas fa-gamepad"></i> Game Serupa</h3>
        <p class="section-subtitle">Mungkin kamu juga suka game-game ini</p>
      </div>
      <a href="<?= BASE_URL; ?>/katalog.php?genre=<?= urlencode(explode('/', $game['genre'])[0]); ?>" class="section-link">
        Lihat Genre <i class="bi bi-arrow-right"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($related_games as $rg): ?>
      <div class="col-sm-6 col-lg-3">
        <div class="game-card">
          <div class="game-cover-wrapper">
            <img src="<?= BASE_URL; ?>/assets/images/games/<?= htmlspecialchars($rg['cover']); ?>"
                 onerror="this.src='https://placehold.co/600x340/151c2c/a855f7?text=<?= urlencode($rg['name']); ?>'"
                 alt="<?= htmlspecialchars($rg['name']); ?>"
                 class="game-cover-img" loading="lazy">
            <span class="badge-difficulty badge-<?= strtolower($rg['difficulty']); ?>"><?= htmlspecialchars($rg['difficulty']); ?></span>
          </div>
          <div class="p-3 d-flex flex-column flex-grow-1">
            <span class="badge-genre mb-2"><?= htmlspecialchars($rg['genre']); ?></span>
            <h5 class="fw-700 mb-2" style="font-size:0.9rem;"><?= htmlspecialchars($rg['name']); ?></h5>
            <div class="price-badge <?= $rg['price'] == 0 ? 'price-free' : ''; ?> mb-3">
              <?= $rg['price'] == 0 ? 'Gratis' : 'Rp ' . number_format($rg['price'], 0, ',', '.'); ?>
            </div>
            <div class="mt-auto">
              <a href="<?= BASE_URL; ?>/detail-game.php?slug=<?= $rg['slug']; ?>" class="btn-purple w-100 d-flex justify-content-center" style="padding:0.5rem;font-size:0.82rem;">
                <i class="bi bi-eye me-1"></i>Lihat Detail
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function fillInlinePreset(cpu, ram, gpu, vram, os) {
  document.getElementById('inline-cpu').value  = cpu;
  document.getElementById('inline-ram').value  = ram;
  document.getElementById('inline-gpu').value  = gpu;
  document.getElementById('inline-vram').value = vram;
  document.getElementById('inline-os').value   = os;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
