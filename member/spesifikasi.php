<?php
// member/spesifikasi.php - Kelola Spesifikasi Laptop Tersimpan
$page_title = "Spesifikasi Laptop Saya - GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];

// Fetch current specs
$stmt = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$spec = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpu  = trim($_POST['cpu']);
    $ram  = (int)$_POST['ram'];
    $gpu  = trim($_POST['gpu']);
    $vram = (int)$_POST['vram'];
    $os   = trim($_POST['os']);

    if (empty($cpu) || empty($gpu)) {
        $_SESSION['flash_error'] = "CPU dan GPU wajib diisi!";
    } else {
        $stmt_save = $pdo->prepare("INSERT INTO user_specs (user_id, cpu, ram, gpu, vram, os) 
                                    VALUES (:uid, :cpu, :ram, :gpu, :vram, :os) 
                                    ON DUPLICATE KEY UPDATE cpu=:cpu, ram=:ram, gpu=:gpu, vram=:vram, os=:os, updated_at=NOW()");
        $stmt_save->execute([
            ':uid'  => $user_id,
            ':cpu'  => $cpu,
            ':ram'  => $ram,
            ':gpu'  => $gpu,
            ':vram' => $vram,
            ':os'   => $os
        ]);

        $_SESSION['flash_success'] = "Spesifikasi laptop berhasil disimpan ke akun Anda!";
        header("Location: " . BASE_URL . "/member/spesifikasi.php");
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
          <li><a href="<?= BASE_URL; ?>/member/spesifikasi.php" class="active"><i class="bi bi-laptop"></i> Spesifikasi Saya</a></li>
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
      <div class="card-custom p-4 p-md-5">
        <h3 class="text-white fw-bold mb-3"><i class="bi bi-laptop text-cyan me-2"></i> Spesifikasi Laptop Terdaftar</h3>
        <p class="text-secondary small mb-4">Spesifikasi ini digunakan oleh engine GameCheck untuk menganalisis kecocokan secara otomatis setiap kali Anda menjelajahi game.</p>

        <form method="POST" action="<?= BASE_URL; ?>/member/spesifikasi.php">
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label for="cpu" class="form-label text-white fw-bold">Prosesor / CPU</label>
              <input type="text" id="cpu" name="cpu" class="form-control form-control-custom" value="<?= htmlspecialchars($spec['cpu'] ?? ''); ?>" placeholder="Contoh: Intel Core i5-11400H" required>
            </div>
            <div class="col-md-6">
              <label for="ram" class="form-label text-white fw-bold">Ukuran RAM</label>
              <select id="ram" name="ram" class="form-select form-select-custom" required>
                <option value="4" <?= (($spec['ram'] ?? 8) == 4) ? 'selected' : ''; ?>>4 GB</option>
                <option value="8" <?= (($spec['ram'] ?? 8) == 8) ? 'selected' : ''; ?>>8 GB</option>
                <option value="16" <?= (($spec['ram'] ?? 8) == 16) ? 'selected' : ''; ?>>16 GB</option>
                <option value="32" <?= (($spec['ram'] ?? 8) == 32) ? 'selected' : ''; ?>>32 GB</option>
                <option value="64" <?= (($spec['ram'] ?? 8) == 64) ? 'selected' : ''; ?>>64 GB</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="vram" class="form-label text-white fw-bold">Ukuran VRAM</label>
              <select id="vram" name="vram" class="form-select form-select-custom" required>
                <option value="1" <?= (($spec['vram'] ?? 4) == 1) ? 'selected' : ''; ?>>1 GB</option>
                <option value="2" <?= (($spec['vram'] ?? 4) == 2) ? 'selected' : ''; ?>>2 GB</option>
                <option value="4" <?= (($spec['vram'] ?? 4) == 4) ? 'selected' : ''; ?>>4 GB</option>
                <option value="6" <?= (($spec['vram'] ?? 4) == 6) ? 'selected' : ''; ?>>6 GB</option>
                <option value="8" <?= (($spec['vram'] ?? 4) == 8) ? 'selected' : ''; ?>>8 GB</option>
                <option value="12" <?= (($spec['vram'] ?? 4) == 12) ? 'selected' : ''; ?>>12 GB+</option>
              </select>
            </div>
            <div class="col-md-8">
              <label for="gpu" class="form-label text-white fw-bold">Kartu Grafis / GPU</label>
              <input type="text" id="gpu" name="gpu" class="form-control form-control-custom" value="<?= htmlspecialchars($spec['gpu'] ?? ''); ?>" placeholder="Contoh: NVIDIA GeForce RTX 3050" required>
            </div>
            <div class="col-md-4">
              <label for="os" class="form-label text-white fw-bold">Sistem Operasi</label>
              <select id="os" name="os" class="form-select form-select-custom" required>
                <option value="Windows 10" <?= (($spec['os'] ?? 'Windows 11') == 'Windows 10') ? 'selected' : ''; ?>>Windows 10</option>
                <option value="Windows 11" <?= (($spec['os'] ?? 'Windows 11') == 'Windows 11') ? 'selected' : ''; ?>>Windows 11</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-cyan px-4 py-2 fw-bold">
            <i class="bi bi-save me-1"></i> Simpan Spesifikasi Laptop
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
