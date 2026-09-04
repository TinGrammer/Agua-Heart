-- Agua Heart Database
CREATE DATABASE IF NOT EXISTS agua_heart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agua_heart;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    location TEXT NOT NULL,
    gallon_type ENUM('Slim','Round') NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    status ENUM('Pending','Delivered','Cancelled') DEFAULT 'Pending',
    date_ordered DATE NOT NULL,
    time_ordered TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username=admin, password=erdie_manaay1728
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$P5Fnl7e5jKYHagaAqhYkle/YiWJz8NPyL1xrQrYbJEv.PmEWdeYdC')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Sample orders
INSERT INTO orders (order_number, customer_name, contact_number, location, gallon_type, quantity, notes, status, date_ordered, time_ordered) VALUES
('AH-00001', 'Maria Santos', '09171234567', '123 Rizal St, Barangay Uno', 'Round', 3, 'Please deliver in the morning', 'Delivered', CURDATE(), '08:30:00'),
('AH-00002', 'Juan dela Cruz', '09281234567', '456 Mabini Ave, Barangay Dos', 'Slim', 5, '', 'Pending', CURDATE(), '09:15:00'),
('AH-00003', 'Ana Reyes', '09391234567', '789 Bonifacio Blvd, Barangay Tres', 'Round', 2, 'Call before delivery', 'Delivered', CURDATE(), '10:00:00'),
('AH-00004', 'Pedro Lim', '09501234567', '321 Luna St, Barangay Cuatro', 'Slim', 4, '', 'Pending', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:20:00'),
('AH-00005', 'Rosa Garcia', '09611234567', '654 Aguinaldo Rd, Barangay Cinco', 'Round', 1, '', 'Delivered', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '11:45:00'),
('AH-00006', 'Liza Dela Rosa', '09770000001', '999 Example Street, Barangay Sample', 'Slim', 2, 'Customer inactive for over 1 month', 'Pending', DATE_SUB(CURDATE(), INTERVAL 45 DAY), '07:10:00');
