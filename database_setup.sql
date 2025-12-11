-- GreenPay Database Setup
-- Run this in phpMyAdmin after creating the 'greenpay' database

-- Create students table (if not exists)
CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    dob_password VARCHAR(10) NOT NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Migration: If upgrading from old schema with full_name column
-- Run these commands manually in phpMyAdmin or MySQL console:
-- ALTER TABLE students ADD COLUMN first_name VARCHAR(100) NOT NULL AFTER id;
-- ALTER TABLE students ADD COLUMN middle_name VARCHAR(100) NULL AFTER first_name;
-- ALTER TABLE students ADD COLUMN last_name VARCHAR(100) NOT NULL AFTER middle_name;
-- UPDATE students SET first_name = SUBSTRING_INDEX(full_name, ' ', 1), last_name = SUBSTRING_INDEX(full_name, ' ', -1), middle_name = CASE WHEN LENGTH(full_name) - LENGTH(REPLACE(full_name, ' ', '')) > 1 THEN TRIM(SUBSTRING(full_name, LOCATE(' ', full_name) + 1, LENGTH(full_name) - LOCATE(' ', full_name) - LENGTH(SUBSTRING_INDEX(full_name, ' ', -1)))) ELSE NULL END;
-- ALTER TABLE students DROP COLUMN full_name;

-- If table already exists, add image_path column
ALTER TABLE students ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL;

-- Create canteen_staff table (if not exists)
CREATE TABLE IF NOT EXISTS canteen_staff (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create student_balances table
CREATE TABLE IF NOT EXISTS student_balances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Create transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_date DATETIME NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Ensure transaction_date column stores date and time (DATETIME type)
-- If the column exists but is not DATETIME, update it:
ALTER TABLE transactions MODIFY COLUMN transaction_date DATETIME NOT NULL;

