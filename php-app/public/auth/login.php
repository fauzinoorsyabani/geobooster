<?php

declare(strict_types=1);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline' https://*.clerk.accounts.dev https://*.clerk.com https://challenges.cloudflare.com; img-src 'self' data: https://img.clerk.com https://images.clerk.dev https://*.googleusercontent.com https://api.qrserver.com https://ui-avatars.com; connect-src 'self' https://*.clerk.accounts.dev https://*.clerk.com https://api.clerk.com https://challenges.cloudflare.com https://api.qrserver.com; frame-src 'self' https://challenges.cloudflare.com https://*.clerk.accounts.dev https://*.clerk.com; worker-src 'self' blob:; frame-ancestors 'none'; base-uri 'self'");

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use GeoBooster\Security\Csrf;

Csrf::start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/', true, 303);
    exit;
}

$app = require dirname(__DIR__, 2) . '/config/app.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk — ZiuLabs Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">

  <!-- ClerkJS Authentication Engine -->
  <script
    async
    crossorigin="anonymous"
    data-clerk-publishable-key="<?= htmlspecialchars($app['clerk_publishable_key'], ENT_QUOTES, 'UTF-8') ?>"
    src="<?= htmlspecialchars($app['clerk_frontend_api'], ENT_QUOTES, 'UTF-8') ?>/npm/@clerk/clerk-js@5/dist/clerk.browser.js"
    type="text/javascript">
  </script>

  <style>
    .auth-page-wrap {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 32px 20px;
      background: radial-gradient(circle at 50% 0%, rgba(0, 102, 255, 0.08) 0%, rgba(248, 250, 252, 1) 70%);
    }
    .auth-container {
      width: 100%;
      max-width: 440px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .auth-header-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 24px;
      text-decoration: none;
    }
    .auth-header-brand span {
      font-weight: 800;
      font-size: 1.25rem;
      color: #0066ff;
      letter-spacing: -0.02em;
    }
    #clerk-sign-in-mount {
      width: 100%;
      display: flex;
      justify-content: center;
      min-height: 380px;
    }
    .clerk-loading-skeleton {
      width: 100%;
      background: #ffffff;
      border: 1px solid rgba(0, 102, 255, 0.12);
      border-radius: 20px;
      padding: 32px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 14px;
    }
    .clerk-loading-spinner {
      width: 32px;
      height: 32px;
      border: 3px solid rgba(0, 102, 255, 0.15);
      border-top-color: #0066ff;
      border-radius: 50%;
      animation: clerkSpin 0.8s linear infinite;
    }
    @keyframes clerkSpin {
      to { transform: rotate(360deg); }
    }
    .auth-demo-box {
      width: 100%;
      margin-top: 24px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 18px 20px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .btn-demo-auth {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 10px 16px;
      margin-top: 10px;
      background: #f8fafc;
      border: 1px dashed #0066ff;
      border-radius: 10px;
      color: #0066ff;
      font-size: 0.86rem;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .btn-demo-auth:hover {
      background: rgba(0, 102, 255, 0.08);
      transform: translateY(-1px);
    }
    .auth-footer-links {
      margin-top: 20px;
      text-align: center;
      font-size: 0.84rem;
      color: #64748b;
    }
    .auth-footer-links a {
      color: #0066ff;
      text-decoration: none;
      font-weight: 600;
    }
    .auth-footer-links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="auth-page-wrap">
    <div class="auth-container">
      <a href="/" class="auth-header-brand" title="Ke Beranda ZiuLabs">
        <img src="/assets/logos/ziulabs-icon.webp" alt="ZiuLabs" width="36" height="36">
        <span>ZiuLabs Store</span>
      </a>

      <!-- Clerk SignIn Component Mount Point -->
      <div id="clerk-sign-in-mount">
        <div class="clerk-loading-skeleton" id="clerk-loading-skeleton">
          <div class="clerk-loading-spinner"></div>
          <div style="font-size: 0.9rem; font-weight: 600; color: #475569;">Memuat autentikasi Clerk...</div>
        </div>
      </div>

      <!-- Quick Demo Login for testing -->
      <div class="auth-demo-box">
        <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 700;">Pengujian Sandbox</div>
        <a class="btn-demo-auth" href="/auth/demo-login.php">
          ⚡ Masuk Instant Akun Demo
        </a>
      </div>

      <div class="auth-footer-links">
        <p>Login Staf / Operator? <a href="/admin/">Masuk Admin Console &rarr;</a></p>
        <p style="margin-top: 10px;"><a href="/">&larr; Kembali ke Beranda</a></p>
      </div>
    </div>
  </div>

  <script>
  (function() {
    function syncAndRedirect(user) {
      if (!user) {
        window.location.href = '/dashboard/';
        return;
      }
      var email = user.primaryEmailAddress ? user.primaryEmailAddress.emailAddress : '';
      var name = user.fullName || (user.firstName ? (user.firstName + ' ' + (user.lastName || '')).trim() : 'Pelanggan');
      var avatar = user.imageUrl || '';
      var clerkId = user.id;

      fetch('/api/clerk-sync.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          clerk_id: clerkId,
          email: email,
          name: name,
          avatar: avatar
        })
      })
      .then(function() {
        window.location.href = '/dashboard/';
      })
      .catch(function() {
        window.location.href = '/dashboard/';
      });
    }

    async function initClerk() {
      if (typeof Clerk === 'undefined') {
        setTimeout(initClerk, 80);
        return;
      }
      try {
        await Clerk.load();
        if (Clerk.user) {
          syncAndRedirect(Clerk.user);
        } else {
          var mountNode = document.getElementById('clerk-sign-in-mount');
          if (mountNode) {
            mountNode.innerHTML = '';
            Clerk.mountSignIn(mountNode, {
              fallbackRedirectUrl: '/dashboard/'
            });
          }
        }

        Clerk.addListener(function(emission) {
          if (emission.user) {
            syncAndRedirect(emission.user);
          }
        });
      } catch (err) {
        console.error('Clerk login mount error:', err);
        var skel = document.getElementById('clerk-loading-skeleton');
        if (skel) {
          skel.innerHTML = '<div style="color: #dc2626; font-size: 0.9rem;">Gagal memuat Clerk: ' + (err.message || 'Error') + '</div>';
        }
      }
    }

    if (document.readyState === 'loading') {
      window.addEventListener('load', initClerk);
    } else {
      initClerk();
    }
  })();
  </script>
</body>
</html>
