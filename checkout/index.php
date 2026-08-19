<?php
// checkout/index.php — Halaman Checkout Modern v2.0
$page_title = "Checkout - GameCheck";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$payment_config = require __DIR__ . '/../config/payment.php';

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$order_code = isset($_GET['order_code']) ? trim($_GET['order_code']) : '';

$order = null;
$product = null;

if ($order_code !== '') {
    $stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.description as product_desc, p.price as product_price 
                           FROM orders o 
                           JOIN products p ON o.product_id = p.id 
                           WHERE o.order_code = :code AND o.user_id = :uid LIMIT 1");
    $stmt->execute([':code' => $order_code, ':uid' => $_SESSION['user_id']]);
    $order = $stmt->fetch();
    if ($order) $product_id = $order['product_id'];
}

if (!$order && $product_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'active' LIMIT 1");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch();
}

$use_cart_mode = (!$order && !$product); // Cart mode from localStorage
$display_name  = $order ? $order['product_name']  : ($product ? $product['name'] : 'Pesanan dari Keranjang');
$display_price = $order ? $order['amount']         : ($product ? $product['price'] : 0);
$current_code  = $order ? $order['order_code']     : 'GC-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
?>

<div class="container-xxl px-3 px-lg-4 py-5" style="max-width:1400px;">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL; ?>/cart.php">Keranjang</a></li>
      <li class="breadcrumb-item active">Checkout</li>
    </ol>
  </nav>

  <h1 class="fw-800 mb-5 text-gradient" style="font-size:2rem;">
    <i class="bi bi-bag-check me-2"></i>Checkout
  </h1>

  <div class="row g-4 align-items-start">
    <!-- Left: Steps + Payment -->
    <div class="col-lg-8">

      <!-- Step 1: Order Summary -->
      <div class="checkout-card">
        <h5 class="fw-700 mb-4" style="font-family:var(--font-heading);display:flex;align-items:center;gap:10px;">
          <span style="width:28px;height:28px;border-radius:50%;background:var(--gradient-main);color:#fff;font-size:0.85rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">1</span>
          Ringkasan Pesanan
        </h5>

        <div id="checkout-items">
          <!-- Cart items rendered by JS if cart mode, else PHP product -->
          <?php if ($product || $order): ?>
            <div class="d-flex gap-3 align-items-center p-3 rounded-lg" style="background:var(--bg-input);border:1px solid var(--border-color);">
              <div class="flex-grow-1">
                <div class="fw-700"><?= htmlspecialchars($display_name); ?></div>
                <div class="text-secondary small">Produk Digital</div>
              </div>
              <div class="fw-800" style="color:var(--accent-light);">
                Rp <?= number_format($display_price, 0, ',', '.'); ?>
              </div>
            </div>
          <?php else: ?>
            <div id="cart-checkout-items" class="d-flex flex-column gap-2">
              <p class="text-secondary small"><i class="bi bi-info-circle me-1"></i>Item dari keranjang akan ditampilkan di bawah.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Step 2: Voucher -->
      <div class="checkout-card">
        <h5 class="fw-700 mb-4" style="font-family:var(--font-heading);display:flex;align-items:center;gap:10px;">
          <span style="width:28px;height:28px;border-radius:50%;background:var(--bg-surface);color:var(--text-muted);border:1px solid var(--border-color);font-size:0.85rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">2</span>
          Kode Voucher (Opsional)
        </h5>
        <div class="d-flex gap-2">
          <input type="text" id="co-voucher" class="form-control" placeholder="Contoh: GAMECHECK10" style="font-size:0.875rem;">
          <button class="btn-purple" style="padding:0.55rem 1.25rem;font-size:0.875rem;white-space:nowrap;" onclick="applyCoVoucher()">
            Gunakan
          </button>
        </div>
        <div id="co-voucher-status" class="mt-2" style="font-size:0.8rem;"></div>
        <div class="mt-2 text-muted" style="font-size:0.75rem;">
          Coba: <code>GAMECHECK10</code>, <code>HEMAT50K</code>, <code>GRATIS999</code>
        </div>
      </div>

      <!-- Step 3: Payment Method -->
      <div class="checkout-card">
        <h5 class="fw-700 mb-4" style="font-family:var(--font-heading);display:flex;align-items:center;gap:10px;">
          <span style="width:28px;height:28px;border-radius:50%;background:var(--bg-surface);color:var(--text-muted);border:1px solid var(--border-color);font-size:0.85rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">3</span>
          Metode Pembayaran
        </h5>

        <div class="row g-3">
          <div class="col-4">
            <button class="payment-method-btn selected" id="pm-qris" onclick="selectPayment('qris')">
              <i class="bi bi-qr-code-scan" style="color:var(--accent-light);"></i>
              <span>QRIS</span>
            </button>
          </div>
          <div class="col-4">
            <button class="payment-method-btn" id="pm-ewallet" onclick="selectPayment('ewallet')">
              <i class="bi bi-wallet2" style="color:var(--success);"></i>
              <span>E-Wallet</span>
            </button>
          </div>
          <div class="col-4">
            <button class="payment-method-btn" id="pm-bank" onclick="selectPayment('bank')">
              <i class="bi bi-bank" style="color:var(--warning);"></i>
              <span>Transfer Bank</span>
            </button>
          </div>
        </div>

        <!-- QRIS Panel -->
        <div id="pm-panel-qris" class="mt-4">
          <div class="text-center p-4 rounded-xl" style="background:var(--bg-input);border:1px solid var(--border-color);">
            <div class="mb-3">
              <span class="badge-genre px-3 py-2" style="font-size:0.8rem;">QRIS — Standar Indonesia</span>
            </div>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=GAMECHECK-<?= urlencode($current_code); ?>&color=7c3aed&bgcolor=0f1220"
                 alt="QRIS Code" class="rounded-lg mb-3" style="border:2px solid var(--accent);padding:8px;background:var(--bg-card);">
            <div class="fw-700 mb-1">Scan dengan Aplikasi E-Wallet / M-Banking</div>
            <div class="text-secondary small">GoPay &bull; OVO &bull; Dana &bull; ShopeePay &bull; BCA &bull; Mandiri</div>
            <?php if (!empty($payment_config['ENABLE_DEMO_PAYMENT'])): ?>
            <div class="mt-3 p-2 rounded" style="background:rgba(245,158,11,0.1);border:1px solid var(--warning);font-size:0.78rem;color:var(--warning);">
              <i class="bi bi-lightning me-1"></i>Mode Demo — Tidak ada pembayaran nyata yang diproses
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- E-Wallet Panel -->
        <div id="pm-panel-ewallet" class="mt-4" style="display:none;">
          <div class="p-4 rounded-xl" style="background:var(--bg-input);border:1px solid var(--border-color);">
            <div class="fw-700 mb-3">Pilih E-Wallet:</div>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach (['GoPay','OVO','Dana','ShopeePay','LinkAja'] as $ew): ?>
                <button class="btn btn-sm" style="background:var(--bg-card);border:1px solid var(--border-color);color:var(--text-secondary);border-radius:var(--radius-md);padding:0.5rem 1rem;">
                  <?= $ew; ?>
                </button>
              <?php endforeach; ?>
            </div>
            <div class="mt-3 p-2 rounded" style="background:rgba(245,158,11,0.1);border:1px solid var(--warning);font-size:0.78rem;color:var(--warning);">
              <i class="bi bi-info-circle me-1"></i>E-Wallet akan diarahkan ke aplikasi masing-masing untuk konfirmasi (Simulasi)
            </div>
          </div>
        </div>

        <!-- Bank Panel -->
        <div id="pm-panel-bank" class="mt-4" style="display:none;">
          <div class="p-4 rounded-xl" style="background:var(--bg-input);border:1px solid var(--border-color);">
            <div class="fw-700 mb-3">Nomor Rekening Tujuan:</div>
            <div class="d-flex flex-column gap-2">
              <?php
              $banks = [
                  ['BCA',     '8888 9999 0000', 'GameCheck Store'],
                  ['Mandiri', '1234 5678 9012', 'GameCheck Store'],
                  ['BRI',     '0987 6543 2109', 'GameCheck Store'],
              ];
              foreach ($banks as [$bank, $no, $name]): ?>
              <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background:var(--bg-card);border:1px solid var(--border-color);">
                <div>
                  <div class="fw-700 small"><?= $bank; ?></div>
                  <div class="text-secondary" style="font-size:0.75rem;"><?= $name; ?></div>
                </div>
                <div class="fw-700 font-monospace"><?= $no; ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3 p-2 rounded" style="background:rgba(245,158,11,0.1);border:1px solid var(--warning);font-size:0.78rem;color:var(--warning);">
              <i class="bi bi-info-circle me-1"></i>Transfer Bank hanya untuk simulasi — upload bukti transfer (Simulasi)
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Summary Sticky -->
    <div class="col-lg-4">
      <div class="checkout-card sticky-top" style="top:80px;">
        <h5 class="fw-700 mb-4" style="font-family:var(--font-heading);">Total Pembayaran</h5>

        <div class="d-flex justify-content-between mb-2" style="font-size:0.875rem;">
          <span class="text-secondary">Subtotal</span>
          <span id="co-subtotal" class="fw-600"><?php echo $use_cart_mode ? '-' : 'Rp ' . number_format($display_price, 0, ',', '.'); ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:0.875rem;">
          <span class="text-secondary">Diskon Voucher</span>
          <span id="co-discount" class="fw-600" style="color:var(--success);">-Rp 0</span>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:0.875rem;">
          <span class="text-secondary">Biaya Layanan</span>
          <span class="fw-600" style="color:var(--success);">Gratis</span>
        </div>
        <hr style="border-color:var(--border-color);">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <span class="fw-800" style="font-size:1.1rem;">Total</span>
          <span id="co-total" class="fw-800" style="font-size:1.4rem;color:var(--accent-light);">
            <?= $use_cart_mode ? 'Rp 0' : 'Rp ' . number_format($display_price, 0, ',', '.'); ?>
          </span>
        </div>

        <?php if ($product || $order): ?>
          <form method="POST" action="<?= BASE_URL; ?>/checkout/proses.php">
            <input type="hidden" name="product_id"  value="<?= $product_id; ?>">
            <input type="hidden" name="order_code"  value="<?= htmlspecialchars($current_code); ?>">
            <input type="hidden" name="amount"       value="<?= $display_price; ?>">
            <button type="submit" class="btn-purple w-100 py-3 pulse-glow" style="font-size:1rem;">
              <i class="bi bi-lightning-fill me-2"></i>Bayar Sekarang
            </button>
          </form>
        <?php else: ?>
          <button class="btn-purple w-100 py-3 pulse-glow" style="font-size:1rem;" onclick="simulateCheckout()">
            <i class="bi bi-lightning-fill me-2"></i>Bayar Sekarang
          </button>
        <?php endif; ?>

        <div class="mt-3 text-center text-muted" style="font-size:0.78rem;">
          <i class="bi bi-shield-check me-1"></i>Transaksi aman & terenkripsi
        </div>

        <!-- Kode transaksi -->
        <div class="mt-3 p-2 rounded" style="background:var(--bg-input);font-size:0.78rem;color:var(--text-muted);">
          Kode: <span class="font-monospace text-accent"><?= htmlspecialchars($current_code); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Payment method switcher
function selectPayment(method) {
  ['qris','ewallet','bank'].forEach(m => {
    document.getElementById('pm-'+ m)?.classList.remove('selected');
    const panel = document.getElementById('pm-panel-' + m);
    if (panel) panel.style.display = 'none';
  });
  document.getElementById('pm-' + method)?.classList.add('selected');
  const p = document.getElementById('pm-panel-' + method);
  if (p) p.style.display = 'block';
}

// Vouchers
const CO_VOUCHERS = {
  'GAMECHECK10': { type: 'percent', value: 10, label: 'Diskon 10%' },
  'GRATIS999':   { type: 'fixed',   value: 999999, label: 'Semua Gratis' },
  'HEMAT50K':    { type: 'fixed',   value: 50000,  label: 'Hemat Rp 50.000' },
};
let coDiscount = 0;

function applyCoVoucher() {
  const code = document.getElementById('co-voucher').value.trim().toUpperCase();
  const statusEl = document.getElementById('co-voucher-status');
  const voucher = CO_VOUCHERS[code];
  if (!voucher) {
    statusEl.innerHTML = '<span style="color:var(--danger)"><i class="bi bi-x-circle me-1"></i>Kode tidak valid.</span>';
    coDiscount = 0;
  } else {
    const sub = getCoSubtotal();
    coDiscount = voucher.type === 'percent' ? Math.round(sub * voucher.value / 100) : Math.min(voucher.value, sub);
    statusEl.innerHTML = `<span style="color:var(--success)"><i class="bi bi-check-circle me-1"></i>${voucher.label} berhasil!</span>`;
  }
  updateCoTotals();
}

function getCoSubtotal() {
  <?php if ($use_cart_mode): ?>
  return CartManager.getTotal();
  <?php else: ?>
  return <?= $display_price; ?>;
  <?php endif; ?>
}

function updateCoTotals() {
  const sub = getCoSubtotal();
  const total = Math.max(0, sub - coDiscount);
  const fmt = n => n === 0 ? '<span style="color:var(--success)">Gratis</span>' : 'Rp ' + Number(n).toLocaleString('id-ID');
  document.getElementById('co-subtotal').innerHTML = fmt(sub);
  document.getElementById('co-discount').textContent = '-Rp ' + Number(coDiscount).toLocaleString('id-ID');
  document.getElementById('co-total').innerHTML = fmt(total);
}

function simulateCheckout() {
  const cart = CartManager.getCart();
  if (!cart.length) { showToast('Keranjangmu kosong!', 'error'); return; }

  const btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Memproses...';

  setTimeout(() => {
    // Clear cart on "success"
    localStorage.removeItem('gc_cart');
    CartManager.updateBadge?.();
    showToast('<i class="fas fa-glass-cheers text-warning"></i> Pembayaran berhasil! (Simulasi)', 'success');
    setTimeout(() => {
      window.location.href = window.GC.base_url + '/index.php';
    }, 2000);
  }, 2000);
}

// Init cart items in checkout (cart mode)
document.addEventListener('DOMContentLoaded', () => {
  updateCoTotals();
  <?php if ($use_cart_mode): ?>
  const cart = CartManager.getCart();
  const container = document.getElementById('cart-checkout-items');
  if (container) {
    if (!cart.length) {
      container.innerHTML = '<div class="text-center py-3 text-muted">Keranjang kosong — <a href="' + window.GC.base_url + '/katalog.php">tambah game</a></div>';
    } else {
      container.innerHTML = cart.map(item => {
        const priceStr = parseFloat(item.price) === 0 ? '<span style="color:var(--success)">Gratis</span>' : 'Rp ' + Number(item.price).toLocaleString('id-ID');
        return `<div class="d-flex justify-content-between align-items-center p-3 rounded-lg mb-2" style="background:var(--bg-input);border:1px solid var(--border-color);">
          <div class="fw-600 text-truncate me-3">${item.name || 'Game'}</div>
          <div class="fw-700 flex-shrink-0">${priceStr}</div>
        </div>`;
      }).join('');
    }
    updateCoTotals();
  }
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
