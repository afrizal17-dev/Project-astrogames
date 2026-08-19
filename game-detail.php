<?php
$pageTitle = 'Detail Game';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? 'cyberpunk-2077';
$game = getGameByIdOrSlug($slug);

if (!$game) {
    header("Location: " . BASE_URL . "/games.php");
    exit;
}

$pageTitle = $game['title'];
$userSpec = $_SESSION['user_specs'];
$compat = calculateCompatibility($userSpec, $game);
$finalPrice = $game['price'] * (1 - $game['discount_percentage']/100);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <!-- GAME BANNER HEADER -->
    <div class="card bg-card border border-purple overflow-hidden mb-4 shadow-lg">
        <div style="height: 320px; background-image: url('<?php echo $game['banner_image']; ?>'); background-size: cover; background-position: center; position: relative;">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(12, 6, 32, 0.4) 0%, rgba(12, 6, 32, 0.95) 100%);"></div>
            <div class="position-absolute bottom-0 start-0 p-4 w-100 d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
                <div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <?php foreach ($game['genres'] as $g): ?>
                            <span class="badge bg-purple text-white px-3 py-1 rounded-pill"><?php echo htmlspecialchars($g); ?></span>
                        <?php endforeach; ?>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold"><i class="fas fa-star text-warning"></i> <?php echo $game['rating']; ?> / 5</span>
                    </div>
                    <h1 class="display-5 text-white fw-bold mb-1"><?php echo htmlspecialchars($game['title']); ?></h1>
                    <div class="text-light fs-6">Developer: <strong class="text-purple"><?php echo htmlspecialchars($game['developer']); ?></strong> • Rilis: <?php echo htmlspecialchars($game['release_date']); ?></div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-md-end">
                        <div class="display-6 text-purple fw-bold mb-0"><?php echo formatRupiah($finalPrice); ?></div>
                        <?php if ($game['discount_percentage'] > 0): ?>
                            <div class="small text-muted text-decoration-line-through"><?php echo formatRupiah($game['price']); ?> (-<?php echo $game['discount_percentage']; ?>%)</div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-purple btn-lg px-4 py-3 fw-bold btn-add-cart" data-game-id="<?php echo $game['id']; ?>" data-game-title="<?php echo htmlspecialchars($game['title']); ?>">
                        <i class="fas fa-shopping-cart"></i> Beli Game
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- COMPATIBILITY SCORE CARD (STUNNING DARK PURPLE GLASS) -->
    <div class="card bg-card border border-purple p-4 rounded-4 shadow-lg mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-3 text-center border-end border-purple">
                <div class="display-3 fw-bold <?php echo ($compat['score'] >= 80) ? 'text-success' : (($compat['score'] >= 60) ? 'text-warning' : 'text-danger'); ?>">
                    <?php echo $compat['score']; ?>%
                </div>
                <div class="fw-bold text-white fs-5">Skor Kecocokan</div>
                <div class="small text-muted">Berdasarkan Laptop Spesifikasi Anda</div>
            </div>
            <div class="col-md-9">
                <div class="compatibility-badge <?php echo strtolower($compat['status']); ?> fs-6 px-3 py-2 mb-2">
                    <?php echo $compat['badgeText']; ?>
                </div>
                <p class="text-light fs-5 mb-2"><?php echo htmlspecialchars($compat['statusDesc']); ?></p>
                <div class="row g-2 mt-2">
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-purple text-center small">
                            <span class="text-muted d-block">Estimasi FPS</span>
                            <strong class="text-purple"><?php echo $compat['recommendedSettings']['performance']; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-purple text-center small">
                            <span class="text-muted d-block">Resolusi</span>
                            <strong class="text-purple"><?php echo $compat['recommendedSettings']['resolution']; ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-dark border border-purple text-center small">
                            <span class="text-muted d-block">Settingan Grafis</span>
                            <strong class="text-purple"><?php echo $compat['recommendedSettings']['graphics']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GAME DESCRIPTION & REQUIREMENT BREAKDOWN -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card bg-card border border-purple p-4 shadow-lg mb-4">
                <h4 class="text-white fw-bold mb-3"><i class="fas fa-align-left text-purple me-2"></i> Deskripsi Lengkap Game</h4>
                <div class="text-light fs-6 leading-relaxed style-description mb-4" style="white-space: pre-line; line-height: 1.8;">
                    <?php echo htmlspecialchars($game['description'] ?? $game['short_description']); ?>
                </div>

                <?php if (!empty($game['video_url'])): ?>
                <h5 class="text-white fw-bold mb-3"><i class="fas fa-film text-purple me-2"></i> Trailer Game</h5>
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden border border-purple">
                    <iframe src="<?php echo getYoutubeEmbedUrl($game['video_url']); ?>" title="Trailer" allowfullscreen></iframe>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card bg-card border border-purple p-4 shadow-lg">
                <h4 class="text-white fw-bold mb-3"><i class="fas fa-cogs text-purple me-2"></i> Kebutuhan Spesifikasi PC/Laptop</h4>

                <div class="mb-4">
                    <h6 class="text-warning fw-bold mb-2"><i class="fas fa-minus-circle me-1"></i> Minimum Requirements</h6>
                    <ul class="list-unstyled text-light small d-flex flex-column gap-2">
                        <li class="p-2 bg-dark rounded border border-purple"><strong>CPU:</strong> <?php echo htmlspecialchars($game['min_cpu']); ?></li>
                        <li class="p-2 bg-dark rounded border border-purple"><strong>GPU:</strong> <?php echo htmlspecialchars($game['min_gpu']); ?></li>
                        <li class="p-2 bg-dark rounded border border-purple"><strong>RAM / VRAM:</strong> <?php echo $game['min_ram_gb']; ?> GB RAM • <?php echo $game['min_vram_gb']; ?> GB VRAM</li>
                        <li class="p-2 bg-dark rounded border border-purple"><strong>Storage:</strong> <?php echo $game['min_storage_gb']; ?> GB <?php echo $game['storage_type']; ?></li>
                    </ul>
                </div>

                <div>
                    <h6 class="text-success fw-bold mb-2"><i class="fas fa-check-circle me-1"></i> Recommended Requirements</h6>
                    <ul class="list-unstyled text-light small d-flex flex-column gap-2">
                        <li class="p-2 bg-dark rounded border border-purple"><strong>CPU:</strong> <?php echo htmlspecialchars($game['rec_cpu'] ?? 'Intel Core i7 / Ryzen 7'); ?></li>
                        <li class="p-2 bg-dark rounded border border-purple"><strong>GPU:</strong> <?php echo htmlspecialchars($game['rec_gpu'] ?? 'NVIDIA RTX 3070 / RX 6800'); ?></li>
                        <li class="p-2 bg-dark rounded border border-purple"><strong>RAM:</strong> <?php echo $game['rec_ram_gb'] ?? 16; ?> GB RAM</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
