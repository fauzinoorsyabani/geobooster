-- Geo Booster M1 RBAC seed
INSERT INTO roles (name, description) VALUES
  ('operator', 'Support dan fulfillment operasional'),
  ('admin', 'Katalog, order, dan operasional bisnis'),
  ('finance', 'Payment, refund, dan reconciliation'),
  ('super_admin', 'Owner dengan akses konfigurasi dan akses user terbatas'),
  ('read_only', 'Monitoring tanpa mutation')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO permissions (name, description) VALUES
  ('catalog.view', 'Melihat katalog internal'),
  ('catalog.manage', 'Membuat dan mengubah produk/SKU'),
  ('inventory.manage', 'Mengubah status stok'),
  ('orders.view', 'Melihat order'),
  ('orders.fulfill', 'Memproses fulfillment'),
  ('payments.view', 'Melihat payment dan settlement'),
  ('payments.refund', 'Mengajukan atau menyetujui refund'),
  ('users.manage', 'Mengelola user dan role'),
  ('settings.manage', 'Mengelola konfigurasi kritis'),
  ('audit.view', 'Melihat audit log')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.name = 'operator' AND p.name IN ('catalog.view','orders.view','orders.fulfill','audit.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.name = 'admin' AND p.name IN ('catalog.view','catalog.manage','inventory.manage','orders.view','orders.fulfill','payments.view','audit.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.name = 'finance' AND p.name IN ('orders.view','payments.view','payments.refund','audit.view');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.name = 'super_admin';
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.name = 'read_only' AND p.name IN ('catalog.view','orders.view','payments.view','audit.view');
