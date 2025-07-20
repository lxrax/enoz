-- This file contains the consolidated database schema for the Zoning and Locational Clearance System.
-- It is intended to be run once on a new database to set up all necessary tables and columns.

-- =================================================================================================
-- TABLE: users
-- Stores user accounts for the system.
-- =================================================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE
);

-- Insert an initial admin user (replace 'admin_password' with a hashed password in a real application)
INSERT INTO users (username, password, is_admin) VALUES ('admin', '$2y$10$7R.i.U2j.G5.eR.eS.Q.o.p.Q.S.r.T.u.v.w.x.y.z.A.B', TRUE); -- Hashed "admin123"

-- =================================================================================================
-- TABLE: settings
-- Stores application-wide settings.
-- =================================================================================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

-- Insert default values so the settings page can run UPDATE queries
INSERT INTO settings (setting_key, setting_value) VALUES
('province_name', 'Province of Aklan'),
('municipality_name', 'Municipality of Batan'),
('municipality_logo_path', 'uploads/logo lgu.jpg'),
('default_signatory_name', 'ENGR. ROGER A. REYES');

-- =================================================================================================
-- TABLE: zoning_certificates
-- Stores information for zoning certificates.
-- =================================================================================================
CREATE TABLE zoning_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    date_filed DATE NOT NULL,
    issue_date DATE NOT NULL,
    certificate_number VARCHAR(50) NOT NULL UNIQUE,
    expiration_date DATE NOT NULL,
    tax_declaration VARCHAR(100),
    lot_no VARCHAR(100) NULL,
    land_area VARCHAR(100) NULL,
    project_location TEXT NOT NULL,
    purpose TEXT,
    zoning_classification VARCHAR(255) NOT NULL,
    fees_paid DECIMAL(10, 2) NOT NULL,
    or_number VARCHAR(100) NOT NULL,
    encoded_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (encoded_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =================================================================================================
-- TABLE: locational_clearances
-- Stores information for locational clearances.
-- =================================================================================================
CREATE TABLE locational_clearances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_address TEXT NULL,
    developer_name VARCHAR(255) NOT NULL,
    developer_address TEXT NULL,
    project_name VARCHAR(255) NOT NULL,
    right_over_land VARCHAR(255) NULL,
    land_area VARCHAR(100) NULL,
    building_area VARCHAR(100) NULL,
    evaluation_data TEXT(1000) NULL DEFAULT NULL,
    decision TEXT NULL,
    project_location TEXT NOT NULL,
    date_filed DATE NOT NULL,
    issue_date DATE NOT NULL,
    clearance_number VARCHAR(50) NOT NULL UNIQUE,
    expiration_date DATE NOT NULL,
    fees_paid DECIMAL(10, 2) NOT NULL,
    or_number VARCHAR(100) NOT NULL,
    issued_at VARCHAR(255),
    condition1_monitoring BOOLEAN DEFAULT FALSE,
    condition2_non_compliance BOOLEAN DEFAULT FALSE,
    condition3_other_agencies BOOLEAN DEFAULT FALSE,
    condition4_activity_applied_for BOOLEAN DEFAULT FALSE,
    condition5_no_major_expansion BOOLEAN DEFAULT FALSE,
    condition6_not_cert_ownership BOOLEAN DEFAULT FALSE,
    condition7_misrepresentation BOOLEAN DEFAULT FALSE,
    condition8_commencement_period BOOLEAN DEFAULT FALSE,
    condition9_revoked BOOLEAN NOT NULL DEFAULT FALSE,
    condition10_provisional BOOLEAN NOT NULL DEFAULT FALSE,
    encoded_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (encoded_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =================================================================================================
-- TABLE: fishing_gear_permits
-- Stores information for fishing gear permits.
-- =================================================================================================
CREATE TABLE fishing_gear_permits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gear_type ENUM('Taba', 'Bentahan', 'Fish Cage', 'Talabahan') NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_resident_of VARCHAR(255),
    location VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    or_number VARCHAR(100),
    amount_paid DECIMAL(10, 2),
    date_paid DATE,
    issued_at VARCHAR(255),
    encoded_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (encoded_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);
