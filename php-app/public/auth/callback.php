<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Database;
use GeoBooster\Security\Csrf;
use GeoBooster\Security\AuditLogger;

Csrf::start();

$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if (!$code || !$state || !isset($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
    http_response_code(400);
    exit('Autentikasi Google gagal atau sesi kadaluarsa. Silakan coba kembali.');
}

unset($_SESSION['oauth_state']);

$clientId = getenv('GOOGLE_CLIENT_ID') ?: '';
$clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: 'http://127.0.0.1:8080/auth/callback.php';

// Exchange code for token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$response = curl_exec($ch);
curl_close($ch);

$tokenJson = json_decode((string) $response, true);
$accessToken = $tokenJson['access_token'] ?? null;

if (!$accessToken) {
    http_response_code(400);
    exit('Gagal mengambil token otorisasi dari Google. Silakan periksa kredensial .env Anda.');
}

// Fetch Google User Profile
$infoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($infoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
$userResponse = curl_exec($ch);
curl_close($ch);

$googleUser = json_decode((string) $userResponse, true);
$googleId = (string) ($googleUser['id'] ?? '');
$email = strtolower(trim((string) ($googleUser['email'] ?? '')));
$name = trim((string) ($googleUser['name'] ?? 'Pengguna Google'));
$avatar = (string) ($googleUser['picture'] ?? '');

if ($email === '' || $googleId === '') {
    http_response_code(400);
    exit('Data akun Google tidak lengkap.');
}

try {
    $db = Database::connect();
    
    // Check by google_id or email
    $stmt = $db->prepare('SELECT id, display_name, email, avatar_url FROM users WHERE google_id = :gid OR email = :email LIMIT 1');
    $stmt->execute(['gid' => $googleId, 'email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = (int) $user['id'];
        $update = $db->prepare('UPDATE users SET google_id = :gid, avatar_url = :avatar, last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute(['gid' => $googleId, 'avatar' => $avatar, 'id' => $userId]);
    } else {
        // Register new user
        $publicId = rtrim(strtr(base64_encode(random_bytes(19)), '+/', '-_'), '=');
        $insert = $db->prepare('INSERT INTO users (public_id, email, display_name, avatar_url, google_id, status, mfa_enabled) VALUES (:pid, :email, :name, :avatar, :gid, \'active\', 0)');
        $insert->execute([
            'pid' => $publicId,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
            'gid' => $googleId,
        ]);
        $userId = (int) $db->lastInsertId();
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_avatar'] = $avatar;

    (new AuditLogger($db))->record($userId, 'google_login_success', 'user', $userId, ['email' => $email]);

    header('Location: /dashboard/', true, 303);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    exit('Terjadi kesalahan saat memproses login database: ' . htmlspecialchars($e->getMessage()));
}
