CREATE DATABASE IF NOT EXISTS pc_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pc_club;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE computers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seat_number VARCHAR(10) NOT NULL UNIQUE,
    description VARCHAR(255),
    status ENUM('available', 'maintenance') DEFAULT 'available'
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    computer_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE CASCADE,
    INDEX idx_time (computer_id, start_time, end_time)
);

INSERT INTO computers (seat_number, description) VALUES
('PC-01', 'RTX 3060 / Intel i5-12400 / 16 GB RAM'),
('PC-02', 'RTX 3060 / Intel i5-12400 / 16 GB RAM'),
('PC-03', 'RTX 4070 / Intel i7-13700 / 32 GB RAM'),
('PC-04', 'RTX 4070 / Intel i7-13700 / 32 GB RAM'),
('PC-05', 'RTX 4090 / Intel i9-13900K / 64 GB RAM — VIP'),
('PC-06', 'RTX 4090 / Intel i9-13900K / 64 GB RAM — VIP');