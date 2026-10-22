<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/helpers.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\Csrf;

Csrf::start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php', true, 303);
    exit;
}

$userName = $_SESSION['user_name'] ?? 'Pelanggan ZiuLabs';
$userEmail = $_SESSION['user_email'] ?? '';
$userAvatar = $_SESSION['user_avatar'] ?? '';

try {
    $db = Database::connect();
    
    // Fetch orders by user email (and include demo buyer aliases if in demo session)
    if ($userEmail === 'demo.buyer@ziulabs.id') {
        $stmt = $db->prepare(
            'SELECT o.*, 
                    oi.sku_name_snapshot, oi.unit_price_minor,
                    f.payload_encrypted, f.status AS fulfillment_status
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN fulfillments f ON f.order_id = o.id
             WHERE o.customer_email IN (:email, \'budi@test.com\', \'buyer@ziulabs.id\')
             ORDER BY o.created_at DESC'
        );
        $stmt->execute(['email' => $userEmail]);
    } else {
        $stmt = $db->prepare(
            'SELECT o.*, 
                    oi.sku_name_snapshot, oi.unit_price_minor,
                    f.payload_encrypted, f.status AS fulfillment_status
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN fulfillments f ON f.order_id = o.id
             WHERE o.customer_email = :email
             ORDER BY o.created_at DESC'
        );
        $stmt->execute(['email' => $userEmail]);
    }
    $orders = $stmt->fetchAll();

    $totalPaid = count(array_filter($orders, fn($o) => in_array($o['status'], ['paid', 'fulfilled'], true)));
    $totalPending = count(array_filter($orders, fn($o) => !in_array($o['status'], ['paid', 'fulfilled', 'cancelled'], true)));
    $totalSpentMinor = array_reduce(
        array_filter($orders, fn($o) => in_array($o['status'], ['paid', 'fulfilled'], true)),
        fn($acc, $o) => $acc + (int) $o['total_minor'],
        0
    );

} catch (Throwable $e) {
    http_response_code(500);
    exit('Terjadi kesalahan memuat dashboard: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Customer Portal — ZiuLabs Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* ─── shadcn/ui Design Tokens & Reset ─── */
    :root {
      --background: #ffffff;
      --foreground: #09090b;
      --card: #ffffff;
      --card-foreground: #09090b;
      --popover: #ffffff;
      --popover-foreground: #09090b;
      --primary: #0066ff;
      --primary-foreground: #ffffff;
      --secondary: #f4f4f5;
      --secondary-foreground: #18181b;
      --muted: #f4f4f5;
      --muted-foreground: #71717a;
      --accent: #f4f4f5;
      --accent-foreground: #18181b;
      --destructive: #ef4444;
      --destructive-foreground: #fafafa;
      --border: #e4e4e7;
      --input: #e4e4e7;
      --ring: #0066ff;
      --radius: 0.75rem;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: #fafafa;
      color: var(--foreground);
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ─── Minimalist shadcn Navbar ─── */
    .portal-nav {
      background: #ffffff;
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(8px);
      background: rgba(255, 255, 255, 0.95);
    }
    .portal-nav-inner {
      max-width: 1040px;
      margin: 0 auto;
      padding: 0 24px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .portal-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .portal-badge {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 9999px;
      background: #eff6ff;
      color: #0066ff;
      border: 1px solid #bfdbfe;
    }
    .portal-nav-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .btn-nav-outline {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      background: #ffffff;
      font-size: 13px;
      font-weight: 600;
      color: var(--foreground);
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .btn-nav-outline:hover {
      background: var(--muted);
      border-color: #d4d4d8;
    }
    .btn-nav-logout {
      display: inline-flex;
      align-items: center;
      padding: 7px 12px;
      border-radius: var(--radius);
      font-size: 13px;
      font-weight: 600;
      color: var(--destructive);
      text-decoration: none;
      transition: background 0.15s ease;
    }
    .btn-nav-logout:hover {
      background: #fef2f2;
    }

    /* ─── Main Content Container ─── */
    .portal-main {
      max-width: 1040px;
      margin: 32px auto 80px auto;
      padding: 0 24px;
    }

    /* ─── User Profile Card ─── */
    .user-profile-header {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .user-profile-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .user-avatar-circle {
      width: 56px;
      height: 56px;
      border-radius: 9999px;
      background: #0066ff;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      font-weight: 800;
      border: 2px solid #ffffff;
      box-shadow: 0 0 0 2px #bfdbfe;
      object-fit: cover;
    }
    .user-headline h1 {
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: var(--foreground);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .user-headline p {
      font-size: 13px;
      color: var(--muted-foreground);
      margin-top: 2px;
    }
    .badge-verified {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 9999px;
    }

    /* ─── shadcn Metric Stats Grid ─── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 32px;
    }
    .stat-card {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .stat-card:hover {
      border-color: #cbd5e1;
      transform: translateY(-1px);
    }
    .stat-card-title {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted-foreground);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    .stat-card-number {
      font-size: 1.75rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: var(--foreground);
    }
    .stat-card-sub {
      font-size: 12px;
      color: var(--muted-foreground);
      margin-top: 4px;
    }

    /* ─── shadcn Tabs & Controls ─── */
    .portal-controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 20px;
    }
    .shadcn-tabs {
      display: inline-flex;
      align-items: center;
      background: var(--muted);
      padding: 4px;
      border-radius: var(--radius);
      border: 1px solid var(--border);
    }
    .shadcn-tab-btn {
      background: transparent;
      border: none;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 600;
      color: var(--muted-foreground);
      border-radius: calc(var(--radius) - 3px);
      cursor: pointer;
      transition: all 0.15s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .shadcn-tab-btn.is-active {
      background: #ffffff;
      color: var(--foreground);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }
    .shadcn-tab-count {
      font-size: 11px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 9999px;
      background: var(--secondary);
      color: var(--foreground);
    }
    .shadcn-tab-btn.is-active .shadcn-tab-count {
      background: #eff6ff;
      color: #0066ff;
    }

    .shadcn-search {
      position: relative;
      min-width: 280px;
    }
    .shadcn-search-input {
      width: 100%;
      height: 38px;
      padding: 0 14px 0 36px;
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 13px;
      font-family: inherit;
      color: var(--foreground);
      outline: none;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .shadcn-search-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
    }
    .shadcn-search-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted-foreground);
      pointer-events: none;
    }

    /* ─── shadcn Order Card ─── */
    .order-card-wrap {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .order-card {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      transition: border-color 0.15s ease;
    }
    .order-card:hover {
      border-color: #cbd5e1;
    }
    .order-card-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 16px;
    }
    .order-meta-id {
      font-family: 'JetBrains Mono', monospace;
      font-size: 13px;
      font-weight: 700;
      color: var(--foreground);
      background: var(--muted);
      padding: 3px 8px;
      border-radius: 6px;
      border: 1px solid var(--border);
    }
    .order-meta-date {
      font-size: 12px;
      color: var(--muted-foreground);
      margin-left: 8px;
    }

    /* Status Pills */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 9999px;
    }
    .status-pill.status-fulfilled, .status-pill.status-paid {
      background: #ecfdf5;
      color: #059669;
      border: 1px solid #a7f3d0;
    }
    .status-pill.status-awaiting, .status-pill.status-inquiry {
      background: #fffbeb;
      color: #d97706;
      border: 1px solid #fde68a;
    }

    .order-card-middle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .order-product-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--foreground);
      margin-bottom: 4px;
    }
    .order-product-price {
      font-size: 14px;
      font-weight: 700;
      color: #0066ff;
      font-family: 'JetBrains Mono', monospace;
    }
    .btn-action-primary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: #0066ff;
      color: #ffffff;
      border-radius: var(--radius);
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
      transition: background 0.15s ease, transform 0.15s ease;
    }
    .btn-action-primary:hover {
      background: #0052cc;
      transform: translateY(-1px);
    }

    /* ─── Credential Box Overhaul (shadcn Vault) ─── */
    .credential-pool-wrap {
      margin-top: 18px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
    }
    .cred-card {
      background: #fbfcfe;
      border: 1px solid #bbf7d0;
      border-radius: var(--radius);
      padding: 16px 20px;
    }
    .cred-card.cred-pending {
      border-color: #fde68a;
      background: #fffdf5;
    }
    .cred-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 700;
      font-size: 13px;
      color: #065f46;
      padding-bottom: 10px;
      border-bottom: 1px dashed #d1fae5;
      margin-bottom: 12px;
    }
    .cred-badge-active {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 9999px;
      background: #d1fae5;
      color: #047857;
    }
    .cred-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 8px;
    }
    .cred-label {
      width: 100px;
      font-size: 11px;
      font-weight: 700;
      color: #065f46;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      flex-shrink: 0;
    }
    .cred-val-wrap {
      flex: 1;
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 6px 12px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 13px;
      color: #09090b;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .btn-cred-copy, .btn-cred-toggle {
      background: #18181b;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.15s ease;
    }
    .btn-cred-copy:hover {
      background: #0066ff;
    }
    .btn-cred-toggle {
      background: #f4f4f5;
      color: #27272a;
      border: 1px solid var(--border);
    }
    .btn-cred-toggle:hover {
      background: #e4e4e7;
    }
    .cred-notes {
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px solid #f1f5f9;
      color: #065f46;
      font-size: 12px;
    }

    /* ─── Empty State ─── */
    .empty-card {
      background: #ffffff;
      border: 1px dashed var(--border);
      border-radius: var(--radius);
      padding: 64px 24px;
      text-align: center;
    }
    .empty-icon {
      width: 48px;
      height: 48px;
      color: var(--muted-foreground);
      margin: 0 auto 16px auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr 1fr;
      }
      .portal-controls {
        flex-direction: column;
        align-items: stretch;
      }
      .shadcn-search {
        min-width: 100%;
      }
      .cred-row {
        flex-wrap: wrap;
      }
      .cred-label {
        width: 100%;
      }
    }
    @media (max-width: 500px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Minimalist Topbar -->
  <header class="portal-nav">
    <div class="portal-nav-inner">
      <a href="/" class="portal-brand">
        <img src="/assets/logos/ziulabs-icon.webp" alt="ZiuLabs" width="30" height="30">
        <span style="font-weight: 800; font-size: 1.15rem; color: #0066ff; letter-spacing: -0.02em;">ZiuLabs</span>
        <span class="portal-badge">Customer Portal</span>
      </a>

      <div class="portal-nav-actions">
        <a href="/" class="btn-nav-outline">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          <span>Katalog Toko</span>
        </a>
        <a href="https://wa.me/62895609250509" target="_blank" rel="noopener" class="btn-nav-outline" style="color: #059669;">
          <span>Bantuan CS</span>
        </a>
        <a href="/auth/logout.php" class="btn-nav-logout" title="Keluar dari akun">
          Keluar
        </a>
      </div>
    </div>
  </header>

  <main class="portal-main">
    <!-- User Profile Header -->
    <section class="user-profile-header">
      <div class="user-profile-left">
        <?php if (!empty($userAvatar)): ?>
          <img src="<?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>" class="user-avatar-circle">
        <?php else: ?>
          <div class="user-avatar-circle"><?= strtoupper(substr($userName, 0, 1)) ?></div>
        <?php endif; ?>
        <div class="user-headline">
          <h1>
            <span><?= htmlspecialchars($userName) ?></span>
            <span class="badge-verified">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Terverifikasi
            </span>
          </h1>
          <p><?= htmlspecialchars($userEmail ?: 'Pelanggan Terdaftar') ?> &bull; Akses Akun &amp; Kredensial Lisensi Aktif</p>
        </div>
      </div>

      <div>
        <a href="/#catalog" class="btn-action-primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
          <span>Beli Produk Baru</span>
        </a>
      </div>
    </section>

    <!-- shadcn Metric Stats Grid -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-title">
          <span>Total Pesanan</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div class="stat-card-number"><?= count($orders) ?></div>
        <div class="stat-card-sub">Semua riwayat checkout</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-title">
          <span>Produk Aktif</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #059669;"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="m7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="stat-card-number" style="color: #059669;"><?= $totalPaid ?></div>
        <div class="stat-card-sub">Kredensial &amp; lisensi siap pakai</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-title">
          <span>Garansi Toko</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #0066ff;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
        </div>
        <div class="stat-card-number" style="font-size: 1.4rem;">30 Hari</div>
        <div class="stat-card-sub">Penggantian akun &amp; full support</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-title">
          <span>Total Nilai</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
        </div>
        <div class="stat-card-number" style="font-size: 1.25rem;">
          Rp<?= number_format((int) ($totalSpentMinor / 100), 0, ',', '.') ?>
        </div>
        <div class="stat-card-sub">Akumulasi transaksi berhasil</div>
      </div>
    </section>

    <!-- Controls: shadcn Tabs + Live Search -->
    <div class="portal-controls">
      <div class="shadcn-tabs">
        <button type="button" class="shadcn-tab-btn is-active" data-filter="all">
          <span>Semua Pesanan</span>
          <span class="shadcn-tab-count"><?= count($orders) ?></span>
        </button>
        <button type="button" class="shadcn-tab-btn" data-filter="paid">
          <span>Aktif &amp; Bergaransi</span>
          <span class="shadcn-tab-count"><?= $totalPaid ?></span>
        </button>
        <button type="button" class="shadcn-tab-btn" data-filter="pending">
          <span>Menunggu Bayar</span>
          <span class="shadcn-tab-count"><?= $totalPending ?></span>
        </button>
      </div>

      <div class="shadcn-search">
        <svg class="shadcn-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="order-search-input" class="shadcn-search-input" placeholder="Cari nama produk atau Order ID...">
      </div>
    </div>

    <!-- Order Cards List -->
    <div class="order-card-wrap" id="orders-list">
      <?php if (empty($orders)): ?>
        <div class="empty-card">
          <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/><path d="m10 15 2 2 4-4"/></svg>
          <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--foreground); margin-bottom: 6px;">Belum Ada Riwayat Pesanan</h3>
          <p style="font-size: 13px; color: var(--muted-foreground); max-width: 440px; margin: 0 auto 20px auto;">Pilih produk digital dari katalog resmi untuk langsung memulai transaksi dengan garansi 30 hari.</p>
          <a href="/#catalog" class="btn-action-primary">Lihat Katalog Produk Sekarang &rarr;</a>
        </div>
      <?php else: ?>
        <?php foreach ($orders as $ord): 
          $isOrderPaid = in_array($ord['status'], ['paid', 'fulfilled'], true);
          $priceFormatted = 'Rp' . number_format((int) ($ord['total_minor'] / 100), 0, ',', '.');
          $filterCategory = $isOrderPaid ? 'paid' : 'pending';
          $searchData = strtolower(($ord['public_id'] ?? '') . ' ' . ($ord['sku_name_snapshot'] ?? ''));
        ?>
          <div class="order-card js-order-card" data-category="<?= $filterCategory ?>" data-search="<?= htmlspecialchars($searchData) ?>">
            <div class="order-card-top">
              <div style="display: flex; align-items: center;">
                <span class="order-meta-id"><?= htmlspecialchars($ord['public_id']) ?></span>
                <span class="order-meta-date"><?= htmlspecialchars($ord['created_at']) ?></span>
              </div>
              <div>
                <span class="status-pill status-<?= htmlspecialchars($ord['status']) ?>">
                  <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                  <?= $isOrderPaid ? 'Lunas &amp; Aktif' : 'Menunggu Bayar' ?>
                </span>
              </div>
            </div>

            <div class="order-card-middle">
              <div>
                <h2 class="order-product-title">
                  <?= htmlspecialchars($ord['sku_name_snapshot'] ?? 'Produk Digital ZiuLabs') ?>
                </h2>
                <div class="order-product-price"><?= $priceFormatted ?> &bull; <span style="font-size: 12px; color: var(--muted-foreground); font-weight: normal;">Garansi 30 Hari</span></div>
              </div>

              <div>
                <?php if ($isOrderPaid): ?>
                  <a href="/order/payment.php?id=<?= urlencode($ord['public_id']) ?>" class="btn-nav-outline" style="border-color: #a7f3d0; color: #047857; background: #ecfdf5;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    <span>Detail Lisensi &rarr;</span>
                  </a>
                <?php else: ?>
                  <a href="/order/payment.php?id=<?= urlencode($ord['public_id']) ?>" class="btn-action-primary">
                    <span>Bayar Sekarang (QRIS) &rarr;</span>
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Credential Vault Section -->
            <?php if ($isOrderPaid && !empty($ord['payload_encrypted'])): ?>
              <?= renderFulfillmentCard($ord['payload_encrypted']) ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <!-- Interactive Search and Tab Filter JS -->
  <script>
  (function() {
    var tabBtns = document.querySelectorAll('.shadcn-tab-btn');
    var searchInput = document.getElementById('order-search-input');
    var orderCards = Array.from(document.querySelectorAll('.js-order-card'));
    var currentFilter = 'all';
    var currentQuery = '';

    function filterOrders() {
      var visibleCount = 0;
      orderCards.forEach(function(card) {
        var matchesCategory = (currentFilter === 'all' || card.dataset.category === currentFilter);
        var matchesSearch = (!currentQuery || card.dataset.search.indexOf(currentQuery) !== -1);
        if (matchesCategory && matchesSearch) {
          card.style.display = 'block';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
    }

    tabBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        tabBtns.forEach(function(b) { b.classList.remove('is-active'); });
        btn.classList.add('is-active');
        currentFilter = btn.dataset.filter;
        filterOrders();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', function() {
        currentQuery = this.value.trim().toLowerCase();
        filterOrders();
      });
    }
  })();
  </script>
</body>
</html>
