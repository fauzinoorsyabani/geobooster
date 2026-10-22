<?php

declare(strict_types=1);

/**
 * Layout: Header & Head tag
 * @var array $app
 */
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title><?= e($app['name']) ?> — Solusi Lengkap Produk Digital &amp; AI</title>
  <meta name="description" content="<?= e($app['name']) ?> — Platform resmi katalog produk digital premium dan tools AI terverifikasi dengan aktivasi cepat via WhatsApp." />

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet" />

  <!-- Stylesheet with Cache Busting -->
  <link rel="stylesheet" href="/assets/style.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/assets/style.css') ?>" />

  <!-- ClerkJS Authentication Engine -->
  <script
    async
    crossorigin="anonymous"
    data-clerk-publishable-key="<?= e($app['clerk_publishable_key']) ?>"
    src="<?= e($app['clerk_frontend_api']) ?>/npm/@clerk/clerk-js@5/dist/clerk.browser.js"
    type="text/javascript">
  </script>
</head>

<body>

  <!-- Background Ambient Glow & Web3 Grid (100% GPU, Zero Lag) -->
  <div class="ambient-scene" aria-hidden="true">
    <div class="ambient-grid"></div>
    <div class="grid-spotlight" id="grid-spotlight"></div>
    <div class="grid-scan-beam"></div>
    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>
    <div class="ambient-orb ambient-orb-3"></div>
  </div>
