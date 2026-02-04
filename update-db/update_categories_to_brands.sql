-- update_categories_to_brands.sql

-- 1. Rename the main table
RENAME TABLE categories TO brands;

-- 2. Update columns in products table
-- Check if foreign keys exist first and drop them if necessary? 
-- Assuming no strict foreign keys or allowing simple rename.
ALTER TABLE products CHANGE category_id brand_id INT;

-- 3. Update columns in subcategories table
ALTER TABLE subcategories CHANGE category_id brand_id INT;

-- Note: You might need to update any indexes or foreign keys manually if they don't automatically update.
-- For example if there was a FK constraint named `fk_products_categories`, it might need to be dropped and recreated pointing to `brands`.
