<?php

declare(strict_types=1);

/**
 * Component: Slide-over Shopping Cart Drawer (shadcn Zinc styled)
 * @var array $app
 */
?>
<!-- ─── Shopping Cart Drawer Modal ─── -->
<div class="cart-drawer-backdrop" id="cart-drawer-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="cart-drawer">
    <!-- Drawer Head -->
    <div class="cart-drawer-head">
      <div class="cart-head-title">
        <div style="display: flex; align-items: center; gap: 8px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #0066ff;">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Keranjang Belanja</h3>
        </div>
        <span class="cart-head-badge" id="cart-drawer-badge">0 Item</span>
      </div>
      <button type="button" class="btn-close-cart" id="btn-close-cart" aria-label="Tutup Keranjang">&times;</button>
    </div>

    <!-- Drawer Body -->
    <div class="cart-drawer-body">
      <!-- Empty State -->
      <div class="cart-empty-state" id="cart-empty-view">
        <div class="cart-empty-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
        </div>
        <h4 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">Keranjang Belanja Kosong</h4>
        <p style="font-size: 0.88rem; color: #64748b; margin: 0 0 20px 0; max-width: 260px;">Temukan produk AI &amp; lisensi digital terbaik dengan promo spesial.</p>
        <button type="button" class="btn-browse-catalog" id="btn-browse-catalog">Jelajahi Katalog &rarr;</button>
      </div>

      <!-- Item List Container -->
      <div class="cart-items-container" id="cart-items-view" style="display: none;">
        <div class="cart-items-list" id="cart-items-list">
          <!-- Dynamically populated via JS -->
        </div>
      </div>
    </div>

    <!-- Drawer Footer (Visible when items exist) -->
    <div class="cart-drawer-footer" id="cart-footer" style="display: none;">
      <div class="cart-summary-box">
        <div class="cart-sum-row">
          <span>Subtotal Item</span>
          <span class="sum-val" id="cart-subtotal-val">Rp0</span>
        </div>
        <div class="cart-sum-row promo" id="cart-discount-row">
          <span>Hemat Promo</span>
          <span class="sum-val text-green" id="cart-discount-val">-Rp0</span>
        </div>
        <div class="cart-sum-divider"></div>
        <div class="cart-sum-row grand">
          <span>Total Pembayaran</span>
          <span class="sum-val total" id="cart-total-val">Rp0</span>
        </div>
      </div>

      <button type="button" class="btn-checkout-cart" id="btn-checkout-cart">
        <span>Lanjut ke Checkout Pesanan &rarr;</span>
      </button>

      <div class="cart-actions-sub">
        <button type="button" class="btn-clear-cart" id="btn-clear-cart">Kosongkan Keranjang</button>
      </div>

      <div class="cart-trust-notes">
        <span>QRIS Otomatis</span>
        <span>Pengiriman Instan</span>
        <span>Garansi 30 Hari</span>
      </div>
    </div>
  </div>
</div>

<!-- Floating Toast Notification when adding to Cart -->
<div class="cart-toast" id="cart-toast" aria-live="polite">
  <div class="cart-toast-inner">
    <div class="toast-icon">✓</div>
    <div class="toast-content">
      <strong id="toast-title">Produk ditambahkan!</strong>
      <span id="toast-desc">Item siap di keranjang belanja.</span>
    </div>
    <button type="button" class="toast-btn-action" id="toast-view-cart">Lihat</button>
  </div>
</div>
