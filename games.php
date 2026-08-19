<?php
$pageTitle = 'Katalog Game Digital';
require_once __DIR__ . '/includes/functions.php';

$allGames = getAllGames();
$userSpec = $_SESSION['user_specs'];
$genres = getMasterGenres();

$search = trim($_GET['q'] ?? '');
$selectedGenre = trim($_GET['genre'] ?? '');

$filteredGames = array_filter($allGames, function($g) use ($search, $selectedGenre) {
    $matchSearch = empty($search) || (stripos($g['title'], $search) !== false) || (stripos($g['developer'], $search) !== false);
    $genresString = is_array($g['genres']) ? implode(', ', $g['genres']) : ($g['genre_names'] ?? '');
    $matchGenre = empty($selectedGenre) || (stripos($genresString, $selectedGenre) !== false);
    return $matchSearch && $matchGenre;
});

// Calculate compatibility and sort from highest score to lowest
foreach ($filteredGames as $key => $game) {
    $filteredGames[$key]['compat_data'] = calculateCompatibility($userSpec, $game);
}

usort($filteredGames, function($a, $b) {
    return $b['compat_data']['score'] <=> $a['compat_data']['score'];
});

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE TITLE -->
    <div class="mb-4 text-center text-md-start">
        <h1 class="display-6 text-white fw-bold mb-1"><i class="fas fa-th-large text-purple me-2"></i> KATALOG GAME DIGITAL</h1>
        <p class="text-muted mb-0">Temukan koleksi game PC original dan cek tingkat kecocokannya dengan spesifikasi laptopmu.</p>
    </div>

    <!-- FILTER BAR CARD (DARK PURPLE - NO WHITE BOX) -->
    <div class="card bg-card border border-purple p-3 shadow-lg mb-4">
        <form method="GET" action="<?php echo BASE_URL; ?>/games.php">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-9">
                    <select name="genre" class="astro-select" onchange="this.form.submit()">
                        <option value="">Semua Genre Game</option>
                        <?php foreach ($genres as $gen): ?>
                            <option value="<?php echo $gen['name']; ?>" <?php echo ($selectedGenre === $gen['name']) ? 'selected' : ''; ?>><?php echo $gen['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-purple w-100 fw-bold"><i class="fas fa-filter me-1"></i> Filter Game</button>
                </div>
            </div>
        </form>
    </div>

    <!-- GAMES GRID -->
    <div class="row g-4">
        <?php if (empty($filteredGames)): ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted display-1 mb-3"><i class="fas fa-ghost"></i></div>
                <h3 class="text-white fw-bold">Tidak ada game yang cocok dengan pencarian Anda.</h3>
                <p class="text-muted">Coba ganti kata kunci atau pilih genre game yang berbeda.</p>
                <a href="<?php echo BASE_URL; ?>/games.php" class="btn btn-outline-purple mt-2">Tampilkan Semua Game</a>
            </div>
        <?php else: ?>
            <?php foreach ($filteredGames as $game): 
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
                        <div class="small text-warning mb-2"><i class="fas fa-star text-warning"></i> <?php echo $game['rating']; ?> • <?php echo implode(', ', array_slice($game['genres'], 0, 2)); ?></div>
                        
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

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
