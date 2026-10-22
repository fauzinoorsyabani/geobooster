<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\AuditLogger;
use GeoBooster\Security\Auth;
use GeoBooster\Security\Authorizer;
use GeoBooster\Security\Csrf;
use GeoBooster\Security\Database;
use GeoBooster\Security\RateLimiter;

function h(string|int|null $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }

Csrf::start();
if ((getenv('ADMIN_ENABLED') ?: '') !== 'true') {
    http_response_code(503);
    ?><!doctype html><meta charset="utf-8"><title>ZiuLabs Admin unavailable</title><style>body{font-family:system-ui;max-width:680px;margin:12vh auto;padding:24px;background:#f7f7f3;color:#17171b}main{border:1px solid #ddd;padding:28px;background:#fff}code{background:#eee;padding:3px 6px}</style><main><h1>Admin console tidak tersedia</h1><p>Set <code>ADMIN_ENABLED=true</code> pada environment untuk mengaktifkan.</p></main><?php
    exit;
}
try {
    $db = Database::connect();
    $auth = new Auth($db);
    $userId = $auth->userId();
} catch (Throwable $error) {
    http_response_code(503);
    ?><!doctype html><meta charset="utf-8"><title>ZiuLabs Admin unavailable</title><style>body{font-family:system-ui;max-width:680px;margin:12vh auto;padding:24px;background:#f7f7f3;color:#17171b}main{border:1px solid #ddd;padding:28px;background:#fff}code{background:#eee;padding:3px 6px}</style><main><h1>Admin console belum aktif</h1><p>Database belum terhubung pada environment ini. Konfigurasikan <code>DB_DSN</code>, jalankan migration, lalu buat owner melalui provisioning command.</p></main><?php
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);
    if ($action === 'login') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $rateLimiter = new RateLimiter($db);
        if (!$rateLimiter->checkLoginLimit($ip)) {
            http_response_code(429);
            $loginError = 'Terlalu banyak percobaan. Coba lagi nanti.';
        } else {
            $rateLimiter->recordLoginAttempt($ip);
            if ($auth->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                $rateLimiter->clearLoginAttempts($ip);
                redirect('/admin/');
            }
            $loginError = 'Email atau password tidak valid.';
        }
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
        } elseif ($action === 'edit_sku') {
            $authorizer->requirePermission($userId, 'catalog.manage');
            $skuId = filter_input(INPUT_POST, 'sku_id', FILTER_VALIDATE_INT);
            $label = trim((string) ($_POST['label'] ?? ''));
            $price = filter_input(INPUT_POST, 'price_minor', FILTER_VALIDATE_INT);
            $stock = (string) ($_POST['stock_status'] ?? '');
            if (!$skuId || $label === '' || !$price || $price <= 0 || !in_array($stock, ['available', 'sold_out', 'paused'], true)) {
                http_response_code(422); exit('Invalid SKU data.');
            }
            $stmt = $db->prepare('UPDATE skus SET label = :label, price_minor = :price, stock_status = :stock WHERE id = :id');
            $stmt->execute(['label' => $label, 'price' => $price, 'stock' => $stock, 'id' => $skuId]);
            $audit->record($userId, 'edit_sku', 'sku', $skuId, ['label' => $label, 'price_minor' => $price, 'stock_status' => $stock]);
            redirect('/admin/?tab=products');
        } elseif ($action === 'edit_product') {
            $authorizer->requirePermission($userId, 'catalog.manage');
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['name'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            if (!$productId || $name === '' || $category === '') {
                http_response_code(422); exit('Invalid product data.');
            }
            $stmt = $db->prepare('UPDATE products SET name = :name, category = :category, description = :description WHERE id = :id');
            $stmt->execute(['name' => $name, 'category' => $category, 'description' => $description === '' ? null : $description, 'id' => $productId]);
            $audit->record($userId, 'edit_product', 'product', $productId, ['name' => $name, 'category' => $category]);
            redirect('/admin/?tab=products');
        } elseif ($action === 'publish_product') {
            $authorizer->requirePermission($userId, 'catalog.manage');
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if (!$productId) { http_response_code(422); exit('Invalid ID.'); }
            $check = $db->prepare('SELECT compliance_status FROM products WHERE id = :id');
            $check->execute(['id' => $productId]);
            if ($check->fetchColumn() !== 'verified') {
                http_response_code(422); exit('Product compliance not verified.');
            }
            $db->prepare('UPDATE products SET status = \'published\' WHERE id = :id')->execute(['id' => $productId]);
            $audit->record($userId, 'publish_product', 'product', $productId);
            redirect('/admin/?tab=products');
        } elseif ($action === 'archive_product') {
            $authorizer->requirePermission($userId, 'catalog.manage');
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if (!$productId) { http_response_code(422); exit('Invalid ID.'); }
            $db->prepare('UPDATE products SET status = \'archived\' WHERE id = :id')->execute(['id' => $productId]);
            $audit->record($userId, 'archive_product', 'product', $productId);
            redirect('/admin/?tab=products');
        } elseif ($action === 'operator_note') {
            $authorizer->requirePermission($userId, 'orders.fulfill');
            $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $note = trim(strip_tags((string) ($_POST['note'] ?? '')));
            if (!$orderId || $note === '' || strlen($note) > 500) {
                http_response_code(422); exit('Invalid note.');
            }
            $audit->record($userId, 'operator_note', 'order', $orderId, ['note' => $note]);
            redirect('/admin/?tab=orders');
        } elseif ($action === 'bulk_import_credentials') {
            $authorizer->requirePermission($userId, 'inventory.manage');
            $skuId = filter_input(INPUT_POST, 'sku_id', FILTER_VALIDATE_INT);
            $type = (string) ($_POST['credential_type'] ?? 'account');
            $rawText = trim((string) ($_POST['raw_text'] ?? ''));
            $notes = trim((string) ($_POST['notes'] ?? ''));
            if (!$skuId || $rawText === '' || !in_array($type, ['account', 'license_key', 'invite_link', 'custom'], true)) {
                http_response_code(422); exit('Data kredensial tidak valid.');
            }
            $engine = new \GeoBooster\Fulfillment\FulfillmentEngine($db);
            $count = $engine->bulkImport($skuId, $type, $rawText, $notes ?: null);
            $audit->record($userId, 'bulk_import_credentials', 'sku', $skuId, ['count' => $count, 'type' => $type]);
            redirect('/admin/?tab=credentials&msg=imported_' . $count);
        } elseif ($action === 'retry_dispatch') {
            $authorizer->requirePermission($userId, 'orders.fulfill');
            $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            if (!$orderId) { http_response_code(422); exit('Order ID tidak valid.'); }
            $engine = new \GeoBooster\Fulfillment\FulfillmentEngine($db);
            $res = $engine->dispatchOrder($orderId);
            redirect('/admin/?tab=orders&order_id=' . $orderId);
        }
    }
}

if ($userId === null) {
    ?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ZiuLabs Admin Login</title><link rel="icon" type="image/png" href="/assets/logos/ziulabs-icon.png"><link rel="shortcut icon" href="/favicon.ico"><link rel="apple-touch-icon" href="/assets/logos/ziulabs-icon.png"><link rel="stylesheet" href="/admin/style.css"></head><body class="auth-page"><main class="auth-card"><div class="eyebrow">ZIULABS / INTERNAL</div><h1>Admin console</h1><p>Masuk untuk mengelola katalog dan order.</p><?php if (isset($loginError)): ?><div class="alert"><?= h($loginError) ?></div><?php endif; ?><form method="post" action="?action=login"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><label>Email<input type="email" name="email" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button class="primary" type="submit">Masuk</button></form><small>Admin ZiuLabs Store.</small></main></body></html><?php
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
        'pool_available' => (int) $db->query("SELECT COUNT(*) FROM sku_credentials WHERE status = 'available'")->fetchColumn(),
        'audit' => (int) $db->query('SELECT COUNT(*) FROM audit_logs WHERE created_at >= CURRENT_DATE')->fetchColumn(),
    ];
    $products = $db->query('SELECT s.id AS sku_id, p.id AS product_id, p.name, p.category, p.description, p.compliance_status, p.status, s.label, s.price_minor, s.currency, s.stock_status FROM skus s JOIN products p ON p.id = s.product_id WHERE p.status <> "archived" ORDER BY p.name, s.id LIMIT 100')->fetchAll();
    
    $credentialSummary = [];
    $recentCreds = [];
    if ($tab === 'credentials') {
        $credentialSummary = (new \GeoBooster\Fulfillment\FulfillmentEngine($db))->getStockSummary();
        $recentCreds = $db->query('SELECT c.*, s.label AS sku_label, p.name AS product_name, o.public_id AS order_public_id FROM sku_credentials c JOIN skus s ON s.id = c.sku_id JOIN products p ON p.id = s.product_id LEFT JOIN orders o ON o.id = c.assigned_order_id ORDER BY c.id DESC LIMIT 50')->fetchAll();
    }
    
    $orderFilter = $_GET['filter'] ?? 'all';
    $orderQuery = 'SELECT id, public_id, customer_name, customer_phone, status, total_minor, currency, created_at FROM orders';
    $orderParams = [];
    if ($orderFilter !== 'all') {
        $orderQuery .= ' WHERE status = :status';
        $orderParams['status'] = $orderFilter;
    }
    $orderQuery .= ' ORDER BY created_at DESC LIMIT 50';
    $stmt = $db->prepare($orderQuery);
    $stmt->execute($orderParams);
    $orders = $stmt->fetchAll();
    
    $orderDetailId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
    $orderItems = [];
    if ($orderDetailId) {
        $stmt = $db->prepare('SELECT sku_name_snapshot, unit_price_minor, quantity FROM order_items WHERE order_id = :id');
        $stmt->execute(['id' => $orderDetailId]);
        $orderItems = $stmt->fetchAll();
    }
    
    $auditPage = max(0, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 0);
    $auditAction = $_GET['filter_action'] ?? '';
    $auditQuery = 'SELECT al.actor_type, al.action, al.entity_type, al.entity_id, al.created_at, u.display_name AS actor_name FROM audit_logs al LEFT JOIN users u ON u.id = al.actor_id';
    $auditParams = [];
    if ($auditAction !== '') {
        $auditQuery .= ' WHERE al.action = :action';
        $auditParams['action'] = $auditAction;
    }
    $auditQuery .= ' ORDER BY al.created_at DESC LIMIT 30 OFFSET ' . ($auditPage * 30);
    $stmt = $db->prepare($auditQuery);
    $stmt->execute($auditParams);
    $audits = $stmt->fetchAll();
} catch (Throwable $error) {
    http_response_code(403);
    exit('Forbidden.');
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ZiuLabs Admin</title><link rel="icon" type="image/png" href="/assets/logos/ziulabs-icon.png"><link rel="shortcut icon" href="/favicon.ico"><link rel="apple-touch-icon" href="/assets/logos/ziulabs-icon.png"><link rel="stylesheet" href="/admin/style.css"></head><body><header class="topbar"><a class="brand" href="/admin/">Z<span> L</span></a><form method="post" action="?action=logout"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><button class="logout" type="submit">Keluar</button></form></header><main class="admin-shell"><div class="heading"><div><div class="eyebrow">ZIULABS / CONTROL ROOM</div><h1>Good morning, operator.</h1><p>Katalog, order, dan audit dalam satu tempat.</p></div><a class="public-link" href="/" target="_blank" rel="noopener">Lihat website ↗</a></div><nav class="tabs"><a class="<?= $tab === 'overview' ? 'active' : '' ?>" href="/admin/?tab=overview">Overview</a><a class="<?= $tab === 'products' ? 'active' : '' ?>" href="/admin/?tab=products">Products & SKU</a><a class="<?= $tab === 'credentials' ? 'active' : '' ?>" href="/admin/?tab=credentials">Credential Pool</a><a class="<?= $tab === 'orders' ? 'active' : '' ?>" href="/admin/?tab=orders">Order queue</a><a class="<?= $tab === 'audit' ? 'active' : '' ?>" href="/admin/?tab=audit">Audit log</a></nav><section class="metrics"><div><small>CATALOG</small><strong><?= $counts['products'] ?></strong><span>published records</span></div><div><small>INVENTORY</small><strong><?= $counts['available'] ?></strong><span>SKU tersedia</span></div><div><small>CREDENTIAL POOL</small><strong><?= $counts['pool_available'] ?></strong><span>stok siap kirim</span></div><div><small>ATTENTION</small><strong><?= $counts['orders'] ?></strong><span>order perlu action</span></div></section><?php if ($tab === 'products'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">CATALOG / SKU</div><h2>Inventory control</h2></div><span class="muted">Harga tersimpan sebagai integer minor unit.</span></div><div class="table-wrap"><table><thead><tr><th>Product</th><th>Label</th><th>Price</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($products as $product): ?><tr>
<td>
    <form class="inline-form" method="post" action="?action=edit_product" style="display:flex;gap:4px">
        <input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>">
        <input type="hidden" name="product_id" value="<?= h($product['product_id']) ?>">
        <input type="hidden" name="category" value="<?= h($product['category']) ?>">
        <input type="text" name="name" value="<?= h($product['name']) ?>" required style="max-width:150px">
        <button type="submit">✎</button>
    </form>
    <div style="margin-top:4px">
    <?php if ($product['status'] === 'draft'): ?>
        <form class="inline-form" method="post" action="?action=publish_product" style="display:inline"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><input type="hidden" name="product_id" value="<?= h($product['product_id']) ?>"><button type="submit" <?= $product['compliance_status'] !== 'verified' ? 'disabled title="Pending compliance"' : '' ?>>Publish</button></form>
    <?php else: ?>
        <form class="inline-form" method="post" action="?action=archive_product" style="display:inline"><input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>"><input type="hidden" name="product_id" value="<?= h($product['product_id']) ?>"><button type="submit">Archive</button></form>
    <?php endif; ?>
    </div>
</td>
<td>
    <form class="inline-form" method="post" action="?action=edit_sku" style="display:flex;gap:4px">
        <input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>">
        <input type="hidden" name="sku_id" value="<?= h($product['sku_id']) ?>">
        <input type="text" name="label" value="<?= h($product['label']) ?>" required style="max-width:100px">
</td>
<td>
        <?= h($product['currency']) ?> <input type="number" name="price_minor" value="<?= h($product['price_minor']) ?>" required style="width: 80px">
</td>
<td><span class="status <?= h($product['stock_status']) ?>"><?= h($product['stock_status']) ?></span></td>
<td>
        <select name="stock_status">
            <option value="available" <?= $product['stock_status'] === 'available' ? 'selected' : '' ?>>available</option>
            <option value="sold_out" <?= $product['stock_status'] === 'sold_out' ? 'selected' : '' ?>>sold_out</option>
            <option value="paused" <?= $product['stock_status'] === 'paused' ? 'selected' : '' ?>>paused</option>
        </select>
        <button type="submit">Simpan</button>
    </form>
</td>
</tr><?php endforeach; ?></tbody></table></div></section><?php elseif ($tab === 'credentials'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">FULFILLMENT / INVENTORY</div><h2>Credential Pool Management</h2></div><span class="muted">Stok akun dan lisensi instan untuk pengiriman otomatis saat QRIS terbayar.</span></div>
<div style="background:#f8fafc;border:1px solid var(--line);border-radius:8px;padding:20px;margin-bottom:24px">
<h3 style="font-size:15px;font-weight:700;margin:0 0 8px 0">+ Impor Massal Kredensial ke Pool</h3>
<p style="font-size:12px;color:var(--muted);margin:0 0 14px 0">Akun: <code>email:password</code> per baris. Lisensi / Invite: satu token/link per baris.</p>
<form method="post" action="?action=bulk_import_credentials">
<input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:10px">
<div><label style="margin:0 0 4px;font-size:11px">Pilih SKU Produk Target</label><select name="sku_id" required style="width:100%;padding:8px;border:1px solid var(--line);border-radius:4px"><?php foreach ($products as $p): ?><option value="<?= h($p['sku_id']) ?>"><?= h($p['name']) ?> [<?= h($p['label']) ?>]</option><?php endforeach; ?></select></div>
<div><label style="margin:0 0 4px;font-size:11px">Tipe Kredensial</label><select name="credential_type" required style="width:100%;padding:8px;border:1px solid var(--line);border-radius:4px"><option value="account">Akun (Email &amp; Password)</option><option value="license_key">Kode Lisensi / Serial</option><option value="invite_link">Link Undangan / Invite</option></select></div>
</div>
<div style="margin-bottom:10px"><label style="margin:0 0 4px;font-size:11px">Daftar Data Kredensial (Satu per baris)</label><textarea name="raw_text" required rows="4" placeholder="user1@example.com:Password123&#10;user2@example.com:Password456" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:4px;font-family:monospace;font-size:12px"></textarea></div>
<div style="display:flex;gap:10px;align-items:flex-end"><div style="flex:1"><label style="margin:0 0 4px;font-size:11px">Catatan Garansi / Panduan (Opsional)</label><input type="text" name="notes" placeholder="Contoh: Garansi 30 Hari via CS ZiuLabs." style="width:100%;padding:8px;border:1px solid var(--line);border-radius:4px;font-size:12px"></div><button type="submit" class="button light" style="margin-top:0;padding:10px 18px">+ Masukkan ke Pool</button></div>
</form>
</div>
<h3 style="font-size:15px;font-weight:700;margin:24px 0 12px 0">Status Stok Pool per SKU</h3>
<div class="table-wrap"><table><thead><tr><th>Produk &amp; Paket</th><th>Tersedia (Siap Kirim)</th><th>Terjual</th><th>Total Pool</th><th>Status SKU</th></tr></thead><tbody>
<?php foreach ($credentialSummary as $cs): ?><tr><td><strong><?= h($cs['product_name']) ?></strong> <span class="muted">[<?= h($cs['sku_label']) ?>]</span></td><td><span class="status <?= $cs['available_count'] > 0 ? 'available' : 'sold_out' ?>"><?= (int) $cs['available_count'] ?> unit</span></td><td><?= (int) $cs['assigned_count'] ?> unit</td><td><?= (int) $cs['total_pool'] ?> unit</td><td><span class="status <?= h($cs['stock_status']) ?>"><?= h($cs['stock_status']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div>
<h3 style="font-size:15px;font-weight:700;margin:32px 0 12px 0">Riwayat Kredensial Terbaru</h3>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Produk</th><th>Tipe</th><th>Detail Kredensial</th><th>Order</th><th>Status</th><th>Waktu</th></tr></thead><tbody>
<?php foreach ($recentCreds as $rc): ?><tr><td>#<?= h($rc['id']) ?></td><td><?= h($rc['product_name']) ?> <small class="muted">[<?= h($rc['sku_label']) ?>]</small></td><td><code><?= h($rc['credential_type']) ?></code></td><td><?php if (!empty($rc['account_email'])): ?><code><?= h($rc['account_email']) ?></code> (Pass: •••••)<?php elseif (!empty($rc['license_key'])): ?><code><?= h(substr($rc['license_key'], 0, 24)) ?>...</code><?php elseif (!empty($rc['invite_url'])): ?><a href="<?= h($rc['invite_url']) ?>" target="_blank" rel="noopener">Link Undangan ↗</a><?php endif; ?></td><td><?php if (!empty($rc['order_public_id'])): ?><a href="?tab=orders&order_id=<?= h($rc['assigned_order_id']) ?>"><strong><?= h($rc['order_public_id']) ?></strong></a><?php else: ?><span class="muted">-</span><?php endif; ?></td><td><span class="status <?= $rc['status'] === 'available' ? 'available' : 'paid' ?>"><?= h($rc['status']) ?></span></td><td><?= h($rc['assigned_at'] ?: $rc['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
</section><?php elseif ($tab === 'orders'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">OPERATIONS / ORDERS</div><h2>Order queue</h2></div>
<form method="get" action="/admin/"><input type="hidden" name="tab" value="orders"><select name="filter" onchange="this.form.submit()"><option value="all">All Status</option><option value="inquiry" <?= ($orderFilter ?? '') === 'inquiry' ? 'selected' : '' ?>>Inquiry</option><option value="awaiting_payment" <?= ($orderFilter ?? '') === 'awaiting_payment' ? 'selected' : '' ?>>Awaiting Payment</option><option value="manual_review" <?= ($orderFilter ?? '') === 'manual_review' ? 'selected' : '' ?>>Manual Review</option></select></form>
</div>
<?php if (!empty($orderItems)): ?>
<div class="panel" style="margin:16px 0;background:#fff">
    <strong>Items for Order #<?= h($orderDetailId) ?></strong>
    <ul style="margin:8px 0"><?php foreach ($orderItems as $item): ?><li><?= h($item['quantity']) ?>x <?= h($item['sku_name_snapshot']) ?> @ <?= number_format((int) $item['unit_price_minor'], 0, ',', '.') ?></li><?php endforeach; ?></ul>
    <form method="post" action="?action=operator_note" style="margin-top:10px;display:flex;gap:8px">
        <input type="hidden" name="_csrf" value="<?= h(Csrf::token()) ?>">
        <input type="hidden" name="order_id" value="<?= h($orderDetailId) ?>">
        <input type="text" name="note" placeholder="Add operator note (audit log)" required maxlength="500" style="flex:1">
        <button type="submit">Save Note</button>
    </form>
</div>
<?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><a href="?tab=orders&order_id=<?= h($order['id']) ?>"><strong><?= h($order['public_id']) ?></strong></a></td><td><?= h($order['customer_name'] ?: 'Anonymous') ?><br><small><?= h($order['customer_phone']) ?></small></td><td><span class="status <?= h($order['status']) ?>"><?= h($order['status']) ?></span></td><td><?= h($order['currency']) ?> <?= number_format((int) $order['total_minor'], 0, ',', '.') ?></td><td><?= h($order['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></section><?php elseif ($tab === 'audit'): ?><section class="panel"><div class="panel-head"><div><div class="eyebrow">SECURITY / AUDIT</div><h2>Recent audit events</h2></div>
<form method="get" action="/admin/" style="display:flex;gap:8px"><input type="hidden" name="tab" value="audit"><input type="text" name="filter_action" placeholder="Filter action..." value="<?= h($auditAction ?? '') ?>"><button type="submit">Filter</button></form>
</div><div class="table-wrap"><table><thead><tr><th>Actor</th><th>Action</th><th>Entity</th><th>Time</th></tr></thead><tbody><?php foreach ($audits as $audit): ?><tr><td><?= h($audit['actor_name'] ?: ($audit['actor_type'] === 'system' ? 'System' : 'Unknown')) ?></td><td><?= h($audit['action']) ?></td><td><?= h($audit['entity_type']) ?> #<?= h($audit['entity_id']) ?></td><td><?= h($audit['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div style="margin-top:16px"><a class="button light" href="?tab=audit&filter_action=<?= h($auditAction) ?>&page=<?= max(0, ($auditPage ?? 0) - 1) ?>">&larr; Prev</a> <a class="button light" href="?tab=audit&filter_action=<?= h($auditAction) ?>&page=<?= ($auditPage ?? 0) + 1 ?>">Next &rarr;</a></div>
</section><?php else: ?><section class="dashboard-grid"><div class="panel"><div class="eyebrow">NEXT ACTIONS</div><h2>Keep the catalog clean.</h2><p class="muted">Verifikasi compliance sebelum publish, pastikan stok benar, dan jangan simpan credential produk pada order atau notes.</p><a class="button" href="/admin/?tab=products">Kelola SKU →</a></div><div class="panel dark"><div class="eyebrow">RELEASE GATE</div><h2>Payment belum live.</h2><p>Aktifkan hanya setelah provider sandbox, signed webhook, refund SOP, dan reconciliation lulus.</p><a class="button light" href="/admin/?tab=orders">Lihat order →</a></div></section><?php endif; ?></main></body></html>
