<?php
// produk.php - Katalog Produk Digital GameCheck
$page_title = "Katalog Produk Digital - GameCheck";
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY id ASC");
$products = $stmt->fetchAll();
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <span class="badge bg-cyan text-dark fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase">
      <i class="bi bi-bag-check-fill me-1"></i> Official Store
    </span>
    <h1 class="display-6 text-gradient fw-bold">Katalog Produk Digital GameCheck</h1>
    <p class="text-secondary mx-auto" style="max-width: 650px;">
      Tingkatkan performa laptop gaming kamu dan dapatkan koleksi panduan optimasi eksklusif yang siap diunduh secara instan.
    </p>
  </div>

  <!-- Notice Alert -->
  <div class="alert alert-info border-cyan text-white p-3 rounded-4 mb-5 mx-auto" style="background: rgba(0, 242, 254, 0.08); max-width: 850px;">
    <i class="bi bi-shield-check text-cyan me-2"></i> 
    <strong>Catatan Penting:</strong> Seluruh fitur utama GameCheck seperti <em>Cek Spesifikasi</em> dan <em>Rekomendasi Game</em> adalah <strong>100% GRATIS</strong>. Produk digital di bawah ini adalah produk opsional bagi kamu yang ingin mengoptimalkan sistem Windows & laptop gaming lebih lanjut.
  </div>

  <!-- Products Grid -->
  <div class="row g-4 justify-content-center">
    <?php foreach ($products as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-custom p-4 h-100 d-flex flex-direction-column">
          <div class="mb-3 text-center bg-dark p-3 rounded-3 border border-secondary" style="height: 180px; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-file-earmark-zip-fill text-cyan display-1"></i>
          </div>

          <h4 class="text-white fw-bold mb-2"><?= htmlspecialchars($p['name']); ?></h4>
          <p class="text-secondary small mb-4 flex-grow-1">
            <?= nl2br(htmlspecialchars($p['description'])); ?>
          </p>

          <div class="pt-3 border-top border-secondary mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-secondary small">Harga Satuan</span>
              <span class="text-cyan fw-extrabold fs-4">
                Rp <?= number_format($p['price'], 0, ',', '.'); ?>
              </span>
            </div>

            <a href="<?= BASE_URL; ?>/checkout/index.php?product_id=<?= $p['id']; ?>" class="btn btn-cyan w-100 py-2 fw-bold">
              <i class="bi bi-cart-plus-fill me-1"></i> Beli Sekarang
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
