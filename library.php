<?php
$pageTitle = 'My Library';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$userId = (int)($_SESSION['user']['id'] ?? 2);

$libraryGames = dbFetchAll("
    SELECT g.*, MAX(o.created_at) as purchased_at 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN games g ON oi.game_id = g.id
    WHERE o.user_id = ? AND o.payment_status = 'paid'
    GROUP BY g.id
    ORDER BY purchased_at DESC
", [$userId]);
?>

<main class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 text-white fw-bold mb-1"><i class="fas fa-cubes text-purple me-2"></i> <i class="fas fa-gamepad"></i> MY GAME LIBRARY</h1>
            <p class="text-muted mb-0">Koleksi lisensi game digital resmi yang telah Anda beli.</p>
        </div>
        <span class="badge bg-purple fs-6 px-3 py-2"><?php echo count($libraryGames); ?> Game Owned</span>
    </div>

    <?php if (empty($libraryGames)): ?>
        <div class="card bg-card border border-secondary p-5 text-center my-5">
            <i class="fas fa-gamepad fa-4x text-muted mb-3"></i>
            <h3 class="text-white fw-bold">Library Anda masih kosong.</h3>
            <p class="text-muted mb-4">Game yang telah dibeli akan otomatis muncul di sini.</p>
            <a href="games.php" class="btn btn-purple btn-lg">Jelajahi Game</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($libraryGames as $game): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="game-card">
                    <div class="game-card-img-wrapper">
                        <img src="<?php echo $game['cover_image']; ?>" alt="<?php echo $game['title']; ?>" class="game-card-img">
                    </div>
                    <div class="game-card-body">
                        <h3 class="game-card-title text-white"><?php echo $game['title']; ?></h3>
                        <div class="small text-muted mb-2"><?php echo $game['developer']; ?></div>
                        
                        <span class="badge bg-success px-3 py-2 mb-3">Purchased </span>

                        <div class="mt-auto d-grid gap-2">
                            <button class="btn btn-success btn-sm btn-download" data-title="<?php echo htmlspecialchars($game['title']); ?>">
                                <i class="fas fa-download me-1"></i> Download Game
                            </button>
                            <button class="btn btn-purple btn-sm" onclick="alert('Digital Key ASTRO-<?php echo strtoupper(substr(md5($game['slug']), 0, 16)); ?> telah disalin!');">
                                <i class="fas fa-key me-1"></i> Lihat Product Key
                            </button>
                            <a href="game-detail.php?slug=<?php echo $game['slug']; ?>" class="btn btn-outline-purple btn-sm">Halaman Game</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<!-- Download Simulation Modal -->
<div class="modal fade" id="downloadModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-card border-purple">
            <div class="modal-header border-purple">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-cloud-download-alt text-info me-2"></i> ASTRO DOWNLOADER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="dlCloseBtn"></button>
            </div>
            <div class="modal-body p-4">
                <h5 id="dlGameTitle" class="text-white mb-3">Downloading Game...</h5>
                
                <div class="progress mb-3" style="height: 25px; background-color: #180e38;">
                    <div id="dlProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;">0%</div>
                </div>

                <div class="d-flex justify-content-between text-muted small mb-4">
                    <span id="dlSpeed"><i class="fas fa-tachometer-alt"></i> Menghitung kecepatan...</span>
                    <span id="dlTimeLeft"><i class="fas fa-clock"></i> Estimasi: 1 Menit</span>
                </div>

                <div id="dlStatusBox" class="alert bg-dark text-info border border-info py-3 text-center mb-0" style="display: none;">
                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                    <strong class="fs-5">Game berhasil di download dan install!</strong>
                </div>
            </div>
            <div class="modal-footer border-purple" style="display: none;" id="dlFooter">
                <button type="button" class="btn btn-success w-100 fw-bold py-2" data-bs-dismiss="modal"><i class="fas fa-play me-2"></i> MAINKAN SEKARANG</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dlBtns = document.querySelectorAll('.btn-download');
    let dlModal;
    if (document.getElementById('downloadModal')) {
        dlModal = new bootstrap.Modal(document.getElementById('downloadModal'));
    }
    
    const dlGameTitle = document.getElementById('dlGameTitle');
    const dlProgressBar = document.getElementById('dlProgressBar');
    const dlSpeed = document.getElementById('dlSpeed');
    const dlTimeLeft = document.getElementById('dlTimeLeft');
    const dlStatusBox = document.getElementById('dlStatusBox');
    const dlFooter = document.getElementById('dlFooter');
    const dlCloseBtn = document.getElementById('dlCloseBtn');
    
    let dlInterval = null;

    dlBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const title = this.getAttribute('data-title');
            
            // Reset UI for new download
            dlGameTitle.innerHTML = `Mengunduh: <strong class="text-info">${title}</strong>`;
            dlProgressBar.style.width = '0%';
            dlProgressBar.textContent = '0%';
            dlProgressBar.classList.add('progress-bar-animated');
            dlProgressBar.classList.remove('bg-info');
            dlProgressBar.classList.add('bg-success');
            
            dlSpeed.innerHTML = '<i class="fas fa-tachometer-alt text-warning"></i> Menghubungkan...';
            dlTimeLeft.innerHTML = '<i class="fas fa-clock text-info"></i> Menghitung...';
            
            dlStatusBox.style.display = 'none';
            dlFooter.style.display = 'none';
            dlCloseBtn.style.display = 'none'; // Prevent closing while downloading
            
            dlModal.show();
            
            // Simulate 60 seconds download
            let secondsLeft = 60;
            let currentProgress = 0;
            
            if (dlInterval) clearInterval(dlInterval);
            
            dlInterval = setInterval(() => {
                secondsLeft--;
                currentProgress += (100 / 60); // approx 1.66% per second
                
                if (currentProgress > 100) currentProgress = 100;
                
                dlProgressBar.style.width = `${currentProgress}%`;
                dlProgressBar.textContent = `${Math.floor(currentProgress)}%`;
                
                // Randomize network speed between 45 - 85 MB/s
                const speed = (Math.random() * (85 - 45) + 45).toFixed(1);
                dlSpeed.innerHTML = `<i class="fas fa-tachometer-alt text-warning"></i> ${speed} MB/s`;
                
                // Format time left
                const min = Math.floor(secondsLeft / 60);
                const sec = secondsLeft % 60;
                dlTimeLeft.innerHTML = `<i class="fas fa-clock text-info"></i> Sisa Waktu: ${min}:${sec.toString().padStart(2, '0')}`;
                
                // Install phase at 90%
                if (secondsLeft <= 5) {
                    dlSpeed.innerHTML = `<i class="fas fa-cogs text-info"></i> Menginstal file...`;
                    dlProgressBar.classList.replace('bg-success', 'bg-info');
                }
                
                if (secondsLeft <= 0) {
                    clearInterval(dlInterval);
                    dlProgressBar.classList.remove('progress-bar-animated');
                    dlProgressBar.textContent = '100% Selesai';
                    dlSpeed.innerHTML = '<i class="fas fa-check text-success"></i> Selesai';
                    dlTimeLeft.innerHTML = '';
                    
                    dlStatusBox.style.display = 'block';
                    dlFooter.style.display = 'block';
                    dlCloseBtn.style.display = 'block';
                }
            }, 1000);
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

