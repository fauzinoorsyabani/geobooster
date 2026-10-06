# Geo Booster — Admin Console M1

## Status

M1 foundation sudah disiapkan: schema MySQL/MariaDB, seed RBAC, rollback, PDO bootstrap, CSRF helper, server-side authorization, audit logger, dan migration runner.

UI admin belum diaktifkan pada public production karena database credentials, initial owner invite, MFA policy, dan hosting PHP runtime belum dikonfirmasi. Jangan mengaktifkan route admin tanpa empat hal tersebut.

## Route target

```text
GET  /admin/login
POST /admin/login
POST /admin/logout
GET  /admin
GET  /admin/orders
GET  /admin/products
POST /admin/products
POST /admin/skus/:id/stock
GET  /admin/payments
GET  /admin/audit
```

Semua mutation wajib memakai session authentication, RBAC permission, CSRF, input validation, prepared statement, dan audit log.

## Dashboard M1

### Overview

- Inquiry baru.
- Order menunggu pembayaran.
- Order perlu manual review.
- SKU tersedia/habis.
- Payment failure.
- Fulfillment yang tertunda.

### Product/SKU management

- Edit nama, kategori, terms version.
- Edit harga dalam integer minor unit.
- Set stock status.
- Publish hanya bila `compliance_status=verified`.
- Archive, jangan hard delete.

### Order queue

- Filter status.
- View snapshot order.
- Assign fulfillment.
- Tambahkan internal note tanpa menyimpan credential produk.
- Manual review untuk nominal/provider mismatch.

### Finance

- Payment status.
- Provider event ID.
- Reconciliation summary.
- Refund request dengan reason dan actor.

## Initial owner provisioning

Tidak ada default password di repository. Buat owner melalui one-time provisioning command di environment production, lalu:

1. Invite email owner.
2. Set password dengan Argon2id.
3. Aktifkan MFA.
4. Login pertama memaksa session rotation.
5. Catat provisioning pada audit log.

Command yang tersedia:

```bash
cd php-app
php bin/create-owner.php owner@example.com "Geo Booster Owner"
```

Password diminta secara interaktif dan tidak ditulis ke command history, source code, atau log aplikasi. Command ini harus dijalankan hanya setelah `up` dan `seed`, lalu MFA wajib diaktifkan sebelum production.

## Release gate

Admin console belum boleh public sebelum:

- DB connection berhasil melalui secret manager/environment.
- HTTPS dan secure cookie aktif.
- Login brute-force test lulus.
- CSRF test lulus.
- Cross-role authorization test lulus.
- Audit log test lulus.
- Owner MFA aktif.
- Backup dan restore drill selesai.
