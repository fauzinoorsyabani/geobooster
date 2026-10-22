<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/helpers.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\Csrf;

Csrf::start();

$publicId = trim((string) ($_GET['id'] ?? ''));

if ($publicId === '') {
    header('Location: /', true, 303);
    exit;
}

try {
    $db = Database::connect();
    $stmt = $db->prepare('SELECT * FROM orders WHERE public_id = :id LIMIT 1');
    $stmt->execute(['id' => $publicId]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        exit('Pesanan tidak ditemukan.');
    }

    $itemStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
    $itemStmt->execute(['order_id' => $order['id']]);
    $items = $itemStmt->fetchAll();

    // Check fulfillment credentials if paid
    $fulfillment = null;
    if ($order['status'] === 'paid' || $order['status'] === 'fulfilled') {
        $fulStmt = $db->prepare('SELECT * FROM fulfillments WHERE order_id = :order_id LIMIT 1');
        $fulStmt->execute(['order_id' => $order['id']]);
        $fulfillment = $fulStmt->fetch();
    }

    $isPaid = ($order['status'] === 'paid' || $order['status'] === 'fulfilled');
    $totalFormatted = 'Rp' . number_format((int) ($order['total_minor'] / 100), 0, ',', '.');
    $itemName = !empty($items) ? $items[0]['sku_name_snapshot'] : 'Produk Digital ZiuLabs';

    // WhatsApp Notification & Direct Chat Integration
    $waNotifier = new \GeoBooster\Notifications\WhatsAppNotifier($db);
    $waFulfillmentUrl = null;
    if ($isPaid && !empty($order['customer_phone'])) {
        $rawItems = json_decode($fulfillment['payload_encrypted'] ?? '[]', true) ?: [];
        $waFulfillmentUrl = \GeoBooster\Notifications\WhatsAppNotifier::buildDirectChatUrl(
            $order['customer_phone'],
            $waNotifier->formatFulfillmentMessage($order, $rawItems)
        );
    }
    $waCsHelpUrl = \GeoBooster\Notifications\WhatsAppNotifier::buildDirectChatUrl(
        '62895609250509',
        'Halo CS ZiuLabs, saya butuh bantuan untuk pesanan ' . $order['public_id']
    );

} catch (Throwable $e) {
    http_response_code(500);
    exit('Terjadi kesalahan database: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isPaid ? 'Pesanan Berhasil' : 'Pembayaran Pesanan' ?> — <?= htmlspecialchars($order['public_id']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <style>
    .pay-page-wrap {
      min-height: 100vh;
      background: radial-gradient(circle at 50% 0%, rgba(0, 102, 255, 0.07) 0%, rgba(248, 250, 252, 1) 60%);
      padding: 40px 20px 80px 20px;
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }
    .pay-container {
      max-width: 600px;
      margin: 0 auto;
    }
    .pay-card {
      background: #ffffff;
      border: 1px solid rgba(0, 102, 255, 0.16);
      border-radius: 24px;
      padding: 36px 32px;
      box-shadow: 0 16px 40px rgba(10, 25, 50, 0.07);
    }
    .pay-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 18px;
      border-bottom: 1px solid #e2e8f0;
    }
    .pay-brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .pay-status-pill {
      font-size: 0.8rem;
      font-weight: 700;
      padding: 6px 14px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .pay-status-pill.awaiting {
      background: #fffbeb;
      color: #b45309;
      border: 1px solid #fde68a;
    }
    .pay-status-pill.paid {
      background: #ecfdf5;
      color: #047857;
      border: 1px solid #a7f3d0;
    }
    .pay-amount-box {
      background: linear-gradient(135deg, #f0f7ff, #e6f0fa);
      border: 1px solid #c7dfff;
      border-radius: 18px;
      padding: 22px;
      text-align: center;
      margin-bottom: 26px;
    }
    .pay-amount-label {
      font-size: 0.85rem;
      font-weight: 600;
      color: #0052cc;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .pay-amount-val {
      font-size: 2.2rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.02em;
      margin: 6px 0;
    }
    .pay-timer {
      font-size: 0.88rem;
      color: #64748b;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .pay-timer strong {
      color: #dc2626;
      font-family: 'JetBrains Mono', monospace;
    }
    .qris-box {
      text-align: center;
      background: radial-gradient(circle at 50% 0%, rgba(0, 102, 255, 0.05) 0%, #ffffff 80%);
      border: 1.5px solid rgba(0, 102, 255, 0.22);
      border-radius: 24px;
      padding: 24px 20px;
      margin-bottom: 24px;
      box-shadow: 0 10px 30px rgba(0, 102, 255, 0.06);
    }
    .qris-banner-img {
      width: 100%;
      max-width: 310px;
      height: auto;
      border-radius: 20px;
      margin: 12px auto 14px auto;
      display: block;
      box-shadow: 0 12px 32px rgba(0, 102, 255, 0.16);
      border: 1px solid rgba(0, 102, 255, 0.2);
      transition: transform 0.2s ease;
    }
    .qris-banner-img:hover {
      transform: scale(1.02);
    }
    .qris-download-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f0f7ff;
      border: 1px solid #bfdbfe;
      color: #0284c7;
      font-size: 0.8rem;
      font-weight: 700;
      padding: 6px 14px;
      border-radius: 999px;
      text-decoration: none;
      margin-bottom: 8px;
      transition: all 0.15s ease;
    }
    .qris-download-btn:hover {
      background: #e0f2fe;
      color: #0369a1;
      border-color: #93c5fd;
    }
    .order-details-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      font-size: 0.9rem;
    }
    .order-details-table td {
      padding: 10px 0;
      border-bottom: 1px solid #f1f5f9;
    }
    .order-details-table td:last-child {
      text-align: right;
      font-weight: 600;
      color: #0f172a;
    }
    .order-details-table td:first-child {
      color: #64748b;
    }
    .license-card {
      background: #ecfdf5;
      border: 1.5px solid #10b981;
      border-radius: 18px;
      padding: 24px;
      margin: 20px 0;
    }
    .license-card h4 {
      margin: 0 0 10px 0;
      color: #065f46;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1.05rem;
    }
    .license-credential-box {
      background: #ffffff;
      border: 1px solid #a7f3d0;
      border-radius: 12px;
      padding: 14px 16px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.92rem;
      color: #0f172a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 12px 0;
    }
    .btn-copy-code {
      background: #0066ff;
      color: #ffffff;
      border: none;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-simulate-pay {
      display: block;
      width: 100%;
      background: #10b981;
      color: #ffffff;
      padding: 14px 20px;
      border: none;
      border-radius: 14px;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
      transition: all 0.2s ease;
      margin: 14px 0;
    }
    .btn-simulate-pay:hover {
      background: #059669;
      transform: translateY(-1px);
    }
    .btn-back-home {
      display: block;
      text-align: center;
      color: #0066ff;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      margin-top: 20px;
    }
    /* Credential Pool Customer Card */
    .credential-pool-wrap {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin: 14px 0;
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
  <div class="pay-page-wrap">
    <div class="pay-container">
      <div class="pay-card">
        <div class="pay-header">
          <div class="pay-brand">
            <img src="/assets/logos/ziulabs-icon.webp" alt="ZiuLabs" width="32" height="32">
            <span style="font-weight: 800; color: #0066ff;">ZiuLabs Checkout</span>
          </div>
          <span class="pay-status-pill <?= $isPaid ? 'paid' : 'awaiting' ?>">
            <?= $isPaid ? 'Lunas (Terverifikasi)' : 'Menunggu Pembayaran' ?>
          </span>
        </div>

        <?php if ($isPaid): ?>
          <!-- SUCCESS VIEW -->
          <div style="text-align: center; margin: 10px 0 24px 0;">
            <div style="width: 64px; height: 64px; background: #d1fae5; color: #059669; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 12px;">✓</div>
            <h2 style="font-size: 1.45rem; font-weight: 800; color: #065f46; margin: 0;">Pembayaran Berhasil!</h2>
            <p style="color: #64748b; font-size: 0.92rem; margin-top: 6px;">Terima kasih, <strong><?= htmlspecialchars($order['customer_name'] ?? 'Pelanggan') ?></strong>. Produk digital kamu sudah aktif dan siap digunakan.</p>
          </div>

          <div class="license-card">
            <h4>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <span>Akses Lisensi &amp; Kredensial Akun</span>
            </h4>
            <p style="font-size: 0.88rem; color: #047857; margin: 0 0 8px 0;">Salin data kredensial di bawah ini untuk aktivasi langsung:</p>
            
            <?= renderFulfillmentCard($fulfillment['payload_encrypted'] ?? '') ?>


            <div style="font-size: 0.85rem; color: #065f46; line-height: 1.6; margin-top: 10px;">
              <strong>Panduan Aktivasi:</strong>
              <ol style="margin: 6px 0 0 18px; padding: 0;">
                <li>Buka aplikasi/layanan resmi terkait.</li>
                <li>Gunakan kredensial atau kode lisensi di atas untuk masuk/aktivasi.</li>
                <li>Butuh bantuan? Tim ZiuLabs siap mendampingi via WhatsApp.</li>
              </ol>
            </div>
          </div>

          <table class="order-details-table">
            <tr><td>Nomor Pesanan</td><td><?= htmlspecialchars($order['public_id']) ?></td></tr>
            <tr><td>Produk</td><td><?= htmlspecialchars($itemName) ?></td></tr>
            <tr><td>Total Dibayar</td><td style="color: #059669; font-weight: 800;"><?= $totalFormatted ?></td></tr>
            <tr><td>Nomor WhatsApp</td><td><?= htmlspecialchars($order['customer_phone']) ?></td></tr>
            <tr><td>Email</td><td><?= htmlspecialchars($order['customer_email'] ?? '-') ?></td></tr>
          </table>

          <div style="display: flex; gap: 12px; margin-top: 18px; flex-wrap: wrap;">
            <?php if ($waFulfillmentUrl): ?>
              <a href="<?= htmlspecialchars($waFulfillmentUrl) ?>" target="_blank" style="flex: 1; min-width: 220px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #22c55e; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 12px; font-weight: 700; font-size: 0.92rem; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25); transition: background 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                Simpan di WhatsApp Saya
              </a>
            <?php endif; ?>
            <a href="/dashboard/" class="btn-simulate-pay" style="flex: 1; min-width: 220px; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin: 0;">
              Buka Customer Dashboard &rarr;
            </a>
          </div>

        <?php else: ?>
          <!-- AWAITING PAYMENT (QRIS) VIEW -->
          <div class="pay-amount-box">
            <span class="pay-amount-label">TOTAL JUMLAH YANG DIBAYAR</span>
            <div class="pay-amount-val"><?= $totalFormatted ?></div>
            <div class="pay-timer">
              <span>Selesaikan pembayaran dalam</span>
              <strong id="timer-display">14:59</strong>
            </div>
          </div>

          <div class="qris-box">
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 6px;">
              <span style="font-weight: 800; font-size: 1.1rem; color: #0f172a;">QRIS RESMI ZIULABS</span>
              <span style="background: #ecfdf5; color: #047857; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">SCAN &amp; BAYAR</span>
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 12px 0;">Buka m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet (GoPay, OVO, Dana, ShopeePay), lalu scan kode QR resmi di bawah ini:</p>
            
            <a href="/assets/qris-ziulabs.png" target="_blank" title="Klik untuk memperbesar gambar QR">
              <img class="qris-banner-img" src="/assets/qris-ziulabs.png" alt="QRIS Resmi ZiuLabs">
            </a>

            <div>
              <a href="/assets/qris-ziulabs.png" download="QRIS-ZiuLabs-<?= htmlspecialchars($order['public_id']) ?>.png" class="qris-download-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Unduh / Simpan Gambar QR
              </a>
            </div>
            
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; margin-top: 10px; font-size: 0.84rem; color: #475569;">
              Pastikan nominal pembayaran sesuai: <strong style="color: #0066ff;"><?= $totalFormatted ?></strong>
            </div>
          </div>

          <!-- Sandbox Testing Simulator Button -->
          <form method="post" action="/api/simulate-payment.php">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['public_id']) ?>">
            <button type="submit" class="btn-simulate-pay">
              ⚡ Simulasi Pembayaran Sukses (Uji Coba Sandbox)
            </button>
          </form>

          <table class="order-details-table">
            <tr><td>Nomor Pesanan</td><td><?= htmlspecialchars($order['public_id']) ?></td></tr>
            <tr><td>Item</td><td><?= htmlspecialchars($itemName) ?></td></tr>
            <tr><td>Pemesan</td><td><?= htmlspecialchars($order['customer_name'] ?? 'Pelanggan') ?> (<?= htmlspecialchars($order['customer_phone']) ?>)</td></tr>
            <tr><td>Metode</td><td>QRIS Nasional (Bebas Biaya Admin)</td></tr>
          </table>

          <div style="text-align: center; margin-top: 14px;">
            <a href="<?= htmlspecialchars($waCsHelpUrl) ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: #059669; text-decoration: none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
              Butuh bantuan pembayaran? Chat CS di WhatsApp
            </a>
          </div>

          <script>
            // Live Polling every 3s to auto-detect payment completion
            var orderPublicId = <?= json_encode($order['public_id']) ?>;
            var pollInterval = setInterval(function() {
              fetch('/api/check-payment.php?id=' + encodeURIComponent(orderPublicId))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                  if (data.status === 'paid' || data.status === 'fulfilled') {
                    clearInterval(pollInterval);
                    window.location.reload();
                  }
                })
                .catch(function(err) {});
            }, 3000);

            // 15 Minutes Countdown
            var timeLeft = 15 * 60;
            var timerEl = document.getElementById('timer-display');
            setInterval(function() {
              if (timeLeft <= 0) return;
              timeLeft--;
              var m = Math.floor(timeLeft / 60);
              var s = timeLeft % 60;
              timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }, 1000);
          </script>
        <?php endif; ?>

        <a class="btn-back-home" href="/">&larr; Kembali ke Katalog Toko</a>
      </div>
    </div>
  </div>
</body>
</html>
