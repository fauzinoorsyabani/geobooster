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

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: [];

    $clerkId = trim((string) ($data['clerk_id'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $name = trim((string) ($data['name'] ?? 'Pelanggan ZiuLabs'));
    $avatar = trim((string) ($data['avatar'] ?? ''));

    if ($clerkId === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Data pengguna Clerk tidak valid.']);
        exit;
    }

    $db = Database::connect();

    // Check if user already exists by google_id/clerk_id or email
    $stmt = $db->prepare('SELECT id, public_id, email, display_name FROM users WHERE google_id = :gid OR email = :email LIMIT 1');
    $stmt->execute(['gid' => $clerkId, 'email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = (int) $user['id'];
        $update = $db->prepare('UPDATE users SET google_id = :gid, avatar_url = :avatar, display_name = :name, last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute([
            'gid' => $clerkId,
            'avatar' => $avatar,
            'name' => $name,
            'id' => $userId,
        ]);
    } else {
        $publicId = rtrim(strtr(base64_encode(random_bytes(19)), '+/', '-_'), '=');
        $insert = $db->prepare('INSERT INTO users (public_id, email, display_name, avatar_url, google_id, status, mfa_enabled) VALUES (:pid, :email, :name, :avatar, :gid, \'active\', 0)');
        $insert->execute([
            'pid' => $publicId,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
            'gid' => $clerkId,
        ]);
        $userId = (int) $db->lastInsertId();
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_avatar'] = $avatar;
    $_SESSION['clerk_id'] = $clerkId;

    (new AuditLogger($db))->record($userId, 'clerk_login_sync', 'user', $userId, ['clerk_id' => $clerkId]);

    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'name' => $name,
        'email' => $email,
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Gagal sinkronisasi sesi: ' . $e->getMessage()]);
    exit;
}
