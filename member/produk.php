<?php
// member/produk.php - Produk Digital yang Dimiliki User
$page_title = "Produk Saya - GameCheck Member";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];

// Get purchased products
$stmt = $pdo->prepare("SELECT up.*, p.name, p.slug, p.description, p.image, p.price, o.order_code 
                       FROM user_products up 
                       JOIN products p ON up.product_id = p.id 
                       JOIN orders o ON up.order_id = o.id 
                       WHERE up.user_id = :uid 
                       ORDER BY up.activated_at DESC");
$stmt->execute([':uid' => $user_id]);
$my_products = $stmt->fetchAll();
?>

<div class="container py-5">
  <div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/member/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li><a href="<?= BASE_URL; ?>/member/spesifikasi.php"><i class="bi bi-laptop"></i> Spesifikasi Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/rekomendasi.php"><i class="bi bi-stars"></i> Rekomendasi Game</a></li>
          <li><a href="<?= BASE_URL; ?>/member/produk.php" class="active"><i class="bi bi-bag-check"></i> Produk Saya</a></li>
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
          <h3 class="text-gradient fw-bold mb-1"><i class="bi bi-bag-check me-2"></i> Produk Digital Saya</h3>
          <p class="text-secondary small mb-0">Daftar produk yang telah berhasil Anda beli dan aktif secara permanen di akun Anda.</p>
        </div>
        <a href="<?= BASE_URL; ?>/produk.php" class="btn btn-cyan btn-sm">
          <i class="bi bi-cart-plus me-1"></i> Beli Produk Lain
        </a>
      </div>

      <?php if (count($my_products) > 0): ?>
        <div class="row g-4">
          <?php foreach ($my_products as $mp): ?>
            <div class="col-md-6">
              <div class="card-custom p-4 h-100 d-flex flex-column border-success">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
                  <span class="text-secondary small">Order: <code>#<?= htmlspecialchars($mp['order_code']); ?></code></span>
                </div>

                <h4 class="text-white fw-bold mb-2"><?= htmlspecialchars($mp['name']); ?></h4>
                <p class="text-secondary small mb-3 flex-grow-1">
                  <?= htmlspecialchars($mp['description']); ?>
                </p>

                <div class="pt-3 border-top border-secondary mt-auto text-secondary small">
                  <div class="mb-3"><i class="bi bi-calendar-check me-1 text-cyan"></i> Diaktifkan: <?= date('d M Y, H:i', strtotime($mp['activated_at'])); ?> WIB</div>
                  
                  <button type="button" class="btn btn-cyan w-100 fw-bold" onclick="alert('File produk digital telah diaktifkan di akun Anda! (Simulasi download file versi demo).');">
                    <i class="bi bi-download me-1"></i> Unduh / Akses Produk
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="card-custom p-5 text-center">
          <i class="bi bi-bag-x display-2 text-cyan mb-3"></i>
          <h4 class="text-white fw-bold mb-2">Belum Memiliki Produk</h4>
          <p class="text-secondary mb-4">Anda belum membeli produk digital apapun. Lihat katalog produk untuk panduan optimasi gaming.</p>
          <a href="<?= BASE_URL; ?>/produk.php" class="btn btn-cyan">Jelajahi Katalog Produk</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
