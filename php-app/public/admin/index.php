<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\AuditLogger;
use GeoBooster\Security\Auth;
use GeoBooster\Security\Authorizer;
use GeoBooster\Security\Csrf;
use GeoBooster\Security\Database;

function h(string|int|null $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }

Csrf::start();
try {
    $db = Database::connect();
    $auth = new Auth($db);
    $userId = $auth->userId();
} catch (Throwable $error) {
    http_response_code(503);
    ?><!doctype html><meta charset="utf-8"><title>Geo Booster Admin unavailable</title><style>body{font-family:system-ui;max-width:680px;margin:12vh auto;padding:24px;background:#f7f7f3;color:#17171b}main{border:1px solid #ddd;padding:28px;background:#fff}code{background:#eee;padding:3px 6px}</style><main><h1>Admin console belum aktif</h1><p>Database belum terhubung pada environment ini. Konfigurasikan <code>DB_DSN</code>, jalankan migration, lalu buat owner melalui provisioning command.</p></main><?php
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);
    if ($action === 'login') {
        if ($auth->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            redirect('/admin/');
        }
        $loginError = 'Email atau password tidak valid.';
    } elseif ($action === 'logout') {
        if ($userId !== null) {
            (new AuditLogger($db))->record($userId, 'logout', 'user', $userId);
        }
        $auth->logout();
        redirect('/admin/');
    } elseif ($userId !== null) {
        $authorizer = new Authorizer($db);
        $audit = new AuditLogger($db);
        if ($action === 'stock') {
            $authorizer->requirePermission($userId, 'inventory.manage');
            $skuId = filter_input(INPUT_POST, 'sku_id', FILTER_VALIDATE_INT);
            $stock = (string) ($_POST['stock_status'] ?? 'paused');
            if (!$skuId || !in_array($stock, ['available', 'sold_out', 'paused'], true)) { http_response_code(422); exit('Invalid SKU update.'); }
            $stmt = $db->prepare('UPDATE skus SET stock_status = :stock WHERE id = :id');
            $stmt->execute(['stock' => $stock, 'id' => $skuId]);
            $audit->record($userId, 'update_stock', 'sku', $skuId, ['stock_status' => $stock]);
            redirect('/admin/?tab=products');
        }
    }
}

if ($userId === null) {
    ?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Geo Booster Admin Login</title><link rel="stylesheet" href="/admin/style.css"></head><body class="auth-page"><main class="auth-card"><div class="eyebrow">GEO BOOSTER / INTERNAL</div><h1>Admin console</h1><p>Masuk untuk mengelola katalog dan order.</p><?php if (isset($loginError)): ?><div class="alert"><?= h($loginError) ?></div><?php endif; ?><form method="post" action="?action=login"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><label>Email<input type="email" name="email" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="primary" type="submit">Masuk</button></form><small>Admin belum aktif sampai database dan owner account dikonfigurasi.</small></main></body></html><?php
    exit;
}

$authorizer = new Authorizer($db);
$tab = $_GET['tab'] ?? 'overview';
try {
    $authorizer->requirePermission($userId, 'catalog.view');
    $counts = [
        'products' => (int) $db->query("SELECT COUNT(*) FROM products WHERE status <> 'archived'")->fetchColumn(),
        'available' => (int) $db->query("SELECT COUNT(*) FROM skus WHERE stock_status = 'available'")->fetchColumn(),
        'orders' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('inquiry','awaiting_payment','manual_review')")->fetchColumn(),
        'audit' => (int) $db->query('SELECT COUNT(*) FROM audit_logs WHERE created_at >= CURRENT_DATE')->fetchColumn(),
    ];
    $products = $db->query('SELECT s.id AS sku_id, p.name, s.label, s.price_minor, s.currency, s.stock_status FROM skus s JOIN products p ON p.id = s.product_id WHERE p.status <> "archived" ORDER BY p.name, s.id LIMIT 100')->fetchAll();
    $orders = $db->query('SELECT public_id, customer_name, customer_phone, status, total_minor, currency, created_at FROM orders ORDER BY created_at DESC LIMIT 30')->fetchAll();
    $audits = $db->query('SELECT actor_type, action, entity_type, entity_id, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 30')->fetchAll();
} catch (Throwable $error) {
    http_response_code(403);
    exit('Forbidden.');
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Geo Booster Admin</title><link rel="stylesheet" href="/admin/style.css"></head><body><header class="topbar"><a class="brand" href="/admin/">G<span> B</span></a><form method="post" action="?action=logout"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><button class="logout" type="submit">Keluar</button></form></header><main class="admin-shell"><div class="heading"><div><div class="eyebrow">INTERNAL / CONTROL ROOM</div><h1>Good morning, operator.</h1><p>Katalog, order, dan audit dalam satu tempat.</p></div><a class="public-link" href="/" target="_blank" rel="noopener">Lihat website ↗</a></div><nav class="tabs"><a class="<?= $tab === 'overview' ? 'active' : '' ?>" href="/admin/?tab=overview">Overview</a><a class="<?= $tab === 'products' ? 'active' : '' ?>" href="/admin/?tab=products">Products & SKU</a><a class="<?= $tab === 'orders' ? 'active' : '' ?>" href="/admin/?tab=orders">Order queue</a><a class="<?= $tab === 'audit' ? 'active' : '' ?>" href="/admin/?tab=audit">Audit log</a></nav><section class="metrics"><div><small>CATALOG</small><strong><?= $counts['products'] ?></strong><span>published records</span></div><div><small>INVENTORY</small><strong><?= $counts['available'] ?></strong><span>SKU tersedia</span></div><div><small>ATTENTION</small><strong><?= $counts['orders'] ?></strong><span>order perlu action</span></div><div><small>TODAY</small><strong><?= $counts['audit'] ?></strong><span>audit events</span></div></section><?php if ($tab === 'products'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">CATALOG / SKU</div><h2>Inventory control</h2></div><span class="muted">Harga tersimpan sebagai integer minor unit.</span></div><div class="table-wrap"><table><thead><tr><th>Product</th><th>Label</th><th>Price</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td><?= h($product['name']) ?></td><td><?= h($product['label']) ?></td><td><?= h($product['currency']) ?> <?= number_format((int) $product['price_minor'], 0, ',', '.') ?></td><td><span class="status <?= h($product['stock_status']) ?>"><?= h($product['stock_status']) ?></span></td><td><form class="inline-form" method="post" action="?action=stock"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><input type="hidden" name="sku_id" value="<?= h($product['sku_id']) ?>"><select name="stock_status"><option value="available">available</option><option value="sold_out">sold_out</option><option value="paused">paused</option></select><button type="submit">Simpan</button></form></td></tr><?php endforeach; ?></tbody></table></div></section><?php elseif ($tab === 'orders'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">OPERATIONS / ORDERS</div><h2>Order queue</h2></div></div><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><strong><?= h($order['public_id']) ?></strong></td><td><?= h($order['customer_name'] ?: 'Anonymous') ?><br><small><?= h($order['customer_phone']) ?></small></td><td><span class="status <?= h($order['status']) ?>"><?= h($order['status']) ?></span></td><td><?= h($order['currency']) ?> <?= number_format((int) $order['total_minor'], 0, ',', '.') ?></td><td><?= h($order['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></section><?php elseif ($tab === 'audit'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">SECURITY / AUDIT</div><h2>Recent audit events</h2></div></div><div class="table-wrap"><table><thead><tr><th>Actor</th><th>Action</th><th>Entity</th><th>Time</th></tr></thead><tbody><?php foreach ($audits as $audit): ?><tr><td><?= h($audit['actor_type']) ?></td><td><?= h($audit['action']) ?></td><td><?= h($audit['entity_type']) ?> #<?= h($audit['entity_id']) ?></td><td><?= h($audit['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></section><?php else: ?><section class="dashboard-grid"><div class="panel"><div class="eyebrow">NEXT ACTIONS</div><h2>Keep the catalog clean.</h2><p class="muted">Verifikasi compliance sebelum publish, pastikan stok benar, dan jangan simpan credential produk pada order atau notes.</p><a class="button" href="/admin/?tab=products">Kelola SKU →</a></div><div class="panel dark"><div class="eyebrow">RELEASE GATE</div><h2>Payment belum live.</h2><p>Aktifkan hanya setelah provider sandbox, signed webhook, refund SOP, dan reconciliation lulus.</p><a class="button light" href="/admin/?tab=orders">Lihat order →</a></div></section><?php endif; ?></main></body></html>
