-- Student Performance Prediction System Database
-- Run this in phpMyAdmin to create the database

CREATE DATABASE IF NOT EXISTS student_prediction;
USE student_prediction;

-- Users table for admin login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    attendance DECIMAL(5,2) NOT NULL,
    study_hours DECIMAL(4,2) NOT NULL,
    previous_grade DECIMAL(5,2) NOT NULL,
    extracurricular TINYINT(1) DEFAULT 0,
    parent_education ENUM('None', 'Primary', 'Secondary', 'Higher') DEFAULT 'Secondary',
    family_income ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Predictions table
CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    pass_fail VARCHAR(10),
    pass_probability DECIMAL(5,2),
    dropout_risk VARCHAR(10),
    dropout_probability DECIMAL(5,2),
    needs_tutoring VARCHAR(10),
    tutoring_probability DECIMAL(5,2),
    predicted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ML Models table (stores serialized models)
CREATE TABLE ml_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(50) NOT NULL UNIQUE,
    model_data LONGBLOB NOT NULL,
    accuracy DECIMAL(5,2),
    trained_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample students
INSERT INTO students (student_id, name, age, gender, attendance, study_hours, previous_grade, extracurricular, parent_education, family_income) VALUES
('STU001', 'John Smith', 18, 'Male', 85.50, 4.5, 78.00, 1, 'Higher', 'Medium'),
('STU002', 'Sarah Johnson', 17, 'Female', 92.00, 6.0, 88.50, 1, 'Higher', 'High'),
('STU003', 'Mike Brown', 19, 'Male', 65.00, 2.0, 55.00, 0, 'Secondary', 'Low'),
('STU004', 'Emily Davis', 18, 'Female', 78.00, 3.5, 72.00, 0, 'Secondary', 'Medium'),
('STU005', 'David Wilson', 17, 'Male', 45.00, 1.0, 42.00, 0, 'Primary', 'Low');
