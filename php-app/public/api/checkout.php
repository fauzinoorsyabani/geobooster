<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\Csrf;
use GeoBooster\Security\AuditLogger;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

try {
    Csrf::start();
    
    // Verify CSRF token
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    Csrf::verify(is_string($token) ? $token : null);

    $db = Database::connect();

    $cartItemsRaw = trim((string) ($_POST['cart_items'] ?? ''));
    $cartItems = [];
    if ($cartItemsRaw !== '') {
        $cartItems = json_decode($cartItemsRaw, true) ?: [];
    }

    $skuId = filter_input(INPUT_POST, 'sku_id', FILTER_VALIDATE_INT);
    $customerName = trim((string) ($_POST['customer_name'] ?? ''));
    $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
    $customerEmail = strtolower(trim((string) ($_POST['customer_email'] ?? '')));
    $paymentMethod = (string) ($_POST['payment_method'] ?? 'qris');

    if ($customerName === '' || strlen($customerName) > 120) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Nama lengkap wajib diisi (maks. 120 karakter).']);
        exit;
    }

    if ($customerPhone === '' || strlen($customerPhone) < 9 || strlen($customerPhone) > 20) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Nomor WhatsApp valid wajib diisi (9-20 digit).']);
        exit;
    }

    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Alamat email tidak valid.']);
        exit;
    }

    if (!in_array($paymentMethod, ['qris', 'va'], true)) {
        $paymentMethod = 'qris';
    }

    // Resolve items to order (either from cart or single SKU)
    $itemsToOrder = [];
    $totalMinor = 0;
    $currency = 'IDR';
    $termsVersion = 'v1.0';

    if (!empty($cartItems)) {
        // Multi-item cart checkout
        foreach ($cartItems as $cItem) {
            $cSkuId = (int) ($cItem['sku_id'] ?? 0);
            $cQty = max(1, (int) ($cItem['quantity'] ?? 1));
            if ($cSkuId <= 0) continue;

            $stmt = $db->prepare(
                'SELECT s.id AS sku_id, s.price_minor, s.currency, s.stock_status, s.terms_version, p.name AS product_name, s.label AS sku_label 
                 FROM skus s 
                 JOIN products p ON p.id = s.product_id 
                 WHERE s.id = :sku_id LIMIT 1'
            );
            $stmt->execute(['sku_id' => $cSkuId]);
            $skuRow = $stmt->fetch();

            if (!$skuRow) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => "Produk (SKU #{$cSkuId}) tidak ditemukan."]);
                exit;
            }

            if ($skuRow['stock_status'] !== 'available') {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => "Maaf, stok produk '{$skuRow['product_name']}' sedang habis."]);
                exit;
            }

            $currency = $skuRow['currency'];
            $termsVersion = $skuRow['terms_version'];
            $lineName = $skuRow['product_name'] . ($skuRow['sku_label'] !== 'Default' ? ' (' . $skuRow['sku_label'] . ')' : '');
            $totalMinor += ((int) $skuRow['price_minor']) * $cQty;

            $itemsToOrder[] = [
                'sku_id' => (int) $skuRow['sku_id'],
                'name' => $lineName,
                'price_minor' => (int) $skuRow['price_minor'],
                'quantity' => $cQty,
            ];
        }
    } else {
        // Single SKU checkout
        if (!$skuId || $skuId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Produk tidak valid.']);
            exit;
        }

        $stmt = $db->prepare(
            'SELECT s.id AS sku_id, s.price_minor, s.currency, s.stock_status, s.terms_version, p.name AS product_name, s.label AS sku_label 
             FROM skus s 
             JOIN products p ON p.id = s.product_id 
             WHERE s.id = :sku_id LIMIT 1'
        );
        $stmt->execute(['sku_id' => $skuId]);
        $skuRow = $stmt->fetch();

        if (!$skuRow) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'SKU produk tidak ditemukan.']);
            exit;
        }

        if ($skuRow['stock_status'] !== 'available') {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Maaf, stok produk ini sedang habis atau tidak tersedia.']);
            exit;
        }

        $currency = $skuRow['currency'];
        $termsVersion = $skuRow['terms_version'];
        $lineName = $skuRow['product_name'] . ($skuRow['sku_label'] !== 'Default' ? ' (' . $skuRow['sku_label'] . ')' : '');
        $totalMinor = (int) $skuRow['price_minor'];

        $itemsToOrder[] = [
            'sku_id' => (int) $skuRow['sku_id'],
            'name' => $lineName,
            'price_minor' => (int) $skuRow['price_minor'],
            'quantity' => 1,
        ];
    }

    if (empty($itemsToOrder)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Keranjang pesanan kosong.']);
        exit;
    }

    // Begin Order creation
    $db->beginTransaction();

    // Generate 26-char public ID (e.g. ord_ + 22 chars)
    $publicId = 'ord_' . rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    $summaryItemName = count($itemsToOrder) === 1 
        ? $itemsToOrder[0]['name'] 
        : $itemsToOrder[0]['name'] . ' (+' . (count($itemsToOrder) - 1) . ' item lainnya)';

    $orderStmt = $db->prepare(
        'INSERT INTO orders (public_id, customer_name, customer_email, customer_phone, status, payment_method, total_minor, currency, terms_version, expires_at) 
         VALUES (:public_id, :customer_name, :customer_email, :customer_phone, \'awaiting_payment\', :payment_method, :total_minor, :currency, :terms_version, NOW() + INTERVAL 30 MINUTE)'
    );
    $orderStmt->execute([
        'public_id' => $publicId,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone,
        'payment_method' => $paymentMethod,
        'total_minor' => $totalMinor,
        'currency' => $currency,
        'terms_version' => $termsVersion,
    ]);

    $orderId = (int) $db->lastInsertId();

    $itemStmt = $db->prepare(
        'INSERT INTO order_items (order_id, sku_id, sku_name_snapshot, unit_price_minor, quantity) 
         VALUES (:order_id, :sku_id, :sku_name, :price_minor, :quantity)'
    );
    foreach ($itemsToOrder as $ordItem) {
        $itemStmt->execute([
            'order_id' => $orderId,
            'sku_id' => $ordItem['sku_id'],
            'sku_name' => $ordItem['name'],
            'price_minor' => $ordItem['price_minor'],
            'quantity' => $ordItem['quantity'],
        ]);
    }

    // Audit log
    $userId = $_SESSION['user_id'] ?? null;
    (new AuditLogger($db))->record($userId ? (int) $userId : null, 'order_created', 'order', $orderId, [
        'public_id' => $publicId,
        'item_count' => count($itemsToOrder),
        'amount_minor' => $totalMinor,
    ]);

    $db->commit();

    // Trigger automated WhatsApp invoice notification
    $waUrl = null;
    try {
        $notifier = new \GeoBooster\Notifications\WhatsAppNotifier($db);
        $waRes = $notifier->sendInvoiceNotification([
            'id' => $orderId,
            'public_id' => $publicId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'total_minor' => $totalMinor,
        ], $summaryItemName);
        $waUrl = $waRes['direct_url'] ?? null;
    } catch (\Throwable) {
        // Non-blocking notification failure
    }

    echo json_encode([
        'success' => true,
        'order_id' => $publicId,
        'redirect_url' => '/order/payment.php?id=' . urlencode($publicId),
        'whatsapp_url' => $waUrl,
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Gagal memproses pesanan: ' . $e->getMessage()]);
    exit;
}
