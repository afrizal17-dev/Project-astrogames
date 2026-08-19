<?php
// admin/transaksi.php - Data Transaksi Seluruh Pengguna
$page_title = "Data Transaksi - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Handle status change manually by Admin
if (isset($_GET['action']) && $_GET['action'] === 'set_paid' && isset($_GET['order_id'])) {
    $oid = (int)$_GET['order_id'];
    $stmt_o = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt_o->execute([':id' => $oid]);
    $order = $stmt_o->fetch();

    if ($order) {
        // Update order status
        $upd = $pdo->prepare("UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE id = :id");
        $upd->execute([':id' => $oid]);

        // Insert payment log if not exists
        $pay = $pdo->prepare("INSERT INTO payments (order_id, payment_method, transaction_id, status, paid_at) VALUES (:oid, 'MANUAL_ADMIN', :txid, 'SETTLEMENT', NOW())");
        $pay->execute([':oid' => $oid, ':txid' => 'MANUAL-' . time()]);

        // Activate user product if not exists
        $chk_prod = $pdo->prepare("SELECT id FROM user_products WHERE order_id = :oid");
        $chk_prod->execute([':oid' => $oid]);
        if (!$chk_prod->fetch()) {
            $ins_p = $pdo->prepare("INSERT INTO user_products (user_id, product_id, order_id, activated_at) VALUES (:uid, :pid, :oid, NOW())");
            $ins_p->execute([':uid' => $order['user_id'], ':pid' => $order['product_id'], ':oid' => $oid]);
        }

        $_SESSION['flash_success'] = "Status transaksi #" . htmlspecialchars($order['order_code']) . " berhasil diubah menjadi PAID!";
    }
    header("Location: " . BASE_URL . "/admin/transaksi.php");
    exit();
}

$stmt_all = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email, p.name as product_name 
                         FROM orders o 
                         JOIN users u ON o.user_id = u.id 
                         JOIN products p ON o.product_id = p.id 
                         ORDER BY o.created_at DESC");
$orders = $stmt_all->fetchAll();
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-receipt text-cyan me-2"></i> Data Transaksi & Pembayaran</h1>
  </div>

  <div class="row g-4">
    <!-- Sidebar Admin -->
    <div class="col-md-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/games.php"><i class="bi bi-controller"></i> Kelola Game</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/produk.php"><i class="bi bi-bag-check"></i> Kelola Produk</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/transaksi.php" class="active"><i class="bi bi-receipt"></i> Data Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/users.php"><i class="bi bi-people"></i> Data Pengguna</a></li>
          <li><hr class="border-secondary"></li>
          <li><a href="<?= BASE_URL; ?>/index.php"><i class="bi bi-house-door"></i> Kembali ke Website</a></li>
        </ul>
      </div>
    </div>

    <div class="col-md-9">
      <div class="card-custom p-3">
        <div class="table-responsive">
          <table class="table table-custom align-middle mb-0">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Pengguna</th>
                <th>Produk</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Aksi Admin</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr>
                  <td class="text-cyan fw-bold">#<?= htmlspecialchars($o['order_code']); ?></td>
                  <td>
                    <div class="text-white fw-semibold"><?= htmlspecialchars($o['user_name']); ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($o['user_email']); ?></div>
                  </td>
                  <td class="text-secondary small"><?= htmlspecialchars($o['product_name']); ?></td>
                  <td class="text-white fw-bold">Rp <?= number_format($o['amount'], 0, ',', '.'); ?></td>
                  <td>
                    <?php if ($o['payment_status'] === 'paid'): ?>
                      <span class="badge bg-success">PAID</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark"><?= strtoupper($o['payment_status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($o['payment_status'] !== 'paid'): ?>
                      <a href="<?= BASE_URL; ?>/admin/transaksi.php?action=set_paid&order_id=<?= $o['id']; ?>" 
                         class="btn btn-cyan btn-sm text-nowrap" 
                         onclick="return confirm('Tandai transaksi ini sebagai PAID secara manual?');">
                        <i class="bi bi-check-all me-1"></i> Set PAID
                      </a>
                    <?php else: ?>
                      <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i> Terverifikasi</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
