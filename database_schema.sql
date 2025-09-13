-- NAVAKSHARA TechFest Database Schema
-- Run this script to create the database and required tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS navakshara_techfest 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE navakshara_techfest;

-- Create registrations table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    college VARCHAR(200) NOT NULL,
    event ENUM('rc_plane', 'drone_racing', 'robot_war') NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_size INT DEFAULT 1,
    team_members JSON DEFAULT NULL,
    drone_model VARCHAR(100) DEFAULT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for better performance
    INDEX idx_email (email),
    INDEX idx_event (event),
    INDEX idx_registration_date (registration_date),
    INDEX idx_status (status),
    
    -- Unique constraint to prevent duplicate registrations
    UNIQUE KEY unique_registration (email, event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create events table for event management
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_code VARCHAR(50) NOT NULL UNIQUE,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    max_team_size INT DEFAULT 5,
    min_team_size INT DEFAULT 1,
    registration_fee DECIMAL(10,2) DEFAULT 0.00,
    max_registrations INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default events
INSERT INTO events (event_code, event_name, description, event_date, event_time, venue, max_team_size, min_team_size) VALUES
('rc_plane', 'RC Plane Competition', 'Design, build, and pilot your remote-controlled aircraft. Test your aerodynamics knowledge and precision flying skills.', '2025-09-15', '09:00:00', 'Outdoor Flying Field, CUTM Campus', 3, 1),
('drone_racing', 'Drone Racing', 'Navigate through challenging obstacle courses at high speeds. Showcase your piloting skills and technical expertise.', '2025-09-16', '11:00:00', 'Indoor Arena, CUTM Campus', 3, 1),
('robot_war', 'Robot War', 'Build and battle your combat robot in this intense competition. Demonstrate your engineering skills and strategic thinking.', '2025-09-17', '14:00:00', 'Combat Arena, CUTM Campus', 4, 1);

-- Create admin users table (optional, for admin panel)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'moderator', 'viewer') DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notifications table for sending updates
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    INDEX idx_is_sent (is_sent),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create a view for registration statistics
CREATE VIEW registration_stats AS
SELECT 
    event,
    COUNT(*) as total_registrations,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_registrations,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_registrations,
    SUM(team_size) as total_participants,
    AVG(team_size) as avg_team_size
FROM registrations 
GROUP BY event;

-- Create sample admin user (password: admin123 - change in production!)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@navakshara.cutm.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Display success message
SELECT 'Database schema created successfully!' as status;