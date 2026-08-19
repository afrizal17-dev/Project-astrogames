<?php
// admin/games.php - Kelola Daftar Game (CRUD Admin)
$page_title = "Kelola Game - Admin GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    $del_stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
    $del_stmt->execute([':id' => $game_id]);
    $_SESSION['flash_success'] = "Game berhasil dihapus dari database.";
    header("Location: " . BASE_URL . "/admin/games.php");
    exit();
}

// Fetch all games
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM games WHERE name LIKE :s OR genre LIKE :s ORDER BY id DESC");
    $stmt->execute([':s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM games ORDER BY id DESC");
}
$games = $stmt->fetchAll();
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
    <div>
      <h1 class="display-6 text-white fw-bold mb-0"><i class="bi bi-controller text-cyan me-2"></i> Kelola Katalog Game</h1>
      <p class="text-secondary small mb-0">Tambah, ubah, dan hapus game serta perincian spesifikasinya</p>
    </div>
    <a href="<?= BASE_URL; ?>/admin/tambah-game.php" class="btn btn-cyan">
      <i class="bi bi-plus-circle me-1"></i> Tambah Game Baru
    </a>
  </div>

  <div class="row g-4">
    <!-- Sidebar Admin -->
    <div class="col-md-3">
      <div class="sidebar-member">
        <ul class="sidebar-menu">
          <li><a href="<?= BASE_URL; ?>/admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/games.php" class="active"><i class="bi bi-controller"></i> Kelola Game</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/produk.php"><i class="bi bi-bag-check"></i> Kelola Produk</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/transaksi.php"><i class="bi bi-receipt"></i> Data Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/admin/users.php"><i class="bi bi-people"></i> Data Pengguna</a></li>
          <li><hr class="border-secondary"></li>
          <li><a href="<?= BASE_URL; ?>/index.php"><i class="bi bi-house-door"></i> Kembali ke Website</a></li>
        </ul>
      </div>
    </div>

    <div class="col-md-9">
      <!-- Search Form -->
      <div class="card-custom p-3 mb-4">
        <form method="GET" action="<?= BASE_URL; ?>/admin/games.php" class="d-flex gap-2">
          <input type="text" name="search" class="form-control form-control-custom" placeholder="Cari nama game..." value="<?= htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-cyan">Cari</button>
        </form>
      </div>

      <div class="card-custom p-3">
        <div class="table-responsive">
          <table class="table table-custom align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Game</th>
                <th>Genre</th>
                <th>Min RAM / VRAM</th>
                <th>Difficulty</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($games as $g): ?>
                <tr>
                  <td>#<?= $g['id']; ?></td>
                  <td class="text-white fw-bold"><?= htmlspecialchars($g['name']); ?></td>
                  <td class="text-cyan small"><?= htmlspecialchars($g['genre']); ?></td>
                  <td class="text-secondary small"><?= $g['minimum_ram']; ?> GB / <?= $g['minimum_vram']; ?> GB</td>
                  <td>
                    <span class="badge-difficulty badge-<?= strtolower($g['difficulty']); ?> position-relative top-0 right-0">
                      <?= $g['difficulty']; ?>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <a href="<?= BASE_URL; ?>/admin/edit-game.php?id=<?= $g['id']; ?>" class="btn btn-outline-cyan btn-sm"><i class="bi bi-pencil"></i></a>
                      <a href="<?= BASE_URL; ?>/admin/games.php?action=delete&id=<?= $g['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus game ini?');"><i class="bi bi-trash"></i></a>
                    </div>
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
