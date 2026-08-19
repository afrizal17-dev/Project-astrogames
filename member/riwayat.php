<?php
// member/riwayat.php - Riwayat Transaksi & Pembayaran
$page_title = "Riwayat Transaksi - GameCheck Member";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.image as product_image 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       WHERE o.user_id = :uid 
                       ORDER BY o.created_at DESC");
$stmt->execute([':uid' => $user_id]);
$orders = $stmt->fetchAll();
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
          <li><a href="<?= BASE_URL; ?>/member/produk.php"><i class="bi bi-bag-check"></i> Produk Saya</a></li>
          <li><a href="<?= BASE_URL; ?>/member/riwayat.php" class="active"><i class="bi bi-receipt"></i> Riwayat Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/member/profil.php"><i class="bi bi-person-gear"></i> Pengaturan Profil</a></li>
          <li><a href="<?= BASE_URL; ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="text-gradient fw-bold mb-1"><i class="bi bi-receipt me-2"></i> Riwayat Transaksi Pembelian</h3>
          <p class="text-secondary small mb-0">Daftar seluruh invoice dan status pembayaran produk GameCheck Anda.</p>
        </div>
      </div>

      <?php if (count($orders) > 0): ?>
        <div class="card-custom p-3 border-secondary">
          <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Kode Order</th>
                  <th>Produk</th>
                  <th>Total Bayar</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $ord): ?>
                  <tr>
                    <td><strong class="text-cyan">#<?= htmlspecialchars($ord['order_code']); ?></strong></td>
                    <td class="text-white fw-semibold"><?= htmlspecialchars($ord['product_name']); ?></td>
                    <td class="text-white fw-bold">Rp <?= number_format($ord['amount'], 0, ',', '.'); ?></td>
                    <td>
                      <?php if ($ord['payment_status'] === 'paid'): ?>
                        <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i> PAID</span>
                      <?php elseif ($ord['payment_status'] === 'pending'): ?>
                        <span class="badge bg-warning text-dark px-3 py-1"><i class="bi bi-clock me-1"></i> PENDING</span>
                      <?php else: ?>
                        <span class="badge bg-danger text-white px-3 py-1"><?= strtoupper($ord['payment_status']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-secondary small"><?= date('d/m/Y H:i', strtotime($ord['created_at'])); ?></td>
                    <td>
                      <?php if ($ord['payment_status'] === 'pending'): ?>
                        <a href="<?= BASE_URL; ?>/checkout/index.php?order_code=<?= $ord['order_code']; ?>" class="btn btn-cyan btn-sm text-nowrap">
                          <i class="bi bi-qr-code me-1"></i> Bayar QRIS
                        </a>
                      <?php else: ?>
                        <a href="<?= BASE_URL; ?>/member/produk.php" class="btn btn-outline-cyan btn-sm text-nowrap">
                          <i class="bi bi-box-seam me-1"></i> Lihat Produk
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="card-custom p-5 text-center">
          <i class="bi bi-receipt-cutoff display-2 text-cyan mb-3"></i>
          <h4 class="text-white fw-bold mb-2">Belum Ada Transaksi</h4>
          <p class="text-secondary mb-4">Anda belum pernah membuat transaksi pembelian.</p>
          <a href="<?= BASE_URL; ?>/produk.php" class="btn btn-cyan">Beli Produk Sekarang</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
