<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(string|int|float|null $value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('brandLogo')) {
    function brandLogo(string $name): string {
        return match (true) {
            str_contains($name, 'Gemini') => 'gemini.webp',
            str_contains($name, 'CapCut') => 'capcut.webp',
            str_contains($name, 'Microsoft') => 'microsoftoffice.svg',
            str_contains($name, 'Adobe') => 'adobe.svg',
            str_contains($name, 'Duolingo') => 'duolingo.svg',
            str_contains($name, 'Canva') => 'canva.svg',
            str_contains($name, 'iLovePDF') => 'ilovepdf.svg',
            str_contains($name, 'Gmail') || str_contains($name, 'Gmails') => 'gmail.svg',
            str_contains($name, 'Crunchyroll') => 'crunchyroll.svg',
            str_contains($name, 'Avira') => 'avira.svg',
            str_contains($name, 'Notion') => 'notion.svg',
            str_contains($name, 'EDX') => 'edx.svg',
            str_contains($name, 'Prime Video') => 'primevideo.svg',
            str_contains($name, 'JetBrains') => 'jetbrains.svg',
            str_contains($name, 'Figma') => 'figma.svg',
            str_contains($name, 'Framer') => 'framer.svg',
            str_contains($name, 'Miro') => 'miro.svg',
            str_contains($name, 'Autodesk') => 'autodesk.svg',
            str_contains($name, 'YT ') => 'youtube.svg',
            str_contains($name, 'HBO') => 'hbo.svg',
            str_contains($name, 'Grok') => 'grok.webp',
            default => 'openai.svg',
        };
    }
}

if (!function_exists('whatsappLink')) {
    function whatsappLink(string $number, string $name, string $sku, bool $available): string {
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);
        $message = $available
            ? "Halo ZiuLabs, saya ingin menanyakan harga dan ketersediaan paket [{$sku}] {$name}. Mohon informasi lengkapnya."
            : "Halo ZiuLabs, saya ingin mendapat info saat stok [{$sku}] {$name} kembali tersedia.";
        return 'https://wa.me/' . $cleanNumber . '?text=' . rawurlencode($message);
    }
}

if (!function_exists('renderFulfillmentCard')) {
    function renderFulfillmentCard(string $payload): string {
        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            // Raw string fallback
            return '<div class="license-credential-box">' .
                   '<span>' . e($payload) . '</span>' .
                   '<button type="button" class="btn-copy-code" onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText); this.innerText=\'Tersalin!\';">Salin</button>' .
                   '</div>';
        }

        $items = isset($decoded['sku_name']) ? [$decoded] : $decoded;
        $html = '<div class="credential-pool-wrap">';

        foreach ($items as $item) {
            $isManual = ($item['type'] ?? '') === 'manual_queue';
            if ($isManual) {
                $html .= '<div class="cred-card cred-pending">' .
                         '<div class="cred-card-header">' . e($item['sku_name'] ?? 'Item') . ' — Antrean Pemrosesan</div>' .
                         '<p class="cred-card-text">' . e($item['message'] ?? 'Stok sedang disiapkan secara manual oleh tim ZiuLabs.') . '</p>' .
                         '</div>';
                continue;
            }

            $html .= '<div class="cred-card">' .
                     '<div class="cred-card-header">' .
                     '<span>' . e($item['sku_name'] ?? 'Kredensial Produk') . '</span>' .
                     '<span class="cred-badge-active">Aktif &amp; Bergaransi</span>' .
                     '</div><div class="cred-card-body">';

            if (!empty($item['account_email'])) {
                $html .= '<div class="cred-row">' .
                         '<span class="cred-label">Email / ID Akun</span>' .
                         '<div class="cred-val-wrap"><code>' . e($item['account_email']) . '</code></div>' .
                         '<div class="cred-actions-bar">' .
                         '<button type="button" class="btn-cred-copy" onclick="navigator.clipboard.writeText(\'' . e(addslashes($item['account_email'])) . '\'); this.innerText=\'Tersalin!\';">Salin Email</button>' .
                         '</div></div>';
            }

            if (!empty($item['account_password'])) {
                $html .= '<div class="cred-row">' .
                         '<span class="cred-label">Password</span>' .
                         '<div class="cred-val-wrap"><code class="js-pass-reveal" data-pass="' . e($item['account_password']) . '">••••••••••••</code></div>' .
                         '<div class="cred-actions-bar">' .
                         '<button type="button" class="btn-cred-toggle" onclick="var c=this.closest(\'.cred-row\').querySelector(\'.js-pass-reveal\'); if(c.innerText===\'••••••••••••\'){ c.innerText=c.dataset.pass; this.innerText=\'Sembunyikan\'; } else { c.innerText=\'••••••••••••\'; this.innerText=\'Lihat\'; }">Lihat</button>' .
                         '<button type="button" class="btn-cred-copy" onclick="navigator.clipboard.writeText(\'' . e(addslashes($item['account_password'])) . '\'); this.innerText=\'Tersalin!\';">Salin Password</button>' .
                         '</div></div>';
            }

            if (!empty($item['license_key'])) {
                $html .= '<div class="cred-row">' .
                         '<span class="cred-label">Kode Lisensi</span>' .
                         '<div class="cred-val-wrap"><code>' . e($item['license_key']) . '</code></div>' .
                         '<div class="cred-actions-bar">' .
                         '<button type="button" class="btn-cred-copy" onclick="navigator.clipboard.writeText(\'' . e(addslashes($item['license_key'])) . '\'); this.innerText=\'Tersalin!\';">Salin Lisensi</button>' .
                         '</div></div>';
            }

            if (!empty($item['invite_url'])) {
                $html .= '<div class="cred-row">' .
                         '<span class="cred-label">Link Undangan</span>' .
                         '<div class="cred-val-wrap"><a href="' . e($item['invite_url']) . '" target="_blank" rel="noopener" style="color:#0066ff;text-decoration:underline;font-size:0.85rem;word-break:break-all;">Gabung Tim / Buka Undangan ↗</a></div>' .
                         '<div class="cred-actions-bar">' .
                         '<button type="button" class="btn-cred-copy" onclick="navigator.clipboard.writeText(\'' . e(addslashes($item['invite_url'])) . '\'); this.innerText=\'Tersalin!\';">Salin Link</button>' .
                         '</div></div>';
            }

            if (!empty($item['additional_notes'])) {
                $html .= '<div class="cred-notes"><small>' . e($item['additional_notes']) . '</small></div>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';
        return $html;
    }
}
