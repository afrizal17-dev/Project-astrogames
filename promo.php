<?php
$pageTitle = 'Promo & Diskon Terhebat';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$allGames = getAllGames();
$userSpec = $_SESSION['user_specs'];

// Filter game diskon
$promoGames = array_filter($allGames, function($g) {
    return $g['discount_percentage'] > 0;
});
?>

<main class="container py-4">

    <div class="card bg-card border border-purple p-4 mb-5 text-center shadow-lg" style="background: linear-gradient(135deg, rgba(20,22,31,0.95), rgba(139,92,246,0.3));">
        <h1 class="display-5 fw-bold mb-2 animated-promo-title"><i class="fas fa-fire promo-icon-pulse"></i> PROMO & DISKON TERBESAR</h1>
        <p class="text-light fs-5 mb-0">Nikmati potongan harga hingga 75% untuk game-game favoritmu. Promo terbatas!</p>
    </div>

    <div class="row g-4">
        <?php foreach ($promoGames as $game): 
            $compat = calculateCompatibility($userSpec, $game);
            $finalPrice = $game['price'] * (1 - $game['discount_percentage']/100);
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="game-card">
                <span class="badge-discount">-<?php echo $game['discount_percentage']; ?>% OFF</span>

                <div class="game-card-img-wrapper">
                    <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo $game['title']; ?>" class="game-card-img">
                </div>

                <div class="game-card-body">
                    <h3 class="game-card-title text-white"><?php echo $game['title']; ?></h3>
                    <div class="small text-warning mb-2"><i class="fas fa-star text-warning"></i> <?php echo $game['rating']; ?></div>

                    <div class="compatibility-badge <?php echo strtolower($compat['status']); ?>">
                        <?php echo $compat['badgeText']; ?>
                    </div>

                    <div class="mt-auto">
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <div class="fw-bold text-purple fs-5"><?php echo formatRupiah($finalPrice); ?></div>
                            <div class="small text-muted text-decoration-line-through"><?php echo formatRupiah($game['price']); ?></div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="game-detail.php?slug=<?php echo $game['slug']; ?>" class="btn btn-purple btn-sm">Beli Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
