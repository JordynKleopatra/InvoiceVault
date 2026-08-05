-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 06, 2026 at 12:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `invoicevault`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `ip_address`, `log_time`) VALUES
(1, 1, 'User logged in', '::1', '2026-08-04 20:19:08'),
(2, 1, 'Added customer: ACC0001', '::1', '2026-08-05 19:37:12'),
(3, 1, 'Added customer: ACC20006', '::1', '2026-08-05 19:53:48'),
(4, 1, 'Deleted customer ACC20006', '::1', '2026-08-05 19:54:50'),
(5, 1, 'Created invoice INV-2026-103539', '::1', '2026-08-05 20:11:48'),
(6, 1, 'Updated invoice INV-2026-103539', '::1', '2026-08-05 20:16:34'),
(7, 1, 'Deleted invoice INV-2026-103539', '::1', '2026-08-05 20:18:20'),
(8, 1, 'Created invoice INV-2026-427978', '::1', '2026-08-05 20:23:47'),
(9, 1, 'Deleted invoice INV-2026-427978', '::1', '2026-08-05 20:23:58'),
(10, 1, 'Created invoice INV-2026-079546', '::1', '2026-08-05 20:32:29'),
(11, 1, 'Recorded payment for INV-2026-079546', '::1', '2026-08-05 20:32:57'),
(12, 1, 'Updated payment ID 1', '::1', '2026-08-05 20:34:59'),
(13, 1, 'Recorded payment for INV-2026-079546', '::1', '2026-08-05 20:37:14'),
(14, 1, 'Deleted payment for INV-2026-079546', '::1', '2026-08-05 20:37:32'),
(15, 1, 'Added customer: ACC20006', '::1', '2026-08-05 20:48:08'),
(16, 1, 'Created invoice INV-2026-617636', '::1', '2026-08-05 20:48:36'),
(17, 1, 'Recorded payment for INV-2026-617636', '::1', '2026-08-05 20:49:48'),
(18, 1, 'Recorded payment for INV-2026-617636', '::1', '2026-08-05 20:51:00'),
(19, 1, 'Created invoice INV-2026-398081', '::1', '2026-08-05 21:33:27'),
(20, 1, 'Recorded payment for INV-2026-398081', '::1', '2026-08-05 21:36:45');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `account_status` enum('Active','Inactive') DEFAULT 'Active',
  `account_number` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `province`, `postal_code`, `account_status`, `account_number`, `created_at`) VALUES
(1, 'john', 'smith', 'john@example.com', '0821234567', '123 main street', 'pretoria', 'gauteng', '0002', 'Active', 'ACC0001', '2026-08-05 19:37:12'),
(3, 'Olebogeng', 'Chailane', 'ole@123.com', '0987654321', '321 main street', 'cape town', 'western cape', '0998', 'Active', 'ACC20006', '2026-08-05 20:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` varchar(30) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('Unpaid','Partially Paid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `vat`, `total`, `status`, `created_at`) VALUES
(3, 'INV-2026-079546', 1, '2026-08-05', '2026-08-12', 99999999.99, 99999999.99, 99999999.99, 'Partially Paid', '2026-08-05 20:32:29'),
(4, 'INV-2026-617636', 3, '2026-07-31', '2026-08-31', 100000.00, 15000.00, 115000.00, 'Paid', '2026-08-05 20:48:36'),
(5, 'INV-2026-398081', 3, '2025-02-05', '2026-08-05', 1000000.00, 150000.00, 1150000.00, 'Partially Paid', '2026-08-05 21:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','EFT') NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `payment_date`, `amount_paid`, `payment_method`, `reference_number`, `created_at`) VALUES
(2, 3, '2026-08-05', 500000.00, 'Cash', '556656', '2026-08-05 20:37:14'),
(3, 4, '2026-08-05', 10000.00, 'Cash', '666989', '2026-08-05 20:49:48'),
(4, 4, '2026-08-05', 105000.00, 'Cash', '666989', '2026-08-05 20:51:00'),
(5, 5, '2026-08-05', 6000.00, 'EFT', '666989', '2026-08-05 21:36:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Finance Officer','Auditor') DEFAULT 'Finance Officer',
  `account_status` enum('Active','Inactive','Locked') DEFAULT 'Active',
  `failed_attempts` int(11) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password`, `role`, `account_status`, `failed_attempts`, `last_login`, `created_at`) VALUES
(1, 'Olebogeng Chailane', 'username', 'olebogengchailane@gmail.com', '$2y$10$RTULRWmJUipz3uN.nxx7Le3Vzgvz9m7UUd22GxdzTqsTBreaK37cC', 'Admin', 'Active', 0, '2026-08-04 20:19:08', '2026-08-04 20:07:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
