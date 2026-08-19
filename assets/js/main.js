/**
 * ASTROGAMES - Main Client-Side JavaScript Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initThemeMode();
    initWishlistButtons();
    initCartButtons();
    initCompatibilityForm();
});

/**
 * 1. Live Search Engine with Instant Suggestions (Purple Theme)
 */
function initLiveSearch() {
    const searchInput = document.getElementById('globalSearchInput');
    const searchDropdown = document.getElementById('searchResultsDropdown');

    if (!searchInput || !searchDropdown) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim().toLowerCase();

        if (query.length === 0) {
            searchDropdown.style.display = 'none';
            searchDropdown.innerHTML = '';
            return;
        }

        // Fetch games list via window.ASTRO_GAMES or API fallback
        const games = window.ASTRO_GAMES || [];
        const filtered = games.filter(g => 
            (g.title && g.title.toLowerCase().includes(query)) ||
            (g.developer && g.developer.toLowerCase().includes(query)) ||
            (g.publisher && g.publisher.toLowerCase().includes(query)) ||
            (g.genres && g.genres.some(gen => gen.toLowerCase().includes(query)))
        );

        if (filtered.length === 0) {
            searchDropdown.innerHTML = `
                <div class="p-3 text-center text-muted small" style="background: #180e38; color: #b4bac5;">
                    <i class="fas fa-ghost me-2 text-purple"></i> Game tidak ditemukan untuk "${query}"
                </div>
            `;
        } else {
            const baseUrl = (typeof window.ASTRO_BASE_URL !== 'undefined') ? window.ASTRO_BASE_URL : '.';
            searchDropdown.innerHTML = filtered.slice(0, 5).map(g => {
                const finalPrice = g.price * (1 - (g.discount_percentage || 0)/100);
                const priceFormatted = (finalPrice === 0) ? 'GRATIS' : 'Rp ' + Number(finalPrice).toLocaleString('id-ID');
                return `
                    <a href="${baseUrl}/game-detail.php?slug=${g.slug}" class="search-item">
                        <img src="${g.cover_image}" alt="${g.title}">
                        <div class="flex-grow-1" style="min-width: 0; padding-right: 10px;">
                            <div class="fw-bold text-white mb-1 text-truncate" title="${g.title}" style="font-size: 0.95rem;">${g.title}</div>
                            <div class="small text-muted text-truncate" title="${g.developer || ''}" style="font-size: 0.8rem;">${g.developer || ''} • <i class="fas fa-star text-warning"></i> ${g.rating || '4.5'}</div>
                        </div>
                        <div class="text-end ms-auto flex-shrink-0">
                            <div class="fw-bold text-purple" style="font-size: 0.9rem;">${priceFormatted}</div>
                            ${g.discount_percentage > 0 ? `<span class="badge bg-danger small">-${g.discount_percentage}%</span>` : ''}
                        </div>
                    </a>
                `;
            }).join('');
        }

        searchDropdown.style.display = 'block';
    });

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = e.target.value.trim();
            if (query.length > 0) {
                const baseUrl = (typeof window.ASTRO_BASE_URL !== 'undefined') ? window.ASTRO_BASE_URL : '.';
                window.location.href = `${baseUrl}/games.php?q=${encodeURIComponent(query)}`;
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.style.display = 'none';
        }
    });
}

/**
 * 2. Theme Mode Switcher
 */
function initThemeMode() {
    const savedTheme = localStorage.getItem('astro_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        // Set initial icon
        const icon = themeToggleBtn.querySelector('i');
        if (savedTheme === 'light') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('astro_theme', newTheme);
            
            // Toggle Icon
            if (newTheme === 'light') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
            
            showToast(`Mode ${newTheme === 'dark' ? 'Gelap' : 'Terang'} Diaktifkan`, 'info');
        });
    }
}

/**
 * 3. Wishlist Handler
 */
function initWishlistButtons() {
    document.querySelectorAll('.btn-wishlist-float, .btn-wishlist-toggle').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const gameId = btn.getAttribute('data-game-id');
            if (!gameId) {
                showToast('Gagal: ID Game tidak ditemukan', 'error');
                return;
            }
            
            const isActive = !btn.classList.contains('active');
            const action = isActive ? 'add' : 'remove';
            const baseUrl = (typeof window.ASTRO_BASE_URL !== 'undefined') ? window.ASTRO_BASE_URL : '.';
            
            try {
                const response = await fetch(`${baseUrl}/api/wishlist.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game_id: gameId, action: action })
                });
                const data = await response.json();
                
                if (data.status === 'redirect') {
                    window.location.href = data.url;
                    return;
                }
                
                if (data.status === 'success') {
                    if (action === 'add') {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                    showToast(data.message, action === 'add' ? 'success' : 'warning');
                    
                    // Update counter in navbar if exists
                    const wishlistBadge = document.querySelector('a[title="Wishlist"] .badge');
                    if (wishlistBadge) {
                        wishlistBadge.textContent = data.count;
                        if (data.count === 0) wishlistBadge.remove(); // removes badge if 0
                    } else if (data.count > 0) {
                        // Create badge if not exists and count > 0
                        const wishlistBtn = document.querySelector('a[title="Wishlist"]');
                        if (wishlistBtn) {
                            const badge = document.createElement('span');
                            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                            badge.textContent = data.count;
                            wishlistBtn.appendChild(badge);
                        }
                    }
                } else {
                    showToast('Gagal mengubah wishlist', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan', 'error');
            }
        });
    });
}

/**
 * 4. Cart Handler
 */
function initCartButtons() {
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const gameId = btn.getAttribute('data-game-id');
            const gameTitle = btn.getAttribute('data-game-title') || 'Game';
            
            if (!gameId) {
                showToast('Gagal: ID Game tidak ditemukan', 'error');
                return;
            }
            
            const baseUrl = (typeof window.ASTRO_BASE_URL !== 'undefined') ? window.ASTRO_BASE_URL : '.';
            
            try {
                const response = await fetch(`${baseUrl}/api/cart.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ game_id: gameId, action: 'add' })
                });
                const data = await response.json();
                
                if (data.status === 'redirect') {
                    window.location.href = data.url;
                    return;
                }
                
                if (data.status === 'success') {
                    showToast(`${gameTitle} berhasil ditambahkan ke Keranjang`, 'success');
                    
                    // Update counter in navbar if exists
                    const cartBadge = document.querySelector('a[title="Keranjang"] .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.count;
                        if (data.count === 0) cartBadge.remove(); // removes badge if 0
                    } else if (data.count > 0) {
                        // Create badge if not exists and count > 0
                        const cartBtn = document.querySelector('a[title="Keranjang"]');
                        if (cartBtn) {
                            const badge = document.createElement('span');
                            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark';
                            badge.textContent = data.count;
                            cartBtn.appendChild(badge);
                        }
                    }
                } else {
                    showToast('Gagal menambahkan ke keranjang', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan', 'error');
            }
        });
    });
}

/**
 * 5. Interactive Compatibility Form Calculator
 */
function initCompatibilityForm() {
    const form = document.getElementById('laptopSpecForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const cpuId = document.getElementById('selectCpu').value;
        const ramGb = document.getElementById('selectRam').value;
        const gpuId = document.getElementById('selectGpu').value;
        const vramGb = document.getElementById('selectVram').value;

        showToast('Analisis Spesifikasi Berhasil! Menampilkan Skor Kecocokan...', 'info');

        setTimeout(() => {
            window.location.href = `check-specs.php?analyzed=1&cpu=${cpuId}&ram=${ramGb}&gpu=${gpuId}&vram=${vramGb}`;
        }, 600);
    });
}

/**
 * Toast Notification System
 */
function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const iconMap = {
        success: 'fa-check-circle text-success',
        warning: 'fa-exclamation-triangle text-warning',
        error: 'fa-times-circle text-danger',
        info: 'fa-info-circle text-info'
    };

    const toast = document.createElement('div');
    toast.className = 'astro-toast';
    toast.innerHTML = `
        <i class="fas ${iconMap[type] || 'fa-bell'} fa-lg"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
