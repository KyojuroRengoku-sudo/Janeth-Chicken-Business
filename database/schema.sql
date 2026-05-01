-- ============================================
-- Complete database setup for Janeth's Business
-- ============================================
-- If you get orphaned tablespace errors, first run:
-- DROP DATABASE IF EXISTS inventory_system;
-- CREATE DATABASE inventory_system;
-- USE inventory_system;
-- Then run the entire script below.

SET default_storage_engine=InnoDB;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('Chicken', 'Frozen') NOT NULL DEFAULT 'Chicken',
    selling_price DECIMAL(10,2) DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    visible_input TINYINT(1) DEFAULT 1,
    visible_dashboard TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample products
INSERT INTO products (name, category, selling_price, low_stock_threshold) VALUES
('Fresh Whole Chicken', 'Chicken', 120.00, 10),
('Chicken Breast Fillet', 'Chicken', 150.00, 8),
('Chicken Leg Quarters', 'Chicken', 110.00, 10),
('Frozen Spring Rolls', 'Frozen', 85.00, 12),
('Frozen Fish Fillet', 'Frozen', 180.00, 6),
('Frozen Mixed Vegetables', 'Frozen', 65.00, 15);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert demo users (hashes shown for 'admin123' and 'staff123')
-- To generate real hashes, use: echo password_hash('admin123', PASSWORD_DEFAULT);
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    notes TEXT
) ENGINE=InnoDB;

INSERT INTO suppliers (name, contact) VALUES ('Default Supplier', 'N/A');

-- Stock entries
CREATE TABLE IF NOT EXISTS stock_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_date DATE NOT NULL,
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    qty INT NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Daily expenses
CREATE TABLE IF NOT EXISTS daily_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category VARCHAR(50),
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Liquidations
CREATE TABLE IF NOT EXISTS liquidations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    liquidation_date DATE UNIQUE NOT NULL,
    opening_cash DECIMAL(10,2) DEFAULT 0,
    cash_sales DECIMAL(10,2) DEFAULT 0,
    total_expenses DECIMAL(10,2) DEFAULT 0,
    stock_cost DECIMAL(10,2) DEFAULT 0,
    actual_cash DECIMAL(10,2) DEFAULT 0,
    notes TEXT
) ENGINE=InnoDB;

-- Janeth records (daily inventory)
CREATE TABLE IF NOT EXISTS janeth_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_date DATE NOT NULL,
    product_id INT NOT NULL,
    yesterday_qty INT DEFAULT 0,
    stock_in INT DEFAULT 0,
    remaining_qty INT DEFAULT 0,
    sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_date_product (record_date, product_id)
) ENGINE=InnoDB;