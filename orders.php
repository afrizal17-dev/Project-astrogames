<?php
$pageTitle = 'Riwayat Pesanan';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 2);

// Fetch all orders for this user
$orders = dbFetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <h1 class="display-6 text-white fw-bold mb-4"><i class="fas fa-receipt text-purple me-2"></i> RIWAYAT PESANAN</h1>

    <?php if (empty($orders)): ?>
        <div class="card bg-card border border-secondary p-5 text-center my-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h3 class="text-white fw-bold">Belum Ada Transaksi.</h3>
            <p class="text-muted mb-4">Anda belum pernah melakukan pembelian game apapun.</p>
            <div>
                <a href="games.php" class="btn btn-purple btn-lg px-4 py-2">Mulai Belanja</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card bg-card border border-secondary p-3">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-purple">
                            <th>Order ID</th>
                            <th>Game (Item Pertama)</th>
                            <th>Tanggal</th>
                            <th>Metode Bayar</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            // Fetch the first game in the order for display purposes
                            $firstItem = dbFetchOne("
                                SELECT g.title, g.cover_image 
                                FROM order_items oi 
                                JOIN games g ON oi.game_id = g.id 
                                WHERE oi.order_id = ? 
                                LIMIT 1
                            ", [$order['id']]);
                            
                            // Check if there are more items
                            $totalItems = dbFetchOne("SELECT COUNT(*) as cnt FROM order_items WHERE order_id = ?", [$order['id']])['cnt'];
                            $moreText = $totalItems > 1 ? " + " . ($totalItems - 1) . " Game Lainnya" : "";
                            
                            $statusClass = $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark';
                            $statusText = strtoupper($order['payment_status']);
                        ?>
                        <tr>
                            <td class="fw-bold">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td>
                                <?php if ($firstItem): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars($firstItem['cover_image']); ?>" alt="Cover" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <div>
                                        <div class="fw-bold text-white"><?php echo htmlspecialchars($firstItem['title']) . $moreText; ?></div>
                                        <div class="small text-muted">Digital Key</div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Item tidak ditemukan</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo strtoupper($order['payment_method']); ?></td>
                            <td class="fw-bold text-purple"><?php echo formatRupiah($order['final_price']); ?></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                            <td><a href="library.php" class="btn btn-outline-purple btn-sm">Ke Library</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
