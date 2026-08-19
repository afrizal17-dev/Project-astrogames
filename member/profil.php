<?php
// member/profil.php - Edit Profil & Pengaturan Akun
$page_title = "Pengaturan Profil - GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);

    if (empty($name) || empty($email)) {
        $_SESSION['flash_error'] = "Nama dan email tidak boleh kosong!";
    } else {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, password = :pass WHERE id = :id");
            $update_stmt->execute([':name' => $name, ':email' => $email, ':pass' => $hashed, ':id' => $user_id]);
        } else {
            $update_stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
            $update_stmt->execute([':name' => $name, ':email' => $email, ':id' => $user_id]);
        }

        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['flash_success'] = "Profil Anda berhasil diperbarui!";
        header("Location: " . BASE_URL . "/member/profil.php");
        exit();
    }
}
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
          <li><a href="<?= BASE_URL; ?>/member/riwayat.php"><i class="bi bi-receipt"></i> Riwayat Transaksi</a></li>
          <li><a href="<?= BASE_URL; ?>/member/profil.php" class="active"><i class="bi bi-person-gear"></i> Pengaturan Profil</a></li>
          <li><a href="<?= BASE_URL; ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>

    <!-- Main Form Area -->
    <div class="col-lg-9">
      <div class="card-custom p-4 p-md-5">
        <h3 class="text-white fw-bold mb-4"><i class="bi bi-person-gear text-cyan me-2"></i> Pengaturan Profil Member</h3>

        <form method="POST" action="<?= BASE_URL; ?>/member/profil.php">
          <div class="mb-3">
            <label for="name" class="form-label text-white fw-bold">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="form-control form-control-custom" value="<?= htmlspecialchars($user['name']); ?>" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label text-white fw-bold">Alamat Email</label>
            <input type="email" id="email" name="email" class="form-control form-control-custom" value="<?= htmlspecialchars($user['email']); ?>" required>
          </div>

          <div class="mb-4 pt-3 border-top border-secondary">
            <label for="new_password" class="form-label text-white fw-bold">Password Baru (Opsional)</label>
            <input type="password" id="new_password" name="new_password" class="form-control form-control-custom" placeholder="Biarkan kosong jika tidak ingin mengubah password">
          </div>

          <button type="submit" class="btn btn-cyan px-4 py-2 fw-bold">
            <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
