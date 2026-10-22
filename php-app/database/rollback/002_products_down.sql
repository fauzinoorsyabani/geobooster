-- Geo Booster M1-T05 rollback
UPDATE products SET status = 'archived' WHERE id BETWEEN 1 AND 25;
