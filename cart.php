<?php
$pageTitle = 'Keranjang Belanja';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk mengakses keranjang.";
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Handle Item Deletion from Cart
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $removeId = (int)$_GET['id'];
    if (isset($_SESSION['cart']) && ($key = array_search($removeId, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    }
    header("Location: " . BASE_URL . "/cart.php?removed=1");
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$cartGames = [];
$totalOriginal = 0;
$totalFinal = 0;

foreach ($_SESSION['cart'] as $gid) {
    $game = getGameByIdOrSlug($gid);
    if ($game) {
        $final = $game['price'] * (1 - $game['discount_percentage']/100);
        $game['final_price'] = $final;
        $cartGames[] = $game;
        $totalOriginal += $game['price'];
        $totalFinal += $final;
    }
}
?>

<main class="container py-4">

    <h1 class="display-6 text-white fw-bold mb-4">KERANJANG BELANJA</h1>

    <?php if (empty($cartGames)): ?>
        <div class="card bg-card border border-secondary p-5 text-center my-5">
            <h3 class="text-white fw-bold">Keranjang masih kosong.</h3>
            <p class="text-muted mb-4">Anda belum menambahkan game ke keranjang belanja.</p>
            <div>
                <a href="games.php" class="btn btn-purple btn-lg px-4 py-2">Jelajahi Game Sekarang</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card bg-card border border-secondary p-3">
                    <h5 class="text-white fw-bold mb-3">Item Game dalam Keranjang (<?php echo count($cartGames); ?>)</h5>
                    
                    <?php foreach ($cartGames as $g): ?>
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom border-secondary">
                        <img src="<?php echo $g['cover_image']; ?>" alt="<?php echo $g['title']; ?>" style="width: 70px; height: 90px; object-fit: cover; border-radius: 6px;">
                        <div class="flex-grow-1">
                            <h5 class="text-white fw-bold mb-1"><?php echo $g['title']; ?></h5>
                            <div class="small text-muted mb-1"><?php echo $g['developer']; ?> • License Key Digital</div>
                            <span class="badge bg-purple small">Digital Product</span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-purple fs-5"><?php echo formatRupiah($g['final_price']); ?></div>
                            <?php if ($g['discount_percentage'] > 0): ?>
                                <div class="small text-muted text-decoration-line-through"><?php echo formatRupiah($g['price']); ?></div>
                            <?php endif; ?>
                            <a href="cart.php?action=remove&id=<?php echo $g['id']; ?>" class="btn btn-link text-danger p-0 small mt-1">Hapus</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-card border border-purple p-4">
                    <h5 class="text-white fw-bold mb-3">Ringkasan Belanja</h5>
                    
                    <div class="d-flex justify-content-between text-muted mb-2">
                        <span>Total Harga Normal:</span>
                        <span><?php echo formatRupiah($totalOriginal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-success mb-3">
                        <span>Total Diskon Game:</span>
                        <span>-<?php echo formatRupiah($totalOriginal - $totalFinal); ?></span>
                    </div>

                    <hr class="border-secondary my-3">

                    <div class="d-flex justify-content-between text-white fw-bold fs-5 mb-4">
                        <span>Total Pembayaran:</span>
                        <span class="text-purple"><?php echo formatRupiah($totalFinal); ?></span>
                    </div>

                    <a href="checkout.php" class="btn btn-purple btn-lg w-100 py-3 fw-bold">
                        Lanjut Checkout <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
