/**
 * GameCheck App JS — v2.0 Purple Edition
 * Features: Dark/Light Mode, Live Search, Cart Manager, Wishlist, Toast, Help Modal
 */

// ─────────────────────────────────────────────────────────────
// 0. CONSTANTS — injected from PHP via window.GC
// ─────────────────────────────────────────────────────────────
const BASE_URL = window.GC?.base_url || '';

// ─────────────────────────────────────────────────────────────
// 1. DARK / LIGHT MODE
// ─────────────────────────────────────────────────────────────
const ThemeManager = (() => {
  const STORAGE_KEY = 'gc_theme';
  const root = document.documentElement;

  function getTheme() {
    return localStorage.getItem(STORAGE_KEY) || 'dark';
  }

  function setTheme(theme) {
    root.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
    updateToggleBtns(theme);
  }

  function toggle() {
    const current = getTheme();
    setTheme(current === 'dark' ? 'light' : 'dark');
  }

  function updateToggleBtns(theme) {
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.innerHTML = theme === 'dark'
        ? '<i class="bi bi-sun-fill"></i>'
        : '<i class="bi bi-moon-stars-fill"></i>';
      btn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    });
  }

  function init() {
    setTheme(getTheme());
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.addEventListener('click', toggle);
    });
  }

  return { init, toggle, getTheme, setTheme };
})();

// ─────────────────────────────────────────────────────────────
// 2. LIVE SEARCH
// ─────────────────────────────────────────────────────────────
const SearchManager = (() => {
  let debounceTimer = null;
  let currentQuery = '';

  function init() {
    const inputs = document.querySelectorAll('.navbar-search-input');
    inputs.forEach(input => {
      const wrapper = input.closest('.search-wrapper');
      if (!wrapper) return;
      const dropdown = wrapper.querySelector('.search-dropdown');
      const clearBtn  = wrapper.querySelector('.search-clear-btn');

      input.addEventListener('input', () => {
        const q = input.value.trim();
        currentQuery = q;
        if (clearBtn) clearBtn.classList.toggle('visible', q.length > 0);

        clearTimeout(debounceTimer);
        if (q.length < 2) {
          closeDropdown(dropdown);
          return;
        }
        debounceTimer = setTimeout(() => fetchResults(q, dropdown, wrapper), 280);
      });

      input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && dropdown.innerHTML.trim()) {
          dropdown.classList.add('open');
        }
      });

      clearBtn?.addEventListener('click', () => {
        input.value = '';
        input.focus();
        clearBtn.classList.remove('visible');
        closeDropdown(dropdown);
      });

      document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) closeDropdown(dropdown);
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDropdown(dropdown);
        if (e.key === 'Enter' && input.value.trim()) {
          window.location.href = `${BASE_URL}/katalog.php?search=${encodeURIComponent(input.value.trim())}`;
        }
      });
    });
  }

  async function fetchResults(q, dropdown, wrapper) {
    dropdown.innerHTML = '<div class="search-loading"><i class="bi bi-arrow-repeat"></i> Mencari...</div>';
    dropdown.classList.add('open');

    try {
      const res = await fetch(`${BASE_URL}/api/search.php?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      renderResults(data.results, dropdown, q);
    } catch (e) {
      dropdown.innerHTML = '<div class="search-no-result">Gagal memuat hasil pencarian.</div>';
    }
  }

  function renderResults(results, dropdown, q) {
    if (!results.length) {
      dropdown.innerHTML = `<div class="search-no-result"><i class="bi bi-search me-2"></i>Tidak ada game untuk "<strong>${escHtml(q)}</strong>"</div>`;
      return;
    }

    dropdown.innerHTML = results.map(g => {
      const imgSrc = `${BASE_URL}/assets/images/games/${g.cover}`;
      const fallback = `https://placehold.co/44x44/151c2c/a855f7?text=${encodeURIComponent(g.name.substring(0,2))}`;
      return `
        <a class="search-result-item" href="${BASE_URL}/detail-game.php?slug=${escHtml(g.slug)}">
          <img src="${imgSrc}" 
               onerror="this.src='${fallback}'" 
               alt="${escHtml(g.name)}" 
               class="search-result-thumb">
          <div class="flex-grow-1 min-width-0">
            <div class="search-result-name">${escHtml(g.name)}</div>
            <div class="search-result-meta">${escHtml(g.genre)} &bull; ${g.price_fmt}</div>
          </div>
          <span class="badge-difficulty badge-${g.difficulty.toLowerCase()} position-static ms-2" style="font-size:0.62rem;padding:2px 7px;">${escHtml(g.difficulty)}</span>
        </a>`;
    }).join('');
  }

  function closeDropdown(dropdown) {
    dropdown?.classList.remove('open');
  }

  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  return { init };
})();

// ─────────────────────────────────────────────────────────────
// 3. CART MANAGER (localStorage)
// ─────────────────────────────────────────────────────────────
const CartManager = (() => {
  const STORAGE_KEY = 'gc_cart';

  function getCart() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
    catch { return []; }
  }

  function saveCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    updateBadge();
    renderMiniCart();
  }

  function add(item) {
    // item: { id, name, price, slug, cover }
    const cart = getCart();
    if (cart.find(c => c.id === item.id)) {
      showToast('Game sudah ada di keranjang!', 'info');
      return;
    }
    cart.push(item);
    saveCart(cart);
    showToast(`<i class="fas fa-shopping-cart"></i> ${item.name} ditambahkan ke keranjang!`, 'success');
    // Pulse cart icon
    document.querySelectorAll('.cart-icon-btn').forEach(btn => {
      btn.classList.add('pulse-glow');
      setTimeout(() => btn.classList.remove('pulse-glow'), 1500);
    });
  }

  function remove(id) {
    let cart = getCart();
    cart = cart.filter(c => c.id != id);
    saveCart(cart);
  }

  function getTotal() {
    return getCart().reduce((sum, c) => sum + parseFloat(c.price || 0), 0);
  }

  function updateBadge() {
    const count = getCart().length;
    document.querySelectorAll('.cart-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
  }

  function renderMiniCart() {
    const cart = getCart();
    const body = document.getElementById('mini-cart-body');
    const footer = document.getElementById('mini-cart-footer');
    if (!body) return;

    if (!cart.length) {
      body.innerHTML = `
        <div class="mini-cart-empty">
          <i class="bi bi-cart-x"></i>
          <p>Keranjangmu masih kosong</p>
          <a href="${BASE_URL}/katalog.php" class="btn-purple d-inline-flex mt-2" style="font-size:0.8rem;padding:0.5rem 1.2rem;">
            <i class="bi bi-grid me-2"></i> Lihat Game
          </a>
        </div>`;
      if (footer) footer.style.display = 'none';
      return;
    }

    body.innerHTML = cart.map(item => {
      const img = `${BASE_URL}/assets/images/games/${item.cover}`;
      const fb  = `https://placehold.co/52x52/151c2c/a855f7?text=${encodeURIComponent((item.name||'G').substring(0,1))}`;
      const priceStr = parseFloat(item.price) === 0 ? '<span style="color:var(--success)">Gratis</span>' : 'Rp ' + numberFmt(item.price);
      return `
        <div class="mini-cart-item">
          <img src="${img}" onerror="this.src='${fb}'" alt="${escHtml(item.name)}" class="mini-cart-item-thumb">
          <div class="flex-grow-1 min-width-0">
            <div class="mini-cart-item-name text-truncate">${escHtml(item.name)}</div>
            <div class="mini-cart-item-price">${priceStr}</div>
          </div>
          <button class="mini-cart-item-remove" onclick="CartManager.remove(${item.id})" title="Hapus">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>`;
    }).join('');

    if (footer) {
      footer.style.display = 'block';
      const totalEl = document.getElementById('mini-cart-total');
      if (totalEl) {
        const total = getTotal();
        totalEl.innerHTML = total === 0
          ? '<span style="color:var(--success)">Gratis</span>'
          : 'Rp ' + numberFmt(total);
      }
    }
  }

  function openPanel() {
    document.getElementById('mini-cart-overlay')?.classList.add('open');
    document.getElementById('mini-cart-panel')?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closePanel() {
    document.getElementById('mini-cart-overlay')?.classList.remove('open');
    document.getElementById('mini-cart-panel')?.classList.remove('open');
    document.body.style.overflow = '';
  }

  function init() {
    updateBadge();
    renderMiniCart();

    document.querySelectorAll('.cart-icon-btn').forEach(btn => {
      btn.addEventListener('click', openPanel);
    });
    document.getElementById('mini-cart-overlay')?.addEventListener('click', closePanel);
    document.getElementById('mini-cart-close')?.addEventListener('click', closePanel);
  }

  function escHtml(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

  return { init, add, remove, getCart, getTotal, openPanel, closePanel, renderMiniCart };
})();
window.CartManager = CartManager;

// ─────────────────────────────────────────────────────────────
// 4. WISHLIST MANAGER (localStorage)
// ─────────────────────────────────────────────────────────────
const WishlistManager = (() => {
  const STORAGE_KEY = 'gc_wishlist';

  function getList() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
    catch { return []; }
  }

  function save(list) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    updateBadge();
    updateButtons();
  }

  function toggle(item) {
    let list = getList();
    const idx = list.findIndex(w => w.id == item.id);
    if (idx >= 0) {
      list.splice(idx, 1);
      showToast(`️ ${item.name} dihapus dari wishlist`, 'info');
    } else {
      list.push(item);
      showToast(`️ ${item.name} ditambahkan ke wishlist!`, 'success');
    }
    save(list);
    return idx < 0; // true = added
  }

  function has(id) {
    return getList().some(w => w.id == id);
  }

  function remove(id) {
    let list = getList().filter(w => w.id != id);
    save(list);
  }

  function updateBadge() {
    const count = getList().length;
    document.querySelectorAll('.wishlist-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
  }

  function updateButtons() {
    document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
      const id = btn.dataset.wishlistId;
      const active = has(id);
      btn.classList.toggle('active', active);
      const icon = btn.querySelector('i');
      if (icon) {
        icon.className = active ? 'bi bi-heart-fill' : 'bi bi-heart';
      }
    });
  }

  function init() {
    updateBadge();
    updateButtons();

    document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const id    = btn.dataset.wishlistId;
        const name  = btn.dataset.wishlistName || 'Game';
        const price = btn.dataset.wishlistPrice || '0';
        const slug  = btn.dataset.wishlistSlug  || '';
        const cover = btn.dataset.wishlistCover || 'default_game.jpg';
        WishlistManager.toggle({ id, name, price, slug, cover });
      });
    });
  }

  return { init, toggle, has, remove, getList, updateBadge, updateButtons };
})();
window.WishlistManager = WishlistManager;

// ─────────────────────────────────────────────────────────────
// 5. TOAST NOTIFICATIONS
// ─────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
  let container = document.getElementById('gc-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'gc-toast-container';
    container.className = 'toast-container-custom';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  const icons = { success: 'bi-check-circle-fill', error: 'bi-exclamation-circle-fill', info: 'bi-info-circle-fill' };
  toast.className = `gc-toast toast-${type}`;
  toast.innerHTML = `<i class="bi ${icons[type] || icons.success}"></i> ${message}`;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(20px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}
window.showToast = showToast;
// legacy
window.showToastSuccess = (msg) => showToast(msg, 'success');

// ─────────────────────────────────────────────────────────────
// 6. HELP MODAL
// ─────────────────────────────────────────────────────────────
const HelpModal = (() => {
  function open() {
    document.getElementById('help-modal-overlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function close() {
    document.getElementById('help-modal-overlay')?.classList.remove('open');
    document.body.style.overflow = '';
  }
  function init() {
    document.querySelectorAll('.help-trigger-btn').forEach(btn => btn.addEventListener('click', open));
    document.getElementById('help-modal-overlay')?.addEventListener('click', (e) => {
      if (e.target === document.getElementById('help-modal-overlay')) close();
    });
    document.getElementById('help-modal-close')?.addEventListener('click', close);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  }
  return { init, open, close };
})();

// ─────────────────────────────────────────────────────────────
// 7. SPEC PRESET FILL (legacy support)
// ─────────────────────────────────────────────────────────────
function fillSpecPreset(cpu, ram, gpu, vram, os) {
  const f = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
  f('cpu', cpu); f('ram', ram); f('gpu', gpu); f('vram', vram); f('os', os);
  showToast('Preset spesifikasi berhasil dimasukkan!', 'success');
}
window.fillSpecPreset = fillSpecPreset;

// ─────────────────────────────────────────────────────────────
// 8. INLINE SPEC CHECKER (on detail-game.php)
// ─────────────────────────────────────────────────────────────
const InlineSpecChecker = (() => {
  function init() {
    const form = document.getElementById('inline-spec-form');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const data = {
        cpu:     form.querySelector('#inline-cpu')?.value || '',
        ram:     parseInt(form.querySelector('#inline-ram')?.value || '0'),
        gpu:     form.querySelector('#inline-gpu')?.value || '',
        vram:    parseInt(form.querySelector('#inline-vram')?.value || '0'),
        os:      form.querySelector('#inline-os')?.value || '',
        game_id: form.querySelector('#inline-game-id')?.value || '',
      };

      fetch(`${BASE_URL}/api/check-compat.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => r.json())
      .then(res => renderCompatResult(res))
      .catch(() => showToast('Gagal melakukan pengecekan.', 'error'));
    });
  }

  function renderCompatResult(res) {
    const container = document.getElementById('compat-result');
    if (!container) return;

    const statusColors = {
      'Sangat Cocok':           'var(--success)',
      'Bisa Dimainkan':         'var(--warning)',
      'Bisa dengan Setting Rendah': '#fb923c',
      'Tidak Direkomendasikan': 'var(--danger)',
    };
    const color = statusColors[res.label] || 'var(--text-muted)';

    const breakdownHtml = Object.entries(res.breakdown || {}).map(([key, item]) => {
      const icon = item.passed ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
      const labels = { cpu: 'CPU', ram: 'RAM', gpu: 'GPU', vram: 'VRAM', os: 'OS' };
      return `
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
          <span class="text-secondary small">${icon} ${labels[key] || key}</span>
          <div class="text-end small">
            <div>Kamu: <strong class="text-white">${item.user}</strong></div>
            <div class="text-muted">Min: ${item.req}</div>
          </div>
        </div>`;
    }).join('');

    container.innerHTML = `
      <div class="p-4 rounded-lg" style="border: 2px solid ${color}; background: rgba(0,0,0,0.3)">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span style="font-size:2.5rem">${res.icon}</span>
          <div>
            <div class="fw-bold fs-5" style="color:${color}">${res.label}</div>
            <div class="text-secondary small">${res.reason}</div>
          </div>
          <div class="ms-auto">
            <div class="score-badge" style="background:${color}22; color:${color}; border: 1px solid ${color}">${res.score}/100</div>
          </div>
        </div>
        <div>${breakdownHtml}</div>
      </div>`;
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  return { init };
})();

// ─────────────────────────────────────────────────────────────
// 9. MISC HELPERS
// ─────────────────────────────────────────────────────────────
function numberFmt(n) {
  return Number(n).toLocaleString('id-ID');
}
window.numberFmt = numberFmt;

// Bootstrap tooltips init
function initTooltips() {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
}

// Flash alert auto-dismiss
function initFlashAlerts() {
  document.querySelectorAll('.alert-dismissible').forEach(el => {
    setTimeout(() => {
      try { new bootstrap.Alert(el).close(); } catch {}
    }, 5000);
  });
}

// ─────────────────────────────────────────────────────────────
// 10. BOOTSTRAP
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  SearchManager.init();
  CartManager.init();
  WishlistManager.init();
  HelpModal.init();
  InlineSpecChecker.init();
  initTooltips();
  initFlashAlerts();
});
