<?php

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'");

$app = require __DIR__ . '/../config/app.php';

$products = [
    ['name' => 'Gemini Pro 18 Months Head (Link)', 'category' => 'AI & Productivity', 'price' => 'Rp12.900', 'usd' => '$0.81', 'available' => true, 'sku' => 'AI-GEM-18M'],
    ['name' => 'CapCut Pro 7D FW', 'category' => 'Creative & Design', 'price' => 'Rp3.500', 'usd' => '$0.22', 'available' => true, 'sku' => 'CR-CAP-07D'],
    ['name' => 'Microsoft Office 365 Plus 1 Year', 'category' => 'Business & Education', 'price' => 'Rp7.400', 'usd' => '$0.46', 'available' => true, 'sku' => 'BS-MS-365'],
    ['name' => 'Adobe Express 12M', 'category' => 'Creative & Design', 'price' => 'Rp10.000', 'usd' => '$0.62', 'available' => true, 'sku' => 'CR-ADB-12M'],
    ['name' => 'Adobe Express 12M Pro', 'category' => 'Creative & Design', 'price' => 'Rp11.100', 'usd' => '$0.69', 'available' => true, 'sku' => 'CR-ADB-12P'],
    ['name' => 'Duolingo Super 12M', 'category' => 'Business & Education', 'price' => 'Rp11.100', 'usd' => '$0.69', 'available' => true, 'sku' => 'ED-DUO-12M'],
    ['name' => 'Canva Pro 2 YRS FW', 'category' => 'Creative & Design', 'price' => 'Rp13.300', 'usd' => '$0.83', 'available' => true, 'sku' => 'CR-CAN-02Y'],
    ['name' => 'iLovePDF Premium 1YR', 'category' => 'Business & Education', 'price' => 'Rp14.000', 'usd' => '$0.88', 'available' => true, 'sku' => 'BS-ILV-01Y'],
    ['name' => 'Gmails Accounts', 'category' => 'Utilities', 'price' => 'Rp16.700', 'usd' => '$1.04', 'available' => true, 'sku' => 'UT-GML-ACC'],
    ['name' => 'Crunchyroll MegaFan 15 Day', 'category' => 'Entertainment', 'price' => 'Rp17.800', 'usd' => '$1.11', 'available' => true, 'sku' => 'EN-CRU-15D'],
    ['name' => 'Avira Prime 3 Months', 'category' => 'Utilities', 'price' => 'Rp17.800', 'usd' => '$1.11', 'available' => true, 'sku' => 'UT-AVR-03M'],
    ['name' => 'Notion Plus 12M', 'category' => 'Business & Education', 'price' => 'Rp24.500', 'usd' => '$1.53', 'available' => true, 'sku' => 'BS-NTN-12M'],
    ['name' => 'EDX Premium 12 Months', 'category' => 'Business & Education', 'price' => 'Rp24.500', 'usd' => '$1.53', 'available' => true, 'sku' => 'ED-EDX-12M'],
    ['name' => 'CapCut Pro 1 Month FW', 'category' => 'Creative & Design', 'price' => 'Rp27.500', 'usd' => '$1.72', 'available' => true, 'sku' => 'CR-CAP-01M'],
    ['name' => 'Prime Video 6 Months', 'category' => 'Entertainment', 'price' => 'Rp33.400', 'usd' => '$2.09', 'available' => true, 'sku' => 'EN-PRM-06M'],
    ['name' => 'CapCut Pro 1600 Credits', 'category' => 'Creative & Design', 'price' => 'Rp35.600', 'usd' => '$2.23', 'available' => true, 'sku' => 'CR-CAP-16C'],
    ['name' => 'JetBrains Edu Pack 12M', 'category' => 'Business & Education', 'price' => 'Rp66.700', 'usd' => '$4.17', 'available' => true, 'sku' => 'BS-JTB-12M'],
    ['name' => 'Figma Pro Edu 2YRS', 'category' => 'Creative & Design', 'price' => 'Rp93.400', 'usd' => '$5.84', 'available' => true, 'sku' => 'CR-FIG-02Y'],
    ['name' => 'Framer AI 1 Year', 'category' => 'AI & Productivity', 'price' => 'Rp133.400', 'usd' => '$8.34', 'available' => true, 'sku' => 'AI-FRM-01Y'],
    ['name' => 'CapCut Pro 6 Months', 'category' => 'Creative & Design', 'price' => 'Rp173.900', 'usd' => '$10.87', 'available' => true, 'sku' => 'CR-CAP-06M'],
    ['name' => 'Miro Lifetime Panel 100 Invite', 'category' => 'Business & Education', 'price' => 'Rp177.900', 'usd' => '$11.12', 'available' => true, 'sku' => 'BS-MIR-LFT'],
    ['name' => 'Autodesk Admin 3000 Invite', 'category' => 'Creative & Design', 'price' => 'Rp266.900', 'usd' => '$16.68', 'available' => true, 'sku' => 'CR-ATD-30I'],
    ['name' => 'YT Premium 1 Bulan Invit', 'category' => 'Entertainment', 'price' => 'Rp3.000', 'usd' => '$0.19', 'available' => false, 'sku' => 'EN-YTP-01M'],
    ['name' => 'HBO Max 3 Months', 'category' => 'Entertainment', 'price' => 'Rp22.200', 'usd' => '$1.39', 'available' => false, 'sku' => 'EN-HBO-03M'],
    ['name' => 'Super Grok 9-10 Days (W3D)', 'category' => 'AI & Productivity', 'price' => 'Rp69.800', 'usd' => '$4.36', 'available' => false, 'sku' => 'AI-GRK-10D'],
    ['name' => 'Canva Pro Admin (500 Invitations)', 'category' => 'Creative & Design', 'price' => 'Rp128.200', 'usd' => '$8.01', 'available' => false, 'sku' => 'CR-CAN-50I'],
];

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

function brandLogo(string $name): string {
    return match (true) {
        str_contains($name, 'Gemini') => 'gemini.webp',
        str_contains($name, 'CapCut') => 'capcut.webp',
        str_contains($name, 'Microsoft') => 'microsoftoffice.svg',
        str_contains($name, 'Adobe') => 'adobe.svg',
        str_contains($name, 'Duolingo') => 'duolingo.svg',
        str_contains($name, 'Canva') => 'canva.svg',
        str_contains($name, 'iLovePDF') => 'ilovepdf.svg',
        str_contains($name, 'Gmail') || str_contains($name, 'Gmails') => 'gmail.svg',
        str_contains($name, 'Crunchyroll') => 'crunchyroll.svg',
        str_contains($name, 'Avira') => 'avira.svg',
        str_contains($name, 'Notion') => 'notion.svg',
        str_contains($name, 'EDX') => 'edx.svg',
        str_contains($name, 'Prime Video') => 'primevideo.svg',
        str_contains($name, 'JetBrains') => 'jetbrains.svg',
        str_contains($name, 'Figma') => 'figma.svg',
        str_contains($name, 'Framer') => 'framer.svg',
        str_contains($name, 'Miro') => 'miro.svg',
        str_contains($name, 'Autodesk') => 'autodesk.svg',
        str_contains($name, 'YT ') => 'youtube.svg',
        str_contains($name, 'HBO') => 'hbo.svg',
        str_contains($name, 'Grok') => 'grok.webp',
        default => 'openai.svg',
    };
}

function whatsappLink(string $number, string $name, string $sku, bool $available): string {
    $message = $available
        ? "Halo ZiuLabs, saya ingin menanyakan harga dan ketersediaan paket [{$sku}] {$name}. Mohon informasi lengkapnya."
        : "Halo ZiuLabs, saya ingin mendapat info saat stok [{$sku}] {$name} kembali tersedia.";
    return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $number) . '?text=' . rawurlencode($message);
}

$availableCount = count(array_filter($products, fn(array $p): bool => $p['available']));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title><?= e($app['name']) ?> — Solusi Lengkap Produk Digital &amp; AI</title>
  <meta name="description" content="<?= e($app['name']) ?> — Platform resmi katalog produk digital premium dan tools AI terverifikasi dengan aktivasi cepat via WhatsApp." />

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="/assets/style.css" />
</head>
<body>

  <!-- Background Ambient Glow & Web3 Grid (100% GPU, Zero Lag) -->
  <div class="ambient-scene" aria-hidden="true">
    <div class="ambient-grid"></div>
    <div class="grid-spotlight" id="grid-spotlight"></div>
    <div class="grid-scan-beam"></div>
    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>
    <div class="ambient-orb ambient-orb-3"></div>
  </div>

  <!-- ─── Topbar Navigation (Floating Pill Island) ─── -->
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="#top" aria-label="ZiuLabs Home">
        <img src="/assets/logos/ziulabs-icon.webp" alt="Z" class="brand-logo-img" width="34" height="34" />
        <span class="brand-tag">STORE</span>
      </a>

      <nav class="nav-links" aria-label="Navigasi Utama">
        <a href="#catalog">Katalog</a>
        <a href="#process">Cara Kerja</a>
        <a href="#faq">FAQ</a>
      </nav>

      <div class="topbar-actions">
        <button class="cmdk-trigger" id="cmdk-btn" type="button" aria-label="Cari SKU atau nama produk">
          <!-- Search Icon SVG -->
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <span>Cari SKU</span>
          <kbd>⌘K</kbd>
        </button>

        <a class="btn-header-wa" href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener">
          <!-- WhatsApp Icon SVG -->
          <svg viewBox="0 0 24 24">
            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/>
          </svg>
          <span>Chat WhatsApp</span>
        </a>
      </div>
    </div>
  </header>

  <main id="top">

    <!-- ─── Hero Section ─── -->
    <section class="hero-section">
      <div class="shell hero">
        <div class="hero-content">
          <div class="verified-chip">
            <span class="radar-dot"></span>
            <span>Akses Resmi · Produk Digital &amp; AI</span>
          </div>

          <h1>
            Google AI Pro &amp;<br>
            <span class="rotator-badge">
              <span class="rotator-text" id="hero-rotator">Akses Lengkap Dunia AI.</span>
              <span class="rotator-cursor" aria-hidden="true">|</span>
            </span>
          </h1>

          <p class="hero-lead">
            Buka potensi teknologi AI mutakhir dan produk digital terkurasi dalam satu katalog terpercaya. Produktivitas, kreativitas, dan efisiensi kerja kini jauh lebih cepat, mudah, dan aman.
          </p>

          <!-- 4 Clean Feature Matrix (Vector SVGs, No Emojis) -->
          <div class="feature-matrix">
            <div class="feature-pill">
              <div class="feature-icon-box blue">
                <!-- Lightning Bolt SVG -->
                <svg viewBox="0 0 24 24">
                  <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
              </div>
              <span>Produktivitas Maksimal</span>
            </div>

            <div class="feature-pill">
              <div class="feature-icon-box green">
                <!-- Shield Check SVG -->
                <svg viewBox="0 0 24 24">
                  <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm-2 14.5l-3.5-3.5 1.41-1.41L10 13.67l6.59-6.59 1.41 1.41L10 16.5z"/>
                </svg>
              </div>
              <span>Aman &amp; Terpercaya</span>
            </div>

            <div class="feature-pill">
              <div class="feature-icon-box purple">
                <!-- Devices Monitor SVG -->
                <svg viewBox="0 0 24 24">
                  <path d="M4 6h16v10H4zM2 18h20v2H2z"/>
                </svg>
              </div>
              <span>Multi Perangkat</span>
            </div>

            <div class="feature-pill">
              <div class="feature-icon-box amber">
                <!-- Globe Check SVG -->
                <svg viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
              </div>
              <span>100% Akses Resmi</span>
            </div>
          </div>

          <div class="hero-cta-group">
            <a class="btn-hero-wa" href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener">
              <!-- WhatsApp SVG -->
              <svg viewBox="0 0 24 24">
                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/>
              </svg>
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
                  <svg viewBox="0 0 24 24">
                    <path d="M12 2L14.39 9.61L22 12L14.39 14.39L12 22L9.61 14.39L2 12L9.61 9.61L12 2Z"/>
                  </svg>
                </div>
                <div class="showcase-titles">
                  <h3>Google AI Pro</h3>
                  <p>Akses Lengkap ke Dunia AI Google</p>
                </div>
              </div>
              <span class="showcase-tag-new">BARU</span>
            </div>

            <!-- 2 Mini Highlight Cards -->
            <div class="showcase-grid-mini">
              <div class="mini-box">
                <div class="mini-box-head">
                  <!-- Cloud SVG -->
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/>
                  </svg>
                  <h4>Penyimpanan 5TB</h4>
                </div>
                <p>Simpan file, foto, dan proyek Anda dengan aman di Cloud Drive.</p>
              </div>

              <div class="mini-box">
                <div class="mini-box-head">
                  <!-- Sparkle SVG -->
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L14.39 9.61L22 12L14.39 14.39L12 22L9.61 14.39L2 12L9.61 9.61L12 2Z"/>
                  </svg>
                  <h4>Model AI Unggulan</h4>
                </div>
                <p>Gemini Pro, Claude, ChatGPT, dan model AI mutakhir tanpa batas.</p>
              </div>
            </div>

            <!-- Official Logos Showcase -->
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

            <!-- Deal Banner (Direct Inquiry) -->
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

    <!-- ─── Continuous Marquee Ticker ─── -->
    <div class="marquee-band" aria-label="Ticker Informasi Langsung">
      <div class="marquee-track">
        <span class="marquee-item"><span class="marquee-dot green"></span> Gemini Pro 18 Bulan [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> CapCut Pro 7D &amp; 1 Bulan [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot"></span> Microsoft Office 365 Plus 1 Tahun [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> Canva Pro 2 Tahun Garansi Penuh</span>
        <span class="marquee-item"><span class="marquee-dot"></span> Respon Tim WhatsApp &lt; 15 Menit</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> 100% Legal &amp; Terverifikasi</span>
        <!-- Duplicate for Infinite Loop -->
        <span class="marquee-item"><span class="marquee-dot green"></span> Gemini Pro 18 Bulan [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> CapCut Pro 7D &amp; 1 Bulan [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot"></span> Microsoft Office 365 Plus 1 Tahun [Tersedia]</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> Canva Pro 2 Tahun Garansi Penuh</span>
        <span class="marquee-item"><span class="marquee-dot"></span> Respon Tim WhatsApp &lt; 15 Menit</span>
        <span class="marquee-item"><span class="marquee-dot green"></span> 100% Legal &amp; Terverifikasi</span>
      </div>
    </div>

    <!-- ─── Trust Bar ─── -->
    <section class="trustbar-section">
      <div class="shell">
        <div class="trust-grid">
          <div class="trust-card">
            <div class="trust-icon-wrap blue">
              <!-- Bolt SVG -->
              <svg viewBox="0 0 24 24">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
              </svg>
            </div>
            <div class="trust-copy">
              <h4>Proses Cepat &amp; Aman</h4>
              <p>Konfirmasi dan aktivasi langsung oleh tim</p>
            </div>
          </div>

          <div class="trust-card">
            <div class="trust-icon-wrap green">
              <!-- Credit Card SVG -->
              <svg viewBox="0 0 24 24">
                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
              </svg>
            </div>
            <div class="trust-copy">
              <h4>Pembayaran Mudah</h4>
              <p>Mendukung QRIS, e-Wallet, &amp; Transfer</p>
            </div>
          </div>

          <div class="trust-card">
            <div class="trust-icon-wrap purple">
              <!-- Headset Support SVG -->
              <svg viewBox="0 0 24 24">
                <path d="M12 1a9 9 0 0 0-9 9v7c0 1.66 1.34 3 3 3h3v-8H5v-2a7 7 0 0 1 14 0v2h-4v8h3c1.66 0 3-1.34 3-3v-7a9 9 0 0 0-9-9z"/>
              </svg>
            </div>
            <div class="trust-copy">
              <h4>Bantuan Jika Kendala</h4>
              <p>Pendampingan responsif sepanjang masa aktif</p>
            </div>
          </div>

          <div class="trust-card">
            <div class="trust-icon-wrap amber">
              <!-- Verified Star SVG -->
              <svg viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
              </svg>
            </div>
            <div class="trust-copy">
              <h4>100% Original &amp; Resmi</h4>
              <p>Akses legal dan terjamin kualitasnya</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Catalog Section ─── -->
    <section class="catalog-section" id="catalog">
      <div class="shell">
      <div class="section-head">
        <div>
          <p class="section-eyebrow">KATALOG DIGITAL</p>
          <h2 class="section-title">Pilih Produk &amp; <span class="text-gemini">Akses Pilihanmu.</span></h2>
        </div>
        <p class="section-desc">
          <?= count($products) ?> SKU siap pakai. Filter kategori untuk menemukan produk yang kamu butuhkan atau klik langsung untuk order cepat via WhatsApp.
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
          <a class="product-card <?= $isLive ? '' : 'sold-out' ?>" 
             data-category="<?= e($item['category']) ?>" 
             data-name="<?= e(strtolower($item['name'] . ' ' . $item['sku'])) ?>"
             data-index="<?= $index ?>"
             href="<?= e($waLink) ?>"
             target="_blank"
             rel="noopener">
            
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
                  <span class="inquiry-val">Tanya via WA</span>
                </div>
                <span class="btn-card-order">
                  <span><?= $isLive ? 'Chat WA' : 'Info Stok' ?></span>
                  <span>↗</span>
                </span>
              </div>
            </div>
          </a>
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

    <!-- ─── Process Band ─── -->
    <section class="process-section" id="process">
      <div class="shell">
        <div class="process-band-inner">
          <div class="section-head process-heading">
            <div>
              <p class="section-eyebrow">ALUR PEMESANAN</p>
              <h2 class="section-title">Proses Mudah, Transparan &amp; Cepat</h2>
            </div>
            <p class="section-desc">Pilih produk yang kamu butuhkan, klik untuk chat WhatsApp, dan tim ZiuLabs akan langsung memproses pesananmu.</p>
          </div>

          <div class="process-grid">
            <div class="process-step-card">
              <span class="step-chip">LANGKAH 01</span>
              <h3>Pilih Produk</h3>
              <p>Gunakan filter kategori atau pencarian untuk memilih SKU yang sesuai dan pastikan status berlabel "Tersedia".</p>
            </div>

            <div class="process-step-card">
              <span class="step-chip">LANGKAH 02</span>
              <h3>Hubungi via WhatsApp</h3>
              <p>Klik tombol pada kartu produk. Format pesan otomatis sudah terisi dengan nama dan kode SKU yang kamu pilih.</p>
            </div>

            <div class="process-step-card">
              <span class="step-chip">LANGKAH 03</span>
              <h3>Aktivasi &amp; Panduan</h3>
              <p>Lakukan pembayaran instan via QRIS/Transfer, lalu terima kredensial akun beserta panduan lengkap dalam hitungan menit.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── FAQ Section ─── -->
    <section class="faq-section" id="faq">
      <div class="shell faq-layout">
        <div>
          <p class="section-eyebrow">PERTANYAAN UMUM</p>
          <h2 class="section-title" style="margin-top: 8px;">Hal yang Sering Ditanyakan</h2>
          <p class="section-desc" style="margin-top: 14px;">
            Kebijakan transparan seputar durasi, garansi, dan model layanan digital ZiuLabs.
          </p>
        </div>

        <div class="faq-list">
          <details class="faq-box" open>
            <summary>Bagaimana cara membeli produk di ZiuLabs?</summary>
            <p>Pilih produk yang diinginkan dari katalog, lalu klik tombol "Order WA". Format pesan WhatsApp otomatis akan terbuka dengan nama dan SKU produk. Tim kami segera mengonfirmasi ketersediaan dan detail pembayarannya.</p>
          </details>
          <details class="faq-box">
            <summary>Metode pembayaran apa saja yang didukung?</summary>
            <p>Kami menerima pembayaran melalui QRIS (GoPay, OVO, Dana, ShopeePay), transfer bank BCA, Mandiri, BRI, dan e-Wallet untuk kemudahan transaksi Anda.</p>
          </details>
          <details class="faq-box">
            <summary>Berapa lama proses aktivasi akun?</summary>
            <p>Sebagian besar produk digital dapat diaktivasi dalam waktu 5 hingga 15 menit setelah bukti pembayaran kami verifikasi pada jam operasional.</p>
          </details>
          <details class="faq-box">
            <summary>Apakah produk bergaransi?</summary>
            <p>Semua produk yang kami sediakan memiliki garansi resmi sesuai masa durasi yang tertera. Jika ada kendala teknis, tim kami siap mendampingi melalui WhatsApp.</p>
          </details>
        </div>
      </div>
    </section>

  </main>

  <!-- ─── Footer ─── -->
  <footer class="site-footer">
    <div class="shell footer-inner">
      <div class="footer-brand">
        <img src="/assets/logos/ziulabs-icon.webp" alt="Z" class="footer-logo-img" width="28" height="28" loading="lazy" />
        <span style="font-weight: 700; color: var(--ink-primary);">ZiuLabs Store</span>
        <span>— Solusi Lengkap Produk Digital &amp; AI</span>
      </div>
      <div>
        <span>&copy; <?= date('Y') ?> ZiuLabs. All rights reserved.</span>
      </div>
    </div>
  </footer>

  <!-- ─── ⌘K Modal Dialog ─── -->
  <div class="palette-modal-overlay" id="palette-modal" role="dialog" aria-modal="true" aria-label="Pencarian Cepat SKU">
    <div class="palette-box">
      <div class="palette-search-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input class="palette-input-field" id="palette-search" type="text" placeholder="Ketik nama produk atau kode SKU..." />
        <kbd style="font-family: 'JetBrains Mono', monospace; font-size: 11px; padding: 2px 6px; background: var(--bg-subtle); border-radius: 4px; color: var(--ink-muted);">ESC</kbd>
      </div>
      <div class="palette-list" id="palette-results"></div>
      <div class="palette-foot">
        <span>Tekan ENTER untuk order via WhatsApp</span>
        <span><?= count($products) ?> SKU terdaftar</span>
      </div>
    </div>
  </div>

  <!-- ─── Interactive Scripts (Hardware Accelerated, Zero Lag) ─── -->
  <script>
  (function() {
    var products = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var waNumber = '<?= preg_replace('/[^0-9]/', '', $app['whatsapp_number']) ?>';
    var pageSize = 6;
    var currentPage = 1;
    var currentFilter = 'all';
    var searchQuery = '';

    var cells = Array.from(document.querySelectorAll('.product-card'));
    var pageCounter = document.querySelector('.page-counter');
    var btnPrev = document.querySelector('.page-btn-prev');
    var btnNext = document.querySelector('.page-btn-next');
    var searchInput = document.getElementById('catalog-search');

    function updateCatalog() {
      var visible = cells.filter(function(card) {
        var matchesCat = (currentFilter === 'all' || card.dataset.category === currentFilter);
        var matchesQuery = (!searchQuery || card.dataset.name.indexOf(searchQuery) !== -1);
        return matchesCat && matchesQuery;
      });

      var totalPages = Math.max(1, Math.ceil(visible.length / pageSize));
      currentPage = Math.min(currentPage, totalPages);

      cells.forEach(function(c) { c.hidden = true; });
      visible.slice((currentPage - 1) * pageSize, currentPage * pageSize).forEach(function(c) {
        c.hidden = false;
      });

      if (pageCounter) {
        pageCounter.textContent = 'Halaman ' + currentPage + ' dari ' + totalPages + ' (' + visible.length + ' SKU)';
      }
      if (btnPrev) btnPrev.disabled = (currentPage === 1);
      if (btnNext) btnNext.disabled = (currentPage === totalPages);
    }

    // Tab buttons
    document.querySelectorAll('.filter-tab').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('is-active'); });
        btn.classList.add('is-active');
        currentFilter = btn.dataset.filter;
        currentPage = 1;
        updateCatalog();
      });
    });

    // Realtime search input
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        searchQuery = this.value.trim().toLowerCase();
        currentPage = 1;
        updateCatalog();
      });
    }

    // Pagination
    if (btnPrev) {
      btnPrev.addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; updateCatalog(); }
      });
    }
    if (btnNext) {
      btnNext.addEventListener('click', function() {
        var visible = cells.filter(function(card) {
          var matchesCat = (currentFilter === 'all' || card.dataset.category === currentFilter);
          var matchesQuery = (!searchQuery || card.dataset.name.indexOf(searchQuery) !== -1);
          return matchesCat && matchesQuery;
        });
        if (currentPage < Math.ceil(visible.length / pageSize)) { currentPage++; updateCatalog(); }
      });
    }

    // ─── 3D Showcase Card Tilt (requestAnimationFrame throttled) ───
    var board = document.getElementById('interactive-board');
    if (board && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      var isTicking = false;
      board.addEventListener('mousemove', function(e) {
        if (!isTicking) {
          window.requestAnimationFrame(function() {
            var rect = board.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            board.style.transform = 'perspective(1000px) rotateX(' + (-y * 8).toFixed(2) + 'deg) rotateY(' + (x * 8).toFixed(2) + 'deg) translateY(-4px)';
            isTicking = false;
          });
          isTicking = true;
        }
      });
      board.addEventListener('mouseleave', function() {
        board.style.transform = '';
      });
    }

    // ─── ⌘K Modal Logic ───
    var modal = document.getElementById('palette-modal');
    var cmdkBtn = document.getElementById('cmdk-btn');
    var pSearch = document.getElementById('palette-search');
    var pResults = document.getElementById('palette-results');

    function openCmdk() {
      if (!modal) return;
      modal.classList.add('is-visible');
      renderCmdk('');
      if (pSearch) { pSearch.value = ''; pSearch.focus(); }
    }
    function closeCmdk() {
      if (!modal) return;
      modal.classList.remove('is-visible');
    }

    function renderCmdk(query) {
      if (!pResults) return;
      var q = query.toLowerCase();
      var matches = products.filter(function(p) {
        return !q || p.name.toLowerCase().indexOf(q) !== -1 || p.sku.toLowerCase().indexOf(q) !== -1;
      });

      pResults.innerHTML = '';
      if (matches.length === 0) {
        pResults.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--ink-muted); font-size: 13px;">Tidak ada produk yang cocok</div>';
        return;
      }

      matches.slice(0, 6).forEach(function(p, i) {
        var a = document.createElement('a');
        a.className = 'palette-item' + (i === 0 ? ' is-selected' : '');
        var msg = p.available 
          ? 'Halo ZiuLabs, saya ingin menanyakan harga dan pemesanan paket [' + p.sku + '] ' + p.name + '.'
          : 'Halo ZiuLabs, saya ingin info stok [' + p.sku + '] ' + p.name + '.';
        a.href = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
        a.target = '_blank';
        a.rel = 'noopener';
        a.innerHTML = '<div><strong>[' + p.sku + ']</strong> ' + p.name + '</div><div style="font-family: \'JetBrains Mono\', monospace; font-size: 11px; font-weight: 700; color: var(--blue-ziulabs); white-space: nowrap;">Tanya WA ↗</div>';
        pResults.appendChild(a);
      });
    }

    if (cmdkBtn) cmdkBtn.addEventListener('click', openCmdk);
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) closeCmdk();
      });
    }
    if (pSearch) {
      pSearch.addEventListener('input', function() {
        renderCmdk(this.value.trim());
      });
    }
    window.addEventListener('keydown', function(e) {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        if (modal && modal.classList.contains('is-visible')) closeCmdk();
        else openCmdk();
      }
      if (e.key === 'Escape' && modal && modal.classList.contains('is-visible')) {
        closeCmdk();
      }
    });

    // ─── Web3 Headline Text Rotator (Smooth Typewriter) ───
    (function initRotator() {
      var el = document.getElementById('hero-rotator');
      if (!el) return;
      var phrases = [
        "Akses Lengkap Dunia AI.",
        "Tools Digital Terverifikasi.",
        "Solusi Kerja Produktif.",
        "Aktivasi Instan & Bergaransi."
      ];
      var pIdx = 0;
      var cIdx = phrases[0].length;
      var isDeleting = false;
      var speed = 65;

      function step() {
        var curr = phrases[pIdx];
        if (isDeleting) {
          cIdx--;
          el.textContent = curr.substring(0, cIdx);
          speed = 30;
        } else {
          cIdx++;
          el.textContent = curr.substring(0, cIdx);
          speed = 65;
        }

        if (!isDeleting && cIdx === curr.length) {
          isDeleting = true;
          speed = 2200;
        } else if (isDeleting && cIdx === 0) {
          isDeleting = false;
          pIdx = (pIdx + 1) % phrases.length;
          speed = 400;
        }
        setTimeout(step, speed);
      }
      setTimeout(step, 2000);
    })();

    // ─── Web3 Mouse-Tracking Interactive Grid Spotlight (Only Desktop with Mouse) ───
    var spotlight = document.getElementById('grid-spotlight');
    if (spotlight && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
      var ticking = false;
      window.addEventListener('mousemove', function(e) {
        if (!ticking) {
          window.requestAnimationFrame(function() {
            spotlight.style.background = 'radial-gradient(550px circle at ' + e.clientX + 'px ' + e.clientY + 'px, rgba(0, 102, 255, 0.16) 0%, transparent 80%)';
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true });
    }

    updateCatalog();
  })();
  </script>

</body>
</html>
