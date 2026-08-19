<?php
// rekomendasi.php - Halaman Hasil Rekomendasi Game & Scoring Breakdown
$page_title = "Rekomendasi Game Laptop Saya - GameCheck";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/recommendation-engine.php';

// Get spec data from Session or Database
$user_spec = null;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $user_spec = $stmt->fetch();
}

if (!$user_spec && isset($_SESSION['guest_spec'])) {
    $user_spec = $_SESSION['guest_spec'];
}

// Filter Status GET parameter
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$filter_game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
?>

<div class="container py-5">
  <?php if (!$user_spec): ?>
    <!-- Empty State: Belum Mengisi Spesifikasi -->
    <div class="text-center py-5 my-5 card-custom p-5 mx-auto" style="max-width: 650px;">
      <div class="mb-4">
        <i class="bi bi-laptop-fill text-cyan display-1"></i>
      </div>
      <h2 class="text-white fw-bold mb-2">Spesifikasi Laptop Belum Diisi</h2>
      <p class="text-secondary mb-4">
        Silakan masukkan spesifikasi laptop milikmu terlebih dahulu agar sistem GameCheck dapat menganalisis dan menghitung skor kecocokan game.
      </p>
      <a href="<?= BASE_URL; ?>/cek-spek.php" class="btn btn-cyan btn-lg px-4 pulse-glow">
        <i class="bi bi-cpu me-2"></i> Input Spesifikasi Laptop
      </a>
    </div>
  <?php else: ?>
    <!-- Header Section with User Spec Summary -->
    <div class="card-custom p-4 mb-4 border-cyan">
      <div class="row align-items-center g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-cyan text-dark fw-bold px-3 py-1">LAPTOP SPECIFICATIONS</span>
            <span class="text-muted small"><i class="bi bi-clock-history me-1"></i> Analisis Aktif</span>
          </div>
          <h2 class="text-white fw-bold mb-2">Spesifikasi Laptop Kamu:</h2>
          <div class="d-flex flex-wrap gap-3 text-secondary small">
            <div><i class="bi bi-cpu text-cyan me-1"></i> CPU: <strong class="text-white"><?= htmlspecialchars($user_spec['cpu']); ?></strong></div>
            <div><i class="bi bi-memory text-cyan me-1"></i> RAM: <strong class="text-white"><?= $user_spec['ram']; ?> GB</strong></div>
            <div><i class="bi bi-gpu-card text-cyan me-1"></i> GPU: <strong class="text-white"><?= htmlspecialchars($user_spec['gpu']); ?></strong></div>
            <div><i class="bi bi-collection-play text-cyan me-1"></i> VRAM: <strong class="text-white"><?= $user_spec['vram']; ?> GB</strong></div>
            <div><i class="bi bi-windows text-cyan me-1"></i> OS: <strong class="text-white"><?= htmlspecialchars($user_spec['os']); ?></strong></div>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="<?= BASE_URL; ?>/cek-spek.php" class="btn btn-outline-cyan btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Ubah Spesifikasi
          </a>
        </div>
      </div>
    </div>

    <!-- Filter Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <h3 class="text-gradient fw-bold mb-1"><i class="fas fa-gamepad"></i> Hasil Rekomendasi Game</h3>
        <p class="text-secondary small mb-0">Game yang diurutkan berdasarkan skor kompatibilitas dengan laptopmu</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=all" class="btn btn-sm <?= ($filter_status === 'all') ? 'btn-cyan' : 'btn-dark border-secondary text-secondary'; ?>">
          Semua Game
        </a>
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=sangat-cocok" class="btn btn-sm <?= ($filter_status === 'sangat-cocok') ? 'btn-success' : 'btn-dark border-secondary text-secondary'; ?>">
           Sangat Cocok
        </a>
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=cocok" class="btn btn-sm <?= ($filter_status === 'cocok') ? 'btn-warning' : 'btn-dark border-secondary text-secondary'; ?>">
           Bisa Dimainkan
        </a>
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=rendah" class="btn btn-sm <?= ($filter_status === 'rendah') ? 'btn-outline-warning' : 'btn-dark border-secondary text-secondary'; ?>">
           Setting Rendah
        </a>
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=tidak-cocok" class="btn btn-sm <?= ($filter_status === 'tidak-cocok') ? 'btn-danger' : 'btn-dark border-secondary text-secondary'; ?>">
           Tidak Direkomendasikan
        </a>
      </div>
    </div>

    <!-- Process Recommendations -->
    <?php
    if ($filter_game_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM games WHERE id = :gid");
        $stmt->execute([':gid' => $filter_game_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM games ORDER BY id ASC");
    }
    $all_games = $stmt->fetchAll();

    $recommendations = [];
    foreach ($all_games as $game) {
        $result = analyzeGameCompatibility($user_spec, $game);
        
        // Filter logic
        if ($filter_status === 'sangat-cocok' && $result['score'] < 90) continue;
        if ($filter_status === 'cocok' && ($result['score'] < 70 || $result['score'] >= 90)) continue;
        if ($filter_status === 'rendah' && ($result['score'] < 50 || $result['score'] >= 70)) continue;
        if ($filter_status === 'tidak-cocok' && $result['score'] >= 50) continue;

        $recommendations[] = [
            'game' => $game,
            'result' => $result
        ];
    }

    // Sort by Score Highest to Lowest
    usort($recommendations, function($a, $b) {
        return $b['result']['score'] <=> $a['result']['score'];
    });
    ?>

    <?php if (count($recommendations) > 0): ?>
      <div class="row g-4">
        <?php foreach ($recommendations as $item): 
          $g = $item['game'];
          $res = $item['result'];
        ?>
          <div class="col-lg-6">
            <div class="card-custom p-4 h-100">
              <div class="row g-3 align-items-center">
                <div class="col-sm-5">
                  <div class="game-cover-wrapper rounded-3">
                    <img src="<?= BASE_URL; ?>/assets/images/games/<?= htmlspecialchars($g['cover']); ?>" 
                         onerror="this.src='https://placehold.co/600x340/121826/00f2fe?text=<?= urlencode($g['name']); ?>';" 
                         alt="<?= htmlspecialchars($g['name']); ?>" 
                         class="game-cover-img">
                  </div>
                </div>

                <div class="col-sm-7 d-flex flex-column h-100">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="text-white fw-bold mb-0 me-2"><?= htmlspecialchars($g['name']); ?></h5>
                    <span class="score-badge <?= $res['badge']; ?> text-nowrap">
                      <?= $res['score']; ?>/100
                    </span>
                  </div>

                  <div class="mb-2">
                    <span class="badge <?= $res['badge']; ?> me-1">
                      <?= $res['icon']; ?> <?= $res['label']; ?>
                    </span>
                    <span class="badge bg-dark border border-secondary text-secondary"><?= htmlspecialchars($g['genre']); ?></span>
                  </div>

                  <p class="text-secondary small mb-3 flex-grow-1">
                    <?= htmlspecialchars($res['reason']); ?>
                  </p>

                  <!-- Hardware Checklist Breakdown -->
                  <div class="d-flex flex-wrap gap-2 mb-3 bg-dark p-2 rounded border border-secondary small">
                    <span class="<?= $res['breakdown']['cpu']['passed'] ? 'text-success' : 'text-danger'; ?>">
                      CPU <?= $res['breakdown']['cpu']['passed'] ? '' : ''; ?>
                    </span>
                    <span class="<?= $res['breakdown']['ram']['passed'] ? 'text-success' : 'text-danger'; ?>">
                      RAM <?= $res['breakdown']['ram']['passed'] ? '' : ''; ?>
                    </span>
                    <span class="<?= $res['breakdown']['gpu']['passed'] ? 'text-success' : 'text-danger'; ?>">
                      GPU <?= $res['breakdown']['gpu']['passed'] ? '' : ''; ?>
                    </span>
                    <span class="<?= $res['breakdown']['vram']['passed'] ? 'text-success' : 'text-danger'; ?>">
                      VRAM <?= $res['breakdown']['vram']['passed'] ? '' : ''; ?>
                    </span>
                    <span class="<?= $res['breakdown']['os']['passed'] ? 'text-success' : 'text-danger'; ?>">
                      OS <?= $res['breakdown']['os']['passed'] ? '' : ''; ?>
                    </span>
                  </div>

                  <div class="d-flex gap-2">
                    <a href="<?= BASE_URL; ?>/detail-game.php?slug=<?= $g['slug']; ?>" class="btn btn-cyan btn-sm flex-grow-1">
                      <i class="bi bi-info-circle me-1"></i> Detail Game
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5 card-custom p-5">
        <i class="bi bi-filter-circle display-3 text-cyan mb-3"></i>
        <h4 class="text-white fw-bold mb-2">Tidak Ada Game yang Sesuai Filter</h4>
        <p class="text-secondary mb-3">Tidak ditemukan game untuk kategori status kecocokan ini.</p>
        <a href="<?= BASE_URL; ?>/rekomendasi.php?status=all" class="btn btn-cyan btn-sm">Tampilkan Semua Game</a>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
