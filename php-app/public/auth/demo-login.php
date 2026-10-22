<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\Csrf;
use GeoBooster\Security\AuditLogger;

Csrf::start();

try {
    $db = Database::connect();
    $demoEmail = 'demo.buyer@ziulabs.id';
    $demoName = 'Fauzi (Demo Buyer)';
    $demoAvatar = 'https://ui-avatars.com/api/?name=Fauzi+Noor&background=0066ff&color=fff&size=128';
    $demoGid = 'demo_google_123456789';

    $stmt = $db->prepare('SELECT id, display_name, email, avatar_url FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $demoEmail]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = (int) $user['id'];
        $db->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id')->execute(['id' => $userId]);
    } else {
        $publicId = rtrim(strtr(base64_encode(random_bytes(19)), '+/', '-_'), '=');
        $insert = $db->prepare('INSERT INTO users (public_id, email, display_name, avatar_url, google_id, status, mfa_enabled) VALUES (:pid, :email, :name, :avatar, :gid, \'active\', 0)');
        $insert->execute([
            'pid' => $publicId,
            'email' => $demoEmail,
            'name' => $demoName,
            'avatar' => $demoAvatar,
            'gid' => $demoGid,
        ]);
        $userId = (int) $db->lastInsertId();
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $demoName;
    $_SESSION['user_email'] = $demoEmail;
    $_SESSION['user_avatar'] = $demoAvatar;

    (new AuditLogger($db))->record($userId, 'demo_login_success', 'user', $userId, ['email' => $demoEmail]);

    header('Location: /dashboard/', true, 303);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    exit('Gagal login demo: ' . htmlspecialchars($e->getMessage()));
}
