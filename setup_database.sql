-- Create database if not exists
CREATE DATABASE IF NOT EXISTS library_booking;
USE library_booking;

-- Create users table if not exists
CREATE TABLE IF NOT EXISTS user (
    student_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create admin table if not exists
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create reservations table if not exists
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    table_number INT NOT NULL,
    chairs_used INT NOT NULL DEFAULT 4,
    reservation_time DATETIME NOT NULL,
    status ENUM('reserved', 'cancelled', 'expired') DEFAULT 'reserved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES user(student_id)
);

-- Add indexes for better performance
CREATE INDEX idx_reservation_status ON reservations(status);
CREATE INDEX idx_reservation_time ON reservations(reservation_time);
CREATE INDEX idx_student_reservations ON reservations(student_id, status); 