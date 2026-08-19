<?php
// member/dashboard.php - Member Dashboard Utama
$page_title = "Member Dashboard - GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/recommendation-engine.php';

$user_id = $_SESSION['user_id'];

// Get User Specs
$stmt_spec = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
$stmt_spec->execute([':uid' => $user_id]);
$user_spec = $stmt_spec->fetch();

// Get User Active Products Count
$stmt_prod = $pdo->prepare("SELECT COUNT(*) FROM user_products WHERE user_id = :uid");
$stmt_prod->execute([':uid' => $user_id]);
$total_products = $stmt_prod->fetchColumn();

// Get User Orders Count
$stmt_ord = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
$stmt_ord->execute([':uid' => $user_id]);
$total_orders = $stmt_ord->fetchColumn();

// Get Top 3 Compatible Games if spec exists
$top_recommendations = [];
if ($user_spec) {
    $games_stmt = $pdo->query("SELECT * FROM games ORDER BY id ASC LIMIT 10");
    $games = $games_stmt->fetchAll();
    foreach ($games as $g) {
        $analysis = analyzeGameCompatibility($user_spec, $g);
        if ($analysis['score'] >= 70) {
            $top_recommendations[] = [
                'game' => $g,
                'score' => $analysis['score'],
                'label' => $analysis['label'],
                'badge' => $analysis['badge']
            ];
        }
        if (count($top_recommendations) >= 3) break;
    }
}
?>

<div class="container py-5">
  <div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
      <div class="sidebar-member">
        <div class="text-center mb-4 pb-3 border-bottom border-secondary">
          <i class="bi bi-person-circle display-4 text-cyan mb-2"></i>
          <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($_SESSION['user_name']); ?></h5>
          <span class="badge bg-dark border border-cyan text-cyan small"><?= htmlspecialchars($_SESSION['user_email']); ?></span>
        </div>

        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/member/dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li><a href="<?= BASE_URL; ?>/member/spesifikasi.php"><i class="bi bi-laptop"></i> Spesifikasi Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/rekomendasi.php"><i class="bi bi-stars"></i> Rekomendasi Game</a></li>
          <li><a href="<?= BASE_URL; ?>/member/produk.php"><i class="bi bi-bag-check"></i> Produk Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/riwayat.php"><i class="bi bi-receipt"></i> Riwayat Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/member/profil.php"><i class="bi bi-person-gear"></i> Pengaturan Profil</a></li>
          <li><a href="<?= BASE_URL; ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="text-gradient fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['user_name']); ?>! </h2>
          <p class="text-secondary small mb-0">Kelola spesifikasi laptop, lihat rekomendasi game, dan akses produk digital Anda.</p>
        </div>
      </div>

      <!-- Quick Metrics Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-4">
          <div class="card-custom p-3 text-center">
            <div class="text-cyan fs-3 mb-1"><i class="bi bi-laptop"></i></div>
            <div class="text-secondary small">Status Laptop</div>
            <div class="text-white fw-bold fs-5 mt-1">
              <?= $user_spec ? '<span class="text-success">Tersimpan</span>' : '<span class="text-warning">Belum Ada</span>'; ?>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="card-custom p-3 text-center">
            <div class="text-cyan fs-3 mb-1"><i class="bi bi-bag-check"></i></div>
            <div class="text-secondary small">Produk Dimiliki</div>
            <div class="text-white fw-bold fs-5 mt-1"><?= $total_products; ?> Produk</div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="card-custom p-3 text-center">
            <div class="text-cyan fs-3 mb-1"><i class="bi bi-receipt"></i></div>
            <div class="text-secondary small">Total Transaksi</div>
            <div class="text-white fw-bold fs-5 mt-1"><?= $total_orders; ?> Pesanan</div>
          </div>
        </div>
      </div>

      <!-- Card Specifications Summary -->
      <div class="card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-white fw-bold mb-0"><i class="bi bi-cpu text-cyan me-2"></i> Spesifikasi Laptop Terdaftar</h4>
          <a href="<?= BASE_URL; ?>/member/spesifikasi.php" class="btn btn-cyan btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Update Spesifikasi
          </a>
        </div>

        <?php if ($user_spec): ?>
          <div class="row g-3 text-secondary small bg-dark p-3 rounded-3 border border-secondary">
            <div class="col-md-6"><i class="bi bi-cpu text-cyan me-2"></i> CPU: <strong class="text-white"><?= htmlspecialchars($user_spec['cpu']); ?></strong></div>
            <div class="col-md-6"><i class="bi bi-memory text-cyan me-2"></i> RAM: <strong class="text-white"><?= $user_spec['ram']; ?> GB</strong></div>
            <div class="col-md-6"><i class="bi bi-gpu-card text-cyan me-2"></i> GPU: <strong class="text-white"><?= htmlspecialchars($user_spec['gpu']); ?></strong></div>
            <div class="col-md-6"><i class="bi bi-collection-play text-cyan me-2"></i> VRAM: <strong class="text-white"><?= $user_spec['vram']; ?> GB</strong></div>
            <div class="col-12"><i class="bi bi-windows text-cyan me-2"></i> OS: <strong class="text-white"><?= htmlspecialchars($user_spec['os']); ?></strong></div>
          </div>
        <?php else: ?>
          <div class="text-center py-4 bg-dark rounded-3 border border-warning text-warning">
            <i class="bi bi-exclamation-triangle fs-2 mb-2"></i>
            <p class="mb-2">Anda belum mendaftarkan spesifikasi laptop di akun ini.</p>
            <a href="<?= BASE_URL; ?>/member/spesifikasi.php" class="btn btn-cyan btn-sm">Input Spesifikasi Laptop Sekarang</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Card Recent Recommendations -->
      <div class="card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="text-white fw-bold mb-0"><i class="bi bi-stars text-cyan me-2"></i> Rekomendasi Game Paling Cocok</h4>
          <a href="<?= BASE_URL; ?>/member/rekomendasi.php" class="btn btn-outline-cyan btn-sm">Lihat Semua</a>
        </div>

        <?php if (count($top_recommendations) > 0): ?>
          <div class="row g-3">
            <?php foreach ($top_recommendations as $tr): ?>
              <div class="col-md-4">
                <div class="bg-dark p-3 rounded-3 border border-secondary h-100">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge <?= $tr['badge']; ?>"><?= $tr['label']; ?></span>
                    <span class="fw-bold text-cyan"><?= $tr['score']; ?>/100</span>
                  </div>
                  <h6 class="text-white fw-bold mb-2"><?= htmlspecialchars($tr['game']['name']); ?></h6>
                  <a href="<?= BASE_URL; ?>/detail-game.php?slug=<?= $tr['game']['slug']; ?>" class="btn btn-cyan btn-sm w-100 mt-2">Lihat Detail</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-secondary small mb-0">Input spesifikasi laptop terlebih dahulu untuk melihat rekomendasi game.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
