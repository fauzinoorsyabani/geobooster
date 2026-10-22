<?php

declare(strict_types=1);

/**
 * Component: ⌘K Command Palette Modal Dialog
 * @var array $products
 */
?>
<div class="palette-modal-overlay" id="palette-modal" role="dialog" aria-modal="true" aria-label="Pencarian Cepat SKU">
  <div class="palette-box">
    <div class="palette-search-row">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <input class="palette-input-field" id="palette-search" type="text" placeholder="Ketik nama produk atau kode SKU..." />
      <kbd style="font-family: 'JetBrains Mono', monospace; font-size: 11px; padding: 2px 6px; background: var(--bg-subtle); border-radius: 4px; color: var(--ink-muted);">ESC</kbd>
    </div>
    <div class="palette-list" id="palette-results"></div>
    <div class="palette-foot">
      <span>Tekan ENTER untuk order via WhatsApp</span>
      <span><?= count($products) ?> SKU terdaftar</span>
    </div>
  </div>
</div>
