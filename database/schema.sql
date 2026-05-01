-- ============================================
-- Janeth's Business Inventory System
-- Database: inventory_system
-- ============================================

CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- 1. Products table (with category)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('Chicken', 'Frozen') NOT NULL DEFAULT 'Chicken',
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Janeth daily records (unchanged)
CREATE TABLE IF NOT EXISTS janeth_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_date DATE NOT NULL,
    product_id INT NOT NULL,
    yesterday_qty INT DEFAULT 0,
    stock_in INT DEFAULT 0,
    distributed INT DEFAULT 0,
    sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_date_product (record_date, product_id)
);

-- 3. Insert sample products (Chicken and Frozen)
INSERT INTO products (name, category, price) VALUES
('Fresh Whole Chicken', 'Chicken', 120.00),
('Chicken Breast Fillet', 'Chicken', 150.00),
('Chicken Leg Quarters', 'Chicken', 110.00),
('Frozen Spring Rolls', 'Frozen', 85.00),
('Frozen Fish Fillet', 'Frozen', 180.00),
('Frozen Mixed Vegetables', 'Frozen', 65.00);

-- Optional test record (today)
-- INSERT INTO janeth_records (record_date, product_id, yesterday_qty, stock_in, distributed, sold)
-- VALUES (CURDATE(), 1, 10, 20, 15, 12);