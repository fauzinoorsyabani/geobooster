<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Csrf;

Csrf::start();

$clientId = getenv('GOOGLE_CLIENT_ID') ?: '';
$redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: 'http://127.0.0.1:8080/auth/callback.php';

// If Google Client ID is not configured yet or has placeholder, show helper page with 1-click test login
if ($clientId === '' || str_contains($clientId, 'your-google-client-id') || str_contains($clientId, 'replace-')) {
    ?><!doctype html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Setup Google OAuth — ZiuLabs</title>
      <link rel="stylesheet" href="/assets/style.css">
      <style>
        .setup-card {
          max-width: 560px;
          margin: 60px auto;
          background: #ffffff;
          border: 1px solid rgba(0, 102, 255, 0.15);
          border-radius: 20px;
          padding: 36px;
          box-shadow: 0 16px 36px rgba(10, 20, 50, 0.08);
          font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
          color: #0f172a;
        }
        .setup-head {
          display: flex;
          align-items: center;
          gap: 14px;
          margin-bottom: 20px;
        }
        .google-icon-box {
          width: 48px;
          height: 48px;
          background: #f8fafc;
          border: 1px solid #e2e8f0;
          border-radius: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .setup-title {
          font-size: 1.25rem;
          font-weight: 800;
          margin: 0;
          color: #0f172a;
        }
        .setup-desc {
          font-size: 0.92rem;
          color: #64748b;
          line-height: 1.6;
          margin-top: 4px;
        }
        .step-box {
          background: #f8fafc;
          border: 1px solid #e2e8f0;
          border-radius: 12px;
          padding: 18px;
          margin: 20px 0;
          font-size: 0.88rem;
          line-height: 1.65;
        }
        .step-box ol {
          margin: 0;
          padding-left: 20px;
        }
        .step-box li {
          margin-bottom: 8px;
        }
        .code-snippet {
          background: #0f172a;
          color: #38bdf8;
          padding: 12px 16px;
          border-radius: 10px;
          font-family: monospace;
          font-size: 0.82rem;
          margin: 12px 0 6px 0;
          overflow-x: auto;
        }
        .btn-demo-login {
          display: block;
          width: 100%;
          text-align: center;
          background: linear-gradient(135deg, #0066ff, #0052cc);
          color: #ffffff;
          padding: 13px 20px;
          border-radius: 12px;
          font-weight: 700;
          text-decoration: none;
          box-shadow: 0 4px 14px rgba(0, 102, 255, 0.3);
          transition: transform 0.15s ease;
        }
        .btn-demo-login:hover {
          transform: translateY(-2px);
        }
        .back-link {
          display: block;
          text-align: center;
          margin-top: 16px;
          color: #64748b;
          font-size: 0.88rem;
          text-decoration: none;
        }
      </style>
    </head>
    <body style="background: #f1f5f9; min-height: 100vh; padding: 20px;">
      <div class="setup-card">
        <div class="setup-head">
          <div class="google-icon-box">
            <svg width="24" height="24" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.14-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.97 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
          </div>
          <div>
            <h1 class="setup-title">Google OAuth 2.0 ZiuLabs</h1>
            <p class="setup-desc">Autentikasi resmi 100% gratis dari Google Cloud Console.</p>
          </div>
        </div>

        <div class="step-box">
          <strong>Langkah Pasang Kredensial Asli:</strong>
          <ol>
            <li>Buka <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a> lalu buat project baru.</li>
            <li>Masuk ke <strong>APIs &amp; Services &gt; Credentials</strong> lalu klik <em>Create Credentials &gt; OAuth client ID</em>.</li>
            <li>Pilih tipe <strong>Web Application</strong> dan isi Authorized redirect URI:
              <div class="code-snippet">http://127.0.0.1:8080/auth/callback.php</div>
            </li>
            <li>Salin <strong>Client ID</strong> &amp; <strong>Client Secret</strong> ke file <code>.env</code> Anda:
              <div class="code-snippet">GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com<br>GOOGLE_CLIENT_SECRET=GOCSPX-xxx</div>
            </li>
          </ol>
        </div>

        <a class="btn-demo-login" href="/auth/demo-login.php">
          Masuk dengan Akun Uji Coba (Instant Mock Login) &rarr;
        </a>
        <a class="back-link" href="/">&larr; Kembali ke Katalog Utama</a>
      </div>
    </body>
    </html><?php
    exit;
}

// Generate state token for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl, true, 303);
exit;
