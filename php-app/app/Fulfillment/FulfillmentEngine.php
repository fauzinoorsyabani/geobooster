<?php

declare(strict_types=1);

namespace GeoBooster\Fulfillment;

use PDO;
use RuntimeException;
use GeoBooster\Security\AuditLogger;

final class FulfillmentEngine
{
    public function __construct(private readonly PDO $db) {}

    /**
     * Atomically dispatches credentials from the pool for a paid order.
     * Uses pessimistic locking (FOR UPDATE) to prevent double allocation.
     *
     * @param int $orderId
     * @return array{success: bool, fully_fulfilled: bool, items: array, message: string}
     */
    public function dispatchOrder(int $orderId): array
    {
        // 1. Fetch order details
        $stmtOrder = $this->db->prepare('SELECT id, public_id, customer_name, customer_email, status FROM orders WHERE id = :id LIMIT 1');
        $stmtOrder->execute(['id' => $orderId]);
        $order = $stmtOrder->fetch();

        if (!$order) {
            throw new RuntimeException("Order #{$orderId} tidak ditemukan.");
        }

        // 2. Fetch order items
        $stmtItems = $this->db->prepare('SELECT id, sku_id, sku_name_snapshot, quantity FROM order_items WHERE order_id = :order_id');
        $stmtItems->execute(['order_id' => $orderId]);
        $items = $stmtItems->fetchAll();

        if (empty($items)) {
            throw new RuntimeException("Order #{$orderId} tidak memiliki item.");
        }

        $allFulfilled = true;
        $fulfilledDetails = [];

        foreach ($items as $item) {
            $skuId = (int) $item['sku_id'];
            $skuName = (string) $item['sku_name_snapshot'];

            // Query an available credential with pessimistic lock
            $stmtCred = $this->db->prepare(
                'SELECT * FROM sku_credentials 
                 WHERE sku_id = :sku_id AND status = \'available\' 
                 ORDER BY id ASC LIMIT 1 FOR UPDATE'
            );
            $stmtCred->execute(['sku_id' => $skuId]);
            $credential = $stmtCred->fetch();

            if ($credential) {
                $credId = (int) $credential['id'];

                // Assign credential to this order
                $stmtAssign = $this->db->prepare(
                    'UPDATE sku_credentials 
                     SET status = \'assigned\', assigned_order_id = :order_id, assigned_at = CURRENT_TIMESTAMP 
                     WHERE id = :id'
                );
                $stmtAssign->execute(['order_id' => $orderId, 'id' => $credId]);

                // Construct clean customer payload
                $credType = (string) $credential['credential_type'];
                $itemPayload = [
                    'item_id' => (int) $item['id'],
                    'sku_id' => $skuId,
                    'sku_name' => $skuName,
                    'type' => $credType,
                    'account_email' => $credential['account_email'] ?: null,
                    'account_password' => $credential['account_password'] ?: null,
                    'license_key' => $credential['license_key'] ?: null,
                    'invite_url' => $credential['invite_url'] ?: null,
                    'additional_notes' => $credential['additional_notes'] ?: 'Simpan kredensial ini baik-baik. Dilarang mengubah data akun tanpa izin.',
                    'delivered_at' => date('Y-m-d H:i:s'),
                ];

                $fulfilledDetails[] = $itemPayload;

                // Check remaining pool stock for this SKU
                $stmtStock = $this->db->prepare('SELECT COUNT(*) FROM sku_credentials WHERE sku_id = :sku_id AND status = \'available\'');
                $stmtStock->execute(['sku_id' => $skuId]);
                $remainingStock = (int) $stmtStock->fetchColumn();

                if ($remainingStock === 0) {
                    $this->db->prepare('UPDATE skus SET stock_status = \'sold_out\' WHERE id = :id')->execute(['id' => $skuId]);
                }

            } else {
                // Stock depleted for this SKU - require operator intervention
                $allFulfilled = false;
                $fulfilledDetails[] = [
                    'item_id' => (int) $item['id'],
                    'sku_id' => $skuId,
                    'sku_name' => $skuName,
                    'type' => 'manual_queue',
                    'status' => 'pending_operator',
                    'message' => 'Stok instan sedang diisi ulang. Akun Anda sedang diproses oleh staf ZiuLabs.',
                ];
            }
        }

        $jsonPayload = json_encode($fulfilledDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 3. Update or create record in fulfillments table
        $stmtFul = $this->db->prepare(
            'INSERT INTO fulfillments (order_id, fulfillment_type, payload_encrypted, status, fulfilled_at)
             VALUES (:order_id, :type, :payload, :status, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE 
               payload_encrypted = VALUES(payload_encrypted),
               status = VALUES(status),
               fulfilled_at = CURRENT_TIMESTAMP'
        );
        $stmtFul->execute([
            'order_id' => $orderId,
            'type' => $allFulfilled ? 'instant_pool' : 'manual_queue',
            'payload' => $jsonPayload,
            'status' => $allFulfilled ? 'delivered' : 'pending',
        ]);

        // 4. Update fulfillment_tasks table
        $stmtTask = $this->db->prepare(
            'INSERT INTO fulfillment_tasks (order_id, status, credentials_payload, failure_reason, fulfilled_at)
             VALUES (:order_id, :status, :payload, :reason, ' . ($allFulfilled ? 'CURRENT_TIMESTAMP' : 'NULL') . ')
             ON DUPLICATE KEY UPDATE
               status = VALUES(status),
               credentials_payload = VALUES(credentials_payload),
               failure_reason = VALUES(failure_reason),
               fulfilled_at = VALUES(fulfilled_at)'
        );
        $stmtTask->execute([
            'order_id' => $orderId,
            'status' => $allFulfilled ? 'fulfilled' : 'queued',
            'payload' => $jsonPayload,
            'reason' => $allFulfilled ? null : 'Stok pool habis. Memerlukan pengisian kredensial operator.',
        ]);

        // 5. Update order status
        $newOrderStatus = $allFulfilled ? 'fulfilled' : 'paid';
        $this->db->prepare('UPDATE orders SET status = :status WHERE id = :id')->execute([
            'status' => $newOrderStatus,
            'id' => $orderId,
        ]);

        // Audit log
        (new AuditLogger($this->db))->record(null, 'order_fulfilled_auto', 'order', $orderId, [
            'public_id' => $order['public_id'],
            'all_fulfilled' => $allFulfilled,
            'items_count' => count($items),
        ]);

        return [
            'success' => true,
            'fully_fulfilled' => $allFulfilled,
            'items' => $fulfilledDetails,
            'message' => $allFulfilled 
                ? 'Semua item pesanan berhasil difulfill otomatis dari credential pool.'
                : 'Sebagian atau semua item sedang mengantre fulfillment staf.',
        ];
    }

    /**
     * Add single credential to pool
     */
    public function addCredential(int $skuId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO sku_credentials 
             (sku_id, credential_type, account_email, account_password, license_key, invite_url, additional_notes, status)
             VALUES (:sku_id, :type, :email, :password, :license, :invite, :notes, \'available\')'
        );
        $stmt->execute([
            'sku_id' => $skuId,
            'type' => $data['type'] ?? 'account',
            'email' => !empty($data['email']) ? trim((string) $data['email']) : null,
            'password' => !empty($data['password']) ? trim((string) $data['password']) : null,
            'license' => !empty($data['license']) ? trim((string) $data['license']) : null,
            'invite' => !empty($data['invite']) ? trim((string) $data['invite']) : null,
            'notes' => !empty($data['notes']) ? trim((string) $data['notes']) : null,
        ]);

        $id = (int) $this->db->lastInsertId();

        // Ensure SKU is marked available
        $this->db->prepare('UPDATE skus SET stock_status = \'available\' WHERE id = :id')->execute(['id' => $skuId]);

        return $id;
    }

    /**
     * Bulk import credentials from multiline text
     */
    public function bulkImport(int $skuId, string $type, string $rawText, ?string $defaultNotes = null): int
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText)) ?: [];
        $inserted = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $email = null;
            $password = null;
            $license = null;
            $invite = null;

            if ($type === 'account') {
                if (str_contains($line, ':')) {
                    [$email, $password] = explode(':', $line, 2);
                } elseif (str_contains($line, '|')) {
                    [$email, $password] = explode('|', $line, 2);
                } elseif (str_contains($line, "\t")) {
                    [$email, $password] = explode("\t", $line, 2);
                } else {
                    $parts = preg_split('/\s+/', $line, 2);
                    $email = $parts[0] ?? '';
                    $password = $parts[1] ?? '';
                }
                $email = trim((string) $email);
                $password = trim((string) $password);
            } elseif ($type === 'license_key') {
                $license = $line;
            } elseif ($type === 'invite_link') {
                $invite = $line;
            } else {
                $license = $line;
            }

            if ($email || $password || $license || $invite) {
                $this->addCredential($skuId, [
                    'type' => $type,
                    'email' => $email,
                    'password' => $password,
                    'license' => $license,
                    'invite' => $invite,
                    'notes' => $defaultNotes,
                ]);
                $inserted++;
            }
        }

        return $inserted;
    }

    /**
     * Get stock inventory summary across all SKUs
     */
    public function getStockSummary(): array
    {
        $sql = 'SELECT s.id AS sku_id, s.label AS sku_label, p.name AS product_name, s.stock_status,
                       COUNT(c.id) AS total_pool,
                       SUM(CASE WHEN c.status = \'available\' THEN 1 ELSE 0 END) AS available_count,
                       SUM(CASE WHEN c.status = \'assigned\' THEN 1 ELSE 0 END) AS assigned_count
                FROM skus s
                JOIN products p ON p.id = s.product_id
                LEFT JOIN sku_credentials c ON c.sku_id = s.id
                GROUP BY s.id, s.label, p.name, s.stock_status
                ORDER BY p.name ASC, s.label ASC';

        return $this->db->query($sql)->fetchAll();
    }
}
