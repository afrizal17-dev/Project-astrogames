<?php
$pageTitle = 'Checkout Order';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk checkout pesanan.";
    header("Location: login.php");
    exit;
}

$cartGames = [];
$subtotal = 0;

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

foreach ($_SESSION['cart'] as $gid) {
    $game = getGameByIdOrSlug($gid);
    if ($game) {
        $final = $game['price'] * (1 - $game['discount_percentage']/100);
        $game['final_price'] = $final;
        $cartGames[] = $game;
        $subtotal += $final;
    }
}

$voucherCode = $_GET['voucher'] ?? '';
$voucherDiscount = 0;
$voucherMsg = '';

if ($voucherCode === 'ASTROLAUNCH') {
    $voucherDiscount = $subtotal * 0.20;
    $voucherMsg = '<i class="fas fa-check-circle text-success"></i> Voucher ASTROLAUNCH (Diskon 20%) Berhasil Dipasang!';
} else if ($voucherCode === 'GAMERID') {
    $voucherDiscount = 50000;
    $voucherMsg = '<i class="fas fa-check-circle text-success"></i> Voucher GAMERID (Diskon Rp 50.000) Berhasil Dipasang!';
} else if ($voucherCode === 'PROMO50') {
    $voucherDiscount = $subtotal * 0.50;
    $voucherMsg = '<i class="fas fa-check-circle text-success"></i> Voucher PROMO50 (Diskon 50%) Berhasil Dipasang!';
} else if (!empty($voucherCode)) {
    $voucherMsg = '<i class="fas fa-times-circle text-danger"></i> Kode voucher tidak valid atau sudah kadaluarsa.';
}

$totalFinal = max(0, $subtotal - $voucherDiscount);
?>

<main class="container py-4">

    <h1 class="display-6 text-white fw-bold mb-4"><i class="fas fa-cash-register text-purple me-2"></i> CHECKOUT ORDER</h1>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card bg-card border border-secondary p-4 mb-4">
                <h5 class="text-white fw-bold mb-3">Item yang Dibeli (<?php echo count($cartGames); ?>)</h5>
                
                <?php foreach ($cartGames as $game): ?>
                <div class="d-flex align-items-center gap-3 p-3 bg-dark rounded border border-secondary mb-2">
                    <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo $game['title']; ?>" style="width: 60px; height: 80px; object-fit: cover; border-radius: 6px;">
                    <div class="flex-grow-1">
                        <h5 class="text-white fw-bold mb-1"><?php echo $game['title']; ?></h5>
                        <div class="small text-muted mb-1"><?php echo $game['developer']; ?> • Lisensi Steam Key Instant</div>
                        <span class="badge bg-purple small">Digital Activation</span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-purple"><?php echo formatRupiah($game['final_price']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Voucher Input Form -->
            <div class="card bg-card border border-secondary p-4">
                <h5 class="text-white fw-bold mb-3"><i class="fas fa-ticket-alt text-warning me-2"></i> Masukkan Kode Voucher</h5>
                
                <form method="GET" action="checkout.php" class="d-flex gap-2 mb-2">
                    <input type="text" name="voucher" class="astro-input" placeholder="Contoh: ASTROLAUNCH / GAMERID / PROMO50" value="<?php echo htmlspecialchars($voucherCode); ?>">
                    <button type="submit" class="btn btn-outline-purple px-4">Gunakan</button>
                </form>

                <?php if (!empty($voucherMsg)): ?>
                    <div class="small fw-bold mt-2 <?php echo (strpos($voucherMsg, '<i class="fas fa-check-circle text-success"></i>') !== false) ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $voucherMsg; ?>
                    </div>
                <?php endif; ?>
                <div class="small text-muted mt-2">
                    <i class="fas fa-lightbulb text-warning"></i> Kode rekomendasi: <code class="text-purple">ASTROLAUNCH</code> (Diskon 20%), <code class="text-purple">GAMERID</code> (Potongan Rp 50rb)
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-card border border-purple p-4">
                <h5 class="text-white fw-bold mb-3">Rincian Pembayaran</h5>
                
                <div class="d-flex justify-content-between text-muted mb-2">
                    <span>Subtotal Game:</span>
                    <span><?php echo formatRupiah($subtotal); ?></span>
                </div>
                
                <?php if ($voucherDiscount > 0): ?>
                <div class="d-flex justify-content-between text-success mb-2">
                    <span>Diskon Voucher:</span>
                    <span>-<?php echo formatRupiah($voucherDiscount); ?></span>
                </div>
                <?php endif; ?>

                <hr class="border-secondary my-3">

                <div class="d-flex justify-content-between text-white fw-bold fs-4 mb-4">
                    <span>Total Bayar:</span>
                    <span class="text-purple"><?php echo formatRupiah($totalFinal); ?></span>
                </div>

                <a href="payment.php?amount=<?php echo $totalFinal; ?>" class="btn btn-purple btn-lg w-100 py-3 fw-bold">
                    Lanjut Pembayaran <i class="fas fa-credit-card ms-2"></i>
                </a>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
