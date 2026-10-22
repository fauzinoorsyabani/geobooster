<?php

declare(strict_types=1);

/**
 * Component: FAQ Accordion with ZiuBot Helper Card
 * @var array $app
 */
?>
<section class="faq-section" id="faq">
  <div class="shell faq-layout">
    <div>
      <p class="section-eyebrow">PERTANYAAN UMUM</p>
      <h2 class="section-title" style="margin-top: 8px;">Hal yang Sering Ditanyakan</h2>
      <p class="section-desc" style="margin-top: 14px;">
        Kebijakan transparan seputar durasi, garansi, dan model layanan digital ZiuLabs.
      </p>

      <div class="faq-ziubot-card">
        <img src="/assets/mascot/ziubot-512.webp" alt="ZiuBot" class="faq-ziubot-img" width="68" height="67" loading="lazy" />
        <div class="faq-ziubot-copy">
          <strong>Punya pertanyaan lain seputar produk?</strong>
          <p>ZiuBot &amp; tim support siap membantu konsultasi dan jawab pertanyaan via WhatsApp.</p>
          <a href="<?= e($app['support_url']) ?>" target="_blank" rel="noopener" class="btn-faq-chat">
            <span>Chat ZiuBot Sekarang</span>
            <span>→</span>
          </a>
        </div>
      </div>
    </div>

    <div class="faq-list">
      <details class="faq-box" open>
        <summary>Bagaimana cara membeli produk di ZiuLabs?</summary>
        <p>Pilih produk yang diinginkan dari katalog, lalu klik tombol "Order WA". Format pesan WhatsApp otomatis akan terbuka dengan nama dan SKU produk. Tim kami segera mengonfirmasi ketersediaan dan detail pembayarannya.</p>
      </details>
      <details class="faq-box">
        <summary>Metode pembayaran apa saja yang didukung?</summary>
        <p>Kami menerima pembayaran melalui QRIS (GoPay, OVO, Dana, ShopeePay), transfer bank BCA, Mandiri, BRI, dan e-Wallet untuk kemudahan transaksi Anda.</p>
      </details>
      <details class="faq-box">
        <summary>Berapa lama proses aktivasi akun?</summary>
        <p>Sebagian besar produk digital dapat diaktivasi dalam waktu 5 hingga 15 menit setelah bukti pembayaran kami verifikasi pada jam operasional.</p>
      </details>
      <details class="faq-box">
        <summary>Apakah produk bergaransi?</summary>
        <p>Semua produk yang kami sediakan memiliki garansi resmi sesuai masa durasi yang tertera. Jika ada kendala teknis, tim kami siap mendampingi melalui WhatsApp.</p>
      </details>
    </div>
  </div>
</section>
