Database name:donation
-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash TEXT NOT NULL,
    role ENUM('admin', 'donor', 'recipient') NOT NULL,
    status ENUM('pending', 'approved', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ADMINS
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- DONORS
CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    blood_group VARCHAR(5) NOT NULL,
    location VARCHAR(150),
    age INT,
    weight INT,
    is_active BOOLEAN DEFAULT TRUE,
    total_donations INT DEFAULT 0,
    last_donation_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- DONOR VERIFICATIONS
CREATE TABLE donor_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    id_document TEXT,
    medical_report TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (donor_id) REFERENCES donors(id),
    FOREIGN KEY (verified_by) REFERENCES admins(id)
);

-- RECIPIENTS
CREATE TABLE recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    location VARCHAR(150),
    total_requests INT DEFAULT 0,
    active_requests INT DEFAULT 0,
    completed_requests INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- RECIPIENT MEDICAL VERIFICATIONS
CREATE TABLE recipient_medical_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT,
    medical_document TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (recipient_id) REFERENCES recipients(id),
    FOREIGN KEY (verified_by) REFERENCES admins(id)
);

-- BLOOD REQUESTS
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT,
    patient_name VARCHAR(100),
    blood_group VARCHAR(5),
    units_required INT,
    hospital_name VARCHAR(150),
    hospital_location VARCHAR(150),
    contact_phone VARCHAR(20),
    urgency_level ENUM('low', 'medium', 'high', 'critical'),
    is_emergency BOOLEAN DEFAULT FALSE,
    admin_approved BOOLEAN DEFAULT FALSE,
    evidence_document TEXT,
    status ENUM('pending', 'approved', 'matched', 'completed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES recipients(id)
);

-- DONATION REQUESTS
CREATE TABLE donation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_request_id INT,
    donor_id INT,
    status ENUM('sent', 'accepted', 'rejected', 'completed') DEFAULT 'sent',
    appointment_date DATE,
    appointment_time TIME,
    accepted_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (blood_request_id) REFERENCES blood_requests(id),
    FOREIGN KEY (donor_id) REFERENCES donors(id)
);

-- DONATION HISTORY
CREATE TABLE donation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    recipient_id INT,
    blood_group VARCHAR(5),
    units INT,
    hospital_name VARCHAR(150),
    donation_date DATE,
    status ENUM('completed') DEFAULT 'completed',
    FOREIGN KEY (donor_id) REFERENCES donors(id),
    FOREIGN KEY (recipient_id) REFERENCES recipients(id)
);

-- SUSPICIOUS PROFILES
CREATE TABLE suspicious_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reason TEXT,
    risk_level ENUM('low', 'medium', 'high') DEFAULT 'high',
    status ENUM('flagged', 'blocked', 'cleared') DEFAULT 'flagged',
    flagged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- NOTIFICATIONS
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(100),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ADMIN ACTIONS
CREATE TABLE admin_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action_type VARCHAR(50),
    target_table VARCHAR(50),
    target_id INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

Add:admin table
USE donation;
ALTER TABLE admins
ADD password VARCHAR(255) NOT NULL;
INSERT INTO admins (id, password)
VALUES (1, 'a');
