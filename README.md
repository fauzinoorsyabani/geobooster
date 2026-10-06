# Geo Booster

> A secure, modular PHP commerce foundation for curated digital products.

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20Monolith-7A5CFF)](docs/architecture.md)
[![Security](https://img.shields.io/badge/Security-OWASP%20informed-2E7D32)](docs/threat-model.md)

Geo Booster is a PHP-first SaaS foundation for presenting and operating a curated catalog of digital products. It is designed for a small team that needs a reliable path from a public catalog to a secure internal operations console, followed by sandbox payment integration and auditable fulfillment.

The project intentionally starts as a **modular monolith**. Domain boundaries are explicit, but operational complexity is kept low until real traffic, team ownership, and business requirements justify extracting services.

## Current status

| Capability | Status |
|---|---|
| Public catalog | Available |
| Responsive visual design | Available |
| Animated violet motion system | Available |
| Product brand assets | Available |
| WhatsApp inquiry flow | Available |
| MySQL migration and rollback | Ready |
| RBAC seed and permission model | Ready |
| Admin login and console | Ready for a PHP + MySQL runtime |
| Secure owner provisioning | Ready |
| Payment integration | Not enabled; sandbox planning only |
| Automated fulfillment | Not enabled |
| Customer dashboard | Intentionally out of MVP |

> **Important:** Geo Booster must not be used to sell stolen credentials, unauthorized access, or products that violate a provider's terms. Product source, reseller rights, terms, refund policy, and fulfillment eligibility must be verified before publication or payment activation.

## Product direction

Geo Booster follows this operating flow:

```text
Public catalog
    → inquiry or checkout
    → order review
    → hosted payment provider
    → verified signed webhook
    → fulfillment task
    → customer support and audit trail
```

Payment is deliberately not enabled by default. A browser redirect is never treated as proof of payment; only a verified, idempotent provider webhook may transition an order to `paid`.

## Architecture at a glance

```text
Customer / Admin Browser
          │
          ▼
PHP Application — modular monolith
  ├── Public catalog
  ├── Auth and RBAC
  ├── Catalog and inventory
  ├── Orders
  ├── Payment provider adapter
  ├── Fulfillment tasks
  ├── Notifications
  └── Audit log
          │
          ├── MySQL 8 / MariaDB
          ├── Payment provider webhook
          ├── WhatsApp support flow
          └── Monitoring and backups
```

Recommended production topology:

```text
GitHub → PHP web service (Railway or PHP-FPM host)
                  │
                  └── private network → MySQL 8
```

The current Vercel site is a static catalog/preview deployment. The admin console, database, sessions, migrations, and future payment webhook require a PHP runtime and should not be deployed as static files.

## Technology choices

- **PHP 8.3+** — simple server-rendered application without TypeScript.
- **PDO MySQL** — prepared statements, transactions, and least-privilege DB access.
- **MySQL 8 / MariaDB** — relational source of truth for catalog, orders, payments, fulfillment, users, and audit logs.
- **Apache or Nginx + PHP-FPM** — production runtime with `public/` as document root.
- **Vanilla JavaScript and CSS** — progressive enhancement without a frontend build requirement.
- **Hosted payment checkout** — provider-specific details isolated behind an adapter interface.
- **Railway or equivalent PHP host** — practical MVP deployment with a managed/private MySQL service.

## Repository structure

```text
.
├── .agent/skills/                 # Geo Booster engineering rules for AI Agents
├── docs/                          # Architecture, security, product, and operations docs
├── php-app/
│   ├── app/                       # Bootstrap and security/application services
│   ├── bin/                       # Migration and owner provisioning commands
│   ├── config/                    # Non-secret configuration
│   ├── database/
│   │   ├── migrations/            # Forward schema migrations
│   │   ├── seeds/                 # RBAC and safe initial data
│   │   └── rollback/              # Explicit rollback scripts
│   ├── docker/                    # Production Apache configuration
│   ├── public/                    # Public document root
│   │   ├── admin/                 # Internal admin console
│   │   ├── assets/                # Catalog assets
│   │   └── index.php              # Public catalog entry point
│   ├── storage/                   # Runtime storage; never public
│   ├── Dockerfile                 # PHP 8.3 + Apache production image
│   └── vercel.json                # Static/proxy deployment configuration
├── .env.example                   # Environment variable template only
└── run-local.sh                   # Local development launcher
```

## Quick start

### Prerequisites

- PHP 8.3 or newer.
- MySQL 8 or MariaDB.
- PHP extensions: `pdo_mysql` and `mbstring` recommended.
- Git and GitHub access.

### Clone

```bash
git clone https://github.com/fauzinoorsyabani/geobooster.git
cd geobooster
```

### Configure local environment

```bash
cp .env.example .env
```

Set local values in `.env`:

```env
APP_ENV=local
APP_URL=http://127.0.0.1:8080
APP_KEY=generate-a-long-random-secret
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=geo_booster;charset=utf8mb4
DB_USERNAME=geo_booster
DB_PASSWORD=your-local-password
ADMIN_ENABLED=true
SESSION_SECURE_COOKIE=false
```

Never commit `.env`. It is ignored by Git and should contain only local or secret-manager values.

### Create the local database

```bash
sudo mysql
```

```sql
CREATE DATABASE geo_booster CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'geo_booster'@'localhost' IDENTIFIED BY 'your-local-password';
GRANT ALL PRIVILEGES ON geo_booster.* TO 'geo_booster'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Migrate and seed

```bash
cd php-app
php bin/migrate.php up
php bin/migrate.php seed
php bin/create-owner.php owner@example.com "Geo Booster Owner"
```

The owner command asks for a password interactively and hashes it with Argon2id. It does not create a default password.

### Run the application

From the repository root:

```bash
./run-local.sh
```

Or directly:

```bash
cd php-app
php -S 127.0.0.1:8080 -t public
```

Open:

- Public catalog: `http://127.0.0.1:8080/`
- Admin console: `http://127.0.0.1:8080/admin/`

## Database and security model

The initial schema covers:

- `users`, `roles`, `permissions`, `user_roles`, `role_permissions`
- `products` and `skus`
- `orders` and `order_items`
- `payments` and `webhook_events`
- `fulfillment_tasks`
- `audit_logs`
- `app_sessions`

Security controls include:

- PDO prepared statements.
- Argon2id password hashing.
- Session rotation after login.
- HttpOnly and SameSite cookies.
- CSRF token validation for mutations.
- Server-side RBAC checks.
- Audit logging for sensitive actions.
- Unique provider event IDs for webhook idempotency.
- Public document root restricted to `php-app/public`.
- No payment secrets, customer credentials, or `.env` files in Git.

## Roles

| Role | Responsibility |
|---|---|
| `operator` | Support and fulfillment operations |
| `admin` | Catalog, SKU, inventory, and order management |
| `finance` | Payment, refund, and reconciliation visibility |
| `super_admin` | User/role management and critical configuration |
| `read_only` | Monitoring without mutation permissions |

There is intentionally no customer dashboard in the MVP. A secure order link and WhatsApp support flow are simpler until customer account history becomes a demonstrated need.

## Payment roadmap

Payment is implemented as a provider adapter, not as provider-specific logic in the order domain:

```php
interface PaymentProvider
{
    public function createPayment(PaymentRequest $request): PaymentIntent;
    public function verifyWebhook(string $rawBody, array $headers): VerifiedPaymentEvent;
    public function refund(RefundRequest $request): RefundResult;
}
```

Before production payment:

1. Confirm the provider's official API, legal entity, sandbox, QRIS/payment methods, webhook signing, expiry, refund, and settlement behavior.
2. Use hosted checkout.
3. Verify raw webhook body, signature, timestamp, amount, currency, and provider event ID.
4. Make webhook processing idempotent.
5. Test duplicate, replayed, delayed, invalid, and mismatched events.
6. Add reconciliation and refund SOP.
7. Enable production behind a feature flag and internal allowlist.

Do not guess the API or legal status of a provider referred to as “Curies” without official documentation.

## Deployment

The recommended MVP deployment is:

```text
Railway PHP Web Service + Railway MySQL 8
```

The repository includes:

- `php-app/Dockerfile`
- `php-app/docker/000-default.conf`
- `docs/deployment-architecture.md`

Deployment principles:

- PHP service and database should be in the same region/private network.
- `public/` is the only document root.
- Secrets are supplied through environment variables or a secret manager.
- Database backups and restore drills are mandatory.
- Payment remains disabled until compliance and sandbox acceptance criteria pass.

## AI Agents engineering contract

Before changing code, an AI Agent must read the relevant README, architecture, threat model, migration, and module files. Every ticket must define:

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

Every completed task must report:

- Summary of changes.
- Files touched.
- Verification commands and results.
- Remaining risks.
- Recommended follow-up.

AI Agents must not:

- Commit secrets or expose `.env` values.
- Create a default admin password.
- Mark a payment paid from a browser redirect.
- Bypass RBAC or CSRF checks.
- Delete production records without a rollback plan.
- Enable live payment without sandbox evidence and compliance approval.

## Quality gates

Before merge:

```bash
php -l php-app/public/index.php
php -l php-app/public/admin/index.php
php -l php-app/bin/migrate.php
php -l php-app/bin/create-owner.php
git diff --check
```

For payment or fulfillment changes, also require:

- Invalid signature test.
- Duplicate webhook test.
- Replay test.
- Amount mismatch test.
- Authorization and CSRF tests.
- Failure/retry test.
- Audit trail verification.

## Documentation map

- [System Design](docs/architecture.md)
- [Deployment Architecture](docs/deployment-architecture.md)
- [Payment, RBAC, and AI Workflow](docs/architecture-payment-rbac-ai-workflow.md)
- [Project Management](docs/project-management.md)
- [Backend Standards](docs/backend.md)
- [Frontend Standards](docs/frontend.md)
- [Database Plan](docs/database-plan.md)
- [Admin Console M1](docs/admin-console-m1.md)
- [Threat Model](docs/threat-model.md)
- [Clone and AI Agents Guide](docs/clone-and-ai-agents.md)
- [Geo Booster Engineering Skill](.agent/skills/geo-booster-engineering/SKILL.md)

## License and commercial notes

This repository is a project foundation. Add an explicit license before accepting external contributions or distributing the code. Product names and logos remain subject to their respective trademark and usage policies. Business owners are responsible for verifying reseller rights, consumer terms, tax requirements, payment-provider requirements, and applicable law before going live.
