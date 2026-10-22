<?php

declare(strict_types=1);

/**
 * Component: Site Footer
 * @var array $app
 */
?>
<footer class="site-footer">
  <div class="shell footer-inner">
    <div class="footer-brand">
      <img src="/assets/logos/ziulabs-icon.webp" alt="Z" class="footer-logo-img" width="28" height="28" loading="lazy" />
      <span style="font-weight: 700; color: var(--ink-primary);"><?= e($app['name']) ?> Store</span>
      <span>— Solusi Lengkap Produk Digital &amp; AI</span>
    </div>
    <div>
      <span>&copy; <?= date('Y') ?> <?= e($app['name']) ?>. All rights reserved.</span>
    </div>
  </div>
</footer>
