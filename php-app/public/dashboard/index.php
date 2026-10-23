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
  <link rel="icon" type="image/png" href="/assets/logos/ziulabs-icon.png">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/assets/logos/ziulabs-icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* ─── shadcn/ui Unified Design Tokens (Single Monochromatic/Neutral Palette) ─── */
    :root {
      --background: #f8fafc;
      --foreground: #09090b;
      --card: #ffffff;
      --card-foreground: #09090b;
      --primary: #0066ff;
      --primary-foreground: #ffffff;
      --secondary: #f1f5f9;
      --secondary-foreground: #0f172a;
      --muted: #f1f5f9;
      --muted-foreground: #64748b;
      --border: #e2e8f0;
      --border-subtle: #cbd5e1;
      --radius: 0.625rem;
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      width: 100%;
      max-width: 100vw;
      overflow-x: hidden;
      background-color: var(--background);
      color: var(--foreground);
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ─── Minimalist Topbar with Hamburger Menu ─── */
    .portal-nav {
      background: rgba(255, 255, 255, 0.96);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(10px);
      width: 100%;
    }
    .portal-nav-inner {
      max-width: 1040px;
      margin: 0 auto;
      padding: 0 20px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
    }
    .portal-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      min-width: 0;
    }
    .portal-brand img {
      flex-shrink: 0;
    }
    .portal-brand span {
      font-weight: 800;
      font-size: 1.1rem;
      color: #0066ff;
      letter-spacing: -0.02em;
      white-space: nowrap;
    }
    .portal-badge {
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      padding: 3px 8px;
      border-radius: 4px;
      background: #eff6ff;
      color: #0066ff;
      border: 1px solid #bfdbfe;
      white-space: nowrap;
    }

    /* Desktop Navigation Actions */
    .portal-nav-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .btn-nav-outline {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 6px;
      border: 1px solid var(--border);
      background: #ffffff;
      font-size: 13px;
      font-weight: 600;
      color: #0f172a;
      text-decoration: none;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn-nav-outline:hover {
      background: var(--muted);
      border-color: #cbd5e1;
    }
    .btn-nav-logout {
      display: inline-flex;
      align-items: center;
      padding: 7px 12px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      color: #64748b;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .btn-nav-logout:hover {
      color: #ef4444;
      background: #fef2f2;
    }

    /* Hamburger Toggle Button */
    .btn-hamburger {
      display: none;
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px 10px;
      cursor: pointer;
      color: #0f172a;
      align-items: center;
      justify-content: center;
      transition: background 0.15s ease;
    }
    .btn-hamburger:hover {
      background: var(--muted);
    }

    /* Mobile Hamburger Drawer Menu */
    .mobile-nav-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(4px);
      z-index: 90;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    .mobile-nav-backdrop.is-open {
      display: block;
      opacity: 1;
    }
    .mobile-nav-drawer {
      position: fixed;
      top: 0;
      right: -290px;
      width: 280px;
      height: 100%;
      background: #ffffff;
      border-left: 1px solid var(--border);
      z-index: 100;
      padding: 20px;
      display: flex;
      flex-direction: column;
      box-shadow: -8px 0 24px rgba(0, 0, 0, 0.08);
      transition: right 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-sizing: border-box;
    }
    .mobile-nav-drawer.is-open {
      right: 0;
    }
    .mobile-nav-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 16px;
    }
    .mobile-nav-user-title {
      font-weight: 700;
      font-size: 0.95rem;
      color: #0f172a;
    }
    .mobile-nav-user-sub {
      font-size: 0.78rem;
      color: var(--muted-foreground);
      word-break: break-all;
    }
    .btn-close-drawer {
      background: #f1f5f9;
      border: none;
      border-radius: 6px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      color: #475569;
      cursor: pointer;
    }
    .mobile-nav-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex: 1;
    }
    .mobile-nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 600;
      color: #0f172a;
      text-decoration: none;
      transition: background 0.15s ease;
    }
    .mobile-nav-item:hover, .mobile-nav-item:active {
      background: #f1f5f9;
    }
    .mobile-nav-item.logout {
      color: #dc2626;
      margin-top: auto;
      border-top: 1px solid var(--border);
      padding-top: 14px;
      border-radius: 0;
    }

    /* ─── Main Content Container ─── */
    .portal-main {
      max-width: 1040px;
      margin: 24px auto 60px auto;
      padding: 0 20px;
      width: 100%;
      box-sizing: border-box;
    }

    /* ─── User Profile Card ─── */
    .user-profile-header {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      width: 100%;
      box-sizing: border-box;
    }
    .user-profile-left {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 0;
    }
    .user-avatar-circle {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      background: #0f172a;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      font-weight: 800;
      flex-shrink: 0;
    }
    .user-headline h1 {
      font-size: 1.2rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: var(--foreground);
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .user-headline p {
      font-size: 12.5px;
      color: var(--muted-foreground);
      margin-top: 2px;
      word-break: break-word;
    }
    /* Rectangular single-color badge (no green, not round) */
    .badge-verified {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: #f1f5f9;
      color: #0f172a;
      border: 1px solid #cbd5e1;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    /* ─── Metric Stats Grid (Single Monochrome/Neutral Palette) ─── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-bottom: 24px;
      width: 100%;
    }
    .stat-card {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 16px 18px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      transition: border-color 0.15s ease;
      min-width: 0;
      box-sizing: border-box;
    }
    .stat-card:hover {
      border-color: var(--border-subtle);
    }
    .stat-card-title {
      font-size: 11.5px;
      font-weight: 600;
      color: var(--muted-foreground);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 6px;
    }
    .stat-card-title svg {
      color: #64748b;
    }
    .stat-card-number {
      font-size: 1.55rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #0f172a;
      word-break: break-word;
    }
    .stat-card-sub {
      font-size: 11.5px;
      color: var(--muted-foreground);
      margin-top: 3px;
    }

    /* ─── Tabs & Controls ─── */
    .portal-controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 18px;
      width: 100%;
    }
    .shadcn-tabs {
      display: inline-flex;
      align-items: center;
      background: #f1f5f9;
      padding: 3px;
      border-radius: 6px;
      border: 1px solid var(--border);
      max-width: 100%;
      overflow-x: auto;
    }
    .shadcn-tab-btn {
      background: transparent;
      border: none;
      padding: 6px 12px;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--muted-foreground);
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.15s ease;
      display: flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
    }
    .shadcn-tab-btn.is-active {
      background: #ffffff;
      color: #0f172a;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }
    .shadcn-tab-count {
      font-size: 10.5px;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: 4px;
      background: #e2e8f0;
      color: #0f172a;
    }
    .shadcn-tab-btn.is-active .shadcn-tab-count {
      background: #0f172a;
      color: #ffffff;
    }

    .shadcn-search {
      position: relative;
      flex: 1;
      max-width: 320px;
      min-width: 220px;
    }
    .shadcn-search-input {
      width: 100%;
      height: 36px;
      padding: 0 12px 0 34px;
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 13px;
      font-family: inherit;
      color: var(--foreground);
      outline: none;
      transition: border-color 0.15s ease;
    }
    .shadcn-search-input:focus {
      border-color: #0f172a;
    }
    .shadcn-search-icon {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted-foreground);
      pointer-events: none;
    }

    /* ─── Order Cards ─── */
    .order-card-wrap {
      display: flex;
      flex-direction: column;
      gap: 14px;
      width: 100%;
    }
    .order-card {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 22px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      transition: border-color 0.15s ease;
      width: 100%;
      box-sizing: border-box;
      overflow: hidden;
    }
    .order-card:hover {
      border-color: var(--border-subtle);
    }
    .order-card-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 14px;
      flex-wrap: wrap;
    }
    .order-meta-id {
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      font-weight: 700;
      color: var(--foreground);
      background: var(--muted);
      padding: 3px 8px;
      border-radius: 4px;
      border: 1px solid var(--border);
      word-break: break-all;
    }
    .order-meta-date {
      font-size: 11.5px;
      color: var(--muted-foreground);
      margin-left: 8px;
    }

    /* Rectangular single-color status badge (no circle dot, no green) */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 9px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      white-space: nowrap;
    }
    .status-badge.status-paid, .status-badge.status-fulfilled {
      background: #0f172a;
      color: #ffffff;
      border: 1px solid #0f172a;
    }
    .status-badge.status-awaiting, .status-badge.status-inquiry {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #cbd5e1;
    }

    .order-card-middle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      flex-wrap: wrap;
    }
    .order-product-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--foreground);
      margin-bottom: 4px;
      word-break: break-word;
    }
    .order-product-price {
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
      font-family: 'JetBrains Mono', monospace;
    }
    .btn-action-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 16px;
      background: #0066ff;
      color: #ffffff;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 700;
      text-decoration: none;
      transition: background 0.15s ease;
      white-space: nowrap;
    }
    .btn-action-primary:hover {
      background: #0052cc;
    }

    /* ─── Credential Vault (Single Monochrome/Neutral Palette, No Green) ─── */
    .credential-pool-wrap {
      margin-top: 16px;
      padding-top: 14px;
      border-top: 1px solid var(--border);
      width: 100%;
    }
    .cred-card {
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 14px 16px;
      box-sizing: border-box;
      width: 100%;
    }
    .cred-card.cred-pending {
      border-color: #cbd5e1;
      background: #ffffff;
    }
    .cred-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-weight: 700;
      font-size: 12.5px;
      color: #0f172a;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 10px;
      flex-wrap: wrap;
    }
    /* Rectangular single-color badge (no green) */
    .cred-badge-active {
      font-size: 10.5px;
      font-weight: 800;
      text-transform: uppercase;
      padding: 2px 7px;
      border-radius: 4px;
      background: #e2e8f0;
      color: #0f172a;
      white-space: nowrap;
    }
    .cred-row {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 6px;
      margin-bottom: 10px;
      width: 100%;
    }
    .cred-label {
      font-size: 11px;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .cred-val-wrap {
      background: #ffffff;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 7px 12px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12.5px;
      color: #09090b;
      word-break: break-all;
      overflow-wrap: anywhere;
      box-sizing: border-box;
      width: 100%;
    }
    .cred-actions-bar {
      display: flex;
      gap: 8px;
      width: 100%;
      margin-top: 2px;
    }
    .btn-cred-copy, .btn-cred-toggle {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #0f172a;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      padding: 7px 12px;
      font-size: 11.5px;
      font-weight: 700;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.15s ease;
      box-sizing: border-box;
    }
    .btn-cred-copy:hover {
      background: #0066ff;
    }
    .btn-cred-toggle {
      background: #f1f5f9;
      color: #334155;
      border: 1px solid var(--border);
    }
    .btn-cred-toggle:hover {
      background: #e2e8f0;
    }
    .cred-notes {
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px solid var(--border);
      color: #475569;
      font-size: 11.5px;
      line-height: 1.4;
    }

    /* ─── Empty State ─── */
    .empty-card {
      background: #ffffff;
      border: 1px dashed var(--border);
      border-radius: var(--radius);
      padding: 48px 20px;
      text-align: center;
      box-sizing: border-box;
      width: 100%;
    }
    .empty-icon {
      width: 44px;
      height: 44px;
      color: var(--muted-foreground);
      margin: 0 auto 14px auto;
    }

    /* ─── Responsive Mobile Overrides (Strictly Paten, Zero Horizontal Scroll) ─── */
    @media (max-width: 768px) {
      .portal-nav-actions {
        display: none;
      }
      .btn-hamburger {
        display: inline-flex;
      }
      .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }
      .portal-controls {
        flex-direction: column;
        align-items: stretch;
      }
      .shadcn-search {
        max-width: 100%;
      }
    }

    @media (max-width: 540px) {
      .portal-nav-inner {
        padding: 0 14px;
      }
      .portal-main {
        padding: 0 12px;
        margin-top: 16px;
      }
      .user-profile-header {
        padding: 16px 14px;
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
      }
      .user-profile-left {
        gap: 12px;
      }
      .user-avatar-circle {
        width: 42px;
        height: 42px;
        font-size: 1rem;
      }
      .user-headline h1 {
        font-size: 1.1rem;
      }
      .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
      }
      .stat-card {
        padding: 12px 14px;
      }
      .stat-card-number {
        font-size: 1.35rem;
      }
      .order-card {
        padding: 16px 14px;
        border-radius: 12px;
      }
      .order-card-middle {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
      }
      .order-card-middle > div:last-child {
        width: 100%;
      }
      .btn-action-primary, .order-card-middle .btn-nav-outline {
        width: 100%;
        text-align: center;
        justify-content: center;
      }
    }

    @media (max-width: 380px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Minimalist Topbar with Hamburger Toggle -->
  <header class="portal-nav">
    <div class="portal-nav-inner">
      <a href="/" class="portal-brand">
        <img src="/assets/logos/ziulabs-icon.webp" alt="ZiuLabs" width="30" height="30">
        <span>ZiuLabs</span>
        <span class="portal-badge">Customer Portal</span>
      </a>

      <!-- Desktop Nav Actions -->
      <div class="portal-nav-actions">
        <a href="/" class="btn-nav-outline">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          <span>Katalog Toko</span>
        </a>
        <a href="https://wa.me/62895609250509" target="_blank" rel="noopener" class="btn-nav-outline">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          <span>Bantuan CS</span>
        </a>
        <a href="/auth/logout.php" class="btn-nav-logout" title="Keluar dari akun">
          Keluar
        </a>
      </div>

      <!-- Hamburger Menu Button -->
      <button type="button" class="btn-hamburger" id="btn-hamburger" aria-label="Buka Menu Navigasi">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
      </button>
    </div>
  </header>

  <!-- Mobile Hamburger Drawer & Backdrop -->
  <div class="mobile-nav-backdrop" id="mobile-nav-backdrop"></div>
  <aside class="mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Menu Mobile">
    <div class="mobile-nav-head">
      <div>
        <div class="mobile-nav-user-title"><?= htmlspecialchars($userName) ?></div>
        <div class="mobile-nav-user-sub"><?= htmlspecialchars($userEmail ?: 'Pelanggan') ?></div>
      </div>
      <button type="button" class="btn-close-drawer" id="btn-close-drawer" aria-label="Tutup Menu">&times;</button>
    </div>
    <nav class="mobile-nav-list">
      <a href="/" class="mobile-nav-item">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        <span>Katalog Toko</span>
      </a>
      <a href="https://wa.me/62895609250509" target="_blank" rel="noopener" class="mobile-nav-item">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        <span>Bantuan CS WhatsApp</span>
      </a>
      <a href="/auth/logout.php" class="mobile-nav-item logout">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Keluar dari Akun</span>
      </a>
    </nav>
  </aside>

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

    <!-- Metric Stats Grid (Single Clean Palette) -->
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
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="m7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="stat-card-number"><?= $totalPaid ?></div>
        <div class="stat-card-sub">Kredensial &amp; lisensi siap pakai</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-title">
          <span>Garansi Toko</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
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

    <!-- Controls: Tabs + Live Search -->
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
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span class="order-meta-id"><?= htmlspecialchars($ord['public_id']) ?></span>
                <span class="order-meta-date"><?= htmlspecialchars($ord['created_at']) ?></span>
              </div>
              <div>
                <span class="status-badge status-<?= htmlspecialchars($ord['status']) ?>">
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
                  <a href="/order/payment.php?id=<?= urlencode($ord['public_id']) ?>" class="btn-nav-outline">
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

  <!-- Interactive Search, Tab Filter & Hamburger Drawer JS -->
  <script>
  (function() {
    // Hamburger Drawer Logic
    var hamburgerBtn = document.getElementById('btn-hamburger');
    var closeDrawerBtn = document.getElementById('btn-close-drawer');
    var drawer = document.getElementById('mobile-nav-drawer');
    var backdrop = document.getElementById('mobile-nav-backdrop');

    function openDrawer() {
      if (drawer && backdrop) {
        drawer.classList.add('is-open');
        backdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }
    }
    function closeDrawer() {
      if (drawer && backdrop) {
        drawer.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', openDrawer);
    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);

    // Filter & Search Logic
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
