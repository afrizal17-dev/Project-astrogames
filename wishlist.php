<?php
$pageTitle = 'Wishlist Saya';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk melihat wishlist.";
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Handle Item Deletion from Wishlist
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $removeId = (int)$_GET['id'];
    if (($key = array_search($removeId, $_SESSION['wishlist'])) !== false) {
        unset($_SESSION['wishlist'][$key]);
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']); // reindex
    }
    header("Location: " . BASE_URL . "/wishlist.php?removed=1");
    exit;
}

$allGames = getAllGames();
$wishlistIds = $_SESSION['wishlist'] ?? [];
$wishlistGames = [];

foreach ($allGames as $g) {
    if (in_array($g['id'], $wishlistIds)) {
        $wishlistGames[] = $g;
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 text-white fw-bold mb-1">WISHLIST GAME SAYA</h1>
            <p class="text-muted mb-0">Daftar game yang Anda simpan untuk dibeli di kemudian hari.</p>
        </div>
        <span class="badge bg-purple fs-6 px-3 py-2 rounded-pill"><?php echo count($wishlistGames); ?> Game Disimpan</span>
    </div>

    <?php if (isset($_GET['removed'])): ?>
        <div class="alert alert-warning bg-card border border-warning text-warning rounded-4 p-3 mb-4 text-center fw-bold shadow-sm">
            <i class="fas fa-trash-alt me-2"></i> Game berhasil dihapus dari Wishlist Anda!
        </div>
    <?php endif; ?>

    <?php if (empty($wishlistGames)): ?>
        <div class="card bg-card border border-purple text-center p-5 rounded-4 shadow-lg my-4">

            <h3 class="text-white fw-bold mb-2">Wishlist Anda Masih Kosong</h3>
            <p class="text-muted mb-4 fs-5">Jelajahi katalog game kami dan klik ikon hati pada game favorit Anda untuk menyimpannya di sini.</p>
            <div>
                <a href="<?php echo BASE_URL; ?>/games.php" class="btn btn-purple btn-lg px-4 fw-bold">
                    <i class="fas fa-gamepad me-2"></i> Jelajahi Katalog Game
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlistGames as $game): 
                $finalPrice = $game['price'] * (1 - $game['discount_percentage']/100);
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="game-card">
                    <div class="game-card-img-wrapper">
                        <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-card-img">
                    </div>
                    <div class="game-card-body">
                        <h3 class="game-card-title text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                        <div class="small text-warning mb-2"><i class="fas fa-star text-warning"></i> <?php echo $game['rating']; ?> • <?php echo htmlspecialchars($game['developer']); ?></div>
                        
                        <div class="mt-auto">
                            <div class="fw-bold text-purple fs-5 mb-3"><?php echo formatRupiah($finalPrice); ?></div>
                            <div class="d-grid gap-2">
                                <a href="<?php echo BASE_URL; ?>/game-detail.php?slug=<?php echo $game['slug']; ?>" class="btn btn-outline-purple btn-sm">Lihat Detail</a>
                                <!-- REAL WORKING REMOVE LINK -->
                                <a href="<?php echo BASE_URL; ?>/wishlist.php?action=remove&id=<?php echo $game['id']; ?>" class="btn btn-outline-danger btn-sm fw-bold">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus dari Wishlist
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
