<?php

declare(strict_types=1);

namespace GeoBooster\Security;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(): PDO
    {
        $dsn = getenv('DB_DSN') ?: '';
        $username = getenv('DB_USERNAME') ?: '';
        $password = getenv('DB_PASSWORD') ?: '';
        if ($dsn === '' || $username === '') {
            throw new RuntimeException('Database environment is not configured.');
        }
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}

final class Csrf
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => (getenv('APP_ENV') ?: 'local') === 'production',
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function token(): string
    {
        self::start();
        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): void
    {
        self::start();
        if (!is_string($token) || !hash_equals($_SESSION['_csrf'], $token)) {
            http_response_code(419);
            throw new RuntimeException('Invalid CSRF token.');
        }
    }
}

final class Authorizer
{
    public function __construct(private readonly PDO $db) {}

    public function requirePermission(int $userId, string $permission): void
    {
        $query = 'SELECT 1 FROM user_roles ur JOIN role_permissions rp ON rp.role_id = ur.role_id JOIN permissions p ON p.id = rp.permission_id JOIN users u ON u.id = ur.user_id WHERE u.id = :user_id AND u.status = \'active\' AND p.name = :permission LIMIT 1';
        $statement = $this->db->prepare($query);
        $statement->execute(['user_id' => $userId, 'permission' => $permission]);
        if ($statement->fetchColumn() !== 1) {
            http_response_code(403);
            throw new RuntimeException('Forbidden.');
        }
    }
}

final class AuditLogger
{
    public function __construct(private readonly PDO $db) {}

    public function record(?int $actorId, string $action, string $entityType, ?int $entityId, array $metadata = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $this->db->prepare('INSERT INTO audit_logs (actor_type, actor_id, action, entity_type, entity_id, metadata_json, ip_hash) VALUES (:actor_type, :actor_id, :action, :entity_type, :entity_id, :metadata_json, :ip_hash)');
        $stmt->execute([
            'actor_type' => $actorId === null ? 'system' : 'user',
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata_json' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
            'ip_hash' => $ip === '' ? null : hash('sha256', $ip . (getenv('APP_KEY') ?: '')),
        ]);
    }
}
