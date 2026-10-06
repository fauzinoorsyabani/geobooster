#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use GeoBooster\Security\AuditLogger;
use GeoBooster\Security\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$email = strtolower(trim((string) ($argv[1] ?? '')));
$name = trim((string) ($argv[2] ?? 'Geo Booster Owner'));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php bin/create-owner.php owner@example.com \"Owner Name\"\n");
    exit(1);
}

fwrite(STDOUT, "Password (hidden input): ");
if (function_exists('shell_exec')) {
    shell_exec('stty -echo');
}
$password = trim((string) fgets(STDIN));
if (function_exists('shell_exec')) {
    shell_exec('stty echo');
}
fwrite(STDOUT, "\n");
if (strlen($password) < 12) {
    fwrite(STDERR, "Password must contain at least 12 characters.\n");
    exit(1);
}

try {
    $db = Database::connect();
    $db->beginTransaction();
    // 19 random bytes encode to exactly 26 unpadded base64url characters.
    $publicId = rtrim(strtr(base64_encode(random_bytes(19)), '+/', '-_'), '=');
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    $user = $db->prepare('INSERT INTO users (public_id, email, display_name, password_hash, status, mfa_enabled) VALUES (:public_id, :email, :display_name, :password_hash, \'active\', 0)');
    $user->execute(['public_id' => $publicId, 'email' => $email, 'display_name' => $name, 'password_hash' => $hash]);
    $userId = (int) $db->lastInsertId();
    $role = $db->prepare('SELECT id FROM roles WHERE name = \'super_admin\' LIMIT 1');
    $role->execute();
    $roleId = $role->fetchColumn();
    if (!$roleId) {
        throw new RuntimeException('RBAC seed is missing. Run php bin/migrate.php seed first.');
    }
    $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)')->execute(['user_id' => $userId, 'role_id' => $roleId]);
    (new AuditLogger($db))->record($userId, 'owner_provisioned', 'user', $userId, ['role' => 'super_admin']);
    $db->commit();
    fwrite(STDOUT, "Owner provisioned. Enable MFA before production use.\n");
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Provisioning failed: {$error->getMessage()}\n");
    exit(1);
}
