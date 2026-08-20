-- Database Creation for Desire Travel
CREATE DATABASE IF NOT EXISTS `desire_travel_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `desire_travel_db`;

-- 1. Employees Table (Admin and Staff)
CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `contact` VARCHAR(20) NOT NULL,
    `role` ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    `address` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Employee Login / Logout Audit Activity Table
CREATE TABLE IF NOT EXISTS `employee_logins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NULL,
    `username` VARCHAR(50) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    `login_time` DATETIME NOT NULL,
    `logout_time` DATETIME NULL,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
    `user_agent` TEXT NULL,
    `status` ENUM('logged_in', 'logged_out', 'failed') NOT NULL DEFAULT 'logged_in',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Buses Table
CREATE TABLE IF NOT EXISTS `buses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bus_number` VARCHAR(30) NOT NULL UNIQUE,
    `bus_name` VARCHAR(100) NOT NULL,
    `bus_type` ENUM('AC Sleeper', 'Non-AC Sleeper', 'AC Seater (2x2)', 'Non-AC Seater', 'Luxury Volvo Multi-Axle') NOT NULL DEFAULT 'AC Seater (2x2)',
    `capacity` INT NOT NULL DEFAULT 40,
    `driver_name` VARCHAR(100) NOT NULL,
    `driver_contact` VARCHAR(20) NOT NULL,
    `status` ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Routes Table
CREATE TABLE IF NOT EXISTS `routes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `route_name` VARCHAR(150) NOT NULL UNIQUE,
    `start_point` VARCHAR(100) NOT NULL,
    `end_point` VARCHAR(100) NOT NULL,
    `distance_km` DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
    `estimated_duration` VARCHAR(50) NOT NULL DEFAULT '4h 00m',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Routines / Bus Schedules Table
CREATE TABLE IF NOT EXISTS `routines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bus_id` INT NOT NULL,
    `route_id` INT NOT NULL,
    `travel_date` DATE NOT NULL,
    `departure_time` TIME NOT NULL,
    `arrival_time` TIME NOT NULL,
    `fare` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('scheduled', 'departed', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`bus_id`) REFERENCES `buses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `father_name` VARCHAR(100) NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Male',
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `contact` VARCHAR(20) NOT NULL,
    `cnic` VARCHAR(50) NOT NULL UNIQUE,
    `dob` DATE NULL,
    `address` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(40) NOT NULL UNIQUE,
    `routine_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `booked_by_employee_id` INT NULL,
    `seat_numbers` VARCHAR(100) NOT NULL,
    `seat_count` INT NOT NULL DEFAULT 1,
    `base_fare` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `discount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_fare` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('Paid', 'Pending', 'Refunded') NOT NULL DEFAULT 'Paid',
    `payment_method` ENUM('Cash', 'UPI', 'Credit/Debit Card', 'Net Banking') NOT NULL DEFAULT 'Cash',
    `booking_status` ENUM('Confirmed', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `cancellation_reason` TEXT NULL,
    `cancelled_at` DATETIME NULL,
    `refund_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (`routine_id`) REFERENCES `routines`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`booked_by_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Pricing Rules & System Settings Table
CREATE TABLE IF NOT EXISTS `pricing_rules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rule_key` VARCHAR(50) NOT NULL UNIQUE,
    `rule_name` VARCHAR(100) NOT NULL,
    `value` DECIMAL(10, 2) NOT NULL,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Insert Pricing Rules
INSERT INTO `pricing_rules` (`rule_key`, `rule_name`, `value`, `description`) VALUES
('tier_1_max_km', 'Tier 1 Max Distance (km)', 5.00, 'Up to 5 km'),
('tier_1_fixed_fare', 'Tier 1 Base Fare (₹)', 5.00, 'Flat ₹5 for first 5 km'),
('tier_2_max_km', 'Tier 2 Max Distance (km)', 15.00, 'From 5 to 15 km'),
('tier_2_rate_per_km', 'Tier 2 Rate per km (₹)', 2.00, '₹2/km for next 5-15 km'),
('tier_3_rate_per_km', 'Tier 3 Rate per km (₹)', 1.00, '₹1/km beyond 15 km'),
('luxury_multiplier', 'Luxury / AC Multiplier', 1.25, '25% extra for luxury volvo/ac sleeper')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- Insert Default Employees (passwords: admin123, emp123, clerk123)
-- admin: $2y$10$wK5E2Z3nN.Zq2h4gS21J3O0lB7Xm1N7qA2vKzX.T5d7L4Z7c4sLw6
-- emp: $2y$10$fV2B7vX7lJkZ7rD3sQ1L0O0mB6Xl0N6pA1uJyW.S4c6K3Y6b3rKv5
-- clerk1: $2y$10$fV2B7vX7lJkZ7rD3sQ1L0O0mB6Xl0N6pA1uJyW.S4c6K3Y6b3rKv5
INSERT INTO `employees` (`id`, `employee_code`, `name`, `username`, `password`, `email`, `contact`, `role`, `address`, `status`) VALUES
(1, 'DT-ADM-001', 'Rajesh Patel (Administrator)', 'admin', '$2y$10$fU4m4gA0K3s/9aG12bQ0u.K2L2gR1/sM8E6Xh4n9t5vW2xY3z4A1.', 'admin@desiretravel.com', '+91 9876543210', 'admin', 'Desire Travel HQ, CG Road, Ahmedabad, Gujarat', 'active'),
(2, 'DT-EMP-001', 'Vikas Sharma (Booking Executive)', 'emp', '$2y$10$6jD2jT1yN6yG5bV78kM2qOb7zH3eW1xY2aB4cD5eF6gH7iJ8kL9m.', 'emp@desiretravel.com', '+91 9823456789', 'employee', 'Paldi Station Counter, Ahmedabad, Gujarat', 'active'),
(3, 'DT-EMP-002', 'Pooja Mehta (Ticket Clerk)', 'clerk1', '$2y$10$6jD2jT1yN6yG5bV78kM2qOb7zH3eW1xY2aB4cD5eF6gH7iJ8kL9m.', 'pooja@desiretravel.com', '+91 9712345678', 'employee', 'Surat Terminal Office, Ring Road, Surat, Gujarat', 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Buses
INSERT INTO `buses` (`id`, `bus_number`, `bus_name`, `bus_type`, `capacity`, `driver_name`, `driver_contact`, `status`) VALUES
(1, 'GJ-01-DT-1001', 'Desire Royal Star', 'Luxury Volvo Multi-Axle', 40, 'Ramesh Bhai Barot', '+91 9898011223', 'active'),
(2, 'GJ-01-DT-2002', 'Desire Express Sleeper', 'AC Sleeper', 36, 'Jignesh Makwana', '+91 9898044556', 'active'),
(3, 'GJ-05-DT-3003', 'Desire Comfort 2x2', 'AC Seater (2x2)', 40, 'Mahesh Solanki', '+91 9898077889', 'active'),
(4, 'GJ-06-DT-4004', 'Desire Non-Stop Economy', 'Non-AC Seater', 45, 'Kanti Lal Prajapati', '+91 9898099001', 'active'),
(5, 'GJ-03-DT-5005', 'Desire Rajkot Express', 'AC Sleeper', 36, 'Bharat Sinh Zala', '+91 9898033445', 'active')
ON DUPLICATE KEY UPDATE `bus_name`=VALUES(`bus_name`);

-- Insert Routes
INSERT INTO `routes` (`id`, `route_name`, `start_point`, `end_point`, `distance_km`, `estimated_duration`, `status`) VALUES
(1, 'Ahmedabad - Surat Express', 'Ahmedabad', 'Surat', 265.00, '4h 30m', 'active'),
(2, 'Surat - Ahmedabad Express', 'Surat', 'Ahmedabad', 265.00, '4h 30m', 'active'),
(3, 'Ahmedabad - Rajkot Royal Highway', 'Ahmedabad', 'Rajkot', 215.00, '3h 45m', 'active'),
(4, 'Rajkot - Ahmedabad Royal Highway', 'Rajkot', 'Ahmedabad', 215.00, '3h 45m', 'active'),
(5, 'Ahmedabad - Vadodara Intercity', 'Ahmedabad', 'Vadodara', 110.00, '1h 50m', 'active'),
(6, 'Vadodara - Surat Superfast', 'Vadodara', 'Surat', 155.00, '2h 40m', 'active'),
(7, 'Ahmedabad - Mumbai Sleeper Connect', 'Ahmedabad', 'Mumbai', 530.00, '8h 30m', 'active'),
(8, 'Ahmedabad - Bhavnagar Heritage Route', 'Ahmedabad', 'Bhavnagar', 170.00, '3h 15m', 'active')
ON DUPLICATE KEY UPDATE `route_name`=VALUES(`route_name`);

-- Insert Routines (Bus Schedules)
INSERT INTO `routines` (`id`, `bus_id`, `route_id`, `travel_date`, `departure_time`, `arrival_time`, `fare`, `status`) VALUES
(1, 1, 1, CURRENT_DATE(), '06:00:00', '10:30:00', 450.00, 'scheduled'),
(2, 2, 1, CURRENT_DATE(), '14:30:00', '19:00:00', 550.00, 'scheduled'),
(3, 3, 3, CURRENT_DATE(), '07:30:00', '11:15:00', 380.00, 'scheduled'),
(4, 5, 4, CURRENT_DATE(), '16:00:00', '19:45:00', 420.00, 'scheduled'),
(5, 1, 7, DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), '21:00:00', '05:30:00', 950.00, 'scheduled'),
(6, 4, 5, CURRENT_DATE(), '08:00:00', '09:50:00', 180.00, 'scheduled'),
(7, 3, 8, DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), '10:00:00', '13:15:00', 310.00, 'scheduled')
ON DUPLICATE KEY UPDATE `fare`=VALUES(`fare`);

-- Insert Customers
INSERT INTO `customers` (`id`, `name`, `father_name`, `gender`, `email`, `contact`, `cnic`, `dob`, `address`) VALUES
(1, 'Aarav Bhavsar', 'Kishore Bhavsar', 'Male', 'aarav.b@example.com', '+91 9825123456', '2410-5896-1234', '1995-04-12', 'Navrangpura, Ahmedabad, Gujarat'),
(2, 'Diya Patel', 'Hasmukh Patel', 'Female', 'diya.patel@example.com', '+91 9825654321', '2410-8745-9654', '1998-09-22', 'Athwa Lines, Surat, Gujarat'),
(3, 'Hardik Shah', 'Nitin Shah', 'Male', 'hardik.shah@example.com', '+91 9825789012', '2410-3321-4567', '1990-11-05', 'Kalawad Road, Rajkot, Gujarat'),
(4, 'Priya Joshi', 'Kamlesh Joshi', 'Female', 'priya.joshi@example.com', '+91 9825987654', '2410-9988-7766', '1996-02-18', 'Alkapuri, Vadodara, Gujarat')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Sample Bookings
INSERT INTO `bookings` (`id`, `ticket_number`, `routine_id`, `customer_id`, `booked_by_employee_id`, `seat_numbers`, `seat_count`, `base_fare`, `discount`, `total_fare`, `payment_status`, `payment_method`, `booking_status`, `booking_date`) VALUES
(1, 'DT-2026-1001', 1, 1, 2, '04, 05', 2, 900.00, 0.00, 900.00, 'Paid', 'UPI', 'Confirmed', NOW() - INTERVAL 2 HOUR),
(2, 'DT-2026-1002', 1, 2, 2, '12', 1, 450.00, 0.00, 450.00, 'Paid', 'Cash', 'Confirmed', NOW() - INTERVAL 1 HOUR),
(3, 'DT-2026-1003', 3, 3, 3, '01, 02', 2, 760.00, 0.00, 760.00, 'Paid', 'Credit/Debit Card', 'Confirmed', NOW() - INTERVAL 3 HOUR),
(4, 'DT-2026-1004', 6, 4, 2, '09', 1, 180.00, 0.00, 180.00, 'Refunded', 'Cash', 'Cancelled', NOW() - INTERVAL 5 HOUR)
ON DUPLICATE KEY UPDATE `ticket_number`=VALUES(`ticket_number`);

-- Update cancellation data for sample cancelled booking
UPDATE `bookings` SET `cancellation_reason` = 'Customer travel plan rescheduled', `cancelled_at` = NOW() - INTERVAL 4 HOUR, `refund_amount` = 180.00 WHERE `id` = 4;

-- Insert Sample Employee Logins
INSERT INTO `employee_logins` (`employee_id`, `username`, `role`, `login_time`, `logout_time`, `ip_address`, `user_agent`, `status`) VALUES
(1, 'admin', 'admin', NOW() - INTERVAL 4 HOUR, NULL, '127.0.0.1', 'Desire Travel Admin Console / Chrome', 'logged_in'),
(2, 'emp', 'employee', NOW() - INTERVAL 6 HOUR, NOW() - INTERVAL 1 HOUR, '127.0.0.1', 'Terminal Counter #1 / Chrome', 'logged_out'),
(3, 'clerk1', 'employee', NOW() - INTERVAL 2 HOUR, NULL, '127.0.0.1', 'Terminal Counter #2 / Chrome', 'logged_in');
