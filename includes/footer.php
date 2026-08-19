<footer class="astro-footer mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <a class="navbar-brand mb-3 d-inline-block" href="<?php echo BASE_URL; ?>/index.php">
                    <i class="fas fa-gamepad text-purple"></i> ASTRO<span class="text-white">GAMES</span>
                </a>
                <p class="text-muted small">
                    ASTROGAMES adalah marketplace game digital terdepan yang dilengkapi fitur <strong>Laptop Compatibility Checker</strong> untuk membantu Anda membeli game secara tepat dan tanpa ragu.
                </p>
                <div class="d-flex gap-3 text-muted">
                    <a href="#" class="text-muted"><i class="fab fa-discord fa-lg"></i></a>
                    <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-muted"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-muted"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Navigasi</h6>
                <ul class="list-unstyled text-muted small d-flex flex-column gap-2">
                    <li><a href="<?php echo BASE_URL; ?>/index.php" class="text-muted">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/games.php" class="text-muted">Katalog Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/promo.php" class="text-muted">Promo & Diskon</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/check-specs.php" class="text-muted">Cek Spek Laptop</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Bantuan</h6>
                <ul class="list-unstyled text-muted small d-flex flex-column gap-2">
                    <li><a href="<?php echo BASE_URL; ?>/help.php" class="text-muted">Help Center</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/help.php#faq" class="text-muted">FAQ Compatibility</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/help.php#payment" class="text-muted">Metode Pembayaran</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h6 class="text-white fw-bold mb-3">Metode Pembayaran Resmi</h6>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-secondary p-2"><i class="fas fa-qrcode text-warning me-1"></i> QRIS</span>
                    <span class="badge bg-secondary p-2"><i class="fas fa-university me-1"></i> Virtual Account</span>
                    <span class="badge bg-secondary p-2"><i class="fas fa-wallet text-info me-1"></i> E-Wallet</span>
                </div>
                <div class="p-3 rounded border border-secondary bg-dark text-muted small">
                    <i class="fas fa-shield-alt text-purple me-1"></i> Pembayaran Aman 100% dengan lisensi resmi game digital.
                </div>
            </div>
        </div>

        <hr class="border-secondary my-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-muted small">
            <div>&copy; <?php echo date('Y'); ?> ASTROGAMES. All rights reserved. "Temukan Game. Cek Spek. Main Tanpa Ragu."</div>
            <div>Ditenagai oleh PHP 8 & Laragon Server Engine</div>
        </div>
    </div>
</footer>

<!-- Passing Server Data to JS -->
<script>
    window.ASTRO_GAMES = <?php echo json_encode(getAllGames()); ?>;
    window.ASTRO_BASE_URL = "<?php echo BASE_URL; ?>";
</script>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
