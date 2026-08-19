<?php
$current_page = basename($_SERVER['PHP_SELF']);
$cart_count = count($_SESSION['cart'] ?? []);
$wishlist_count = count($_SESSION['wishlist'] ?? []);
?>
<nav class="navbar navbar-expand-lg astro-navbar">
    <div class="container-fluid px-lg-4">
        <!-- Brand Logo -->
        <a class="navbar-brand me-3" href="<?php echo BASE_URL; ?>/index.php">
            <i class="fas fa-gamepad text-purple"></i> ASTRO<span>GAMES</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <i class="fas fa-bars fa-lg"></i>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'games.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/games.php">
                        Games
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'promo.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/promo.php">
                        Promo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'check-specs.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/check-specs.php">
                        Rekomendasi
                    </a>
                </li>
            </ul>

            <!-- Search Input Integrated in Navbar (Purple Theme) -->
            <div class="nav-search-wrap me-lg-3 my-2 my-lg-0">
                <i class="fas fa-search nav-search-icon"></i>
                <input type="text" id="globalSearchInput" class="nav-search-input" placeholder="Cari game..." autocomplete="off">
                <div id="searchResultsDropdown" class="search-results-dropdown"></div>
            </div>

            <!-- Right Action Items -->
            <div class="d-flex align-items-center gap-2">
                <!-- Theme Toggle Button -->
                <button class="btn btn-outline-purple btn-sm rounded-circle" id="themeToggleBtn" title="Mode Gelap / Terang">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Wishlist -->
                <a href="<?php echo BASE_URL; ?>/wishlist.php" class="btn btn-outline-purple position-relative btn-sm rounded-circle p-2" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <?php if ($wishlist_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $wishlist_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Cart -->
                <a href="<?php echo BASE_URL; ?>/cart.php" class="btn btn-outline-purple position-relative btn-sm rounded-circle p-2 me-2" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Login & Register / Account Buttons (High Contrast & Clear) -->
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-purple dropdown-toggle btn-sm rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php"><i class="fas fa-user-circle me-2"></i> Profil Saya</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/library.php"><i class="fas fa-cubes me-2"></i> My Library</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/orders.php"><i class="fas fa-receipt me-2"></i> Riwayat Pesanan</a></li>
                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-info" href="<?php echo BASE_URL; ?>/admin/index.php"><i class="fas fa-user-shield me-2"></i> Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn-nav-login">Login</a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn-nav-register">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
