<?php
// admin/produk.php - Kelola Produk Digital Admin
$page_title = "Kelola Produk - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Handle Add Product POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    $ins_stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, stock, status) VALUES (:name, :slug, :desc, :price, 999, 'active')");
    $ins_stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $description, ':price' => $price]);
    $_SESSION['flash_success'] = "Produk digital baru berhasil ditambahkan!";
    header("Location: " . BASE_URL . "/admin/produk.php");
    exit();
}

// Handle Delete GET
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $del = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $del->execute([':id' => $pid]);
    $_SESSION['flash_success'] = "Produk berhasil dihapus.";
    header("Location: " . BASE_URL . "/admin/produk.php");
    exit();
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-bag-check text-cyan me-2"></i> Kelola Produk Digital</h1>
  </div>

  <div class="row g-4">
    <!-- Sidebar Admin -->
    <div class="col-md-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/games.php"><i class="bi bi-controller"></i> Kelola Game</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/produk.php" class="active"><i class="bi bi-bag-check"></i> Kelola Produk</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/transaksi.php"><i class="bi bi-receipt"></i> Data Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/users.php"><i class="bi bi-people"></i> Data Pengguna</a></li>
          <li><hr class="border-secondary"></li>
          <li><a href="<?= BASE_URL; ?>/index.php"><i class="bi bi-house-door"></i> Kembali ke Website</a></li>
        </ul>
      </div>
    </div>

    <div class="col-md-9">
      <!-- Add Product Form Box -->
      <div class="card-custom p-4 mb-4">
        <h4 class="text-white fw-bold mb-3"><i class="bi bi-plus-circle text-cyan me-2"></i> Tambah Produk Digital Baru</h4>
        <form method="POST" action="<?= BASE_URL; ?>/admin/produk.php">
          <input type="hidden" name="action" value="add">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label text-white small">Nama Produk *</label>
              <input type="text" name="name" class="form-control form-control-custom" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-white small">Harga (Rp) *</label>
              <input type="number" name="price" class="form-control form-control-custom" required>
            </div>
            <div class="col-12">
              <label class="form-label text-white small">Deskripsi Produk *</label>
              <textarea name="description" class="form-control form-control-custom" rows="2" required></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-cyan btn-sm">Tambah Produk</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Product List Table -->
      <div class="card-custom p-3">
        <div class="table-responsive">
          <table class="table table-custom align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td>#<?= $p['id']; ?></td>
                  <td class="text-white fw-bold"><?= htmlspecialchars($p['name']); ?></td>
                  <td class="text-cyan fw-bold">Rp <?= number_format($p['price'], 0, ',', '.'); ?></td>
                  <td><span class="badge bg-success"><?= strtoupper($p['status']); ?></span></td>
                  <td>
                    <a href="<?= BASE_URL; ?>/admin/produk.php?action=delete&id=<?= $p['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus produk ini?');"><i class="bi bi-trash"></i></a>
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
