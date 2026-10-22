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

$pageTitle = 'Cara Kerja & Alur Pemesanan — ' . $app['name'];
$pageDesc = 'Pelajari panduan langkah demi langkah cara memesan produk digital, aktivasi instan QRIS otomatis, dan garansi resmi ZiuLabs.';
$bodyClass = 'page-process';

require __DIR__ . '/../../views/layouts/header.php';
require __DIR__ . '/../../views/components/navbar.php';
?>
<main id="process-page" class="catalog-standalone-page">
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
      <span class="breadcrumb-current">Cara Kerja</span>
    </nav>

    <!-- Header Section -->
    <div class="store-catalog-header">
      <div class="store-header-left">
        <div class="store-pill-badge">
          <span class="radar-dot"></span>
          <span>ALUR PEMESANAN &amp; AKTIVASI</span>
        </div>
        <h1 class="store-catalog-title">
          Cara Kerja &amp; <span class="text-gemini">Alur Pemesanan.</span>
        </h1>
        <p class="store-catalog-subtitle">
          Proses pemesanan di ZiuLabs dirancang cepat, transparan, dan otomatis. Mulai dari pemilihan paket, pembayaran instan QRIS, hingga pengiriman lisensi resmi ke WhatsApp Anda.
        </p>
      </div>

      <div class="store-header-stats">
        <div class="store-stat-box">
          <div class="stat-dot pulse-green"></div>
          <div class="stat-info">
            <span class="stat-num">1-3 Menit</span>
            <span class="stat-lbl">Estimasi Pengiriman Akun</span>
          </div>
        </div>
        <div class="store-stat-box">
          <div class="stat-dot" style="width: 10px; height: 10px; border-radius: 50%; background: #0066ff;"></div>
          <div class="stat-info">
            <span class="stat-num">30 Hari</span>
            <span class="stat-lbl">Garansi Penuh ZiuLabs</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 4 Detailed Interactive Step Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;">
      
      <div style="background: #ffffff; border: 1px solid var(--border-rule); border-radius: 18px; padding: 26px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #0066ff;"></div>
        <span style="display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: #0066ff; background: rgba(0, 102, 255, 0.08); padding: 4px 10px; border-radius: 999px; margin-bottom: 14px;">LANGKAH 01</span>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Pilih Produk &amp; Lisensi</h3>
        <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 16px 0;">
          Jelajahi etalase katalog digital kami. Cari software AI atau paket produktivitas yang berlabel <strong>Tersedia</strong> (seperti Gemini Pro 18 Months).
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #475569; line-height: 1.6;">
          <li>Filter berdasarkan kategori kebutuhan Anda</li>
          <li>Cek durasi langganan dan rincian paket</li>
          <li>Bisa beli satuan atau tambah ke keranjang</li>
        </ul>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--border-rule); border-radius: 18px; padding: 26px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #3b82f6;"></div>
        <span style="display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: #3b82f6; background: rgba(59, 130, 246, 0.08); padding: 4px 10px; border-radius: 999px; margin-bottom: 14px;">LANGKAH 02</span>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Isi Data &amp; Checkout</h3>
        <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 16px 0;">
          Klik tombol <strong>⚡ Beli</strong> atau lanjutkan dari Keranjang Belanja. Masukkan identitas Anda untuk tujuan pengiriman kredensial lisensi.
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #475569; line-height: 1.6;">
          <li>Nama lengkap pembeli</li>
          <li>Nomor WhatsApp aktif (wajib untuk notifikasi)</li>
          <li>Email aktif untuk cadangan receipt</li>
        </ul>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--border-rule); border-radius: 18px; padding: 26px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #10b981;"></div>
        <span style="display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: #10b981; background: rgba(16, 185, 129, 0.08); padding: 4px 10px; border-radius: 999px; margin-bottom: 14px;">LANGKAH 03</span>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Scan QRIS Realtime</h3>
        <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 16px 0;">
          Sistem membuat invoice unik dengan QRIS Dinamis ZiuLabs. Pindai barcode menggunakan aplikasi e-Wallet atau Mobile Banking apa pun.
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #475569; line-height: 1.6;">
          <li>Mendukung BCA, Mandiri, BRI, BNI, Seabank</li>
          <li>Mendukung GoPay, OVO, DANA, ShopeePay, LinkAja</li>
          <li>Verifikasi mutasi pembayaran otomatis</li>
        </ul>
      </div>

      <div style="background: #ffffff; border: 1px solid var(--border-rule); border-radius: 18px; padding: 26px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #8b5cf6;"></div>
        <span style="display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: #8b5cf6; background: rgba(139, 92, 246, 0.08); padding: 4px 10px; border-radius: 999px; margin-bottom: 14px;">LANGKAH 04</span>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">Aktivasi Instan &amp; Garansi</h3>
        <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 16px 0;">
          Begitu pembayaran terverifikasi, detail login/panduan aktivasi langsung tampil di layar dan terkirim otomatis ke WhatsApp Anda.
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #475569; line-height: 1.6;">
          <li>Kredensial tersimpan aman di Customer Portal</li>
          <li>Panduan aktivasi langkah demi langkah</li>
          <li>Didukung garansi penggantian penuh 30 hari</li>
        </ul>
      </div>

    </div>

    <!-- Assurance & CTA Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 20px; padding: 40px; box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: space-between; gap: 30px; flex-wrap: wrap;">
      <div style="max-width: 600px;">
        <span style="font-size: 12px; font-weight: 800; letter-spacing: 0.08em; color: #38bdf8; text-transform: uppercase;">SIAP MEMULAI?</span>
        <h2 style="font-size: clamp(22px, 3vw, 32px); font-weight: 800; color: #ffffff; margin: 8px 0 12px 0;">Dapatkan Akses Digital Resmi Sekarang</h2>
        <p style="font-size: 0.95rem; color: #94a3b8; line-height: 1.6; margin: 0;">
          Nikmati kemudahan transaksi instan dan jaminan layanan terbaik dari ZiuLabs. Jika butuh konsultasi atau produk khusus, CS kami siap membantu 24/7.
        </p>
      </div>
      <div style="display: flex; gap: 14px; flex-wrap: wrap;">
        <a href="/catalog/" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 700; color: #0f172a; background: #ffffff; padding: 12px 24px; border-radius: 999px; text-decoration: none; transition: transform 0.2s;">
          <span>Lihat Katalog Produk</span>
          <span>&rarr;</span>
        </a>
        <a href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 700; color: #ffffff; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px 24px; border-radius: 999px; text-decoration: none; transition: background 0.2s;">
          <span>Chat CS WhatsApp</span>
          <span>↗</span>
        </a>
      </div>
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
