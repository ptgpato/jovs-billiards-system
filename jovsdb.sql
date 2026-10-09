-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 01:39 AM
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
-- Database: `jovsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `billiard_table`
--

CREATE TABLE `billiard_table` (
  `biltab_ID` int(11) NOT NULL,
  `billiard_table_number` int(11) NOT NULL,
  `billiard_status` varchar(50) NOT NULL,
  `billiard_rate_per_hour` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier`
--

CREATE TABLE `cashier` (
  `cashierID` int(11) NOT NULL,
  `cashier_name` varchar(100) NOT NULL,
  `cashier_number` varchar(20) DEFAULT NULL,
  `cashier_address` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cashier` (`cashierID`, `cashier_name`, `username`, `password`, `role`) VALUES
(1, 'Default Cashier', 'cashier', '$2y$10$pElLnjnsSANNriVQrHEbmuM/fpPidcQAmO7HYaarOGZIoKc30ttVe', 'cashier');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customerID` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_number` varchar(20) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventoryID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `ownerID` int(11) NOT NULL,
  `inventory_change_type` varchar(50) NOT NULL,
  `inventory_quantity` int(11) NOT NULL,
  `inventory_change_date` date NOT NULL,
  `inventory_status_level` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventoryID`, `productID`, `ownerID`, `inventory_change_type`, `inventory_quantity`, `inventory_change_date`, `inventory_status_level`) VALUES
(1, 0, 1, 'Added', 2, '2025-04-28', 'Normal'),
(2, 0, 1, 'Added', 12, '2025-04-28', 'Normal');

--
-- Triggers `inventory`
--
DELIMITER $$
CREATE TRIGGER `update_inventory_date` BEFORE UPDATE ON `inventory` FOR EACH ROW BEGIN
    SET NEW.inventory_change_date = CURRENT_DATE;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `ownerID` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `owner_number` varchar(20) DEFAULT NULL,
  `owner_address` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner`
--

INSERT INTO `owner` (`ownerID`, `owner_name`, `owner_number`, `owner_address`, `username`, `password`, `role`) VALUES
(1, '', NULL, NULL, 'admin', 'admin1', 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productID` int(11) NOT NULL,
  `cashierID` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_date` date DEFAULT NULL,
  `product_status` varchar(50) DEFAULT NULL,
  `product_category` varchar(50) DEFAULT NULL
  ,`product_image_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`productID`, `cashierID`, `product_name`, `product_quantity`, `product_price`, `product_date`, `product_status`, `product_category`) VALUES
(0, NULL, 'hakdog', 14, 2.00, '2025-04-28', 'Available', 'Drinks');

-- --------------------------------------------------------

--
-- Table structure for table `rental`
--

CREATE TABLE `rental` (
  `rental_ID` int(11) NOT NULL,
  `biltab_ID` int(11) NOT NULL,
  `sales_trans_ID` int(11) NOT NULL,
  `rental_start_time` datetime NOT NULL,
  `rental_end_time` datetime DEFAULT NULL,
  `rental_duration_hours` decimal(5,2) DEFAULT NULL,
  `rental_total_cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_report`
--

CREATE TABLE `sales_report` (
  `sales_rep_ID` int(11) NOT NULL,
  `ownerID` int(11) NOT NULL,
  `sales_trans_ID` int(11) NOT NULL,
  `salesrep_total_amount` decimal(10,2) NOT NULL,
  `salesrep_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_transaction`
--

CREATE TABLE `sales_transaction` (
  `sales_trans_ID` int(11) NOT NULL,
  `cashier_ID` int(11) NOT NULL,
  `customerID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `salestrans_payment_method` varchar(50) NOT NULL,
  `salestrans_status` varchar(50) NOT NULL,
  `salestrans_subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$GJ5jzbf.5t/uKPz4YfS1s./ZGnuAEBTKbErN/nIif2zBcwF3KDMxe', 'owner'),
(2, 'cashier', '$2y$10$pElLnjnsSANNriVQrHEbmuM/fpPidcQAmO7HYaarOGZIoKc30ttVe', 'cashier');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billiard_table`
--
ALTER TABLE `billiard_table`
  ADD PRIMARY KEY (`biltab_ID`);

--
-- Indexes for table `cashier`
--
ALTER TABLE `cashier`
  ADD PRIMARY KEY (`cashierID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customerID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventoryID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `ownerID` (`ownerID`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`ownerID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `cashierID` (`cashierID`);

--
-- Indexes for table `rental`
--
ALTER TABLE `rental`
  ADD PRIMARY KEY (`rental_ID`),
  ADD KEY `biltab_ID` (`biltab_ID`),
  ADD KEY `sales_trans_ID` (`sales_trans_ID`);

--
-- Indexes for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD PRIMARY KEY (`sales_rep_ID`),
  ADD KEY `ownerID` (`ownerID`),
  ADD KEY `sales_trans_ID` (`sales_trans_ID`);

--
-- Indexes for table `sales_transaction`
--
ALTER TABLE `sales_transaction`
  ADD PRIMARY KEY (`sales_trans_ID`),
  ADD KEY `cashier_ID` (`cashier_ID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `cashier`
  MODIFY `cashierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `customer`
  MODIFY `customerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
  MODIFY `ownerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `product`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `sales_transaction`
  MODIFY `sales_trans_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`ownerID`) REFERENCES `owner` (`ownerID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`cashierID`) REFERENCES `cashier` (`cashierID`);

--
-- Constraints for table `rental`
--
ALTER TABLE `rental`
  ADD CONSTRAINT `rental_ibfk_1` FOREIGN KEY (`biltab_ID`) REFERENCES `billiard_table` (`biltab_ID`),
  ADD CONSTRAINT `rental_ibfk_2` FOREIGN KEY (`sales_trans_ID`) REFERENCES `sales_transaction` (`sales_trans_ID`);

--
-- Constraints for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD CONSTRAINT `sales_report_ibfk_1` FOREIGN KEY (`ownerID`) REFERENCES `owner` (`ownerID`),
  ADD CONSTRAINT `sales_report_ibfk_2` FOREIGN KEY (`sales_trans_ID`) REFERENCES `sales_transaction` (`sales_trans_ID`);

--
-- Constraints for table `sales_transaction`
--
ALTER TABLE `sales_transaction`
  ADD CONSTRAINT `sales_transaction_ibfk_1` FOREIGN KEY (`cashier_ID`) REFERENCES `cashier` (`cashierID`),
  ADD CONSTRAINT `sales_transaction_ibfk_2` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `sales_transaction_ibfk_3` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
