<?php
$pageTitle = 'Help Center & FAQ';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <div class="text-center max-w-700 mx-auto mb-5">
        <h1 class="display-5 text-white fw-bold mb-2"><i class="fas fa-question-circle text-purple me-2"></i> HELP CENTER & FAQ</h1>
        <p class="text-light fs-5">
            Punya pertanyaan mengenai ASTROGAMES, cara membeli game, atau fitur Laptop Compatibility Checker? Temukan jawabannya di bawah ini.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-card border border-secondary p-4 h-100">
                <h5 class="text-purple fw-bold mb-3"><i class="fas fa-laptop me-2"></i> FAQ Compatibility Checker</h5>
                
                <div class="mb-3">
                    <strong class="text-white">Bagaimana cara kerja Laptop Compatibility Checker?</strong>
                    <p class="text-muted small mt-1">Sistem kami membandingkan skor relatif CPU, GPU, RAM, VRAM, dan OS laptop Anda secara matematis dengan spesifikasi requirement minimum & rekomendasi resmi dari developer game.</p>
                </div>

                <div>
                    <strong class="text-white">Apakah hasilnya 100% akurat?</strong>
                    <p class="text-muted small mt-1">Hasil kalkulasi memberikan estimasi performa realitis (Green / Yellow / Red) yang sangat mendekati kondisi nyata.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-card border border-secondary p-4 h-100">
                <h5 class="text-purple fw-bold mb-3"><i class="fas fa-shopping-cart me-2"></i> FAQ Pembelian & Lisensi</h5>
                
                <div class="mb-3">
                    <strong class="text-white">Bagaimana cara mendapatkan kunci game setelah bayar?</strong>
                    <p class="text-muted small mt-1">Kunci lisensi digital otomatis dikirimkan secara instan ke menu <a href="library.php" class="text-purple"><i class="fas fa-gamepad"></i> My Library</a> Anda begitu pembayaran terkonfirmasi.</p>
                </div>

                <div>
                    <strong class="text-white">Bagaimana cara menggunakan kode voucher?</strong>
                    <p class="text-muted small mt-1">Masukkan kode voucher (seperti <code>ASTROLAUNCH</code>) pada halaman Checkout sebelum melanjutkan ke pembayaran.</p>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

