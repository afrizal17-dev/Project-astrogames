<?php
$pageTitle = 'Pengaturan Akun & Tampilan';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container py-4">

    <h1 class="display-6 text-white fw-bold mb-4"><i class="fas fa-cog text-purple me-2"></i> PENGATURAN & TAMPILAN</h1>

    <div class="row g-4">
        <!-- Appearance Settings -->
        <div class="col-md-6">
            <div class="card bg-card border border-secondary p-4 h-100">
                <h5 class="text-white fw-bold mb-3"><i class="fas fa-palette text-purple me-2"></i> Tampilan (Appearance)</h5>
                
                <div class="p-3 bg-dark rounded border border-secondary mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-white">Mode Tema Tampilan</div>
                        <div class="small text-muted">Beralih antara Mode Gelap (Futuristic Dark) dan Mode Terang.</div>
                    </div>
                    <button id="themeToggleBtnSettings" onclick="document.getElementById('themeToggleBtn').click();" class="btn btn-outline-purple">
                        <i class="fas fa-adjust me-1"></i> Switch Mode
                    </button>
                </div>

                <div class="p-3 bg-dark rounded border border-secondary">
                    <div class="fw-bold text-white mb-2">Pilihan Bahasa (Language)</div>
                    <select class="astro-select">
                        <option value="id" selected>Bahasa Indonesia (Utama)</option>
                        <option value="en">English (US)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="col-md-6">
            <div class="card bg-card border border-secondary p-4 h-100">
                <h5 class="text-white fw-bold mb-3"><i class="fas fa-bell text-warning me-2"></i> Preferensi Notifikasi</h5>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="n1" checked>
                    <label class="form-check-label text-white" for="n1">Notifikasi Promo & Diskon Game</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="n2" checked>
                    <label class="form-check-label text-white" for="n2">Notifikasi Turun Harga Game Wishlist</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="n3" checked>
                    <label class="form-check-label text-white" for="n3">Notifikasi Status Pembayaran</label>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
