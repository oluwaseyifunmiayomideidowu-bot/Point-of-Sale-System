-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2026 at 02:42 PM
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
-- Database: `point_of_sale_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Electronics', 'Electronic devices and accessories', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(7, 'Clothing', 'Clothes and fashion items', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(8, 'Food & Beverages', 'Food, snacks and drinks', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(9, 'Home Appliances', 'Appliances used at home', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(10, 'Stationery', 'Writing and office materials', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(11, 'Personal Care', 'Personal hygiene and care products', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(12, 'Accessories', 'Fashion and lifestyle accessories', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(13, 'Sports & Fitness', 'Sports and fitness equipment', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(14, 'Household Items', 'General household products', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48'),
(15, 'Footwear', 'Shoes, sandals and other footwear', 'active', '2026-09-15 19:16:48', '2026-09-15 19:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `expense_number` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `user_id`, `expense_number`, `category`, `description`, `amount`, `expense_date`) VALUES
(1, 7, 'EXP-20260917124633-232953', 'Electricity', 'Shop electricity bill', 45000.00, '2026-09-17 10:46:33'),
(2, 7, 'EXP-20260917125852-669C91', 'Electricity', 'Shop electricity bill', 45500.00, '2026-09-17 10:58:52'),
(3, 7, 'EXP-20260917125900-4A4A9A', 'Electricity', 'Shop electricity bill', 45600.00, '2026-09-17 10:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `movement_type` enum('Purchase','Sale','Return','Adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `cost_price` decimal(12,2) NOT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `name`, `sku`, `cost_price`, `selling_price`, `quantity`, `reorder_level`, `image`, `status`, `created_at`, `updated_at`) VALUES
(3, 6, 8, 'HP Wireless Mouse', 'VND-9642076D', 8500.00, 12000.00, 30, 10, 'uploads/products/26ea01f3c7760919deda029c60b960aa.webp', 'active', '2026-09-15 19:25:34', '2026-09-16 11:30:52'),
(4, 6, 6, 'Foldable And Flexible Keyboard', 'VND-C0F0EEB1', 7000.00, 10500.00, 43, 8, 'uploads/products/5fc339c4e97dd73504584316532680c3.webp', 'active', '2026-09-15 19:29:21', '2026-09-16 12:09:09'),
(5, 6, 11, 'USB-C Charging Cable', 'VND-B5501D23', 2500.00, 4500.00, 38, 15, 'uploads/products/79fe1b7c827a4655181bf24b7d1ee99e.jpg', 'active', '2026-09-15 20:38:09', '2026-09-16 12:09:09'),
(6, 7, 12, 'Plain Cotton T-Shirt', 'VND-8AD10856', 4500.00, 7500.00, 40, 10, 'uploads/products/a4e04465a21cb7fecf70ca56110cbe24.jpg', 'active', '2026-09-16 22:38:33', '2026-09-16 22:38:33'),
(7, 7, 13, 'Denim Jeans', 'VND-DD32ABF4', 12000.00, 18500.00, 22, 6, 'uploads/products/7a45855724c609c59dbfeb1507402679.jpg', 'active', '2026-09-16 22:41:35', '2026-09-16 22:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `supplier_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `supplier_id`, `user_id`, `purchase_number`, `total_amount`, `status`, `created_at`) VALUES
(2, 6, 2, 'PUR-20260916140836-085887', 3100000.00, 'Pending', '2026-09-16 12:08:36'),
(3, 6, 2, 'PUR-20260916140909-332793', 3100000.00, 'Pending', '2026-09-16 12:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `cost_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `quantity`, `cost_price`, `subtotal`) VALUES
(1, 2, 5, 10, 300000.00, 3000000.00),
(2, 2, 4, 20, 5000.00, 100000.00),
(3, 3, 5, 10, 300000.00, 3000000.00),
(4, 3, 4, 20, 5000.00, 100000.00);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `total_refund` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `sale_id`, `user_id`, `return_number`, `reason`, `total_refund`, `created_at`) VALUES
(1, 10, 2, 'RET-20260916133444-8146DB', 'there is a reason', 4500.00, '2026-09-16 11:34:44'),
(2, 10, 2, 'RET-20260916133529-A2EABC', 'there is a reason', 4500.00, '2026-09-16 11:35:29');

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `return_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_items`
--

INSERT INTO `return_items` (`id`, `return_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 5, 1, 4500.00, 4500.00),
(2, 2, 5, 1, 4500.00, 4500.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `sale_number` varchar(50) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Cash','Card','Transfer') NOT NULL,
  `amount_received` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Completed','Cancelled') NOT NULL DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `sale_number`, `subtotal`, `discount`, `tax`, `total_amount`, `payment_method`, `amount_received`, `change_amount`, `status`, `created_at`) VALUES
(10, 2, 'SALE-20260916-0001', 19500.00, 500.00, 0.00, 19000.00, 'Cash', 850000.00, 831000.00, 'Completed', '2026-09-16 11:30:33'),
(11, 2, 'SALE-20260916-0002', 28500.00, 500.00, 0.00, 28000.00, 'Cash', 850000.00, 822000.00, 'Completed', '2026-09-16 11:30:40'),
(12, 2, 'SALE-20260916-0003', 70500.00, 500.00, 0.00, 70000.00, 'Cash', 850000.00, 780000.00, 'Completed', '2026-09-16 11:30:44'),
(13, 2, 'SALE-20260916-0004', 78000.00, 500.00, 0.00, 77500.00, 'Cash', 850000.00, 772500.00, 'Completed', '2026-09-16 11:30:52');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL,
  `sale_number` varchar(50) DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `sale_number`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(13, 10, 'SALE-20260916-0001', 5, 2, 4500.00, 9000.00),
(14, 10, 'SALE-20260916-0001', 4, 1, 10500.00, 10500.00),
(15, 11, 'SALE-20260916-0002', 5, 4, 4500.00, 18000.00),
(16, 11, 'SALE-20260916-0002', 4, 1, 10500.00, 10500.00),
(17, 12, 'SALE-20260916-0003', 5, 4, 4500.00, 18000.00),
(18, 12, 'SALE-20260916-0003', 4, 5, 10500.00, 52500.00),
(19, 13, 'SALE-20260916-0004', 5, 4, 4500.00, 18000.00),
(20, 13, 'SALE-20260916-0004', 3, 5, 12000.00, 60000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_sequences`
--

CREATE TABLE `sale_sequences` (
  `sale_date` date NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_sequences`
--

INSERT INTO `sale_sequences` (`sale_date`, `last_number`) VALUES
('2026-09-16', 4);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Chinedu', 'Okafor', 'chinedu.okafor', 'chinedu.okafor@example.com', '08000000001', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(7, 'Aisha', 'Bello', 'aisha.bello', 'aisha.bello@example.com', '08000000002', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(8, 'David', 'Johnson', 'david.johnson', 'david.johnson@example.com', '08000000003', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(9, 'Grace', 'Williams', 'grace.williams', 'grace.williams@example.com', '08000000004', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(10, 'Ibrahim', 'Musa', 'ibrahim.musa', 'ibrahim.musa@example.com', '08000000005', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(11, 'Esther', 'Adeyemi', 'esther.adeyemi', 'esther.adeyemi@example.com', '08000000006', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(12, 'Michael', 'Brown', 'michael.brown', 'michael.brown@example.com', '08000000007', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(13, 'Fatima', 'Abdullahi', 'fatima.abdullahi', 'fatima.abdullahi@example.com', '08000000008', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(14, 'Daniel', 'Smith', 'daniel.smith', 'daniel.smith@example.com', '08000000009', 'Inactive', '2026-09-15 19:17:25', '2026-09-15 19:17:25'),
(15, 'Blessing', 'Eze', 'blessing.eze', 'blessing.eze@example.com', '08000000010', 'Active', '2026-09-15 19:17:25', '2026-09-15 19:17:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Administrator','Manager','Cashier') NOT NULL DEFAULT 'Administrator',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `password`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(2, 'Name', 'names', 'name', 'name@gmail.com', '0987654567', '$2y$12$pZfUgOWHAqb6GSVeU8snaeAu/TWrsFbPQcShvCIwKAvyYpnmVe9Si', 'Administrator', 'Active', '2026-09-15 19:06:40', '2026-09-12 14:36:04', '2026-09-15 18:06:40'),
(7, 'Ade', 'Bello', 'Ade.Bello', 'ade.bello@example.com', '08000000003', '$2y$12$G8PFkpH2Da5cucSG3dESw.DAH6s/IgpamdDobyxLjg5JJUmHNDDOa', 'Administrator', 'Active', '2026-09-16 14:57:55', '2026-09-16 13:42:05', '2026-09-16 13:57:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_number` (`expense_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_number` (`sale_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_sale_number` (`sale_number`);

--
-- Indexes for table `sale_sequences`
--
ALTER TABLE `sale_sequences`
  ADD PRIMARY KEY (`sale_date`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`),
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`),
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_number` FOREIGN KEY (`sale_number`) REFERENCES `sales` (`sale_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
