-- Geo Booster M1-T05 seed: 002_products.sql
SET @terms = 'v1.0-2026';

-- 1. Gemini Pro
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (1, 'gemini-pro-18m', 'Gemini Pro 18 Months Head (Link)', 'AI & Productivity', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (1, 'Default', 1290000, 'available', @terms);

-- 2. CapCut Pro 7D
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (2, 'capcut-pro-7d', 'CapCut Pro 7D FW', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (2, 'Default', 350000, 'available', @terms);

-- 3. Office 365
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (3, 'office-365-1y', 'Microsoft Office 365 Plus 1 Year', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (3, 'Default', 740000, 'available', @terms);

-- 4 & 5. Adobe Express 12M (2 variants)
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (4, 'adobe-express-12m', 'Adobe Express 12M', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (4, 'Standard', 1000000, 'available', @terms);
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (4, 'Priority', 1110000, 'available', @terms);

-- 6. Duolingo
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (5, 'duolingo-super-12m', 'Duolingo Super 12M', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (5, 'Default', 1110000, 'available', @terms);

-- 7. Canva Pro 2 YRS
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (6, 'canva-pro-2y', 'Canva Pro 2 YRS FW', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (6, 'Default', 1330000, 'available', @terms);

-- 8. iLovePDF
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (7, 'ilovepdf-1y', 'iLovePDF Premium 1YR', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (7, 'Default', 1400000, 'available', @terms);

-- 9. Gmails
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (8, 'gmails-accounts', 'Gmails Accounts', 'Utilities', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (8, 'Default', 1670000, 'available', @terms);

-- 10. Crunchyroll
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (9, 'crunchyroll-15d', 'Crunchyroll MegaFan 15 Day', 'Entertainment', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (9, 'Default', 1780000, 'available', @terms);

-- 11. Avira
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (10, 'avira-prime-3m', 'Avira Prime 3 Months', 'Utilities', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (10, 'Default', 1780000, 'available', @terms);

-- 12. Notion
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (11, 'notion-plus-12m', 'Notion Plus 12M', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (11, 'Default', 2450000, 'available', @terms);

-- 13. EDX
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (12, 'edx-premium-12m', 'EDX Premium 12 Months', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (12, 'Default', 2450000, 'available', @terms);

-- 14. CapCut Pro 1M
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (13, 'capcut-pro-1m', 'CapCut Pro 1 Month FW', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (13, 'Default', 2750000, 'available', @terms);

-- 15. Prime Video 6M
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (14, 'prime-video-6m', 'Prime Video 6 Months', 'Entertainment', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (14, 'Default', 3340000, 'available', @terms);

-- 16. CapCut Pro 1600 Credits
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (15, 'capcut-pro-credits', 'CapCut Pro 1600 Credits', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (15, 'Default', 3560000, 'available', @terms);

-- 17. JetBrains Edu
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (16, 'jetbrains-edu-12m', 'JetBrains Edu Pack 12M', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (16, 'Default', 6670000, 'available', @terms);

-- 18. Figma Pro Edu
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (17, 'figma-pro-edu-2y', 'Figma Pro Edu 2YRS', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (17, 'Default', 9340000, 'available', @terms);

-- 19. Framer AI
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (18, 'framer-ai-1y', 'Framer AI 1 Year', 'AI & Productivity', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (18, 'Default', 13340000, 'available', @terms);

-- 20. CapCut Pro 6M
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (19, 'capcut-pro-6m', 'CapCut Pro 6 Months', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (19, 'Default', 17390000, 'available', @terms);

-- 21. Miro Lifetime
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (20, 'miro-lifetime-100', 'Miro Lifetime Panel 100 Invite', 'Business & Education', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (20, 'Default', 17790000, 'available', @terms);

-- 22. Autodesk Admin
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (21, 'autodesk-admin-3000', 'Autodesk Admin 3000 Invite', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (21, 'Default', 26690000, 'available', @terms);

-- 23. YT Premium
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (22, 'yt-premium-1m', 'YT Premium 1 Bulan Invit', 'Entertainment', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (22, 'Default', 30000, 'sold_out', @terms);

-- 24. HBO Max
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (23, 'hbo-max-3m', 'HBO Max 3 Months', 'Entertainment', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (23, 'Default', 222000, 'sold_out', @terms);

-- 25. Super Grok
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (24, 'super-grok-9d', 'Super Grok 9-10 Days (W3D)', 'AI & Productivity', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (24, 'Default', 698000, 'sold_out', @terms);

-- 26. Canva Admin
INSERT IGNORE INTO products (id, slug, name, category, status, compliance_status) VALUES (25, 'canva-admin-500', 'Canva Pro Admin (500 Invitations)', 'Creative & Design', 'draft', 'pending');
INSERT IGNORE INTO skus (product_id, label, price_minor, stock_status, terms_version) VALUES (25, 'Default', 1282000, 'sold_out', @terms);
