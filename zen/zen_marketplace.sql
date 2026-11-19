-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2026 at 07:14 PM
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
-- Database: `zen_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(255) NOT NULL,
  `userid` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` int(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `productimage` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `userid`, `title`, `description`, `price`, `category`, `productimage`) VALUES
(1, 1, 'ASUS ROG 5090', 'Brand New,\r\n1 Year Warranty,\r\nTop Tier Performance', 1999, 'gpu', 'uploads/zen_6aff376ce7b829.13581744.png'),
(2, 1, 'Corsair VENGEANCE A7500 Gaming PC', 'GEFORCE RTX 5090,\r\nAMD Ryzen 7 9800X3D,\r\nCORSAIR 64GB VENGEANCE RGB DDR5,\r\nCORSAIR 4TB NVMe SSD,\r\nCORSAIR 3500X CASE,\r\nCORSAIR NAUTILUS 240 LIQUID CPU COOLER,\r\nCORSAIR RS120 ARGB FANS,\r\nX870 MOTHERBOARD,\r\nCORSAIR 1200W POWER SUPPLY', 9600, 'pc', 'uploads/zen_6aff392778d9d5.02363478.png'),
(3, 1, 'Monitor ZOWIE XL2586X+', 'Size 24.5\",\r\nRefresh Rate 600Hz,\r\nDyAc 3 Yes', 999, 'monitor', 'uploads/zen_6aff3a221b37e9.01484807.png'),
(4, 1, 'Keyboard Wooting 60HE+', 'Switches	Lekker L60s switches,\r\nKeycaps	PBT, ABS and Dye-sub options available,\r\nCase	ABS,\r\nStabilisers	Plate-mount stabilisers (screw-in supported),\r\nSwitch Plate	Steel,\r\nPCBA	GH60 (60%),\r\nCable	2.0 meter USB-C to A nylon braided cable,\r\nDimensions	30.2 ', 140, 'keyboard', 'uploads/zen_6aff3b4f4f86a5.09824357.png'),
(5, 1, 'Mouse Razer Viper V4 Pro', 'Size\r\nLength: 127.1 mm / 5.00 in,\r\nWidth: 63.9 mm / 2.51 in,\r\nHeight: 39.9 mm / 1.57 in,\r\nWeight\r\nWhite: <50 g (excluding cable and dongle),\r\nAccessory Compatibility\r\nNone,\r\nPolling Rate / Interval\r\nUp to 8000 Hz (0.125 ms) in both wireless and wired mode', 170, 'mouse', 'uploads/zen_6aff3c54d21c22.84798515.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'ZEN', 'ZEN@gmail.com', '$2y$10$tcNpy6ysNvxXl.U3KB1q/eS/0RpisRrktKrkj.AGtZbNmSqL1GML6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
