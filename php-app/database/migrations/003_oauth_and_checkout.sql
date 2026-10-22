-- ZiuLabs Migration: 003_oauth_and_checkout.sql
-- Enables Google OAuth fields on users and customer_email / payment_method on orders

ALTER TABLE users ADD COLUMN google_id VARCHAR(64) NULL AFTER password_hash;
ALTER TABLE users ADD UNIQUE KEY uq_users_google_id (google_id);
ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER display_name;
ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL;

ALTER TABLE orders ADD COLUMN customer_email VARCHAR(190) NULL AFTER customer_name;
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER status;
