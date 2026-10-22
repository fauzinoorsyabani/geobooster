<?php

declare(strict_types=1);

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline' https://*.clerk.accounts.dev https://*.clerk.com https://challenges.cloudflare.com; img-src 'self' data: https://img.clerk.com https://images.clerk.dev https://*.googleusercontent.com https://api.qrserver.com https://ui-avatars.com; connect-src 'self' https://*.clerk.accounts.dev https://*.clerk.com https://api.clerk.com https://challenges.cloudflare.com https://api.qrserver.com; frame-src 'self' https://challenges.cloudflare.com https://*.clerk.accounts.dev https://*.clerk.com; worker-src 'self' blob:; frame-ancestors 'none'; base-uri 'self'");


// Bootstrap & View Helpers
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/helpers.php';

\GeoBooster\Security\Csrf::start();

// Data & Config
$app = require __DIR__ . '/../config/app.php';
$products = require __DIR__ . '/../data/products.php';
$availableCount = count(array_filter($products, fn(array $p): bool => $p['available']));

// Modular Template Assembly
require __DIR__ . '/../views/layouts/header.php';
require __DIR__ . '/../views/components/navbar.php';
?>
<main id="top">
  <?php
  require __DIR__ . '/../views/components/hero.php';
  require __DIR__ . '/../views/components/marquee.php';
  require __DIR__ . '/../views/components/trust.php';
  require __DIR__ . '/../views/components/catalog.php';
  require __DIR__ . '/../views/components/process.php';
  require __DIR__ . '/../views/components/faq.php';
  ?>
</main>
<?php
require __DIR__ . '/../views/components/footer.php';
require __DIR__ . '/../views/components/ziubot-widget.php';
require __DIR__ . '/../views/components/cmdk.php';
require __DIR__ . '/../views/components/checkout-modal.php';
require __DIR__ . '/../views/layouts/scripts.php';

