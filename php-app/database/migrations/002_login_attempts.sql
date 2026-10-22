-- Geo Booster M1-T03: login_attempts table for rate limiting
-- MySQL 8.0+ / MariaDB 10.6+

CREATE TABLE login_attempts (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_hash      CHAR(64)        NOT NULL,
  attempted_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_attempts_ip_time (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
