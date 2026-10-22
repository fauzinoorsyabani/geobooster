-- ZiuLabs Migration: 004_credential_pool.sql
-- Credential pool table for automated digital product fulfillment (Account/License/Invite dispatch)

CREATE TABLE IF NOT EXISTS sku_credentials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sku_id BIGINT UNSIGNED NOT NULL,
  credential_type ENUM('account','license_key','invite_link','custom') NOT NULL DEFAULT 'account',
  account_email VARCHAR(190) NULL,
  account_password VARCHAR(255) NULL,
  license_key TEXT NULL,
  invite_url VARCHAR(500) NULL,
  additional_notes TEXT NULL,
  status ENUM('available','assigned','reserved','invalid') NOT NULL DEFAULT 'available',
  assigned_order_id BIGINT UNSIGNED NULL,
  assigned_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sku_credentials_sku_status (sku_id, status),
  KEY idx_sku_credentials_order (assigned_order_id),
  CONSTRAINT fk_sku_credentials_sku FOREIGN KEY (sku_id) REFERENCES skus (id) ON DELETE CASCADE,
  CONSTRAINT fk_sku_credentials_order FOREIGN KEY (assigned_order_id) REFERENCES orders (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
