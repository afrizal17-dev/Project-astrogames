<?php
$pageTitle = 'Temukan Game yang Cocok untuk Laptopmu';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$allGames = getAllGames();
$userSpec = $_SESSION['user_specs'] ?? null;
$genres = getMasterGenres();

$heroCyberpunk = getGameByIdOrSlug('cyberpunk-2077');
$heroForza = getGameByIdOrSlug('forza-horizon-5');
$heroGta = getGameByIdOrSlug('gta-v');
$defaultBg1 = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80';
$defaultBg2 = 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1600&q=80';
$defaultBg3 = 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=1600&q=80';
?>

<main class="container py-4">

    <!-- HERO SLIDER CAROUSEL SECTION (BACKGROUND SLIDE GAME IMAGES) -->
    <div id="heroCarousel" class="carousel slide hero-slider-container" data-bs-ride="carousel" data-bs-interval="4000">
        <!-- Carousel Indicators -->
        <div class="carousel-indicators mb-3">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <div class="carousel-inner">
            <!-- SLIDE 1: Cyberpunk 2077 / Main Banner -->
            <div class="carousel-item active">
                <div class="hero-slide-item" style="background-image: url('<?php echo $heroCyberpunk ? htmlspecialchars($heroCyberpunk['banner_image']) : $defaultBg1; ?>');">
                    <div class="hero-slide-overlay">
                        <div class="row align-items-center g-4 w-100">
                            <div class="col-lg-7">
                                <div class="hero-tag">
                                    Game Store + Spec Analyzer v2.0
                                </div>
                                <h1 class="hero-title">
                                    Temukan Game yang<br>
                                    <span style="color: var(--purple-glow);">Cocok untuk Laptopmu</span>
                                </h1>
                                <p class="hero-subtitle">
                                    Cek kompatibilitas game dengan spesifikasi laptopmu, beli game favorit, dan nikmati gaming tanpa khawatir lag.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <a href="<?php echo BASE_URL; ?>/check-specs.php" class="btn-purple-glow">
                                        Cek Laptop Saya
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/games.php" class="btn-purple-outline">
                                        Lihat Semua Game
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="stat-pill">16 Games</div>
                                    <div class="stat-pill">7 Gratis</div>
                                    <div class="stat-pill">Analisis Instan</div>
                                </div>
                            </div>
                            <div class="col-lg-5 d-none d-lg-block">
                                <div class="hero-featured-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-purple text-white px-2 py-1 small fw-bold">FEATURED</span>
                                        <span class="text-warning small fw-bold"><i class="fas fa-star"></i> 4.9</span>
                                    </div>
                                    <h3 class="text-white fw-bold h4 mb-1">Cyberpunk 2077</h3>
                                    <div class="small text-muted mb-2">Action / RPG / Open World</div>
                                    <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=cyberpunk-2077" class="btn btn-sm btn-outline-purple w-100">Lihat Detail Game</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: Forza Horizon 5 -->
            <div class="carousel-item">
                <div class="hero-slide-item" style="background-image: url('<?php echo $heroForza ? htmlspecialchars($heroForza['banner_image']) : $defaultBg2; ?>');">
                    <div class="hero-slide-overlay">
                        <div class="row align-items-center g-4 w-100">
                            <div class="col-lg-7">
                                <div class="hero-tag">
                                    Racing Open World Best-Seller
                                </div>
                                <h1 class="hero-title">
                                    Forza Horizon 5<br>
                                    <span style="color: var(--purple-glow);">Balapan Tanpa Batas</span>
                                </h1>
                                <p class="hero-subtitle">
                                    Nikmati petualangan balap grafis tinggi di lanskap meksiko dengan optimasi spesifikasi laptop terbaik.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=forza-horizon-5" class="btn-purple-glow">
                                        Beli Forza Horizon 5
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/check-specs.php" class="btn-purple-outline">
                                        Cek Kompatibilitas
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="stat-pill">Rating 4.9/5</div>
                                    <div class="stat-pill">Diskon -35%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: GTA V -->
            <div class="carousel-item">
                <div class="hero-slide-item" style="background-image: url('<?php echo $heroGta ? htmlspecialchars($heroGta['banner_image']) : $defaultBg3; ?>');">
                    <div class="hero-slide-overlay">
                        <div class="row align-items-center g-4 w-100">
                            <div class="col-lg-7">
                                <div class="hero-tag">
                                    Action Open World Legendaris
                                </div>
                                <h1 class="hero-title">
                                    Grand Theft Auto V<br>
                                    <span style="color: var(--purple-glow);">Premium Edition</span>
                                </h1>
                                <p class="hero-subtitle">
                                    Jelajahi Los Santos bersama jutaan pemain. Garansi 100% lisensi resmi digital.
                                </p>
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=gta-v" class="btn-purple-glow">
                                        Beli Sekarang -50%
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- KENAPA MEMILIH ASTROGAMES (DARK PURPLE CARDS) -->
    <section class="mb-5">
        <div class="text-center mb-4">
            <h2 class="display-6 text-white fw-bold mb-2">Kenapa Memilih ASTROGAMES?</h2>
            <p class="text-muted fs-5">Solusi terbaik belanja game digital tanpa ragu performa laptop.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card bg-card border border-purple p-4 h-100 text-center">
                    <div class="mb-3 text-purple"><i class="fas fa-laptop-code fa-3x"></i></div>
                    <h5 class="text-white fw-bold mb-2">Analisis Kompatibilitas</h5>
                    <p class="text-muted small mb-0">Ketahui secara pasti apakah laptopmu kuat memainkan game sebelum membeli.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card bg-card border border-purple p-4 h-100 text-center">
                    <div class="mb-3 text-purple"><i class="fas fa-shield-alt fa-3x"></i></div>
                    <h5 class="text-white fw-bold mb-2">100% Lisensi Resmi</h5>
                    <p class="text-muted small mb-0">Kunci aktivasi resmi terhubung langsung dengan platform publisher resmi.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card bg-card border border-purple p-4 h-100 text-center">
                    <div class="mb-3 text-purple"><i class="fas fa-bolt fa-3x"></i></div>
                    <h5 class="text-white fw-bold mb-2">Pengiriman Instan</h5>
                    <p class="text-muted small mb-0">Kunci produk digital langsung terkirim otomatis ke menu My Library setelah bayar.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card bg-card border border-purple p-4 h-100 text-center">
                    <div class="mb-3 text-purple"><i class="fas fa-wallet fa-3x"></i></div>
                    <h5 class="text-white fw-bold mb-2">Pembayaran Mudah</h5>
                    <p class="text-muted small mb-0">Dukungan QRIS, Virtual Account Bank, dan E-Wallet lokal tanpa biaya tersembunyi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KATALOG PROMO GAME (DARK PURPLE CARDS) -->
    <section class="mb-5">
        <div class="section-title-wrap d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title text-white mb-0">PROMO TERBARU</h2>
            <a href="<?php echo BASE_URL; ?>/promo.php" class="btn btn-outline-purple btn-sm">Lihat Semua Promo <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach (array_slice($allGames, 0, 4) as $game): 
                $compat = calculateCompatibility($userSpec, $game);
                $finalPrice = $game['price'] * (1 - $game['discount_percentage']/100);
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="game-card">
                    <button class="btn-wishlist-float btn-wishlist-toggle" data-game-id="<?php echo $game['id']; ?>" title="Wishlist"><i class="fas fa-heart"></i></button>
                    <div class="game-card-img-wrapper">
                        <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-card-img">
                    </div>
                    <div class="game-card-body">
                        <h3 class="game-card-title text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                        <div class="small text-warning mb-2"><i class="fas fa-star"></i> <?php echo $game['rating']; ?> • <?php echo implode(', ', array_slice($game['genres'], 0, 2)); ?></div>
                        <div class="compatibility-badge <?php echo strtolower($compat['status']); ?>">
                            <?php echo $compat['badgeText']; ?>
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
                                <button class="btn btn-purple btn-sm btn-add-cart" data-game-id="<?php echo $game['id']; ?>" data-game-title="<?php echo htmlspecialchars($game['title']); ?>"><i class="fas fa-shopping-cart me-1"></i> Keranjang</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- WELCOME / RECOMMENDATION MODAL -->
    <div class="modal fade" id="welcomeSpecModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4 px-4">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center text-purple rounded-circle" style="width: 80px; height: 80px; background: rgba(139, 92, 246, 0.15);">
                            <i class="fas fa-microchip fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-3 text-white">Bingung Memilih Game?</h4>
                    <p class="text-muted mb-4" style="font-size: 0.95rem;">
                        Jangan ragu dengan performa laptop Anda. Gunakan fitur cerdas <strong class="text-purple">Cek Spek Game</strong> kami untuk menganalisis spesifikasi laptop Anda secara instan dan dapatkan rekomendasi game yang dijamin 100% berjalan lancar.
                    </p>
                    <div class="d-grid gap-3">
                        <a href="<?php echo BASE_URL; ?>/check-specs.php" class="btn btn-purple btn-lg fw-bold rounded-pill">
                            <i class="fas fa-search me-2"></i> Cek Rekomendasi Sekarang
                        </a>
                        <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tampilkan modal setiap kali halaman dimuat (selalu muncul)
        setTimeout(function() {
            var welcomeModalEl = document.getElementById('welcomeSpecModal');
            if (welcomeModalEl) {
                var welcomeModal = new bootstrap.Modal(welcomeModalEl);
                welcomeModal.show();
            }
        }, 800); // Delay 0.8 detik agar transisi halaman mulus
    });
    </script>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
