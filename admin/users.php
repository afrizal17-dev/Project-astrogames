<?php
// admin/users.php - Data Pengguna & Kelola Role
$page_title = "Data Pengguna - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Handle Toggle Role
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $target_uid = (int)$_GET['id'];
    $stmt_u = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt_u->execute([':id' => $target_uid]);
    $u = $stmt_u->fetch();

    if ($u) {
        $new_role = ($u['role'] === 'admin') ? 'user' : 'admin';
        $upd = $pdo->prepare("UPDATE users SET role = :r WHERE id = :id");
        $upd->execute([':r' => $new_role, ':id' => $target_uid]);
        $_SESSION['flash_success'] = "Role pengguna berhasil diubah menjadi " . strtoupper($new_role) . "!";
    }
    header("Location: " . BASE_URL . "/admin/users.php");
    exit();
}

$users = $pdo->query("SELECT u.*, us.cpu, us.gpu, us.ram 
                      FROM users u 
                      LEFT JOIN user_specs us ON u.id = us.user_id 
                      ORDER BY u.id ASC")->fetchAll();
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-people text-cyan me-2"></i> Data Pengguna Terdaftar</h1>
  </div>

  <div class="row g-4">
    <!-- Sidebar Admin -->
    <div class="col-md-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/games.php"><i class="bi bi-controller"></i> Kelola Game</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/produk.php"><i class="bi bi-bag-check"></i> Kelola Produk</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/transaksi.php"><i class="bi bi-receipt"></i> Data Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/users.php" class="active"><i class="bi bi-people"></i> Data Pengguna</a></li>
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
                <th>ID</th>
                <th>Nama & Email</th>
                <th>Spesifikasi Laptop</th>
                <th>Role</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td>#<?= $u['id']; ?></td>
                  <td>
                    <div class="text-white fw-bold"><?= htmlspecialchars($u['name']); ?></div>
                    <div class="text-cyan small"><?= htmlspecialchars($u['email']); ?></div>
                  </td>
                  <td class="text-secondary small">
                    <?php if ($u['cpu']): ?>
                      <div><i class="bi bi-cpu text-cyan me-1"></i> <?= htmlspecialchars($u['cpu']); ?></div>
                      <div><i class="bi bi-memory text-cyan me-1"></i> RAM: <?= $u['ram']; ?> GB</div>
                    <?php else: ?>
                      <span class="text-muted">Belum ada spek</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($u['role'] === 'admin'): ?>
                      <span class="badge bg-danger text-white">ADMIN</span>
                    <?php else: ?>
                      <span class="badge bg-dark border border-secondary text-secondary">USER</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-secondary small"><?= date('d/m/Y', strtotime($u['created_at'])); ?></td>
                  <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                      <a href="<?= BASE_URL; ?>/admin/users.php?action=toggle_role&id=<?= $u['id']; ?>" 
                         class="btn btn-outline-cyan btn-sm text-nowrap" 
                         onclick="return confirm('Ubah role pengguna ini?');">
                        Switch Role
                      </a>
                    <?php else: ?>
                      <span class="text-muted small">Akun Anda</span>
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
