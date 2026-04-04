-- ==========================================
-- Land Survey Database Setup Script
-- ==========================================
-- Run this script in PHPMyAdmin or MySQL command line
-- to set up the complete database structure

-- Create Database
CREATE DATABASE IF NOT EXISTS land_survey_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE land_survey_db;

-- ==========================================
-- Table: bookings
-- Stores all customer booking requests
-- ==========================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(255) NOT NULL,
    survey_type VARCHAR(100) NOT NULL,
    preferred_date DATE NOT NULL,
    message TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_date (preferred_date),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: admin_users
-- Stores admin user credentials
-- ==========================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: site_data
-- Stores site configuration and settings
-- ==========================================
CREATE TABLE IF NOT EXISTS site_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_key VARCHAR(100) UNIQUE NOT NULL,
    data_value TEXT,
    data_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (data_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Insert Default Admin User
-- Username: admin
-- Password: admin123
-- IMPORTANT: Change this password after first login!
-- ==========================================
INSERT INTO admin_users (username, password_hash, email, full_name) 
VALUES (
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin@sgsurvey.com', 
    'Administrator'
) ON DUPLICATE KEY UPDATE username=username;

-- ==========================================
-- Insert Default Site Data
-- Update these values as needed
-- ==========================================
INSERT INTO site_data (data_key, data_value, data_type) VALUES
    ('phone_primary', '9475465392', 'text'),
    ('phone_secondary', '8637829746', 'text'),
    ('email', 'swarupanandaghosh2@gmail.com', 'text'),
    ('location', 'Kochkunda, Shitla, Bankura', 'text'),
    ('charge_land_survey', '5000', 'number'),
    ('charge_digital_survey', '8000', 'number'),
    ('charge_autocad_sketch', '3000', 'number'),
    ('charge_laser_survey', '10000', 'number')
ON DUPLICATE KEY UPDATE data_key=data_key;

-- ==========================================
-- Insert Sample Bookings (Optional)
-- Uncomment to add sample data for testing
-- ==========================================
/*
INSERT INTO bookings (name, phone, location, survey_type, preferred_date, message, status) VALUES
    ('Rajesh Kumar', '9876543210', 'Bankura Town', 'Digital Land Survey', '2024-04-10', 'Need survey for new construction', 'pending'),
    ('Priya Sharma', '8765432109', 'Bishnupur', 'Land Survey', '2024-04-12', 'Property boundary verification', 'confirmed'),
    ('Amit Patel', '7654321098', 'Saltora', 'AutoCAD Plot Sketch', '2024-04-08', 'Technical drawings needed', 'completed'),
    ('Sunita Devi', '6543210987', 'Kochkunda', 'Laser Range Finder Survey', '2024-04-15', 'Large property measurement', 'pending');
*/

-- ==========================================
-- Database Setup Complete!
-- ==========================================
-- Next Steps:
-- 1. Update db.php with your database credentials
-- 2. Test the website and booking form
-- 3. Login to admin panel with:
--    Username: admin
--    Password: admin123
-- 4. CHANGE THE DEFAULT PASSWORD IMMEDIATELY!
-- ==========================================
