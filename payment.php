<?php
$pageTitle = 'Pembayaran Game';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['flash_error'] = "Sesi Anda telah berakhir, silakan login kembali.";
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$amount = (float)($_GET['amount'] ?? 0);

// For display purposes, get the first game's title, or "Multiple Games"
$firstGameId = $_SESSION['cart'][0];
$firstGame = getGameByIdOrSlug($firstGameId);
$gameTitle = count($_SESSION['cart']) > 1 ? count($_SESSION['cart']) . " Games (termasuk " . $firstGame['title'] . ")" : $firstGame['title'];

$paid = isset($_GET['action']) && $_GET['action'] === 'confirm';

if ($paid) {
    $userId = $_SESSION['user']['id'] ?? 2;
    $orderNumber = 'AG' . date('Ymd') . rand(100, 999);
    $totalOriginalPrice = 0;
    
    foreach ($_SESSION['cart'] as $gid) {
        $g = getGameByIdOrSlug($gid);
        if ($g) $totalOriginalPrice += $g['price'];
    }
    
    $discountAmount = max(0, $totalOriginalPrice - $amount);
    
    dbBegin();
    try {
        dbExecute("INSERT INTO orders (order_number, user_id, total_original_price, discount_amount, final_price, payment_status, payment_method) VALUES (?, ?, ?, ?, ?, 'paid', 'qris')", [$orderNumber, $userId, $totalOriginalPrice, $discountAmount, $amount]);
        $orderId = dbFetchOne("SELECT LAST_INSERT_ID() as id")['id'];
        
        foreach ($_SESSION['cart'] as $gid) {
            $g = getGameByIdOrSlug($gid);
            if ($g) {
                $itemPrice = $g['price'] * (1 - $g['discount_percentage']/100);
                dbExecute("INSERT INTO order_items (order_id, game_id, price, original_price) VALUES (?, ?, ?, ?)", [$orderId, $gid, $itemPrice, $g['price']]);
                
                if (!in_array($gid, $_SESSION['library'])) {
                    $_SESSION['library'][] = $gid;
                }
            }
        }
        dbCommit();
        $_SESSION['cart'] = [];
    } catch (Exception $e) {
        dbRollback();
        die("Terjadi kesalahan saat memproses pesanan: " . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <?php if ($paid): ?>
        <div class="max-w-600 mx-auto text-center py-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success display-1"></i>
            </div>
            <h1 class="display-6 text-white fw-bold mb-2">PAYMENT SUCCESSFUL!</h1>
            <p class="text-light fs-5 mb-4">
                Pembayaran sebesar <strong><?php echo formatRupiah($amount); ?></strong> telah diverifikasi secara otomatis. Game <strong><?php echo htmlspecialchars($gameTitle); ?></strong> telah berhasil masuk ke library Anda!
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="library.php" class="btn btn-purple btn-lg px-4 py-3"><i class="fas fa-gamepad"></i> Buka My Library</a>
                <a href="orders.php" class="btn btn-outline-purple btn-lg px-4 py-3">Lihat Order History</a>
            </div>
        </div>
    <?php else: ?>
        <h1 class="display-6 text-white fw-bold mb-4"><i class="fas fa-wallet text-purple me-2"></i> METODE PEMBAYARAN</h1>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card bg-card border border-secondary p-4 mb-4">
                    <h5 class="text-white fw-bold mb-3">Pilih Metode Pembayaran Resmi</h5>
                    
                    <div class="form-check p-3 rounded bg-dark border border-purple mb-3 d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="qris" checked>
                        <label class="form-check-label text-white fw-bold flex-grow-1" for="qris">
                            <i class="fas fa-qrcode text-warning me-2"></i> QRIS (Scan Semua E-Wallet & Mobile Banking)
                        </label>
                    </div>

                    <div class="form-check p-3 rounded bg-dark border border-secondary mb-3 d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="va">
                        <label class="form-check-label text-white fw-bold flex-grow-1" for="va">
                            <i class="fas fa-university text-info me-2"></i> Virtual Account (BCA, Mandiri, BRI, BNI)
                        </label>
                    </div>

                    <div class="form-check p-3 rounded bg-dark border border-secondary mb-3 d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="payment_method" id="ewallet">
                        <label class="form-check-label text-white fw-bold flex-grow-1" for="ewallet">
                            <i class="fas fa-mobile-alt text-purple me-2"></i> E-Wallet (GoPay, OVO, ShopeePay, DANA)
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card bg-card border border-purple p-4 text-center">
                    <div class="alert bg-dark border border-warning text-warning py-2 px-3 mb-4 d-inline-flex align-items-center justify-content-center mx-auto" style="border-radius: 50px;">
                        <i class="fas fa-clock me-2"></i> 
                        <span class="small me-2">Selesaikan dalam:</span> 
                        <span id="countdownTimer" class="fw-bold fs-5 font-monospace">10:00</span>
                    </div>

                    <h5 class="text-white fw-bold mb-2" id="paymentTitle">QRIS Dynamic Code</h5>
                    <p class="text-muted small mb-3" id="paymentDesc">Scan kode QR di bawah menggunakan aplikasi E-Wallet atau Mobile Banking Anda.</p>

                    <!-- Dynamic Payment Content -->
                    <div id="paymentContent" class="mb-3">
                        <div class="bg-white p-3 rounded d-inline-block mx-auto mb-3" style="width: 220px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=190x190&data=ASTROGAMES-PAY-<?php echo time(); ?>" alt="QRIS" class="w-100">
                        </div>
                    </div>

                    <div class="h3 text-purple fw-bold mb-3"><?php echo formatRupiah($amount); ?></div>

                    <a href="payment.php?amount=<?php echo $amount; ?>&action=confirm" class="btn btn-purple btn-lg w-100 py-3 fw-bold shadow-lg">
                        <i class="fas fa-check-circle me-2"></i> BAYAR SEKARANG
                    </a>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Timer Countdown Logic
    let timeLeft = 600; // 10 minutes
    const timerElement = document.getElementById('countdownTimer');
    const timerInterval = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = "KADALUARSA";
            timerElement.classList.replace('text-warning', 'text-danger');
            return;
        }
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 60) {
            timerElement.parentElement.classList.replace('border-warning', 'border-danger');
            timerElement.parentElement.classList.replace('text-warning', 'text-danger');
        }
    }, 1000);
    const radios = document.querySelectorAll('input[name="payment_method"]');
    const title = document.getElementById('paymentTitle');
    const desc = document.getElementById('paymentDesc');
    const content = document.getElementById('paymentContent');
    
    // Store original QR HTML
    const qrHTML = content.innerHTML;
    
    radios.forEach(r => {
        r.addEventListener('change', function() {
            // Update border classes for UI polish
            document.querySelectorAll('.form-check').forEach(el => {
                el.classList.remove('border-purple');
                el.classList.add('border-secondary');
            });
            this.closest('.form-check').classList.remove('border-secondary');
            this.closest('.form-check').classList.add('border-purple');
            
            if (this.id === 'qris') {
                title.textContent = 'QRIS Dynamic Code';
                desc.textContent = 'Scan kode QR di bawah menggunakan aplikasi E-Wallet atau Mobile Banking Anda.';
                content.innerHTML = qrHTML;
            } else if (this.id === 'va') {
                title.textContent = 'Transfer Virtual Account';
                desc.textContent = 'Gunakan nomor VA berikut untuk membayar melalui ATM atau M-Banking.';
                content.innerHTML = '<div class="bg-dark p-3 rounded border border-purple mb-3"><div class="text-muted small mb-1">Nomor Virtual Account:</div><div class="h4 text-white mb-0 font-monospace text-break">8800-1234-5678-9012</div></div>';
            } else if (this.id === 'ewallet') {
                title.textContent = 'Pembayaran E-Wallet';
                desc.textContent = 'Lakukan transfer ke nomor tujuan berikut dari aplikasi E-Wallet Anda.';
                content.innerHTML = '<div class="bg-dark p-3 rounded border border-purple mb-3"><div class="text-muted small mb-1">Nomor Tujuan Transfer:</div><div class="h4 text-white mb-0 font-monospace">0812-3456-7890</div></div>';
            }
        });
    });
});
</script>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
