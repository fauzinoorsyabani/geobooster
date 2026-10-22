<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use GeoBooster\Security\Database;

try {
    $db = Database::connect();

    echo "Updating products and SKUs in database...\n";

    // 1. Set all SKUs to sold_out first
    $db->exec("UPDATE skus SET stock_status = 'sold_out'");
    echo "Set all SKUs to sold_out.\n";

    // 2. Find Gemini Pro SKU and set price to Rp50.000 (5000000 minor) and stock_status = 'available'
    $stmt = $db->prepare("SELECT s.id, p.name FROM skus s JOIN products p ON p.id = s.product_id WHERE p.name LIKE '%Gemini Pro%' OR s.label LIKE '%Gemini%' LIMIT 1");
    $stmt->execute();
    $gemini = $stmt->fetch();

    if ($gemini) {
        $geminiSkuId = (int) $gemini['id'];
        $db->prepare("UPDATE skus SET price_minor = 5000000, stock_status = 'available' WHERE id = :id")->execute(['id' => $geminiSkuId]);
        echo "Updated Gemini Pro SKU #{$geminiSkuId} ({$gemini['name']}) to Rp50.000 (5,000,000 minor) & available.\n";
    } else {
        echo "Warning: Gemini Pro SKU not found by query, checking all skus...\n";
    }

    // 3. Print verification table
    $rows = $db->query("SELECT s.id, p.name, s.label, s.price_minor, s.stock_status FROM skus s JOIN products p ON p.id = s.product_id ORDER BY s.id ASC")->fetchAll();
    foreach ($rows as $r) {
        $price = 'Rp' . number_format($r['price_minor'] / 100, 0, ',', '.');
        echo sprintf("SKU #%d | %s (%s) | %s | %s\n", $r['id'], $r['name'], $r['label'], $price, $r['stock_status']);
    }

    echo "Done!\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
