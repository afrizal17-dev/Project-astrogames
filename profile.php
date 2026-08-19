<?php
$pageTitle = 'Profil Saya & Spesifikasi Laptop';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$userSession = $_SESSION['user'];
$pdo = getDBConnection();

$user = false;
if ($pdo) {
    // Ambil data user terbaru dari DB
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$userSession['id']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    if (!$pdo) {
        $user = ['id' => $userSession['id'], 'username' => $userSession['username'] ?? 'Unknown', 'email' => $userSession['email'] ?? '', 'role' => $userSession['role'] ?? 'user', 'full_name' => '', 'avatar' => ''];
        $profileError = "Koneksi database gagal. Tidak dapat memuat profil lengkap.";
    } else {
        session_destroy();
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

$userSpec = $_SESSION['user_specs'];
$cpus = getMasterCPUs();
$gpus = getMasterGPUs();

$updatedSpec = false;
$updatedProfile = false;
$profileError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $fullname = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        $avatarName = $user['avatar'];
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['avatar']['tmp_name'];
            $fileName = basename($_FILES['avatar']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExt, $allowed)) {
                $newName = 'avatar_' . $user['id'] . '_' . time() . '.' . $fileExt;
                $destDir = __DIR__ . '/assets/images/avatars/';
                if (!is_dir($destDir)) mkdir($destDir, 0777, true);
                if (move_uploaded_file($tmpName, $destDir . $newName)) {
                    $avatarName = $newName;
                }
            } else {
                $profileError = "Format file avatar tidak didukung (hanya JPG, PNG, WEBP).";
            }
        }
        
        if (empty($profileError)) {
            if (!$pdo) {
                $profileError = "Tidak dapat menyimpan profil: Koneksi database terputus.";
            } else {
                if (!empty($password)) {
                    $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                    $stmtUpdate = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, password = ?, avatar = ? WHERE id = ?");
                    $stmtUpdate->execute([$fullname, $username, $email, $hashedPass, $avatarName, $user['id']]);
                } else {
                    $stmtUpdate = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, avatar = ? WHERE id = ?");
                    $stmtUpdate->execute([$fullname, $username, $email, $avatarName, $user['id']]);
                }
                
                $stmtUser->execute([$user['id']]);
                $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['user']['username'] = $user['username'];
                $_SESSION['user']['email'] = $user['email'];
                $_SESSION['user']['full_name'] = $user['full_name'];
                $_SESSION['user']['avatar'] = $user['avatar'];
                
                $updatedProfile = true;
            }
        }
    } else {
        $_SESSION['user_specs'] = [
            'cpu_id' => (int)($_POST['cpu_id'] ?? $userSpec['cpu_id']),
            'ram_gb' => (int)($_POST['ram_gb'] ?? $userSpec['ram_gb']),
            'gpu_id' => (int)($_POST['gpu_id'] ?? $userSpec['gpu_id']),
            'vram_gb' => (float)($_POST['vram_gb'] ?? $userSpec['vram_gb']),
            'storage_gb' => (int)($_POST['storage_gb'] ?? $userSpec['storage_gb']),
            'storage_type' => $_POST['storage_type'] ?? $userSpec['storage_type'],
            'os' => $_POST['os'] ?? $userSpec['os']
        ];
        $userSpec = $_SESSION['user_specs'];
        $updatedSpec = true;
    }
}

$currentCpuName = 'Intel Core i5-12400F';
foreach ($cpus as $c) { if ($c['id'] == $userSpec['cpu_id']) $currentCpuName = $c['name']; }

$currentGpuName = 'NVIDIA GeForce RTX 3060';
foreach ($gpus as $g) { if ($g['id'] == $userSpec['gpu_id']) $currentGpuName = $g['name']; }

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE TITLE -->
    <div class="mb-4 text-center text-md-start">
        <h1 class="display-6 text-white fw-bold mb-1"><i class="fas fa-user-circle text-purple me-2"></i> PROFIL & SPESIFIKASI LAPTOP</h1>
        <p class="text-muted mb-0">Kelola informasi akun Anda dan sesuaikan data spesifikasi laptop untuk analisis Game Compatibility Checker.</p>
    </div>

    <?php if ($updatedSpec): ?>
        <div class="alert alert-success bg-purple text-white border-0 rounded-4 p-3 mb-4 text-center fw-bold shadow-lg">
            <i class="fas fa-check-circle me-2"></i> Spesifikasi Laptop Anda Berhasil Diperbarui!
        </div>
    <?php endif; ?>
    <?php if ($updatedProfile): ?>
        <div class="alert alert-success bg-purple text-white border-0 rounded-4 p-3 mb-4 text-center fw-bold shadow-lg">
            <i class="fas fa-check-circle me-2"></i> Profil Anda Berhasil Diperbarui!
        </div>
    <?php endif; ?>
    <?php if (!empty($profileError)): ?>
        <div class="alert alert-danger bg-card border-danger text-danger rounded-4 p-3 mb-4 text-center fw-bold shadow-lg">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($profileError); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT COLUMN: USER CARD PROFILE (DARK PURPLE - NO WHITE BOXES) -->
        <div class="col-lg-4">
            <div class="card bg-card border border-purple p-4 text-center shadow-lg">
                <!-- AVATAR WITH NEON GLOW RING -->
                <div class="profile-avatar-wrapper mb-3">
                    <?php 
                        $avatarUrl = (!empty($user['avatar']) && $user['avatar'] !== 'default-avatar.png') 
                            ? BASE_URL . '/assets/images/avatars/' . htmlspecialchars($user['avatar']) 
                            : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80';
                    ?>
                    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="profile-avatar-img" style="object-fit: cover;">
                </div>

                <h3 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h3>
                <div class="badge bg-purple text-white px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-shield-alt me-1"></i> <?php echo strtoupper($user['role']); ?> ASTROGAMES
                </div>
                <div class="text-muted small mb-4"><?php echo htmlspecialchars($user['email'] ?? 'Tidak ada email terdaftar'); ?></div>

                <!-- USER STATS (DARK PANELS) -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="profile-spec-box text-center">
                            <div class="fs-4 fw-bold text-warning"><?php echo count($_SESSION['wishlist'] ?? []); ?></div>
                            <div class="small text-muted">Wishlist</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="profile-spec-box text-center">
                            <div class="fs-4 fw-bold text-success"><?php echo count($_SESSION['library'] ?? [1]); ?></div>
                            <div class="small text-muted">Game Owned</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-purple" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fas fa-user-edit me-2"></i> Edit Profil</button>
                    <a href="<?php echo BASE_URL; ?>/library.php" class="btn btn-outline-purple"><i class="fas fa-gamepad me-2"></i> Buka My Game Library</a>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: LAPTOP SPECIFICATION DISPLAY & EDIT FORM (DARK PURPLE GLASS) -->
        <div class="col-lg-8">
            <div class="card bg-card border border-purple p-4 shadow-lg mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-white fw-bold mb-0"><i class="fas fa-laptop text-purple me-2"></i> Spesifikasi Perangkat Terdaftar</h4>
                    <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check me-1"></i> Mode Analisis Aktif</span>
                </div>

                <!-- CURRENT SPEC GRID (DARK PANELS - NO WHITE BOXES) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="profile-spec-box">
                            <div class="small text-purple fw-bold mb-1"><i class="fas fa-microchip me-1"></i> PROCESSOR (CPU)</div>
                            <div class="fw-bold text-white fs-6"><?php echo $currentCpuName; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-spec-box">
                            <div class="small text-purple fw-bold mb-1"><i class="fas fa-desktop me-1"></i> GRAPHICS (GPU)</div>
                            <div class="fw-bold text-white fs-6"><?php echo $currentGpuName; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-spec-box">
                            <div class="small text-purple fw-bold mb-1"><i class="fas fa-memory me-1"></i> RAM KAPASITAS</div>
                            <div class="fw-bold text-white fs-6"><?php echo $userSpec['ram_gb']; ?> GB RAM</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-spec-box">
                            <div class="small text-purple fw-bold mb-1"><i class="fas fa-hdd me-1"></i> VRAM & STORAGE</div>
                            <?php 
                                $vramDisplay = ($userSpec['vram_gb'] < 1) ? ($userSpec['vram_gb'] * 1024) . ' MB' : $userSpec['vram_gb'] . ' GB';
                            ?>
                            <div class="fw-bold text-white fs-6"><?php echo $vramDisplay; ?> VRAM • <?php echo $userSpec['storage_gb']; ?>GB <?php echo $userSpec['storage_type']; ?></div>
                        </div>
                    </div>
                </div>

                <hr class="border-purple my-4">

                <!-- EDIT SPEC FORM (DARK PURPLE INPUTS) -->
                <h5 class="text-white fw-bold mb-3"><i class="fas fa-edit text-purple me-2"></i> Update Spesifikasi Laptop</h5>
                <form method="POST" action="<?php echo BASE_URL; ?>/profile.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="astro-label">Pilih Processor (CPU)</label>
                            <select name="cpu_id" class="astro-select">
                                <?php foreach ($cpus as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $userSpec['cpu_id']) ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="astro-label">Pilih Kartu Grafis (GPU)</label>
                            <select name="gpu_id" class="astro-select">
                                <?php foreach ($gpus as $g): ?>
                                    <option value="<?php echo $g['id']; ?>" <?php echo ($g['id'] == $userSpec['gpu_id']) ? 'selected' : ''; ?>><?php echo $g['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="astro-label">Kapasitas RAM</label>
                            <select name="ram_gb" class="astro-select">
                                <option value="4" <?php echo ($userSpec['ram_gb'] == 4) ? 'selected' : ''; ?>>4 GB RAM</option>
                                <option value="8" <?php echo ($userSpec['ram_gb'] == 8) ? 'selected' : ''; ?>>8 GB RAM</option>
                                <option value="16" <?php echo ($userSpec['ram_gb'] == 16) ? 'selected' : ''; ?>>16 GB RAM</option>
                                <option value="32" <?php echo ($userSpec['ram_gb'] == 32) ? 'selected' : ''; ?>>32 GB RAM</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="astro-label">VRAM VGA</label>
                            <select name="vram_gb" class="astro-select">
                                <option value="0.0625" <?php echo ($userSpec['vram_gb'] == 0.0625) ? 'selected' : ''; ?>>64 MB VRAM</option>
                                <option value="0.125" <?php echo ($userSpec['vram_gb'] == 0.125) ? 'selected' : ''; ?>>128 MB VRAM</option>
                                <option value="0.25" <?php echo ($userSpec['vram_gb'] == 0.25) ? 'selected' : ''; ?>>256 MB VRAM</option>
                                <option value="0.5" <?php echo ($userSpec['vram_gb'] == 0.5) ? 'selected' : ''; ?>>512 MB VRAM</option>
                                <option value="1" <?php echo ($userSpec['vram_gb'] == 1) ? 'selected' : ''; ?>>1 GB VRAM</option>
                                <option value="2" <?php echo ($userSpec['vram_gb'] == 2) ? 'selected' : ''; ?>>2 GB VRAM</option>
                                <option value="4" <?php echo ($userSpec['vram_gb'] == 4) ? 'selected' : ''; ?>>4 GB VRAM</option>
                                <option value="6" <?php echo ($userSpec['vram_gb'] == 6) ? 'selected' : ''; ?>>6 GB VRAM</option>
                                <option value="8" <?php echo ($userSpec['vram_gb'] == 8) ? 'selected' : ''; ?>>8 GB VRAM</option>
                                <option value="12" <?php echo ($userSpec['vram_gb'] == 12) ? 'selected' : ''; ?>>12 GB VRAM</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-purple py-2 px-4 fw-bold">
                        <i class="fas fa-save me-2"></i> Simpan Spesifikasi Laptop
                    </button>
                </form>
            </div>
        </div>
    </div>

</main>

<!-- EDIT PROFILE MODAL -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-card border-purple">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white fw-bold" id="editProfileModalLabel"><i class="fas fa-user-edit text-purple me-2"></i> Edit Profil Anda</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/profile.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-4 text-center">
                        <div class="mb-2 text-muted small">Pilih Foto Profil Baru (Opsional)</div>
                        <input type="file" name="avatar" class="form-control bg-dark text-light border-secondary" accept="image/png, image/jpeg, image/jpg, image/webp">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-light">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-light">Username</label>
                        <input type="text" name="username" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-light">Email Address</label>
                        <input type="email" name="email" class="form-control bg-dark text-light border-secondary" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-light">Password Baru <span class="text-muted small">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Masukkan password baru">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-purple">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
