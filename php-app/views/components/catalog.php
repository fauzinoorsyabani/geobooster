<?php

declare(strict_types=1);

/**
 * Component: Digital Catalog Section with Category Filter, Search, & Pagination
 * @var array $app
 * @var array $products
 */
?>
<section class="catalog-section" id="catalog">
  <div class="shell">
    <div class="section-head">
      <div>
        <p class="section-eyebrow">KATALOG DIGITAL</p>
        <h2 class="section-title">Pilih Produk &amp; <span class="text-gemini">Akses Pilihanmu.</span></h2>
      </div>
      <p class="section-desc">
        <?= count($products) ?> SKU siap pakai. Filter kategori untuk menemukan produk yang kamu butuhkan atau klik langsung untuk order cepat via WhatsApp / Beli Langsung.
      </p>
    </div>

    <!-- Filter Controls -->
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
</section>
