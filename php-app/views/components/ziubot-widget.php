<?php

declare(strict_types=1);

/**
 * Component: Floating ZiuBot Quick Support Widget
 * @var array $app
 */
?>
<aside class="ziubot-widget" id="ziubot-widget" aria-label="Bantuan ZiuBot">
  <a class="ziubot-bubble-btn" href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener" aria-label="Tanya ZiuBot via WhatsApp">
    <div class="ziubot-avatar-wrap">
      <img src="/assets/mascot/ziubot-512.webp" alt="ZiuBot" class="ziubot-btn-img" width="44" height="44" />
      <span class="ziubot-pulse-dot" title="Online"></span>
    </div>
    <div class="ziubot-tooltip">
      <span class="ziubot-tooltip-name">ZiuBot AI</span>
      <span class="ziubot-tooltip-text">Tanya Stok &amp; Pesan WA</span>
    </div>
  </a>
</aside>
