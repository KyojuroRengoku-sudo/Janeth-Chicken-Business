-- ============================================
-- Janeth's Business Inventory System
-- Database: inventory_system  (v2 – full rebuild)
-- ============================================

CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- 1. Products table
CREATE TABLE IF NOT EXISTS products (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100) NOT NULL,
    category            ENUM('Chicken','Frozen') NOT NULL DEFAULT 'Chicken',
    price               DECIMAL(10,2) DEFAULT 0.00,
    low_stock_threshold INT DEFAULT 10,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Daily inventory records
CREATE TABLE IF NOT EXISTS janeth_records (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    record_date   DATE NOT NULL,
    product_id    INT NOT NULL,
    yesterday_qty INT DEFAULT 0,
    stock_in      INT DEFAULT 0,
    remaining_qty INT DEFAULT 0,
    sold          INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_date_product (record_date, product_id)
);

-- 3. Daily expenses
CREATE TABLE IF NOT EXISTS daily_expenses (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category     VARCHAR(60) NOT NULL DEFAULT 'General',
    description  VARCHAR(200) NOT NULL,
    amount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    added_by     INT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Users table
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60) NOT NULL UNIQUE,
    password   VARCHAR(64) NOT NULL,
    role       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Sample users  (passwords are md5)
--    admin123 → 0192023a7bbd73250516f069df18b500
--    staff123 → 3c4f45b2b4820c553cb10e2d70c97736
INSERT IGNORE INTO users (username, password, role) VALUES
('admin',  '0192023a7bbd73250516f069df18b500', 'admin'),
('staff1', '3c4f45b2b4820c553cb10e2d70c97736', 'staff');

-- 6. Sample products
INSERT IGNORE INTO products (name, category, price, low_stock_threshold) VALUES
('Whole Chicken',        'Chicken', 180.00, 10),
('Chicken Breast Fillet','Chicken', 220.00, 10),
('Chicken Leg Quarter',  'Chicken', 150.00, 10),
('Chicken Wings',        'Chicken', 130.00, 10),
('Chicken Feet (Adidas)','Chicken',  80.00, 10),
('Frozen Spring Rolls',  'Frozen',   85.00,  5),
('Frozen Fish Fillet',   'Frozen',  180.00,  5),
('Frozen Squid Rings',   'Frozen',  150.00,  5),
('Frozen Mixed Veggies', 'Frozen',   65.00,  5),
('Frozen Kikiam',        'Frozen',   60.00,  5);