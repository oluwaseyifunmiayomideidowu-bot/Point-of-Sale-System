INSERT INTO categories (name, description)
VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Clothes and fashion items'),
('Food & Beverages', 'Food, snacks and drinks'),
('Home Appliances', 'Appliances used at home'),
('Stationery', 'Writing and office materials'),
('Personal Care', 'Personal hygiene and care products'),
('Accessories', 'Fashion and lifestyle accessories'),
('Sports & Fitness', 'Sports and fitness equipment'),
('Household Items', 'General household products'),
('Footwear', 'Shoes, sandals and other footwear');

SELECT id, name
FROM categories
ORDER BY id;

INSERT INTO suppliers
(first_name, last_name, username, email, phone, status)
VALUES
('Chinedu', 'Okafor', 'chinedu.okafor', 'chinedu.okafor@example.com', '08000000001', 'Active'),
('Aisha', 'Bello', 'aisha.bello', 'aisha.bello@example.com', '08000000002', 'Active'),
('David', 'Johnson', 'david.johnson', 'david.johnson@example.com', '08000000003', 'Active'),
('Grace', 'Williams', 'grace.williams', 'grace.williams@example.com', '08000000004', 'Active'),
('Ibrahim', 'Musa', 'ibrahim.musa', 'ibrahim.musa@example.com', '08000000005', 'Active'),
('Esther', 'Adeyemi', 'esther.adeyemi', 'esther.adeyemi@example.com', '08000000006', 'Active'),
('Michael', 'Brown', 'michael.brown', 'michael.brown@example.com', '08000000007', 'Active'),
('Fatima', 'Abdullahi', 'fatima.abdullahi', 'fatima.abdullahi@example.com', '08000000008', 'Active'),
('Daniel', 'Smith', 'daniel.smith', 'daniel.smith@example.com', '08000000009', 'Inactive'),
('Blessing', 'Eze', 'blessing.eze', 'blessing.eze@example.com', '08000000010', 'Active');

SELECT id, first_name, last_name, username, status
FROM suppliers
ORDER BY id;

SELECT id, name FROM categories ORDER BY id;
SELECT id, first_name, last_name FROM suppliers ORDER BY id;

INSERT INTO products
(
    category_id,
    supplier_id,
    name,
    sku,
    cost_price,
    selling_price,
    quantity,
    reorder_level,
    status
)
VALUES
(1, 1, 'HP Wireless Mouse', 'ELE-MOU-001', 8500.00, 12000.00, 35, 10, 'active'),
(1, 2, 'USB Keyboard', 'ELE-KEY-001', 7000.00, 10500.00, 28, 8, 'active'),
(1, 3, 'USB-C Charging Cable', 'ELE-CAB-001', 2500.00, 4500.00, 50, 15, 'active'),
(2, 4, 'Plain Cotton T-Shirt', 'CLO-TSH-001', 4500.00, 7500.00, 40, 10, 'active'),
(2, 5, 'Denim Jeans', 'CLO-JEA-001', 12000.00, 18500.00, 22, 6, 'active'),
(3, 6, 'Bottled Water', 'FOO-WAT-001', 150.00, 250.00, 100, 25, 'active'),
(3, 7, 'Chocolate Bar', 'FOO-CHO-001', 500.00, 800.00, 60, 15, 'active'),
(4, 8, 'Electric Kettle', 'HAP-KET-001', 18000.00, 26000.00, 15, 5, 'active'),
(5, 9, 'A4 Notebook', 'STA-NOT-001', 1200.00, 2000.00, 45, 10, 'active'),
(6, 10, 'Body Lotion', 'PER-LOT-001', 2500.00, 4000.00, 30, 8, 'active');

INSERT INTO products
(
    category_id,
    supplier_id,
    name,
    sku,
    cost_price,
    selling_price,
    quantity,
    reorder_level,
    status
)
VALUES
(
    1,
    1,
    'Example Product',
    'ELE-EXA-001',
    5000.00,
    7500.00,
    20,
    5,
    'active'
);