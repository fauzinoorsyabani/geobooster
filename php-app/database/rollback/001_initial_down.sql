-- Geo Booster M1 rollback. Review production dependencies before executing.
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS app_sessions;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS webhook_events;
DROP TABLE IF EXISTS fulfillment_tasks;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS skus;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS=1;
