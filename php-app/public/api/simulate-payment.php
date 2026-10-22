<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\AuditLogger;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$publicId = trim((string) ($_POST['order_id'] ?? ''));

if ($publicId === '') {
    http_response_code(400);
    exit('ID Pesanan tidak valid.');
}

try {
    $db = Database::connect();
    $stmt = $db->prepare('SELECT id, public_id, customer_email, total_minor, currency FROM orders WHERE public_id = :id LIMIT 1');
    $stmt->execute(['id' => $publicId]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        exit('Pesanan tidak ditemukan.');
    }

    $db->beginTransaction();

    // 1. Update order status to paid
    $db->prepare('UPDATE orders SET status = \'paid\' WHERE id = :id')->execute(['id' => $order['id']]);

    // 2. Insert into payments table
    $paymentRef = 'pay_' . rtrim(strtr(base64_encode(random_bytes(14)), '+/', '-_'), '=');
    $payStmt = $db->prepare(
        'INSERT INTO payments (order_id, provider, provider_payment_id, amount_minor, currency, status, verified_at) 
         VALUES (:order_id, \'qris_sandbox\', :ref, :amount, :currency, \'paid\', CURRENT_TIMESTAMP)'
    );
    $payStmt->execute([
        'order_id' => $order['id'],
        'ref' => $paymentRef,
        'amount' => $order['total_minor'],
        'currency' => $order['currency'],
    ]);

    // 3. Automated dispatch from Credential Pool
    $engine = new \GeoBooster\Fulfillment\FulfillmentEngine($db);
    $dispatchResult = $engine->dispatchOrder((int) $order['id']);

    // Audit log
    (new AuditLogger($db))->record(null, 'order_simulated_paid', 'order', (int) $order['id'], [
        'public_id' => $publicId,
        'amount_minor' => $order['total_minor'],
    ]);

    $db->commit();

    header('Location: /order/payment.php?id=' . urlencode($publicId), true, 303);
    exit;

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    exit('Terjadi kesalahan saat simulasi pembayaran: ' . htmlspecialchars($e->getMessage()));
}
