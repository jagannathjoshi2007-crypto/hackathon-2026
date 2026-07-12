-- AssetFlow Database Schema
-- Run via install.php or import in phpMyAdmin

CREATE DATABASE IF NOT EXISTS assetflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE assetflow;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    head_user_id INT NULL,
    parent_id INT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    warranty_months INT NULL,
    extra_fields JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','asset_manager','department_head','employee') DEFAULT 'employee',
    department_id INT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

ALTER TABLE departments ADD FOREIGN KEY (head_user_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    asset_tag VARCHAR(20) NOT NULL UNIQUE,
    serial_number VARCHAR(100) NULL,
    acquisition_date DATE NULL,
    acquisition_cost DECIMAL(12,2) DEFAULT 0,
    condition_note VARCHAR(50) DEFAULT 'Good',
    location VARCHAR(200) NULL,
    status ENUM('available','allocated','reserved','under_maintenance','lost','retired','disposed') DEFAULT 'available',
    is_bookable TINYINT(1) DEFAULT 0,
    photo_path VARCHAR(255) NULL,
    department_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES asset_categories(id),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    user_id INT NOT NULL,
    department_id INT NULL,
    allocated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_return DATE NULL,
    returned_at TIMESTAMP NULL,
    return_condition_notes TEXT NULL,
    status ENUM('active','returned','overdue') DEFAULT 'active',
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE transfer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    status ENUM('requested','approved','rejected','completed') DEFAULT 'requested',
    approved_by INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    requested_by INT NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    status ENUM('pending','approved','rejected','technician_assigned','in_progress','resolved') DEFAULT 'pending',
    technician VARCHAR(120) NULL,
    photo_path VARCHAR(255) NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE audit_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    scope_department_id INT NULL,
    scope_location VARCHAR(200) NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open','in_progress','closed') DEFAULT 'open',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scope_department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE audit_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_cycle_id INT NOT NULL,
    auditor_id INT NOT NULL,
    FOREIGN KEY (audit_cycle_id) REFERENCES audit_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (auditor_id) REFERENCES users(id)
);

CREATE TABLE audit_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_cycle_id INT NOT NULL,
    asset_id INT NOT NULL,
    status ENUM('pending','verified','missing','damaged') DEFAULT 'pending',
    notes TEXT NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (audit_cycle_id) REFERENCES audit_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES assets(id),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_audit_asset (audit_cycle_id, asset_id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(60) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(60) NULL,
    entity_id INT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
