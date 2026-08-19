<?php
$pageTitle = 'Laptop Compatibility Checker';
require_once __DIR__ . '/includes/functions.php';

$cpus = getMasterCPUs();
$gpus = getMasterGPUs();
$allGames = getAllGames();
$userSpec = $_SESSION['user_specs'];

if (isset($_GET['analyzed']) || isset($_POST['analyze'])) {
    $userSpec = [
        'cpu_id' => (int)($_GET['cpu'] ?? $_POST['cpu_id'] ?? $userSpec['cpu_id']),
        'ram_gb' => (int)($_GET['ram'] ?? $_POST['ram_gb'] ?? $userSpec['ram_gb']),
        'gpu_id' => (int)($_GET['gpu'] ?? $_POST['gpu_id'] ?? $userSpec['gpu_id']),
        'vram_gb' => (float)($_GET['vram'] ?? $_POST['vram_gb'] ?? $userSpec['vram_gb']),
        'storage_gb' => 512,
        'storage_type' => 'SSD',
        'os' => 'Windows 11'
    ];
    $_SESSION['user_specs'] = $userSpec;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <!-- HEADLINE SECTION -->
    <div class="text-center mb-4">
        <div class="badge bg-purple px-3 py-2 mb-2 rounded-pill"><i class="fas fa-microchip me-1"></i> ASTRO SPEC ENGINE v2.0</div>
        <h1 class="display-5 text-white fw-bold mb-2"><i class="fas fa-laptop text-purple me-2"></i> CHECK YOUR LAPTOP</h1>
        <p class="text-muted fs-5 max-w-700 mx-auto">Masukkan spesifikasi laptopmu dan sistem kami akan secara otomatis menganalisis serta menampilkan game mana saja yang cocok untuk dimainkan di perangkatmu.</p>
    </div>

    <!-- SPEC SELECTOR FORM CARD (DARK PURPLE - NO WHITE BOX) -->
    <div class="card bg-card border border-purple p-4 shadow-lg mb-5">
        <form id="laptopSpecForm" method="POST" action="<?php echo BASE_URL; ?>/check-specs.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="astro-label"><i class="fas fa-microchip text-purple me-1"></i> Processor (CPU)</label>
                    <select name="cpu_id" id="selectCpu" class="astro-select">
                        <?php foreach ($cpus as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $userSpec['cpu_id']) ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="astro-label"><i class="fas fa-memory text-purple me-1"></i> RAM System</label>
                    <select name="ram_gb" id="selectRam" class="astro-select">
                        <option value="4" <?php echo ($userSpec['ram_gb'] == 4) ? 'selected' : ''; ?>>4 GB</option>
                        <option value="8" <?php echo ($userSpec['ram_gb'] == 8) ? 'selected' : ''; ?>>8 GB</option>
                        <option value="16" <?php echo ($userSpec['ram_gb'] == 16) ? 'selected' : ''; ?>>16 GB</option>
                        <option value="32" <?php echo ($userSpec['ram_gb'] == 32) ? 'selected' : ''; ?>>32 GB</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="astro-label"><i class="fas fa-desktop text-purple me-1"></i> Kartu Grafis (GPU)</label>
                    <select name="gpu_id" id="selectGpu" class="astro-select">
                        <?php foreach ($gpus as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo ($g['id'] == $userSpec['gpu_id']) ? 'selected' : ''; ?>><?php echo $g['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="astro-label"><i class="fas fa-hdd text-purple me-1"></i> VRAM (VGA)</label>
                    <select name="vram_gb" id="selectVram" class="astro-select">
                        <option value="0.0625" <?php echo ($userSpec['vram_gb'] == 0.0625) ? 'selected' : ''; ?>>64 MB</option>
                        <option value="0.125" <?php echo ($userSpec['vram_gb'] == 0.125) ? 'selected' : ''; ?>>128 MB</option>
                        <option value="0.25" <?php echo ($userSpec['vram_gb'] == 0.25) ? 'selected' : ''; ?>>256 MB</option>
                        <option value="0.5" <?php echo ($userSpec['vram_gb'] == 0.5) ? 'selected' : ''; ?>>512 MB</option>
                        <option value="1" <?php echo ($userSpec['vram_gb'] == 1) ? 'selected' : ''; ?>>1 GB</option>
                        <option value="2" <?php echo ($userSpec['vram_gb'] == 2) ? 'selected' : ''; ?>>2 GB</option>
                        <option value="4" <?php echo ($userSpec['vram_gb'] == 4) ? 'selected' : ''; ?>>4 GB</option>
                        <option value="6" <?php echo ($userSpec['vram_gb'] == 6) ? 'selected' : ''; ?>>6 GB</option>
                        <option value="8" <?php echo ($userSpec['vram_gb'] == 8) ? 'selected' : ''; ?>>8 GB</option>
                        <option value="12" <?php echo ($userSpec['vram_gb'] == 12) ? 'selected' : ''; ?>>12 GB</option>
                    </select>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" name="analyze" class="btn btn-purple btn-lg px-5 fw-bold py-3">
                        <i class="fas fa-calculator me-2"></i> CEK KOMPATIBILITAS SEKARANG
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ANALYSIS RESULTS MODAL (DARK PURPLE CARDS) -->
    <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-card border-purple">
                <div class="modal-header border-purple">
                    <h5 class="modal-title text-white fw-bold" id="resultsModalLabel"><i class="fas fa-bullseye text-warning me-2"></i> GAME YANG COCOK UNTUK LAPTOPMU</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <?php 
                        $recommendedGames = [];
                        foreach ($allGames as $game) {
                            $compat = calculateCompatibility($userSpec, $game);
                            // Hanya masukkan game yang statusnya GREEN atau YELLOW
                            if ($compat['status'] !== 'RED') {
                                $game['compat_data'] = $compat;
                                $recommendedGames[] = $game;
                            }
                        }

                        // Urutkan dari skor kompatibilitas tertinggi ke terendah
                        usort($recommendedGames, function($a, $b) {
                            return $b['compat_data']['score'] <=> $a['compat_data']['score'];
                        });
                        
                        if (empty($recommendedGames)): ?>
                            <div class="col-12 text-center py-5">
                                <div class="text-muted display-1 mb-3"><i class="fas fa-laptop text-info"></i></div>
                                <h3 class="text-white fw-bold">Spesifikasi Kurang Memadai</h3>
                                <p class="text-muted">Maaf, berdasarkan spesifikasi laptop yang Anda pilih, saat ini belum ada game yang kami rekomendasikan untuk Anda mainkan dengan lancar.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recommendedGames as $game): 
                                $compat = $game['compat_data'];
                                $finalPrice = $game['price'] * (1 - $game['discount_percentage']/100);
                            ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="game-card">
                                    <button class="btn-wishlist-float btn-wishlist-toggle" data-game-id="<?php echo $game['id']; ?>">️</button>
                                    <div class="game-card-img-wrapper">
                                        <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-card-img">
                                    </div>
                                    <div class="game-card-body">
                                        <h3 class="game-card-title text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <div class="small text-warning mb-2"><i class="fas fa-star text-warning"></i> <?php echo $game['rating']; ?> • Skor: <?php echo $compat['score']; ?>%</div>
                                        
                                        <div class="compatibility-badge <?php echo strtolower($compat['status']); ?> mb-2">
                                            <?php echo $compat['badgeText']; ?>
                                        </div>

                                        <div class="small bg-dark p-2 rounded border border-secondary mb-3">
                                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.8rem;">
                                                <span><i class="fas fa-desktop"></i> Res:</span>
                                                <span class="text-white"><?php echo $compat['recommendedSettings']['resolution']; ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.8rem;">
                                                <span><i class="fas fa-sliders-h"></i> Set:</span>
                                                <span class="text-white"><?php echo $compat['recommendedSettings']['graphics']; ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                                                <span><i class="fas fa-tachometer-alt"></i> FPS:</span>
                                                <span class="text-info fw-bold"><?php echo $compat['recommendedSettings']['performance']; ?></span>
                                            </div>
                                        </div>

                                        <div class="mt-auto">
                                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                                <div class="fw-bold text-purple fs-5"><?php echo formatRupiah($finalPrice); ?></div>
                                                <?php if ($game['discount_percentage'] > 0): ?>
                                                    <div class="small text-muted text-decoration-line-through"><?php echo formatRupiah($game['price']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=<?php echo $game['slug']; ?>" class="btn btn-outline-purple btn-sm">Detail Game</a>
                                                <button class="btn btn-purple btn-sm btn-add-cart" data-game-id="<?php echo $game['id']; ?>" data-game-title="<?php echo htmlspecialchars($game['title']); ?>"><i class="fas fa-shopping-cart"></i> Keranjang</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php if (isset($_POST['analyze']) || isset($_GET['analyzed'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
        resultsModal.show();
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
