<?php
// member/rekomendasi.php - Halaman Rekomendasi Game Khusus Member
$page_title = "Rekomendasi Game Laptop Saya - GameCheck Member";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/recommendation-engine.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$user_spec = $stmt->fetch();
?>

<div class="container py-5">
  <div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/member/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li><a href="<?= BASE_URL; ?>/member/spesifikasi.php"><i class="bi bi-laptop"></i> Spesifikasi Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/rekomendasi.php" class="active"><i class="bi bi-stars"></i> Rekomendasi Game</a></li>
          <li><a href="<?= BASE_URL; ?>/member/produk.php"><i class="bi bi-bag-check"></i> Produk Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/riwayat.php"><i class="bi bi-receipt"></i> Riwayat Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/member/profil.php"><i class="bi bi-person-gear"></i> Pengaturan Profil</a></li>
          <li><a href="<?= BASE_URL; ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-lg-9">
      <?php if (!$user_spec): ?>
        <div class="card-custom p-5 text-center">
          <i class="bi bi-exclamation-circle display-2 text-warning mb-3"></i>
          <h3 class="text-white fw-bold mb-2">Spesifikasi Laptop Belum Diisi</h3>
          <p class="text-secondary mb-4">Silakan simpan spesifikasi laptop Anda terlebih dahulu untuk melihat analisis kecocokan game.</p>
          <a href="<?= BASE_URL; ?>/member/spesifikasi.php" class="btn btn-cyan">Input Spesifikasi Laptop</a>
        </div>
      <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="text-gradient fw-bold mb-1"><i class="bi bi-stars me-2"></i> Rekomendasi Game Laptop Saya</h3>
            <p class="text-secondary small mb-0">Hasil analisis otomatis berdasarkan spesifikasi laptop terdaftar Anda.</p>
          </div>
          <a href="<?= BASE_URL; ?>/member/spesifikasi.php" class="btn btn-outline-cyan btn-sm">Edit Spek</a>
        </div>

        <?php
        $games_stmt = $pdo->query("SELECT * FROM games ORDER BY id ASC");
        $all_games = $games_stmt->fetchAll();

        $recommendations = [];
        foreach ($all_games as $g) {
            $analysis = analyzeGameCompatibility($user_spec, $g);
            $recommendations[] = [
                'game' => $g,
                'result' => $analysis
            ];
        }

        // Sort Highest Score First
        usort($recommendations, function($a, $b) {
            return $b['result']['score'] <=> $a['result']['score'];
        });
        ?>

        <div class="row g-4">
          <?php foreach ($recommendations as $rec): 
            $g = $rec['game'];
            $res = $rec['result'];
          ?>
            <div class="col-md-6">
              <div class="card-custom p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="text-white fw-bold mb-0 me-2"><?= htmlspecialchars($g['name']); ?></h5>
                  <span class="score-badge <?= $res['badge']; ?> text-nowrap"><?= $res['score']; ?>/100</span>
                </div>

                <div class="mb-2">
                  <span class="badge <?= $res['badge']; ?> me-1"><?= $res['icon']; ?> <?= $res['label']; ?></span>
                  <span class="badge bg-dark text-secondary border border-secondary"><?= htmlspecialchars($g['genre']); ?></span>
                </div>

                <p class="text-secondary small mb-3"><?= htmlspecialchars($res['reason']); ?></p>

                <a href="<?= BASE_URL; ?>/detail-game.php?slug=<?= $g['slug']; ?>" class="btn btn-cyan btn-sm w-100 mt-auto">
                  <i class="bi bi-info-circle me-1"></i> Detail Requirements
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
