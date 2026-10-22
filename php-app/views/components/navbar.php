<?php

declare(strict_types=1);

/**
 * Component: Navbar / Header Island
 * @var array $app
 */
?>
<header class="topbar">
  <div class="topbar-inner">
    <a class="brand" href="/" aria-label="<?= e($app['name']) ?> Home">
      <img src="/assets/logos/ziulabs-icon.webp" alt="Z" class="brand-logo-img" width="34" height="34" />
      <span class="brand-tag">STORE</span>
    </a>

    <nav class="nav-links" aria-label="Navigasi Utama">
      <a href="/#catalog">Katalog</a>
      <a href="/#process">Cara Kerja</a>
      <a href="/#faq">FAQ</a>
    </nav>

    <div class="topbar-actions">
      <button class="cmdk-trigger" id="cmdk-btn" type="button" aria-label="Cari SKU atau nama produk">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <span>Cari SKU</span>
        <kbd>⌘K</kbd>
      </button>

      <!-- Cart Drawer Trigger Button -->
      <button class="cart-trigger-btn" id="cart-nav-btn" type="button" aria-label="Buka Keranjang Belanja" title="Lihat Keranjang Belanja">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span class="cart-btn-label">Keranjang</span>
        <span class="cart-badge-count" id="cart-badge-count">0</span>
      </button>

      <!-- Clerk Authentication Slot (UserButton & Sign In) -->
      <div id="clerk-auth-slot" class="clerk-auth-slot">
        <a href="/dashboard/" class="btn-header-dash" id="btn-header-dash" style="display: none;" title="Buka Dashboard Pesanan">
          <span>Dashboard</span>
        </a>
        <div id="clerk-user-button-mount" class="clerk-user-mount" style="display: none;"></div>
        <button type="button" class="btn-header-login" id="btn-clerk-sign-in" title="Masuk / Daftar Akun dengan Clerk">
          <svg width="15" height="15" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.14-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.97 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
          </svg>
          <span>Masuk</span>
        </button>
      </div>
    </div>
  </div>
</header>
