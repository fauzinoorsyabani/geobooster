#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use GeoBooster\Security\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$direction = $argv[1] ?? 'up';
$allowed = ['up', 'seed', 'seed-products', 'down'];
if (!in_array($direction, $allowed, true)) {
    fwrite(STDERR, "Usage: php bin/migrate.php [up|seed|seed-products|down]\n");
    exit(1);
}

$base = dirname(__DIR__) . '/database';

// Map direction -> array of SQL file paths to process
$files = match ($direction) {
    'up'   => [
        $base . '/migrations/001_initial.sql',
        $base . '/migrations/002_login_attempts.sql',
    ],
    'seed'          => [$base . '/seeds/001_rbac.sql'],
    'seed-products' => [$base . '/seeds/002_products.sql'],
    'down'          => [
        $base . '/rollback/002_login_attempts_down.sql',
        $base . '/rollback/001_initial_down.sql',
    ],
};

try {
    $db = Database::connect();

    // M1-T01: Bootstrap migration tracker — idempotent, always runs first.
    $db->exec(
        "CREATE TABLE IF NOT EXISTS schema_migrations (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            filename   VARCHAR(255)    NOT NULL,
            applied_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_schema_migrations_filename (filename)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    foreach ($files as $filePath) {
        $filename = basename($filePath);

        if (!file_exists($filePath)) {
            fwrite(STDERR, "[warn]  {$filename} not found — skipping.\n");
            continue;
        }

        if ($direction === 'down') {
            // Rollback: run DROP file, then remove from tracker
            $db->exec(file_get_contents($filePath));
            $db->prepare('DELETE FROM schema_migrations WHERE filename = :filename')
               ->execute(['filename' => $filename]);
            fwrite(STDOUT, "[down]  {$filename} rolled back.\n");
            continue;
        }

        // Forward: skip if already applied
        $check = $db->prepare('SELECT COUNT(*) FROM schema_migrations WHERE filename = :filename');
        $check->execute(['filename' => $filename]);
        if ((int) $check->fetchColumn() > 0) {
            fwrite(STDOUT, "[skip]  {$filename} already applied.\n");
            continue;
        }

        $db->exec(file_get_contents($filePath));
        $db->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)')
           ->execute(['filename' => $filename]);
        fwrite(STDOUT, "[ok]    {$filename} applied.\n");
    }
} catch (Throwable $error) {
    fwrite(STDERR, "Migration failed: {$error->getMessage()}\n");
    exit(1);
}
