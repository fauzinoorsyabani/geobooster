<?php

declare(strict_types=1);

use GeoBooster\Security\Database;

// Attempt to load live products from MySQL database
try {
    $db = Database::connect();
    $stmt = $db->query(
        "SELECT p.id AS product_id, p.slug, p.name, p.category, p.description,
                s.id AS sku_id, s.label AS sku_label, s.price_minor, s.currency, s.stock_status, s.source_reference
         FROM products p
         JOIN skus s ON s.product_id = p.id
         WHERE p.status = 'published'
         ORDER BY p.id ASC"
    );
    $rows = $stmt->fetchAll();

    if (!empty($rows)) {
        $liveProducts = [];
        foreach ($rows as $row) {
            $priceInt = (int) ($row['price_minor'] / 100);
            $isGemini = str_contains(strtolower((string) $row['name']), 'gemini');
            $isAvailable = $isGemini && ($row['stock_status'] === 'available');
            
            $liveProducts[] = [
                'id' => (int) $row['product_id'],
                'sku_id' => (int) $row['sku_id'],
                'name' => $row['name'],
                'category' => $row['category'],
                'price' => $isGemini ? ('Rp' . number_format($priceInt, 0, ',', '.')) : 'Rp XXX',
                'original_price' => $isGemini ? 'Rp100.000' : null,
                'discount' => $isGemini ? '50%' : null,
                'price_minor' => $isGemini ? (int) $row['price_minor'] : 0,
                'usd' => $isGemini ? ('$' . number_format($priceInt / 16000, 2)) : '$---',
                'available' => $isAvailable,
                'sku' => $row['source_reference'] ?: ('SKU-' . $row['sku_id']),
            ];
        }
        return $liveProducts;
    }
} catch (Throwable) {
    // Fallback to static data if database is offline or not yet configured
}

// Fallback static catalogue (Gemini Pro Rp50.000 with 50% discount, other products Rp XXX and Habis)
return [
    ['id' => 1, 'sku_id' => 1, 'name' => 'Gemini Pro 18 Months Head (Link)', 'category' => 'AI & Productivity', 'price' => 'Rp50.000', 'original_price' => 'Rp100.000', 'discount' => '50%', 'price_minor' => 5000000, 'usd' => '$3.12', 'available' => true, 'sku' => 'AI-GEM-18M'],
    ['id' => 2, 'sku_id' => 2, 'name' => 'CapCut Pro 7D FW', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAP-07D'],
    ['id' => 3, 'sku_id' => 3, 'name' => 'Microsoft Office 365 Plus 1 Year', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'BS-MS-365'],
    ['id' => 4, 'sku_id' => 4, 'name' => 'Adobe Express 12M', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-ADB-12M'],
    ['id' => 4, 'sku_id' => 5, 'name' => 'Adobe Express 12M Pro', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-ADB-12P'],
    ['id' => 5, 'sku_id' => 6, 'name' => 'Duolingo Super 12M', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'ED-DUO-12M'],
    ['id' => 6, 'sku_id' => 7, 'name' => 'Canva Pro 2 YRS FW', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAN-02Y'],
    ['id' => 7, 'sku_id' => 8, 'name' => 'iLovePDF Premium 1YR', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'BS-ILV-01Y'],
    ['id' => 8, 'sku_id' => 9, 'name' => 'Gmails Accounts', 'category' => 'Utilities', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'UT-GML-ACC'],
    ['id' => 9, 'sku_id' => 10, 'name' => 'Crunchyroll MegaFan 15 Day', 'category' => 'Entertainment', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'EN-CRU-15D'],
    ['id' => 10, 'sku_id' => 11, 'name' => 'Avira Prime 3 Months', 'category' => 'Utilities', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'UT-AVR-03M'],
    ['id' => 11, 'sku_id' => 12, 'name' => 'Notion Plus 12M', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'BS-NTN-12M'],
    ['id' => 12, 'sku_id' => 13, 'name' => 'EDX Premium 12 Months', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'ED-EDX-12M'],
    ['id' => 13, 'sku_id' => 14, 'name' => 'CapCut Pro 1 Month FW', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAP-01M'],
    ['id' => 14, 'sku_id' => 15, 'name' => 'Prime Video 6 Months', 'category' => 'Entertainment', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'EN-PRM-06M'],
    ['id' => 15, 'sku_id' => 16, 'name' => 'CapCut Pro 1600 Credits', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAP-16C'],
    ['id' => 16, 'sku_id' => 17, 'name' => 'JetBrains Edu Pack 12M', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'BS-JTB-12M'],
    ['id' => 17, 'sku_id' => 18, 'name' => 'Figma Pro Edu 2YRS', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-FIG-02Y'],
    ['id' => 18, 'sku_id' => 19, 'name' => 'Framer AI 1 Year', 'category' => 'AI & Productivity', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'AI-FRM-01Y'],
    ['id' => 19, 'sku_id' => 20, 'name' => 'CapCut Pro 6 Months', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAP-06M'],
    ['id' => 20, 'sku_id' => 21, 'name' => 'Miro Lifetime Panel 100 Invite', 'category' => 'Business & Education', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'BS-MIR-LFT'],
    ['id' => 21, 'sku_id' => 22, 'name' => 'Autodesk Admin 3000 Invite', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-ATD-30I'],
    ['id' => 22, 'sku_id' => 23, 'name' => 'YT Premium 1 Bulan Invit', 'category' => 'Entertainment', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'EN-YTP-01M'],
    ['id' => 23, 'sku_id' => 24, 'name' => 'HBO Max 3 Months', 'category' => 'Entertainment', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'EN-HBO-03M'],
    ['id' => 24, 'sku_id' => 25, 'name' => 'Super Grok 9-10 Days (W3D)', 'category' => 'AI & Productivity', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'AI-GRK-10D'],
    ['id' => 25, 'sku_id' => 26, 'name' => 'Canva Pro Admin (500 Invitations)', 'category' => 'Creative & Design', 'price' => 'Rp XXX', 'price_minor' => 0, 'usd' => '$---', 'available' => false, 'sku' => 'CR-CAN-50I'],
];
