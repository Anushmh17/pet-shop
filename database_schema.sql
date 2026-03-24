-- Pet Shop Management System
-- Database Creation Script

-- 1. Database name: petshop_db
CREATE DATABASE IF NOT EXISTS petshop_db;
USE petshop_db;

-- 2. PETS TABLE: Master inventory
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    source VARCHAR(100), -- Dealer, Customer
    type VARCHAR(50), -- Single, Pair
    qty INT DEFAULT 0,
    price DECIMAL(10, 2), -- Selling Price
    cost DECIMAL(10, 2), -- Purchase Cost
    icon VARCHAR(10), -- Emoji or code
    alert_level INT DEFAULT 10,
    stop_alert TINYINT(1) DEFAULT 0,
    pet_variety VARCHAR(255) DEFAULT '',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. PET IMAGES TABLE: Related to pets (one pet → many images)
CREATE TABLE IF NOT EXISTS pet_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT,
    image_data LONGTEXT, -- Base64 encoded string
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- 3b. CUSTOMER SUPPLIERS TABLE: Details of customers who supply pets
CREATE TABLE IF NOT EXISTS customer_suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT UNIQUE,                -- one supplier per pet
    full_name VARCHAR(255),
    nic VARCHAR(50),
    nic_photo LONGTEXT,               -- Base64 NIC image
    address TEXT,
    cost_paid DECIMAL(10,2),          -- amount paid to customer
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- 4. SALES TABLE: Transactions logs
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT,
    pet_name VARCHAR(255),
    pet_icon VARCHAR(10),
    qty INT,
    price DECIMAL(10, 2),
    total DECIMAL(10, 2),
    sale_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL
);

-- 5. DRAWER TABLE: Cash/Account tracking per date
CREATE TABLE IF NOT EXISTS drawer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_date DATE UNIQUE,
    drawer_data LONGTEXT, -- Using LONGTEXT for 100% compatibility across all MySQL versions
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
