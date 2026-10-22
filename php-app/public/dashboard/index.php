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
    
    // Fetch orders by user email
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
    $orders = $stmt->fetchAll();

    $totalPaid = count(array_filter($orders, fn($o) => in_array($o['status'], ['paid', 'fulfilled'], true)));

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
  <title>Dashboard Saya — ZiuLabs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    .dash-wrap {
      min-height: 100vh;
      background: #f8fafc;
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      padding-bottom: 60px;
    }
    .dash-header {
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 16px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dash-container {
      max-width: 960px;
      margin: 32px auto;
      padding: 0 20px;
    }
    .user-banner {
      background: linear-gradient(135deg, #0066ff, #0047b3);
      color: #ffffff;
      border-radius: 24px;
      padding: 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
      box-shadow: 0 12px 30px rgba(0, 102, 255, 0.2);
      margin-bottom: 32px;
    }
    .user-info {
      display: flex;
      align-items: center;
      gap: 18px;
    }
    .user-avatar-lg {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.4);
      object-fit: cover;
    }
    .user-initial-lg {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      font-weight: 800;
    }
    .user-stats {
      display: flex;
      gap: 24px;
    }
    .stat-badge {
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 14px;
      padding: 10px 18px;
      text-align: center;
    }
    .stat-badge small {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      opacity: 0.85;
      display: block;
    }
    .stat-badge strong {
      font-size: 1.35rem;
      font-weight: 800;
    }
    .section-title-dash {
      font-size: 1.25rem;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .order-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 22px;
      margin-bottom: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      transition: border-color 0.2s ease;
    }
    .order-card:hover {
      border-color: #0066ff;
    }
    .order-card-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #f1f5f9;
      padding-bottom: 14px;
      margin-bottom: 14px;
    }
    .order-card-body {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 14px;
    }
    .status-badge {
      font-size: 0.75rem;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
      text-transform: uppercase;
    }
    .status-badge.paid {
      background: #ecfdf5;
      color: #047857;
      border: 1px solid #a7f3d0;
    }
    .status-badge.awaiting {
      background: #fffbeb;
      color: #b45309;
      border: 1px solid #fde68a;
    }
    .btn-action-dash {
      background: #0066ff;
      color: #ffffff;
      padding: 8px 16px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-action-dash:hover {
      background: #0052cc;
    }
    .empty-state-dash {
      background: #ffffff;
      border: 2px dashed #cbd5e1;
      border-radius: 20px;
      padding: 48px 24px;
      text-align: center;
    /* Credential Pool Customer Card */
    .credential-pool-wrap {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 14px;
    }
    .cred-card {
      background: #ffffff;
      border: 1px solid #a7f3d0;
      border-radius: 16px;
      padding: 16px 18px;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.08);
      text-align: left;
    }
    .cred-card.cred-pending {
      border-color: #fde68a;
      background: #fffbeb;
    }
    .cred-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 700;
      font-size: 0.95rem;
      color: #065f46;
      padding-bottom: 10px;
      border-bottom: 1px dashed #d1fae5;
      margin-bottom: 12px;
    }
    .cred-badge-active {
      font-size: 0.72rem;
      background: #d1fae5;
      color: #047857;
      padding: 3px 8px;
      border-radius: 6px;
      font-weight: 800;
      text-transform: uppercase;
    }
    .cred-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .cred-label {
      width: 90px;
      font-size: 0.8rem;
      font-weight: 700;
      color: #065f46;
      text-transform: uppercase;
      flex-shrink: 0;
    }
    .cred-val-wrap {
      flex: 1;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 6px 12px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.88rem;
      color: #0f172a;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .btn-cred-copy, .btn-cred-toggle {
      background: #0f172a;
      color: #ffffff;
      border: none;
      border-radius: 6px;
      padding: 6px 10px;
      font-size: 0.78rem;
      font-weight: 700;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.15s ease;
    }
    .btn-cred-copy:hover {
      background: #0066ff;
    }
    .btn-cred-toggle {
      background: #e2e8f0;
      color: #334155;
    }
    .btn-cred-toggle:hover {
      background: #cbd5e1;
    }
    .cred-notes {
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px solid #f1f5f9;
      color: #047857;
      font-size: 0.82rem;
    }
  </style>
</head>
<body>
  <div class="dash-wrap">
    <header class="dash-header">
      <div style="display: flex; align-items: center; gap: 12px;">
        <a href="/" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
          <img src="/assets/logos/ziulabs-icon.webp" alt="ZiuLabs" width="30" height="30">
          <span style="font-weight: 800; color: #0066ff; font-size: 1.1rem;">ZiuLabs</span>
        </a>
        <span style="color: #cbd5e1;">|</span>
        <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Area Pembeli</span>
      </div>

      <div style="display: flex; align-items: center; gap: 16px;">
        <a href="/" style="font-size: 0.88rem; font-weight: 600; color: #0066ff; text-decoration: none;">&larr; Belanja Produk Lain</a>
        <a href="/auth/logout.php" style="font-size: 0.85rem; color: #ef4444; font-weight: 600; text-decoration: none;">Keluar</a>
      </div>
    </header>

    <div class="dash-container">
      <!-- User Hero Banner -->
      <div class="user-banner">
        <div class="user-info">
          <?php if (!empty($userAvatar)): ?>
            <img src="<?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>" class="user-avatar-lg">
          <?php else: ?>
            <div class="user-initial-lg"><?= strtoupper(substr($userName, 0, 1)) ?></div>
          <?php endif; ?>
          <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; margin: 0;"><?= htmlspecialchars($userName) ?></h1>
            <p style="margin: 4px 0 0 0; opacity: 0.85; font-size: 0.88rem;"><?= htmlspecialchars($userEmail) ?></p>
          </div>
        </div>

        <div class="user-stats">
          <div class="stat-badge">
            <small>Total Order</small>
            <strong><?= count($orders) ?></strong>
          </div>
          <div class="stat-badge">
            <small>Produk Aktif</small>
            <strong><?= $totalPaid ?></strong>
          </div>
        </div>
      </div>

      <!-- Orders Section -->
      <div class="section-title-dash">
        <span>Riwayat Pesanan &amp; Kredensial Lisensi</span>
        <a href="/#catalog" style="font-size: 0.85rem; color: #0066ff; text-decoration: none;">+ Tambah Produk</a>
      </div>

      <?php if (empty($orders)): ?>
        <div class="empty-state-dash">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">📦</div>
          <h3 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">Belum Ada Riwayat Pesanan</h3>
          <p style="font-size: 0.9rem; max-width: 420px; margin: 8px auto 20px auto;">Pilih produk digital dari katalog untuk memulai pemesanan dengan garansi resmi dan aktivasi cepat.</p>
          <a href="/#catalog" class="btn-action-dash">Lihat Katalog Produk Sekarang &rarr;</a>
        </div>
      <?php else: ?>
        <?php foreach ($orders as $ord): 
          $isOrderPaid = in_array($ord['status'], ['paid', 'fulfilled'], true);
          $priceFormatted = 'Rp' . number_format((int) ($ord['total_minor'] / 100), 0, ',', '.');
        ?>
          <div class="order-card">
            <div class="order-card-head">
              <div>
                <strong style="color: #0f172a; font-family: 'JetBrains Mono', monospace; font-size: 0.95rem;"><?= htmlspecialchars($ord['public_id']) ?></strong>
                <small style="color: #94a3b8; margin-left: 10px;"><?= htmlspecialchars($ord['created_at']) ?></small>
              </div>
              <span class="status-badge <?= $isOrderPaid ? 'paid' : 'awaiting' ?>">
                <?= $isOrderPaid ? 'Lunas / Aktif' : 'Menunggu Bayar' ?>
              </span>
            </div>

            <div class="order-card-body">
              <div>
                <h4 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">
                  <?= htmlspecialchars($ord['sku_name_snapshot'] ?? 'Produk Digital') ?>
                </h4>
                <span style="font-size: 0.92rem; font-weight: 700; color: #0066ff;"><?= $priceFormatted ?></span>
              </div>

              <div>
                <?php if ($isOrderPaid): ?>
                  <a href="/order/payment.php?id=<?= urlencode($ord['public_id']) ?>" class="btn-action-dash" style="background: #10b981;">
                    Lihat Akses &amp; Lisensi &rarr;
                  </a>
                <?php else: ?>
                  <a href="/order/payment.php?id=<?= urlencode($ord['public_id']) ?>" class="btn-action-dash">
                    Bayar Sekarang &rarr;
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($isOrderPaid && !empty($ord['payload_encrypted'])): ?>
              <?= renderFulfillmentCard($ord['payload_encrypted']) ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
