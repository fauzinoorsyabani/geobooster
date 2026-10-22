<?php

declare(strict_types=1);

/**
 * Component: Instant Checkout Drawer / Modal for Digital Products
 * @var array $app
 */
$csrfToken = \GeoBooster\Security\Csrf::token();
$defaultName = $_SESSION['user_name'] ?? '';
$defaultEmail = $_SESSION['user_email'] ?? '';
?>
<!-- ─── Checkout Drawer / Modal ─── -->
<div class="checkout-modal-backdrop" id="checkout-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="checkout-drawer">
    <div class="checkout-head">
      <div class="checkout-head-title">
        <span class="checkout-eyebrow">ORDER DIGITAL INSTAN</span>
        <h3>Checkout Pesanan</h3>
      </div>
      <button type="button" class="btn-close-modal" id="btn-close-checkout" aria-label="Tutup form checkout">&times;</button>
    </div>

    <!-- Product Summary Card -->
    <div class="checkout-product-summary">
      <div id="chk-single-summary" style="display: flex; align-items: center; gap: 14px; width: 100%;">
        <img src="/assets/logos/gemini.webp" alt="Logo" class="checkout-item-logo" id="chk-logo" width="44" height="44">
        <div class="checkout-item-details">
          <div class="checkout-item-meta">
            <span class="checkout-badge-sku" id="chk-sku">AI-GEM-18M</span>
            <span class="checkout-badge-status">Stok Siap</span>
          </div>
          <h4 class="checkout-item-name" id="chk-name">Gemini Pro 18 Months Head (Link)</h4>
          <div class="checkout-item-price-wrap">
            <span class="checkout-item-price" id="chk-price">Rp50.000</span>
            <span class="checkout-item-guarantee">✓ Garansi 30 Hari &amp; Akses Resmi</span>
          </div>
        </div>
      </div>

      <div id="chk-cart-summary" style="display: none; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
          <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Daftar Item Keranjang</span>
          <span style="font-size: 0.82rem; font-weight: 700; color: #0066ff;" id="chk-cart-item-count">1 Item</span>
        </div>
        <div id="chk-cart-items-preview" style="max-height: 140px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;"></div>
      </div>
    </div>

    <!-- Checkout Form -->
    <form id="checkout-form" class="checkout-form" method="post" action="/api/checkout.php">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="sku_id" id="chk-input-sku-id" value="">
      <input type="hidden" name="product_id" id="chk-input-product-id" value="">
      <input type="hidden" name="cart_items" id="chk-input-cart-items" value="">

      <div class="form-section-title">Informasi Penerima Lisensi</div>
      
      <div class="form-group">
        <label for="chk-customer-name">Nama Lengkap <span class="required-star">*</span></label>
        <input type="text" id="chk-customer-name" name="customer_name" placeholder="Contoh: Budi Santoso" value="<?= e($defaultName) ?>" required>
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label for="chk-customer-phone">Nomor WhatsApp <span class="required-star">*</span></label>
          <input type="tel" id="chk-customer-phone" name="customer_phone" placeholder="0812xxxxxxxx" required>
          <small class="input-hint">Kredensial &amp; notifikasi akan dikirim via WA.</small>
        </div>

        <div class="form-group">
          <label for="chk-customer-email">Alamat Email <span class="required-star">*</span></label>
          <input type="email" id="chk-customer-email" name="customer_email" placeholder="nama@email.com" value="<?= e($defaultEmail) ?>" required>
          <small class="input-hint">Untuk invoice resmi &amp; cadangan akses.</small>
        </div>
      </div>

      <div class="form-section-title" style="margin-top: 18px;">Pilih Metode Pembayaran</div>

      <div class="payment-method-selector">
        <label class="payment-method-card is-selected">
          <input type="radio" name="payment_method" value="qris" checked>
          <div class="pm-visual">
            <div class="pm-badge">QRIS OTOMATIS</div>
            <strong>QRIS Instan (Semua Bank &amp; e-Wallet)</strong>
            <span>BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana, ShopeePay</span>
          </div>
          <span class="pm-check">✓</span>
        </label>

        <label class="payment-method-card">
          <input type="radio" name="payment_method" value="va">
          <div class="pm-visual">
            <div class="pm-badge gray">VIRTUAL ACCOUNT</div>
            <strong>Transfer Virtual Account</strong>
            <span>Nomor VA otomatis terverifikasi tanpa upload bukti</span>
          </div>
          <span class="pm-check">✓</span>
        </label>
      </div>

      <!-- Pricing Summary -->
      <div class="checkout-total-box">
        <div class="total-row">
          <span>Subtotal Produk</span>
          <strong id="chk-subtotal">Rp12.900</strong>
        </div>
        <div class="total-row">
          <span>Biaya Layanan &amp; Aktivasi</span>
          <span class="text-green" style="font-weight: 700;">GRATIS (Rp0)</span>
        </div>
        <div class="total-divider"></div>
        <div class="total-row grand-total">
          <span>Total Pembayaran</span>
          <strong class="total-amount" id="chk-grand-total">Rp12.900</strong>
        </div>
      </div>

      <div id="checkout-alert" class="checkout-alert" style="display: none;"></div>

      <button type="submit" class="btn-submit-order" id="btn-submit-order">
        <span class="btn-text">Lanjut ke Pembayaran QRIS &rarr;</span>
        <span class="btn-spinner" style="display: none;">Memproses Pesanan...</span>
      </button>

      <div class="checkout-security-notes">
        <span>🔒 Transaksi Aman 256-bit SSL</span>
        <span>⚡ Aktivasi Otomatis &amp; Panduan Lengkap</span>
      </div>
    </form>
  </div>
</div>
