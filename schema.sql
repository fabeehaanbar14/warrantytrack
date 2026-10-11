-- WarrantyTrack Database Schema
-- Import this file in phpMyAdmin (or run via MySQL CLI) before using the app.

CREATE DATABASE IF NOT EXISTS warrantytrack_db;
USE warrantytrack_db;

-- ============================
-- Table: users
-- Stores both customers and admin staff.
-- Customers log in with email + phone (no password).
-- Admins log in with email + password.
-- ============================
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(20)  NULL,
    password    VARCHAR(255) NULL,  -- only used for admin accounts
    role        ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================
-- Table: products
-- Catalog of electronics/appliances Company X sells.
-- ============================
CREATE TABLE products (
    product_id  INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    category    VARCHAR(100) NOT NULL,
    price       DECIMAL(10,2) NOT NULL
);

-- ============================
-- Table: warranties
-- Links a customer (user) to a product they purchased.
-- ============================
CREATE TABLE warranties (
    warranty_id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id                INT NOT NULL,
    product_id             INT NOT NULL,
    purchase_date           DATE NOT NULL,
    warranty_length_months  INT NOT NULL,
    expiry_date             DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- ============================
-- Table: claims
-- A customer's claim submitted against one of their warranties.
-- ============================
CREATE TABLE claims (
    claim_id            INT AUTO_INCREMENT PRIMARY KEY,
    warranty_id          INT NOT NULL,
    issue_description    TEXT NOT NULL,
    date_submitted       DATETIME DEFAULT CURRENT_TIMESTAMP,
    status               ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    admin_notes          TEXT NULL,
    FOREIGN KEY (warranty_id) REFERENCES warranties(warranty_id) ON DELETE CASCADE
);

-- ============================
-- Sample data so you have something to test with right away
-- ============================
INSERT INTO products (name, category, price) VALUES
('55" 4K Smart TV', 'Television', 649.99),
('Front-Load Washing Machine', 'Appliance', 899.00),
('Wireless Noise-Cancelling Headphones', 'Audio', 249.99),
('Stainless Steel Refrigerator', 'Appliance', 1499.00),
('Robot Vacuum', 'Home', 329.99);

INSERT INTO users (name, email, phone, password, role) VALUES
('Jane Doe', 'jane.doe@example.com', '2505551234', NULL, 'customer'),
('Sam Lee', 'sam.lee@example.com', '2505555678', NULL, 'customer');

INSERT INTO warranties (user_id, product_id, purchase_date, warranty_length_months, expiry_date) VALUES
(1, 1, '2025-06-15', 24, '2027-06-15'),
(1, 3, '2025-01-10', 12, '2026-01-10'),
(2, 2, '2024-11-01', 36, '2027-11-01');

-- NOTE: No admin account is inserted here on purpose, because the password
-- needs to be hashed by PHP (password_hash), not typed in as plain text.
-- Run create_admin.php once (see project root) to create your admin login,
-- then delete that file.
