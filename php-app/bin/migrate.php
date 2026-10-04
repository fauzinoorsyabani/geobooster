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
if (!in_array($direction, ['up', 'seed', 'down'], true)) {
    fwrite(STDERR, "Usage: php bin/migrate.php [up|seed|down]\n");
    exit(1);
}

$path = match ($direction) {
    'up' => dirname(__DIR__) . '/database/migrations/001_initial.sql',
    'seed' => dirname(__DIR__) . '/database/seeds/001_rbac.sql',
    'down' => dirname(__DIR__) . '/database/rollback/001_initial_down.sql',
};

try {
    $db = Database::connect();
    $db->exec(file_get_contents($path));
    fwrite(STDOUT, "Migration {$direction} completed.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Migration failed: {$error->getMessage()}\n");
    exit(1);
}
