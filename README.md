# Geo Booster

Geo Booster adalah fondasi Software as a Service (SaaS) untuk katalog dan penjualan produk digital secara transparan, aman, dan mudah dioperasikan. Versi awal ini memakai **PHP 8.2+ tanpa TypeScript** untuk menjaga kurva belajar tetap rendah.

## Status proyek

Repositori ini sebelumnya berisi aplikasi React/TypeScript dari proyek lain. Fondasi Geo Booster ditambahkan sebagai aplikasi PHP mandiri di `php-app/` agar migrasi dapat dilakukan bertahap tanpa menghapus aset lama.

Versi yang tersedia saat ini adalah **catalogue-first MVP**: landing page, katalog produk, filter kategori, trust signals, dan alur permintaan pesanan yang belum terhubung ke pembayaran atau provisioning otomatis. Fondasi **M1 database/RBAC** sudah ditambahkan, tetapi admin console dan payment production sengaja belum diaktifkan sebelum DB production, owner MFA, compliance, dan provider sandbox siap.

## Website production

Website permanen tersedia di [geo-booster-fauzins-projects.vercel.app](https://geo-booster-fauzins-projects.vercel.app/). Deployment production dibuat dari export statis `php-app/static/`, sedangkan aplikasi PHP di `php-app/public/` tetap menjadi source canonical untuk pengembangan backend berikutnya.

## Menjalankan lokal

```bash
cd php-app
php -S 127.0.0.1:8080 -t public
```

Buka `http://127.0.0.1:8080`.

Untuk IDE Antigravity, buka repository ini lalu jalankan `./run-local.sh` dari terminal. Panduan lengkap tersedia di [docs/antigravity.md](docs/antigravity.md).

## Struktur penting

- `php-app/public/` — web root dan asset yang boleh diakses publik.
- `php-app/config/` — konfigurasi non-rahasia dan data katalog sementara.
- `php-app/database/` — migration, seed RBAC, dan rollback MySQL/MariaDB.
- `php-app/app/` — bootstrap PDO dan security helpers backend.
- `php-app/bin/` — perintah CLI migration.
- `docs/` — system design, project management, backend, frontend, API, data model, dan threat model.
- `.agent/skills/` — aturan kerja agentic engineering yang wajib diikuti saat mengembangkan fitur.

## Prinsip produk

Geo Booster tidak boleh menjual kredensial curian, akses ilegal, atau produk yang melanggar kebijakan penyedia layanan. Integrasi pembayaran dan fulfillment hanya boleh diaktifkan setelah legalitas reseller, terms of service, refund policy, dan bukti kepemilikan lisensi diverifikasi.

## Referensi internal

Mulai dari [System Design](docs/architecture.md), lalu baca [Project Management](docs/project-management.md) dan [Agentic Engineering](docs/agentic-engineering.md).

## Pengujian cepat

```bash
php -l php-app/public/index.php
```

Untuk deployment, gunakan PHP-FPM/Nginx atau Apache dengan document root menunjuk ke `php-app/public/`. Jangan pernah menjadikan `config/`, `storage/`, atau `.env` sebagai web root.

## M1 database setup

Salin `.env.example` menjadi `.env` di environment lokal/production dan isi kredensial database melalui secret manager. Jangan commit `.env`.

```bash
cd php-app
php bin/migrate.php up
php bin/migrate.php seed
```

Migration `down` bersifat destruktif dan hanya boleh dijalankan setelah review backup dan dependency production:

```bash
php bin/migrate.php down
```

Detail role, permission, admin console, dan release gate ada di [docs/admin-console-m1.md](docs/admin-console-m1.md).

Deployment Vercel saat ini menggunakan upload production langsung karena GitHub App Vercel belum terpasang pada akun. Jika GitHub App diaktifkan, project dapat dihubungkan ke branch `main` agar deployment berjalan otomatis setiap push.

Katalog saat ini memuat **26 SKU** dari daftar inventory 21 September 2026. Harga sengaja tidak ditampilkan atau dikirim otomatis; customer menanyakannya melalui WhatsApp `+62 895-6092-50509`. Main page memiliki animasi orb, orbit, floating cards, spark, dan status pulse dengan dukungan `prefers-reduced-motion`. Dua belas visual produk dibuat sebagai aset brand original; SKU lain memakai visual kategori yang dioptimalkan sebagai fallback sampai aset individual berikutnya tersedia.

Kartu katalog terbaru menggunakan logo brand layanan masing-masing, termasuk Gemini, CapCut, Microsoft Office, Adobe, Canva, Figma, Prime Video, YouTube, HBO, Grok, dan lainnya. Detail source, mapping, dan catatan trademark ada di [docs/logo-assets.md](docs/logo-assets.md).
