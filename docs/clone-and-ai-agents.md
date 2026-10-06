# Geo Booster — Clone, MySQL Local, dan AI Agents

## Repository status

Repository canonical saat ini public:

```text
https://github.com/fauzinoorsyabani/geobooster
```

Repository ini sengaja dipisahkan dari repository lama yang sebelumnya menampilkan nama `Cek-Khodam`/`Dapur-Tanggal-Tua`. Gunakan URL `geobooster` untuk pekerjaan baru.

## Clone untuk IDE

### Dengan GitHub CLI

```bash
gh auth login
gh repo clone fauzinoorsyabani/geobooster
cd geobooster
```

### Dengan Git HTTPS

```bash
git clone https://github.com/fauzinoorsyabani/geobooster.git
cd geobooster
```

Repository public dapat di-clone tanpa login. Login GitHub tetap diperlukan untuk push perubahan. Jangan menaruh Personal Access Token di URL, README, `.env`, atau chat.

## Kesiapan lokal yang sudah diverifikasi

Environment development saat ini memiliki:

```text
PHP 8.3
MySQL client 8.0
PDO
pdo_mysql
mysqli
```

Jadi MySQL dan PHP driver sudah tersedia secara lokal. Yang masih harus dibuat adalah database/schema dan user database lokal.

## Setup MySQL lokal

### Cek service

```bash
sudo systemctl status mysql
mysql --version
```

Jika service belum aktif:

```bash
sudo systemctl enable --now mysql
```

### Buat database dan user lokal

Masuk sebagai administrator MySQL:

```bash
sudo mysql
```

Jalankan SQL berikut dan ganti password lokal dengan nilai pribadi:

```sql
CREATE DATABASE geo_booster CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'geo_booster'@'localhost' IDENTIFIED BY 'replace-with-a-local-password';
GRANT ALL PRIVILEGES ON geo_booster.* TO 'geo_booster'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Siapkan environment

Dari root repository:

```bash
cp .env.example .env
```

Isi lokal:

```text
APP_ENV=local
APP_URL=http://127.0.0.1:8080
APP_KEY=<random-local-secret>
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=geo_booster;charset=utf8mb4
DB_USERNAME=geo_booster
DB_PASSWORD=<password-lokal>
ADMIN_ENABLED=true
SESSION_SECURE_COOKIE=false
```

`.env` tidak boleh di-commit.

### Jalankan schema

```bash
cd php-app
php bin/migrate.php up
php bin/migrate.php seed
php bin/create-owner.php owner@example.com "Geo Booster Owner"
```

Jalankan aplikasi:

```bash
php -S 127.0.0.1:8080 -t public
```

Buka:

```text
http://127.0.0.1:8080/
http://127.0.0.1:8080/admin/
```

## AI Agents workflow

AI Agent harus membaca file berikut sebelum coding:

1. `README.md`
2. `docs/architecture.md`
3. `docs/deployment-architecture.md`
4. `docs/agentic-engineering.md`
5. `docs/threat-model.md`
6. `docs/admin-console-m1.md`
7. migration dan source module yang akan disentuh

Setiap task AI Agent harus memiliki:

```text
Goal:
User/operator impact:
Acceptance criteria:
Out of scope:
Security considerations:
Data impact:
Files likely affected:
Verification commands:
Rollback:
```

AI Agent tidak boleh:

- Membaca atau meminta secret dari `.env` untuk ditulis ke output.
- Membuat default admin password.
- Mengubah status payment menjadi paid dari browser redirect.
- Menghapus data production tanpa approval dan rollback plan.
- Mengaktifkan payment production tanpa sandbox test dan compliance review.
- Mengubah schema tanpa migration dan rollback.

AI Agent wajib mengembalikan:

- Ringkasan perubahan.
- File yang disentuh.
- Command verifikasi.
- Hasil verifikasi.
- Risiko tersisa.
- Follow-up yang dibutuhkan.

## Branch workflow

```bash
git checkout -b feat/short-description
# edit dan test
git status
git diff --check
git add <files>
git commit -m "feat: short description"
git push -u origin feat/short-description
```

Jangan meminta AI Agent bekerja langsung di `main` untuk perubahan besar. Gunakan branch dan review diff.

## Deploy preparation

Sebelum deploy ke Railway:

- Private GitHub repo dapat diakses integration yang digunakan.
- Dockerfile lolos review.
- Railway web service dan MySQL dibuat dalam project yang sama.
- DB variables direferensikan melalui environment, bukan source.
- Backup MySQL aktif.
- Owner MFA siap.
- Payment provider masih disabled.
- Domain HTTPS siap.

Blueprint lengkap ada di [deployment-architecture.md](deployment-architecture.md).
