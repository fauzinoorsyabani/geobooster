<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Database;

header('Content-Type: application/json; charset=utf-8');

$publicId = trim((string) ($_GET['id'] ?? ''));

if ($publicId === '') {
    echo json_encode(['status' => 'not_found']);
    exit;
}

try {
    $db = Database::connect();
    $stmt = $db->prepare('SELECT status FROM orders WHERE public_id = :id LIMIT 1');
    $stmt->execute(['id' => $publicId]);
    $status = $stmt->fetchColumn();

    echo json_encode(['status' => $status ?: 'not_found']);
    exit;
} catch (Throwable) {
    echo json_encode(['status' => 'error']);
    exit;
}
