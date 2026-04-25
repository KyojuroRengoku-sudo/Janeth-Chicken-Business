-- ============================================
-- Janeth's Business Inventory System
-- Database: inventory_system
-- ============================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- ============================================
-- 1. Products table (master list)
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. Janeth daily records
-- ============================================
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

-- ============================================
-- 3. Insert sample products
-- ============================================
INSERT INTO products (name, price) VALUES
('Product A', 10.00),
('Product B', 15.50),
('Product C', 8.75),
('Product D', 22.00);

-- Optional: Insert a few test records (example for today)
-- INSERT INTO janeth_records (record_date, product_id, yesterday_qty, stock_in, distributed, sold)
-- VALUES (CURDATE(), 1, 5, 10, 8, 7);