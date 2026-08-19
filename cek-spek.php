<?php
// cek-spek.php - Form Input Spesifikasi Laptop
$page_title = "Cek Spesifikasi Laptop - GameCheck";
require_once __DIR__ . '/includes/header.php';

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

// Fetch saved specs if user is logged in
$saved_cpu = '';
$saved_ram = 8;
$saved_gpu = '';
$saved_vram = 4;
$saved_os = 'Windows 11';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_specs WHERE user_id = :uid");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $user_spec = $stmt->fetch();
    if ($user_spec) {
        $saved_cpu  = $user_spec['cpu'];
        $saved_ram  = $user_spec['ram'];
        $saved_gpu  = $user_spec['gpu'];
        $saved_vram = $user_spec['vram'];
        $saved_os   = $user_spec['os'];
    }
} elseif (isset($_SESSION['guest_spec'])) {
    $saved_cpu  = $_SESSION['guest_spec']['cpu'];
    $saved_ram  = $_SESSION['guest_spec']['ram'];
    $saved_gpu  = $_SESSION['guest_spec']['gpu'];
    $saved_vram = $_SESSION['guest_spec']['vram'];
    $saved_os   = $_SESSION['guest_spec']['os'];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpu  = trim($_POST['cpu']);
    $ram  = (int)$_POST['ram'];
    $gpu  = trim($_POST['gpu']);
    $vram = (int)$_POST['vram'];
    $os   = trim($_POST['os']);
    $target_game = (int)$_POST['target_game_id'];

    if (empty($cpu) || empty($gpu)) {
        $_SESSION['flash_error'] = "Harap lengkapi nama CPU dan GPU laptop Anda!";
    } else {
        $spec_data = [
            'cpu'  => $cpu,
            'ram'  => $ram,
            'gpu'  => $gpu,
            'vram' => $vram,
            'os'   => $os
        ];

        $_SESSION['guest_spec'] = $spec_data;

        // If user is logged in, persist to database
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("INSERT INTO user_specs (user_id, cpu, ram, gpu, vram, os) 
                                   VALUES (:uid, :cpu, :ram, :gpu, :vram, :os) 
                                   ON DUPLICATE KEY UPDATE cpu=:cpu, ram=:ram, gpu=:gpu, vram=:vram, os=:os, updated_at=NOW()");
            $stmt->execute([
                ':uid'  => $_SESSION['user_id'],
                ':cpu'  => $cpu,
                ':ram'  => $ram,
                ':gpu'  => $gpu,
                ':vram' => $vram,
                ':os'   => $os
            ]);
        }

        $_SESSION['flash_success'] = "Analisis spesifikasi laptop berhasil diperbarui!";
        $redirect_url = BASE_URL . "/rekomendasi.php";
        if ($target_game > 0) {
            $redirect_url .= "?game_id=" . $target_game;
        }
        header("Location: " . $redirect_url);
        exit();
    }
}

// Fetch all games for target selection dropdown
$all_games_stmt = $pdo->query("SELECT id, name FROM games ORDER BY name ASC");
$all_games = $all_games_stmt->fetchAll();
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="text-center mb-4">
        <span class="badge bg-cyan text-dark fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase">
          <i class="bi bi-cpu-fill me-1"></i> Form Spesifikasi Laptop
        </span>
        <h1 class="display-6 text-gradient fw-bold">Cek Spesifikasi Laptop Anda</h1>
        <p class="text-secondary">Masukkan spesifikasi hardware laptop milikmu untuk melihat daftar game yang cocok.</p>
      </div>

      <!-- Information Alert Notice -->
      <div class="alert alert-info border-cyan text-white p-3 rounded-4 mb-4" style="background: rgba(0, 242, 254, 0.08);">
        <i class="bi bi-info-circle-fill text-cyan me-2"></i> 
        <strong>Catatan:</strong> Silakan masukkan spesifikasi laptop secara manual di bawah ini. Anda juga dapat menekan salah satu preset cepat di bawah form untuk mempermudah.
      </div>

      <div class="card-custom p-4 p-md-5">
        <form method="POST" action="<?= BASE_URL; ?>/cek-spek.php">
          <input type="hidden" name="target_game_id" value="<?= $game_id; ?>">

          <!-- Target Game selection if coming from game detail -->
          <?php if ($game_id > 0): ?>
            <div class="mb-4 p-3 bg-dark rounded-3 border border-warning">
              <label class="form-label text-warning fw-bold mb-1"><i class="bi bi-bullseye me-1"></i> Analisis Khusus Game:</label>
              <select name="target_game_id" class="form-select form-select-custom border-warning">
                <option value="0">Semua Game dalam Katalog</option>
                <?php foreach ($all_games as $g): ?>
                  <option value="<?= $g['id']; ?>" <?= ($g['id'] == $game_id) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($g['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <div class="row g-4">
            <!-- CPU / Processor -->
            <div class="col-12">
              <label for="cpu" class="form-label text-white fw-bold">
                <i class="bi bi-cpu text-cyan me-1"></i> CPU / Prosesor Laptop <span class="text-danger">*</span>
              </label>
              <input type="text" id="cpu" name="cpu" class="form-control form-control-custom" 
                     placeholder="Contoh: Intel Core i5-11400H atau AMD Ryzen 5 5600H" 
                     value="<?= htmlspecialchars($saved_cpu); ?>" required>
              <div class="form-text text-muted">Sebutkan seri prosesor laptop Anda (misal: Core i3-10100, Core i5, Ryzen 5, dll).</div>
            </div>

            <!-- RAM Dropdown -->
            <div class="col-md-6">
              <label for="ram" class="form-label text-white fw-bold">
                <i class="bi bi-memory text-cyan me-1"></i> Ukuran RAM <span class="text-danger">*</span>
              </label>
              <select id="ram" name="ram" class="form-select form-select-custom" required>
                <option value="4" <?= ($saved_ram == 4) ? 'selected' : ''; ?>>4 GB</option>
                <option value="8" <?= ($saved_ram == 8) ? 'selected' : ''; ?>>8 GB</option>
                <option value="16" <?= ($saved_ram == 16) ? 'selected' : ''; ?>>16 GB</option>
                <option value="32" <?= ($saved_ram == 32) ? 'selected' : ''; ?>>32 GB</option>
                <option value="64" <?= ($saved_ram == 64) ? 'selected' : ''; ?>>64 GB</option>
              </select>
            </div>

            <!-- VRAM Dropdown -->
            <div class="col-md-6">
              <label for="vram" class="form-label text-white fw-bold">
                <i class="bi bi-collection-play text-cyan me-1"></i> VRAM Kartu Grafis <span class="text-danger">*</span>
              </label>
              <select id="vram" name="vram" class="form-select form-select-custom" required>
                <option value="1" <?= ($saved_vram == 1) ? 'selected' : ''; ?>>1 GB</option>
                <option value="2" <?= ($saved_vram == 2) ? 'selected' : ''; ?>>2 GB</option>
                <option value="4" <?= ($saved_vram == 4) ? 'selected' : ''; ?>>4 GB</option>
                <option value="6" <?= ($saved_vram == 6) ? 'selected' : ''; ?>>6 GB</option>
                <option value="8" <?= ($saved_vram == 8) ? 'selected' : ''; ?>>8 GB</option>
                <option value="12" <?= ($saved_vram == 12) ? 'selected' : ''; ?>>12 GB+</option>
              </select>
            </div>

            <!-- GPU / VGA Text Input -->
            <div class="col-md-8">
              <label for="gpu" class="form-label text-white fw-bold">
                <i class="bi bi-gpu-card text-cyan me-1"></i> GPU / VGA Laptop <span class="text-danger">*</span>
              </label>
              <input type="text" id="gpu" name="gpu" class="form-control form-control-custom" 
                     placeholder="Contoh: NVIDIA GeForce RTX 3050 atau Intel UHD Graphics" 
                     value="<?= htmlspecialchars($saved_gpu); ?>" required>
              <div class="form-text text-muted">Sebutkan model VGA discrete atau integrated laptop Anda.</div>
            </div>

            <!-- OS Dropdown -->
            <div class="col-md-4">
              <label for="os" class="form-label text-white fw-bold">
                <i class="bi bi-windows text-cyan me-1"></i> Sistem Operasi <span class="text-danger">*</span>
              </label>
              <select id="os" name="os" class="form-select form-select-custom" required>
                <option value="Windows 10" <?= ($saved_os == 'Windows 10') ? 'selected' : ''; ?>>Windows 10</option>
                <option value="Windows 11" <?= ($saved_os == 'Windows 11') ? 'selected' : ''; ?>>Windows 11</option>
              </select>
            </div>
          </div>

          <!-- Quick Preset Fill Buttons for User Conveniency -->
          <div class="mt-4 pt-3 border-top border-secondary">
            <label class="form-label text-secondary small fw-bold mb-2"> Gunakan Preset Cepat Spesifikasi:</label>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-dark btn-sm border-secondary text-secondary" 
                      onclick="fillSpecPreset('Intel Core i3-10100', 8, 'Intel UHD Graphics 630', 2, 'Windows 10')">
                <i class="fas fa-laptop text-info"></i> Laptop Standard (i3 / 8GB / Intel UHD)
              </button>
              <button type="button" class="btn btn-dark btn-sm border-secondary text-secondary" 
                      onclick="fillSpecPreset('Intel Core i5-11400H', 16, 'NVIDIA GeForce RTX 3050', 4, 'Windows 11')">
                <i class="fas fa-gamepad"></i> Laptop Gaming Entry (i5 / 16GB / RTX 3050)
              </button>
              <button type="button" class="btn btn-dark btn-sm border-secondary text-secondary" 
                      onclick="fillSpecPreset('AMD Ryzen 7 5800H', 16, 'NVIDIA GeForce RTX 3060', 6, 'Windows 11')">
                <i class="fas fa-rocket text-primary"></i> Laptop High Performance (R7 / 16GB / RTX 3060)
              </button>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-cyan btn-lg w-100 py-3">
              <i class="bi bi-play-circle-fill me-2"></i> Analisis Laptop Saya Sekarang
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
