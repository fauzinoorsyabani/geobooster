<?php

declare(strict_types=1);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline' https://*.clerk.accounts.dev https://*.clerk.com https://challenges.cloudflare.com; img-src 'self' data: https://img.clerk.com https://images.clerk.dev https://*.googleusercontent.com https://api.qrserver.com https://ui-avatars.com; connect-src 'self' https://*.clerk.accounts.dev https://*.clerk.com https://api.clerk.com https://challenges.cloudflare.com https://api.qrserver.com; frame-src 'self' https://challenges.cloudflare.com https://*.clerk.accounts.dev https://*.clerk.com; worker-src 'self' blob:; frame-ancestors 'none'; base-uri 'self'");

// Bootstrap & View Helpers
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/helpers.php';

\GeoBooster\Security\Csrf::start();

// Data & Config
$app = require __DIR__ . '/../../config/app.php';
$products = require __DIR__ . '/../../data/products.php';
$availableCount = count(array_filter($products, fn(array $p): bool => $p['available']));
$soldOutCount = count($products) - $availableCount;

$pageTitle = 'Katalog Produk & Akses Digital — ' . $app['name'];
$pageDesc = 'Katalog lengkap lisensi software terverifikasi dan akun AI premium ZiuLabs. Temukan alat produktivitas pilihan Anda dengan aktivasi cepat.';
$bodyClass = 'page-catalog';

require __DIR__ . '/../../views/layouts/header.php';
require __DIR__ . '/../../views/components/navbar.php';
?>
<main id="catalog-page" class="catalog-standalone-page">
  <div class="shell">
    <!-- Breadcrumb -->
    <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
      <a href="/" class="breadcrumb-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span>Beranda</span>
      </a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Katalog Digital</span>
    </nav>

    <!-- Standalone Store Header -->
    <div class="store-catalog-header">
      <div class="store-header-left">
        <div class="store-pill-badge">
          <span class="radar-dot"></span>
          <span>KATALOG DIGITAL RESMI</span>
        </div>
        <h1 class="store-catalog-title">
          Pilih Produk &amp; <span class="text-gemini">Akses Pilihanmu.</span>
        </h1>
        <p class="store-catalog-subtitle">
          Katalog lengkap lisensi dan akses digital resmi ZiuLabs. Hanya <strong>Gemini Pro 18 Months</strong> yang saat ini tersedia dan siap dibeli langsung melalui QRIS otomatis. Produk lainnya berstatus habis (<strong>Rp XXX</strong>) dan belum diinput harganya.
        </p>
      </div>

      <div class="store-header-stats">
        <div class="store-stat-box is-avail">
          <div class="stat-dot pulse-green"></div>
          <div class="stat-info">
            <span class="stat-num"><?= $availableCount ?></span>
            <span class="stat-lbl">Tersedia Beli Langsung</span>
          </div>
        </div>
        <div class="store-stat-box is-waiting">
          <div class="stat-dot neutral-gray"></div>
          <div class="stat-info">
            <span class="stat-num"><?= $soldOutCount ?></span>
            <span class="stat-lbl">Menunggu Restok (Rp XXX)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Notice Banner -->
    <div class="store-notice-banner">
      <div class="notice-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <div class="notice-text">
        <strong>Status Stok &amp; Harga:</strong> Saat ini stok siap beli instan adalah <strong>Gemini Pro 18 Months (Rp50.000 — Diskon 50%)</strong>. Untuk produk lain berstatus <em>Habis (Rp XXX)</em>, harga resmi belum ditentukan. Anda dapat menghubungi admin via WhatsApp untuk menanyakan estimasi ketersediaan.
      </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="filter-bar">
      <div class="filter-tabs" role="tablist">
        <button class="filter-tab is-active" data-filter="all">Semua (<?= count($products) ?>)</button>
        <button class="filter-tab" data-filter="AI & Productivity">AI &amp; Productivity</button>
        <button class="filter-tab" data-filter="Creative & Design">Creative &amp; Design</button>
        <button class="filter-tab" data-filter="Business & Education">Business &amp; Education</button>
        <button class="filter-tab" data-filter="Entertainment">Entertainment</button>
        <button class="filter-tab" data-filter="Utilities">Utilities</button>
      </div>

      <div class="filter-search-box">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="catalog-search" placeholder="Cari nama atau SKU..." aria-label="Cari produk" />
      </div>
    </div>

    <!-- Product Cards Grid -->
    <div class="product-grid" id="product-list">
      <?php foreach ($products as $index => $item): 
        $isLive = $item['available'];
        $waLink = whatsappLink($app['whatsapp_number'], $item['name'], $item['sku'], $isLive);
      ?>
        <div class="product-card <?= $isLive ? '' : 'sold-out' ?>" 
             data-category="<?= e($item['category']) ?>" 
             data-name="<?= e(strtolower($item['name'] . ' ' . $item['sku'])) ?>"
             data-index="<?= $index ?>">
          
          <div class="product-header-visual">
            <img class="card-logo" src="/assets/logos/<?= e(brandLogo($item['name'])) ?>" alt="Logo <?= e($item['name']) ?>" loading="lazy" />
            
            <span class="card-status-pill <?= $isLive ? 'is-live' : 'is-out' ?>">
              <?php if ($isLive): ?><span class="pill-pulse"></span><?php endif; ?>
              <span><?= $isLive ? 'Tersedia' : 'Habis' ?></span>
            </span>
          </div>

          <div class="product-card-body">
            <div class="card-category-meta">
              <span class="card-cat-tag"><?= e($item['category']) ?></span>
              <span class="card-sku-code"><?= e($item['sku']) ?></span>
            </div>

            <h3 class="card-title"><?= e($item['name']) ?></h3>
            <p class="card-desc">Langganan terverifikasi dengan panduan aktivasi resmi dan pendampingan bantuan penuh.</p>

            <div class="card-footer">
              <div class="price-inquiry">
                <span class="inquiry-label">Harga Paket</span>
                <?php if (!empty($item['original_price'])): ?>
                  <div class="price-discount-stack">
                    <span class="price-orig"><?= e($item['original_price']) ?></span>
                    <span class="discount-badge"><?= e($item['discount'] ?? '50%') ?></span>
                  </div>
                <?php endif; ?>
                <span class="inquiry-val"><?= e($item['price']) ?></span>
              </div>
              
              <div class="card-btn-group">
                <?php if ($isLive): ?>
                  <button type="button" class="btn-card-cart js-add-to-cart"
                          data-product-id="<?= e((string)($item['id'] ?? 1)) ?>"
                          data-sku-id="<?= e((string)($item['sku_id'] ?? 1)) ?>"
                          data-sku="<?= e($item['sku']) ?>"
                          data-name="<?= e($item['name']) ?>"
                          data-price="<?= e($item['price']) ?>"
                          data-price-minor="<?= e((string)($item['price_minor'] ?? 5000000)) ?>"
                          data-logo="/assets/logos/<?= e(brandLogo($item['name'])) ?>"
                          title="Tambah ke Keranjang">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span>+ Keranjang</span>
                  </button>

                  <button type="button" class="btn-card-buy js-open-checkout"
                          data-product-id="<?= e((string)($item['id'] ?? 1)) ?>"
                          data-sku-id="<?= e((string)($item['sku_id'] ?? 1)) ?>"
                          data-sku="<?= e($item['sku']) ?>"
                          data-name="<?= e($item['name']) ?>"
                          data-price="<?= e($item['price']) ?>"
                          data-price-minor="<?= e((string)($item['price_minor'] ?? 5000000)) ?>"
                          data-logo="/assets/logos/<?= e(brandLogo($item['name'])) ?>"
                          title="Beli Langsung via QRIS">
                    <span>⚡ Beli</span>
                  </button>
                <?php else: ?>
                  <a class="btn-card-order sold-out-link" href="<?= e($waLink) ?>" target="_blank" rel="noopener" title="Tanyakan info restok via WhatsApp">
                    <span>💬 Info Stok WA</span>
                    <span>↗</span>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination Wrap -->
    <div class="pagination-wrap">
      <button class="page-btn page-btn-prev" type="button" aria-label="Halaman Sebelumnya">←</button>
      <span class="page-counter">Halaman 1</span>
      <button class="page-btn page-btn-next" type="button" aria-label="Halaman Berikutnya">→</button>
    </div>
  </div>
</main>
<?php
require __DIR__ . '/../../views/components/footer.php';
require __DIR__ . '/../../views/components/ziubot-widget.php';
require __DIR__ . '/../../views/components/cmdk.php';
require __DIR__ . '/../../views/components/checkout-modal.php';
require __DIR__ . '/../../views/components/cart-drawer.php';
require __DIR__ . '/../../views/layouts/scripts.php';
