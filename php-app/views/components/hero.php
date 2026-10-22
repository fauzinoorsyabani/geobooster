<?php

declare(strict_types=1);

/**
 * Component: Hero Section with Floating Mascot & 3D Card
 * @var array $app
 * @var array $products
 * @var int $availableCount
 */
?>
<section class="hero-section">
  <div class="shell hero">
    <div class="hero-content">
      <div class="verified-chip">
        <span class="radar-dot"></span>
        <span>Akses Resmi · Produk Digital &amp; AI</span>
      </div>

      <h1 class="hero-headline">
        <span class="hero-headline-line1">
          Google AI Pro &amp;
          <span class="mascot-laptop-floating" id="mascot-hero-laptop" title="ZiuBot — Asisten AI ZiuLabs" aria-label="Maskot ZiuBot">
            <img src="/assets/mascot/ziubot-laptop-256.webp" alt="ZiuBot" width="98" height="114" class="mascot-laptop-img" />
          </span>
        </span><br>
        <span class="rotator-badge">
          <span class="rotator-text" id="hero-rotator">Akses Lengkap Dunia AI.</span>
          <span class="rotator-cursor" aria-hidden="true">|</span>
        </span>
      </h1>

      <p class="hero-lead">
        Buka potensi teknologi AI mutakhir dan produk digital terkurasi dalam satu katalog terpercaya. Produktivitas, kreativitas, dan efisiensi kerja kini jauh lebih cepat, mudah, dan aman.
      </p>

      <!-- 4 Clean Feature Matrix -->
      <div class="feature-matrix">
        <div class="feature-pill">
          <div class="feature-icon-box blue">
            <svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          </div>
          <span>Produktivitas Maksimal</span>
        </div>

        <div class="feature-pill">
          <div class="feature-icon-box green">
            <svg viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm-2 14.5l-3.5-3.5 1.41-1.41L10 13.67l6.59-6.59 1.41 1.41L10 16.5z"/></svg>
          </div>
          <span>Aman &amp; Terpercaya</span>
        </div>

        <div class="feature-pill">
          <div class="feature-icon-box purple">
            <svg viewBox="0 0 24 24"><path d="M4 6h16v10H4zM2 18h20v2H2z"/></svg>
          </div>
          <span>Multi Perangkat</span>
        </div>

        <div class="feature-pill">
          <div class="feature-icon-box amber">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
          </div>
          <span>100% Akses Resmi</span>
        </div>
      </div>

      <div class="hero-cta-group">
        <a class="btn-hero-wa" href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
          <span>Pesan Sekarang — Raih Manfaatnya</span>
        </a>

        <a class="btn-hero-catalog" href="#catalog">
          <span>Lihat Katalog (<?= count($products) ?> SKU)</span>
          <span class="arrow-down">↓</span>
        </a>
      </div>
    </div>

    <!-- ─── Hero 3D Showcase Card ─── -->
    <div class="hero-stage">
      <div class="floating-badge badge-top">
        <span class="badge-dot blue"></span>
        <span>Antigravity AI Ready</span>
      </div>

      <div class="showcase-board" id="interactive-board">
        <div class="showcase-header">
          <div class="showcase-brand-lockup">
            <div class="showcase-star">
              <svg viewBox="0 0 24 24"><path d="M12 2L14.39 9.61L22 12L14.39 14.39L12 22L9.61 14.39L2 12L9.61 9.61L12 2Z"/></svg>
            </div>
            <div class="showcase-titles">
              <h3>Google AI Pro</h3>
              <p>Akses Lengkap ke Dunia AI Google</p>
            </div>
          </div>
          <span class="showcase-tag-new">BARU</span>
        </div>

        <div class="showcase-grid-mini">
          <div class="mini-box">
            <div class="mini-box-head">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>
              <h4>Penyimpanan 5TB</h4>
            </div>
            <p>Simpan file, foto, dan proyek Anda dengan aman di Cloud Drive.</p>
          </div>

          <div class="mini-box">
            <div class="mini-box-head">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L14.39 9.61L22 12L14.39 14.39L12 22L9.61 14.39L2 12L9.61 9.61L12 2Z"/></svg>
              <h4>Model AI Unggulan</h4>
            </div>
            <p>Gemini Pro, Claude, ChatGPT, dan model AI mutakhir tanpa batas.</p>
          </div>
        </div>

        <div class="showcase-logos-bar">
          <span class="bar-label">Dukungan Aplikasi Resmi</span>
          <div class="logo-shelf">
            <div class="logo-shelf-item" title="Google Gemini"><img src="/assets/logos/gemini.webp" alt="Gemini" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="Gmail"><img src="/assets/logos/gmail.svg" alt="Gmail" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="YouTube"><img src="/assets/logos/youtube.svg" alt="YouTube" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="Microsoft Office"><img src="/assets/logos/microsoftoffice.svg" alt="Office 365" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="Canva"><img src="/assets/logos/canva.svg" alt="Canva" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="CapCut"><img src="/assets/logos/capcut.webp" alt="CapCut" width="34" height="34" loading="lazy" /></div>
            <div class="logo-shelf-item" title="Notion"><img src="/assets/logos/notion.svg" alt="Notion" width="34" height="34" loading="lazy" /></div>
          </div>
        </div>

        <div class="showcase-footer-deal">
          <div class="deal-text">
            <small>Informasi &amp; Pemesanan</small>
            <span class="deal-amount">Tanya Harga via WA</span>
            <span class="deal-unit">Respon cepat &amp; konsultasi gratis</span>
          </div>

          <a class="btn-deal-order" href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener">
            <span>Chat Admin</span>
            <span>→</span>
          </a>
        </div>
      </div>

      <div class="floating-badge badge-bottom">
        <span class="badge-dot green"></span>
        <span><?= $availableCount ?> SKU Siap Aktivasi</span>
      </div>
    </div>
  </div>
</section>
