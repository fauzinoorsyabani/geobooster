# Geo Booster — Payment, Dashboard, dan AI Engineering Architecture

## 1. Rekomendasi keputusan

### Payment gateway

Gunakan **hosted checkout** dari payment provider yang memiliki:

- API resmi dan dokumentasi stabil.
- QRIS serta metode pembayaran Indonesia yang dibutuhkan.
- Signed webhook.
- Idempotency key.
- Expiry pembayaran.
- Refund atau void.
- Sandbox.
- Settlement dan reconciliation report.

Untuk MVP, jangan langsung mengunci domain order ke Curies. Nama dan API Curies perlu divalidasi terlebih dahulu melalui dokumentasi resminya. Search publik saat ini menemukan dokumentasi provider bernama **CRING! Payment**, bukan provider yang cukup jelas bernama “Curies”; jadi jangan menebak endpoint, legal status, fee, atau kemampuan refund.

Pilihan implementasi:

1. **Provider utama:** provider Indonesia dengan sandbox dan QRIS yang terdokumentasi baik, misalnya Midtrans, Xendit, atau DOKU setelah business verification.
2. **Curies/CRING:** boleh dibuat sebagai adapter kedua setelah nama provider, dokumentasi, sandbox credentials, webhook signing, dan refund policy terkonfirmasi.
3. **Fallback:** jangan membuat fallback otomatis antar-provider untuk satu order sebelum reconciliation dan idempotency matang. Satu order sebaiknya memiliki satu payment intent aktif.

### Dashboard

Fase MVP tidak membutuhkan dashboard customer. Alur yang lebih sederhana:

```text
Public catalog → Checkout/invoice → Payment provider → Webhook → Fulfillment → WhatsApp/email notification
```

Dashboard yang diperlukan:

| Role | Perlu? | Fungsi |
|---|---:|---|
| Customer | Belum | Checkout lewat link/invoice; order status lewat secure token atau WhatsApp |
| Support/Operator | Ya | Melihat order, konfirmasi inquiry, memproses fulfillment, membalas customer |
| Admin | Ya | Mengelola produk, SKU, stok, harga, terms, refund request, dan laporan |
| Super Admin/Owner | Ya, terbatas | Mengelola user/role, provider config reference, audit, feature flags, dan emergency actions |
| Finance | Opsional | Melihat payment, reconciliation, refund, settlement; tidak boleh mengubah katalog |
| Developer/Read-only | Opsional | Monitoring dan debugging tanpa akses credential atau data sensitif penuh |

**Super admin bukan role harian.** Gunakan hanya untuk perubahan akses, konfigurasi kritis, dan audit. Untuk pekerjaan rutin, gunakan Admin atau Operator.

---

## 2. Arsitektur logical

```text
[Browser]
   |
   v
[Public Catalog / Checkout UI]
   |
   v
[PHP Application]
   |-- Auth + RBAC + CSRF + Rate Limit
   |-- Catalog Service
   |-- Order Service
   |-- Payment Service
   |-- Fulfillment Service
   |-- Notification Service
   |-- Audit Service
   |
   +--> [MySQL/MariaDB]
   +--> [PaymentProvider Adapter]
   |       |-- Midtrans/Xendit/DOKU adapter
   |       +-- Curies/CRING adapter setelah validasi
   +--> [WhatsApp deep link / notification provider]
   +--> [Error monitoring + backup]

[Payment Webhook]
   |
   v
[Webhook Endpoint]
   |-- Read raw body
   |-- Verify signature/timestamp
   |-- Deduplicate provider event ID
   |-- Validate amount/currency/order
   |-- Update payment and order transactionally
   +--> enqueue fulfillment only once
```

### Prinsip penting

- Browser redirect **tidak pernah** menjadi sumber kebenaran pembayaran.
- Hanya webhook yang signature-nya valid yang boleh membuat order menjadi `paid`.
- Harga disalin sebagai snapshot ke `order_items`; perubahan harga katalog tidak mengubah order lama.
- Fulfillment harus idempotent; webhook yang diulang tidak boleh mengirim produk dua kali.
- Credential akun digital tidak boleh disimpan di database biasa atau ditulis ke log.
- Semua aksi admin penting masuk `audit_logs`.

---

## 3. State machine order dan payment

### Order

```text
inquiry
  → awaiting_payment
  → paid
  → fulfilling
  → fulfilled

inquiry/awaiting_payment/paid/fulfilling
  → cancelled

paid/fulfilled
  → refunded
```

### Payment

```text
pending → paid
pending → failed
paid → refunded
```

Rules:

- `paid` hanya dari verified webhook atau rekonsiliasi manual yang memiliki bukti dan audit trail.
- Amount, currency, merchant order ID, dan provider event ID wajib cocok.
- Event lama, duplicate, invalid signature, atau nominal berbeda masuk dead-letter/manual review.
- Refund tidak langsung menghapus order; status berubah dan alasan refund dicatat.

---

## 4. Interface payment provider PHP

Domain tidak bergantung pada SDK provider:

```php
interface PaymentProvider
{
    public function createPayment(PaymentRequest $request): PaymentIntent;

    public function verifyWebhook(
        string $rawBody,
        array $headers
    ): VerifiedPaymentEvent;

    public function refund(RefundRequest $request): RefundResult;
}
```

Struktur yang disarankan:

```text
app/
  Domain/
    Catalog/
    Orders/
    Payments/
    Fulfillment/
  Infrastructure/
    Payments/
      PaymentProvider.php
      MidtransPaymentProvider.php
      XenditPaymentProvider.php
      CuriesPaymentProvider.php   # hanya setelah dokumentasi tervalidasi
  Http/
    Controllers/
      CheckoutController.php
      PaymentWebhookController.php
      AdminOrderController.php
```

`PaymentService` internal yang mengatur state order. Adapter hanya menerjemahkan request/response provider.

---

## 5. RBAC dan permission matrix

Jangan hanya menyembunyikan tombol di frontend. Permission wajib dicek di server.

| Permission | Operator | Admin | Finance | Super Admin |
|---|---:|---:|---:|---:|
| View catalog/order | Ya | Ya | Payment view | Ya |
| Update stock | Terbatas | Ya | Tidak | Ya |
| Update price | Tidak | Ya | Tidak | Ya |
| Fulfill order | Ya | Ya | Tidak | Ya |
| Mark manual review | Ya | Ya | Ya | Ya |
| Approve refund | Tidak | Ya | Ya | Ya |
| View payment details | Terbatas | Ya | Ya | Ya |
| Manage users/roles | Tidak | Tidak | Tidak | Ya |
| Change provider config | Tidak | Tidak | Tidak | Ya |
| View audit log | Terbatas | Ya | Ya | Ya |

Minimum security:

- Password hash Argon2id.
- MFA untuk Admin, Finance, dan Super Admin.
- Session rotation setelah login.
- CSRF token untuk semua mutation.
- Rate limit login, webhook, dan reset password.
- Re-authentication untuk refund, role change, dan provider config.
- Audit log immutable secara aplikasi.
- Super admin emergency action memerlukan reason.

---

## 6. L8 Engineer AI workflow

“L8 Engineer AI” sebaiknya dipakai sebagai **operating model**, bukan sekadar meminta AI menulis kode cepat. AI membantu reasoning dan execution, tetapi manusia tetap accountable untuk business, legal, security, dan production approval.

### Workflow standar

#### Phase A — Understand

AI membaca:

- README dan architecture docs.
- Schema/migrations.
- Route/controller/service terkait.
- Threat model.
- Existing tests dan deployment config.

Output wajib:

- Fakta dari repository.
- Asumsi yang belum tervalidasi.
- Risiko.
- File yang kemungkinan berubah.

#### Phase B — Design

Buat ticket dengan format:

```text
Goal:
User/operator impact:
Acceptance criteria:
Out of scope:
Security considerations:
Data impact:
Rollback:
Verification commands:
```

Untuk payment, tambahkan:

- Provider docs URL.
- Sandbox credentials availability.
- Webhook signing method.
- Duplicate/replay strategy.
- Refund behavior.
- Reconciliation strategy.

#### Phase C — ADR

Catat keputusan besar, contohnya:

- ADR-001: Hosted checkout untuk MVP.
- ADR-002: Payment provider adapter.
- ADR-003: No customer dashboard in MVP.
- ADR-004: Verified webhook as payment source of truth.
- ADR-005: RBAC with least privilege.

#### Phase D — Vertical slice

Kerjakan satu alur kecil end-to-end:

```text
Create order → create payment intent → receive webhook → mark paid → create fulfillment task
```

Jangan membangun seluruh dashboard dan semua provider sekaligus.

#### Phase E — Adversarial verification

AI wajib membuat test untuk:

- Invalid signature.
- Duplicate webhook.
- Replay webhook lama.
- Nominal berbeda.
- Currency berbeda.
- Expired payment.
- Provider timeout.
- Database transaction failure.
- Unauthorized admin endpoint.
- CSRF failure.
- Rate limit.

#### Phase F — Review

Review sebagai beberapa persona:

1. Senior PHP engineer: correctness dan maintainability.
2. Security engineer: auth, webhook, secrets, data exposure.
3. Finance/ops reviewer: refund dan reconciliation.
4. Product manager: edge case dan UX.
5. SRE: logs, alerts, backup, rollback.

#### Phase G — Deploy safely

- Sandbox first.
- Feature flag untuk payment.
- Deploy production dengan provider disabled.
- Smoke test tanpa uang nyata.
- Enable untuk internal allowlist.
- Monitor.
- Expand gradually.

---

## 7. Roadmap realistis

### M1 — Database dan operator console

- MySQL/MariaDB migrations.
- Auth dan RBAC.
- CRUD product/SKU.
- Stock status.
- Order queue.
- Audit log.
- Import 26 SKU.

### M2 — Checkout tanpa payment live

- Customer checkout form.
- Order creation.
- Terms acceptance.
- Invoice/order reference.
- WhatsApp fallback.
- Expiry state.

### M3 — Payment sandbox

- One provider first.
- `PaymentProvider` interface.
- Hosted checkout.
- Signed webhook.
- Idempotency.
- Replay/duplicate tests.
- Reconciliation report.

### M4 — Production payment dan fulfillment

- Business verification.
- Refund SOP.
- Manual review queue.
- Fulfillment template.
- Secret handling/vault reference.
- Alerting dan incident runbook.

### M5 — Customer account, only if needed

Tambahkan customer dashboard jika terdapat kebutuhan nyata seperti:

- Banyak order per customer.
- Riwayat invoice.
- Self-service status.
- Download receipt.
- Support ticket.

Sebelum itu, secure order link plus WhatsApp lebih murah dan lebih aman untuk MVP.

---

## 8. Definition of Done payment

Fitur payment belum dianggap selesai jika:

- Belum ada sandbox test.
- Webhook belum signed dan idempotent.
- Redirect browser masih bisa menandai order `paid`.
- Duplicate event menggandakan fulfillment.
- Refund belum punya audit trail.
- Secret masuk Git atau log.
- Tidak ada reconciliation report.
- Tidak ada rollback dan feature flag.
- Legalitas produk digital belum diverifikasi.
